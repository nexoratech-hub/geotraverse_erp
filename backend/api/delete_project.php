<?php
// backend/api/delete_project.php
// ============================================================
// FIXED: Inafuta copy kwa receiver, original kwa sender
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
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}

// ============================================================
// GET INPUT
// ============================================================
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['project_id']) || !isset($input['department_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
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

    // ============================================================
    // CHECK 1: ORIGINAL PROJECT (Sender's own project)
    // ============================================================
    $stmt = $pdo->prepare("SELECT id, name, department_id, is_deleted FROM projects WHERE id = ?");
    $stmt->execute([$projectId]);
    $project = $stmt->fetch();

    if ($project && $project['is_deleted'] == 0) {
        // Only Sender can delete original project
        if ($project['department_id'] == $departmentId) {
            // SOFT DELETE original
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
            // Receiver cannot delete original
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
    // CHECK 2: SENT PROJECT (Receiver's copy)
    // ============================================================
    // Try by ID first
    $stmt = $pdo->prepare("SELECT id, original_project_id, project_name, from_department_id, to_department_id, is_deleted 
                           FROM sent_projects WHERE id = ?");
    $stmt->execute([$projectId]);
    $sentProject = $stmt->fetch();

    // If not found, try by original_project_id + to_department_id
    if (!$sentProject) {
        $stmt = $pdo->prepare("SELECT id, original_project_id, project_name, from_department_id, to_department_id, is_deleted 
                               FROM sent_projects 
                               WHERE original_project_id = ? AND to_department_id = ? AND is_deleted = 0");
        $stmt->execute([$projectId, $departmentId]);
        $sentProject = $stmt->fetch();
    }

    if ($sentProject && $sentProject['is_deleted'] == 0) {
        $sentId = $sentProject['id'];
        $sentName = $sentProject['project_name'] ?? 'Sent Project';
        
        // Only Receiver can delete their copy
        if ($sentProject['to_department_id'] == $departmentId) {
            // SOFT DELETE sent copy
            $query = "UPDATE sent_projects SET is_deleted = 1, deleted_at = NOW() WHERE id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$sentId]);
            $deleted = true;
            $deletedId = $sentId;
            $deletedName = $sentName;
            $deletedType = 'sent_copy';
            
            // Add to recycle bin
            $recycleQuery = "INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin, deleted_by_name, created_at) 
                            VALUES (?, 'sent_project', ?, ?, 0, ?, NOW())";
            $stmt = $pdo->prepare($recycleQuery);
            $stmt->execute([$sentId, $sentName, $departmentId, $deletedBy]);

            // Soft delete sent daily work
            $query = "UPDATE sent_dailywork SET is_deleted = 1, deleted_at = NOW() 
                      WHERE to_department_id = ? AND dailywork_project_name = ? 
                      AND (is_deleted = 0 OR is_deleted IS NULL)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$departmentId, $sentName]);

            echo json_encode([
                'success' => true,
                'message' => 'Project copy moved to recycle bin',
                'data' => [
                    'deleted_id' => $sentId,
                    'deleted_name' => $sentName,
                    'type' => 'sent_copy'
                ]
            ]);
            exit;
        } else if ($sentProject['from_department_id'] == $departmentId) {
            // Sender can delete sent history record (soft delete)
            $query = "UPDATE sent_projects SET is_deleted = 1, deleted_at = NOW() WHERE id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$sentId]);
            
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
    // NOT FOUND OR ALREADY DELETED
    // ============================================================
    if (!$deleted) {
        // Check if already deleted
        if ($project && $project['is_deleted'] == 1) {
            echo json_encode([
                'success' => false,
                'message' => 'Project is already deleted',
                'debug' => ['project_id' => $projectId]
            ]);
            exit;
        }
        
        if ($sentProject && $sentProject['is_deleted'] == 1) {
            echo json_encode([
                'success' => false,
                'message' => 'Project copy is already deleted',
                'debug' => ['project_id' => $projectId]
            ]);
            exit;
        }
        
        echo json_encode([
            'success' => false,
            'message' => 'Project not found or already deleted for this department',
            'debug' => [
                'project_id' => $projectId,
                'department_id' => $departmentId
            ]
        ]);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>