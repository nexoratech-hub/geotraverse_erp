<?php
// backend/api/delete_project.php

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=geotraverse_erp;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['project_id']) || !isset($input['department_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields: project_id, department_id']);
    exit;
}

$projectId = intval($input['project_id']);
$departmentId = intval($input['department_id']);
$deletedBy = isset($input['deleted_by']) ? $input['deleted_by'] : 'System';

try {
    $deletedCount = 0;
    $deletedItems = [];

    // ============================================================
    // 1. CHECK IF THIS IS AN ORIGINAL PROJECT
    // ============================================================
    $stmt = $pdo->prepare("SELECT id, name, department_id, is_deleted FROM projects WHERE id = ?");
    $stmt->execute([$projectId]);
    $project = $stmt->fetch();

    // ============================================================
    // 2. CHECK IF THIS IS A SENT PROJECT (RECEIVER VIEW)
    // ============================================================
    $sentStmt = $pdo->prepare("SELECT id, original_project_id, project_name, from_department_id, to_department_id, is_deleted 
                               FROM sent_projects WHERE id = ?");
    $sentStmt->execute([$projectId]);
    $sentProject = $sentStmt->fetch();

    // ============================================================
    // 3. CASE 1: ORIGINAL PROJECT - SENDER ANAFUTA
    // ============================================================
    if ($project && $project['department_id'] == $departmentId) {
        // Sender anafuta original project yake
        // Receiver atabaki na copy yake
        
        // Soft delete original project
        $query = "UPDATE projects SET 
                  is_deleted = 1,
                  deleted_by_department = 1,
                  deleted_by_admin = 0,
                  deleted_at = NOW(),
                  deleted_by_department_id = ?
                  WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$departmentId, $projectId]);
        $deletedCount++;
        $deletedItems[] = [
            'table' => 'projects',
            'id' => $projectId,
            'name' => $project['name'],
            'type' => 'original'
        ];

        // Add to recycle bin
        $recycleQuery = "INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin, deleted_by_name, created_at) 
                        VALUES (?, 'project', ?, ?, 0, ?, NOW())";
        $stmt = $pdo->prepare($recycleQuery);
        $stmt->execute([$projectId, $project['name'], $departmentId, $deletedBy]);

        // Soft delete original daily work (sender loses it)
        $query = "UPDATE dailywork SET 
                  is_deleted = 1,
                  deleted_at = NOW()
                  WHERE project_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$projectId]);
        $dailyworkDeleted = $stmt->rowCount();

        if ($dailyworkDeleted > 0) {
            $deletedItems[] = [
                'table' => 'dailywork',
                'count' => $dailyworkDeleted,
                'type' => 'original'
            ];
        }

        // ============================================================
        // RECEIVER ANABAKI NA COPY YAKE - HATUFUTI SENT_PROJECTS
        // ============================================================
        // Hapa hatufuti sent_projects kwa receiver

        $message = "Original project deleted by sender. Receiver still has their copy.";
    }

    // ============================================================
    // 4. CASE 2: SENT PROJECT - RECEIVER ANAFUTA COPY YAKE
    // ============================================================
    else if ($sentProject && $sentProject['to_department_id'] == $departmentId) {
        // Receiver anafuta sent project copy yake
        
        // Soft delete sent project
        $query = "UPDATE sent_projects SET 
                  is_deleted = 1,
                  deleted_at = NOW()
                  WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$projectId]);
        $deletedCount++;
        $deletedItems[] = [
            'table' => 'sent_projects',
            'id' => $projectId,
            'name' => $sentProject['project_name'],
            'type' => 'sent_copy'
        ];

        // Add to recycle bin
        $recycleQuery = "INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin, deleted_by_name, created_at) 
                        VALUES (?, 'sent_project', ?, ?, 0, ?, NOW())";
        $stmt = $pdo->prepare($recycleQuery);
        $stmt->execute([$projectId, $sentProject['project_name'], $departmentId, $deletedBy]);

        // Soft delete sent daily work for this receiver
        $query = "UPDATE sent_dailywork SET 
                  is_deleted = 1,
                  deleted_at = NOW()
                  WHERE original_dailywork_id IN (
                      SELECT id FROM dailywork WHERE project_id = ?
                  )
                  AND to_department_id = ?
                  AND (is_deleted = 0 OR is_deleted IS NULL)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$sentProject['original_project_id'], $departmentId]);
        $dailyworkDeleted = $stmt->rowCount();

        if ($dailyworkDeleted > 0) {
            $deletedItems[] = [
                'table' => 'sent_dailywork',
                'count' => $dailyworkDeleted,
                'type' => 'sent_copy'
            ];
        }

        $message = "Sent project copy deleted by receiver. Sender still has original.";
    }

    // ============================================================
    // 5. CASE 3: SENT PROJECT - SENDER ANAFUTA RECORD YA KUTUMA
    // ============================================================
    else if ($sentProject && $sentProject['from_department_id'] == $departmentId) {
        // Sender anafuta record ya sent project (zile alizotuma)
        // Hii haifuti copy ya receiver
        
        $query = "UPDATE sent_projects SET 
                  is_deleted = 1,
                  deleted_at = NOW()
                  WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$projectId]);
        $deletedCount++;
        $deletedItems[] = [
            'table' => 'sent_projects',
            'id' => $projectId,
            'name' => $sentProject['project_name'],
            'type' => 'sent_from'
        ];

        // Add to recycle bin
        $recycleQuery = "INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin, deleted_by_name, created_at) 
                        VALUES (?, 'sent_project', ?, ?, 0, ?, NOW())";
        $stmt = $pdo->prepare($recycleQuery);
        $stmt->execute([$projectId, $sentProject['project_name'], $departmentId, $deletedBy]);

        // Soft delete sent daily work for this sender
        $query = "UPDATE sent_dailywork SET 
                  is_deleted = 1,
                  deleted_at = NOW()
                  WHERE original_dailywork_id IN (
                      SELECT id FROM dailywork WHERE project_id = ?
                  )
                  AND from_department_id = ?
                  AND (is_deleted = 0 OR is_deleted IS NULL)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$sentProject['original_project_id'], $departmentId]);

        $message = "Sent project record deleted by sender. Receiver still has their copy.";
    }

    // ============================================================
    // 6. CASE 4: NO PROJECT FOUND
    // ============================================================
    else {
        $message = "Project not found or already deleted for this department";
    }

    // ============================================================
    // 7. SEND RESPONSE
    // ============================================================
    if ($deletedCount > 0) {
        echo json_encode([
            'success' => true,
            'message' => $message ?? 'Project deleted successfully',
            'data' => [
                'deleted_count' => $deletedCount,
                'deleted_items' => $deletedItems,
                'deleted_by_department' => $departmentId,
                'note' => 'Soft delete only - data can be restored from recycle bin'
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $message ?? 'Project not found or already deleted for this department'
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>