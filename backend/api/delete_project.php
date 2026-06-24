<?php
// backend/api/delete_project.php

// ============================================================
// ERROR REPORTING
// ============================================================
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// ============================================================
// HEADERS
// ============================================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ============================================================
// DATABASE CONNECTION
// ============================================================
try {
    $pdo = new PDO("mysql:host=localhost;dbname=geotraverse_erp;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    exit;
}

// ============================================================
// GET INPUT DATA
// ============================================================
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['project_id']) || !isset($input['department_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields: project_id, department_id'
    ]);
    exit;
}

$project_id = intval($input['project_id']);
$department_id = intval($input['department_id']);
$deleted_by = isset($input['deleted_by']) ? $input['deleted_by'] : 'System';

try {
    $deleted_count = 0;
    $deleted_ids = [];
    $deleted_items = [];

    // ============================================================
    // 1. ANGAZIA ORIGINAL PROJECT
    // ============================================================
    $query = "SELECT id, name, department_id FROM projects WHERE id = ? AND (is_deleted = 0 OR is_deleted IS NULL)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$project_id]);
    $project = $stmt->fetch();

    // ============================================================
    // 2. ANGAZIA SENT PROJECTS
    // ============================================================
    $query = "SELECT id, project_name, from_department_id, to_department_id 
              FROM sent_projects 
              WHERE original_project_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$project_id]);
    $sentProjects = $stmt->fetchAll();

    // ============================================================
    // 3. IKIWA DEPARTMENT NI SENDER - SOFT DELETE ORIGINAL PROJECT TU
    // ============================================================
    if ($project && $project['department_id'] == $department_id) {
        // SOFT DELETE ORIGINAL PROJECT - SENDER TU
        $query = "UPDATE projects SET 
                  is_deleted = 1,
                  deleted_by_department = 1,
                  deleted_by_admin = 0,
                  deleted_at = NOW(),
                  deleted_by_department_id = ?
                  WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$department_id, $project_id]);
        $deleted_count++;
        $deleted_ids[] = $project_id;
        $deleted_items[] = [
            'table' => 'projects',
            'id' => $project_id,
            'name' => $project['name'],
            'type' => 'original',
            'deleted_by_dept' => $department_id
        ];

        // Add to recycle bin - KWA SENDER TU
        $recycleQuery = "INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin, deleted_by_name, created_at) 
                        VALUES (?, 'project', ?, ?, 0, ?, NOW())";
        $stmt = $pdo->prepare($recycleQuery);
        $stmt->execute([$project_id, $project['name'], $department_id, $deleted_by]);

        // Delete daily work - SENDER TU
        $query = "UPDATE dailywork SET 
                  is_deleted = 1,
                  deleted_at = NOW()
                  WHERE project_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$project_id]);
        $dailywork_deleted = $stmt->rowCount();

        // ============================================================
        // HATUFUTI SENT PROJECTS - RECEIVERS BADO WANAONA
        // ============================================================
        // RECEIVERS WANAONA SENT PROJECTS ZAO
        // HATUFUTI KWA RECEIVERS
    }

    // ============================================================
    // 4. IKIWA DEPARTMENT NI RECEIVER - SOFT DELETE SENT PROJECT YAKE TU
    // ============================================================
    if ($project && $project['department_id'] != $department_id) {
        foreach ($sentProjects as $sent) {
            if ($sent['to_department_id'] == $department_id) {
                // SOFT DELETE SENT PROJECT - RECEIVER TU
                $query = "UPDATE sent_projects SET 
                          is_deleted = 1,
                          deleted_at = NOW()
                          WHERE id = ?";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$sent['id']]);
                $deleted_count++;
                $deleted_ids[] = $sent['id'];
                $deleted_items[] = [
                    'table' => 'sent_projects',
                    'id' => $sent['id'],
                    'name' => $sent['project_name'],
                    'type' => 'sent',
                    'deleted_by_dept' => $department_id
                ];

                // Add to recycle bin - KWA RECEIVER TU
                $recycleQuery = "INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin, deleted_by_name, created_at) 
                                VALUES (?, 'sent_project', ?, ?, 0, ?, NOW())";
                $stmt = $pdo->prepare($recycleQuery);
                $stmt->execute([$sent['id'], $sent['project_name'], $department_id, $deleted_by]);

                // Delete sent daily work - RECEIVER TU
                if ($sent['project_name']) {
                    $query = "UPDATE sent_dailywork SET 
                              is_deleted = 1,
                              deleted_at = NOW()
                              WHERE dailywork_project_name = ? 
                                AND to_department_id = ? 
                                AND (is_deleted = 0 OR is_deleted IS NULL)";
                    $stmt = $pdo->prepare($query);
                    $stmt->execute([$sent['project_name'], $department_id]);
                }
            }
        }
    }

    // ============================================================
    // 5. IKIWA PROJECT HAIPO ORIGINAL, ANGAZIA SENT PROJECT KWA ID
    // ============================================================
    if ($deleted_count == 0) {
        $query = "SELECT id, project_name, from_department_id, to_department_id 
                  FROM sent_projects 
                  WHERE id = ? AND (is_deleted = 0 OR is_deleted IS NULL)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$project_id]);
        $sent = $stmt->fetch();

        if ($sent) {
            // Ikiwa department ni receiver wa sent project hii
            if ($sent['to_department_id'] == $department_id) {
                $query = "UPDATE sent_projects SET 
                          is_deleted = 1,
                          deleted_at = NOW()
                          WHERE id = ?";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$project_id]);
                $deleted_count++;
                $deleted_ids[] = $project_id;
                $deleted_items[] = [
                    'table' => 'sent_projects',
                    'id' => $project_id,
                    'name' => $sent['project_name'],
                    'type' => 'sent',
                    'deleted_by_dept' => $department_id
                ];

                // Add to recycle bin - KWA RECEIVER TU
                $recycleQuery = "INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin, deleted_by_name, created_at) 
                                VALUES (?, 'sent_project', ?, ?, 0, ?, NOW())";
                $stmt = $pdo->prepare($recycleQuery);
                $stmt->execute([$project_id, $sent['project_name'], $department_id, $deleted_by]);

                if ($sent['project_name']) {
                    $query = "UPDATE sent_dailywork SET 
                              is_deleted = 1,
                              deleted_at = NOW()
                              WHERE dailywork_project_name = ? 
                                AND to_department_id = ? 
                                AND (is_deleted = 0 OR is_deleted IS NULL)";
                    $stmt = $pdo->prepare($query);
                    $stmt->execute([$sent['project_name'], $department_id]);
                }
            }
        }
    }

    // ============================================================
    // SEND RESPONSE
    // ============================================================
    if ($deleted_count > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Project soft deleted successfully for department ' . $department_id,
            'data' => [
                'deleted_count' => $deleted_count,
                'deleted_ids' => $deleted_ids,
                'deleted_items' => $deleted_items,
                'dailywork_deleted' => $dailywork_deleted ?? 0,
                'deleted_by_department' => $department_id,
                'note' => 'Only deleted for department ' . $department_id . ' - other departments still see it'
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Project not found or already deleted for this department'
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