<?php
// backend/api/delete_project.php
// ============================================================
// UNIVERSAL - Inafanya kazi kwa departments zote
// ============================================================

error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ============================================================
// DATABASE CONNECTION
// ============================================================
$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}

// ============================================================
// GET INPUT
// ============================================================
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['project_id']) || !isset($input['department_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields: project_id, department_id']);
    exit;
}

$projectId = intval($input['project_id']);
$departmentId = intval($input['department_id']);
$deletedBy = isset($input['deleted_by']) ? $input['deleted_by'] : 'System';

try {
    $deleted = false;
    $deletedId = 0;
    $deletedName = '';
    $deletedType = '';
    $deletedItems = [];

    // ============================================================
    // STEP 1: CHECK ORIGINAL PROJECT
    // ============================================================
    $stmt = $pdo->prepare("SELECT id, name, department_id, is_deleted FROM projects WHERE id = ?");
    $stmt->execute([$projectId]);
    $project = $stmt->fetch();

    if ($project && $project['is_deleted'] == 0) {
        // SENDER deleting original
        if ($project['department_id'] == $departmentId) {
            // Soft delete original - inaenda recycle bin
            $query = "UPDATE projects SET 
                      is_deleted = 1,
                      deleted_by_department = 1,
                      deleted_by_admin = 0,
                      deleted_at = NOW(),
                      deleted_by_department_id = ?
                      WHERE id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$departmentId, $projectId]);
            $deleted = true;
            $deletedId = $projectId;
            $deletedName = $project['name'];
            $deletedType = 'original';
            
            // Add to recycle bin
            $recycleQuery = "INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin, deleted_by_name, created_at) 
                            VALUES (?, 'project', ?, ?, 0, ?, NOW())";
            $stmt = $pdo->prepare($recycleQuery);
            $stmt->execute([$projectId, $project['name'], $departmentId, $deletedBy]);

            // Soft delete daily work
            $query = "UPDATE dailywork SET is_deleted = 1, deleted_at = NOW() 
                      WHERE project_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$projectId]);

            echo json_encode([
                'success' => true,
                'message' => 'Original project moved to recycle bin',
                'data' => [
                    'deleted_id' => $projectId,
                    'deleted_name' => $project['name'],
                    'type' => 'original'
                ]
            ]);
            exit;
        } else {
            // Receiver trying to delete original - NOT ALLOWED
            echo json_encode([
                'success' => false,
                'message' => 'You cannot delete the original project. Only the sender can delete it.',
                'debug' => [
                    'project_id' => $projectId,
                    'department_id' => $departmentId,
                    'project_owner' => $project['department_id']
                ]
            ]);
            exit;
        }
    }

    // ============================================================
    // STEP 2: CHECK SENT PROJECT (RECEIVER'S COPY)
    // ============================================================
    // Try by ID
    $stmt = $pdo->prepare("SELECT id, original_project_id, project_name, from_department_id, to_department_id, is_deleted 
                           FROM sent_projects WHERE id = ?");
    $stmt->execute([$projectId]);
    $sentProject = $stmt->fetch();

    // Try by original_project_id + to_department_id
    if (!$sentProject) {
        $stmt = $pdo->prepare("SELECT id, original_project_id, project_name, from_department_id, to_department_id, is_deleted 
                               FROM sent_projects 
                               WHERE original_project_id = ? AND to_department_id = ? AND is_deleted = 0");
        $stmt->execute([$projectId, $departmentId]);
        $sentProject = $stmt->fetch();
    }

    // Try by original_project_id only
    if (!$sentProject) {
        $stmt = $pdo->prepare("SELECT id, original_project_id, project_name, from_department_id, to_department_id, is_deleted 
                               FROM sent_projects 
                               WHERE original_project_id = ? AND is_deleted = 0");
        $stmt->execute([$projectId]);
        $allSentProjects = $stmt->fetchAll();
        
        foreach ($allSentProjects as $sp) {
            if ($sp['to_department_id'] == $departmentId) {
                $sentProject = $sp;
                break;
            }
        }
    }

    if ($sentProject && $sentProject['is_deleted'] == 0) {
        $sentId = $sentProject['id'];
        $sentName = $sentProject['project_name'] ?? 'Sent Project';
        $originalProjectId = $sentProject['original_project_id'] ?? $projectId;
        
        // RECEIVER deleting their copy
        if ($sentProject['to_department_id'] == $departmentId) {
            // 1. Delete sent daily work
            $query = "UPDATE sent_dailywork SET 
                      is_deleted = 1, 
                      deleted_at = NOW() 
                      WHERE to_department_id = ? 
                        AND dailywork_project_name = ? 
                        AND (is_deleted = 0 OR is_deleted IS NULL)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$departmentId, $sentName]);
            $dailyworkDeleted = $stmt->rowCount();
            
            if ($dailyworkDeleted > 0) {
                $deletedItems[] = [
                    'table' => 'sent_dailywork',
                    'count' => $dailyworkDeleted,
                    'type' => 'sent_copy_deleted_by_receiver'
                ];
            }

            // 2. Delete sent project (HARD DELETE - permanent)
            $query = "DELETE FROM sent_projects WHERE id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$sentId]);
            $deleted = true;
            $deletedId = $sentId;
            $deletedName = $sentName;
            $deletedType = 'sent_copy';
            
            $deletedItems[] = [
                'table' => 'sent_projects',
                'id' => $sentId,
                'name' => $sentName,
                'type' => 'sent_copy_deleted_by_receiver'
            ];

            // 3. Add to recycle bin
            $recycleQuery = "INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin, deleted_by_name, created_at) 
                            VALUES (?, 'sent_project', ?, ?, 0, ?, NOW())";
            $stmt = $pdo->prepare($recycleQuery);
            $stmt->execute([$sentId, $sentName, $departmentId, $deletedBy]);

            echo json_encode([
                'success' => true,
                'message' => 'Project copy deleted from your dashboard',
                'data' => [
                    'deleted_id' => $sentId,
                    'deleted_name' => $sentName,
                    'original_project_id' => $originalProjectId,
                    'type' => 'sent_copy',
                    'deleted_items' => $deletedItems,
                    'note' => 'Only your copy was deleted. The original remains with the sender.'
                ]
            ]);
            exit;
            
        } else if ($sentProject['from_department_id'] == $departmentId) {
            // Sender deleting sent history
            $query = "DELETE FROM sent_projects WHERE id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$sentId]);
            $deleted = true;
            $deletedId = $sentId;
            $deletedName = $sentName;
            $deletedType = 'sent_history';
            
            echo json_encode([
                'success' => true,
                'message' => 'Sent history record removed',
                'data' => [
                    'deleted_id' => $sentId,
                    'deleted_name' => $sentName,
                    'type' => 'sent_history'
                ]
            ]);
            exit;
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'You are not authorized to delete this project copy.',
                'debug' => [
                    'your_department' => $departmentId,
                    'receiver_department' => $sentProject['to_department_id']
                ]
            ]);
            exit;
        }
    }

    // ============================================================
    // STEP 3: ALREADY DELETED
    // ============================================================
    if ($project && $project['is_deleted'] == 1) {
        echo json_encode(['success' => false, 'message' => 'Project is already deleted']);
        exit;
    }
    
    if ($sentProject && $sentProject['is_deleted'] == 1) {
        echo json_encode(['success' => false, 'message' => 'Project copy is already deleted']);
        exit;
    }

    // ============================================================
    // STEP 4: NOT FOUND
    // ============================================================
    echo json_encode([
        'success' => false,
        'message' => 'Project not found or already deleted for this department',
        'debug' => [
            'project_id' => $projectId,
            'department_id' => $departmentId
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>