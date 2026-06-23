<?php
// delete_project.php - Delete Projects and Sent Projects

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB Connection Error: ' . $e->getMessage()]);
    exit;
}

// Get input data
$data = json_decode(file_get_contents('php://input'), true);

// ============================================================
// DEBUG: Log what we received
// ============================================================
error_log("DELETE PROJECT - Input data: " . print_r($data, true));

if (!$data || empty($data['project_id'])) {
    echo json_encode(['success' => false, 'message' => 'Project ID required']);
    exit;
}

$projectId = (int)$data['project_id'];
$departmentId = isset($data['department_id']) ? (int)$data['department_id'] : 0;
$deletedByDepartment = isset($data['deleted_by_department']) ? (int)$data['deleted_by_department'] : $departmentId;
$deletedByAdmin = isset($data['deleted_by_admin']) ? (int)$data['deleted_by_admin'] : 0;

error_log("DELETE PROJECT - Project ID: $projectId, Department ID: $departmentId");

try {
    $pdo->beginTransaction();
    
    $deleted = false;
    $projectName = '';
    $projectSource = '';
    $affectedRows = 0;
    
    // ============================================================
    // 1. CHECK AND DELETE FROM projects TABLE
    // ============================================================
    $checkStmt = $pdo->prepare("
        SELECT id, name, department_id, sent_from_dept, sent_to_dept, is_deleted 
        FROM projects 
        WHERE id = ?
    ");
    $checkStmt->execute([$projectId]);
    $project = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    error_log("DELETE PROJECT - Project found: " . print_r($project, true));
    
    if ($project) {
        // If already deleted, return success
        if ($project['is_deleted'] == 1) {
            echo json_encode(['success' => true, 'message' => 'Project already deleted']);
            $pdo->commit();
            exit;
        }
        
        $projectName = $project['name'];
        $projectSource = 'projects';
        
        // Check permission
        $isOwner = ($project['department_id'] == $departmentId);
        $isRecipient = ($project['sent_to_dept'] == $departmentId);
        
        if (!$isOwner && !$isRecipient && $deletedByAdmin == 0) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            $pdo->commit();
            exit;
        }
        
        // ============================================================
        // !!! SOFT DELETE - UPDATE is_deleted TO 1 !!!
        // ============================================================
        $stmt = $pdo->prepare("
            UPDATE projects 
            SET 
                is_deleted = 1,
                deleted_by_department = :dept_deleted,
                deleted_by_admin = :admin_deleted,
                deleted_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            'dept_deleted' => $deletedByDepartment,
            'admin_deleted' => $deletedByAdmin,
            'id' => $projectId
        ]);
        
        $affectedRows = $stmt->rowCount();
        error_log("DELETE PROJECT - Projects table affected rows: $affectedRows");
        
        $deleted = true;
        
        // Add to recycle bin
        addToRecycleBin($pdo, $projectId, 'project', $projectName, $deletedByDepartment, $deletedByAdmin);
    }
    
    // ============================================================
    // 2. CHECK AND DELETE FROM sent_projects TABLE
    // ============================================================
    if (!$deleted) {
        $checkSentStmt = $pdo->prepare("
            SELECT 
                id, 
                project_name, 
                from_department_id, 
                to_department_id,
                is_deleted
            FROM sent_projects 
            WHERE id = ?
        ");
        $checkSentStmt->execute([$projectId]);
        $sentProject = $checkSentStmt->fetch(PDO::FETCH_ASSOC);
        
        error_log("DELETE PROJECT - Sent project found: " . print_r($sentProject, true));
        
        if ($sentProject) {
            // If already deleted, return success
            if ($sentProject['is_deleted'] == 1) {
                echo json_encode(['success' => true, 'message' => 'Sent project already deleted']);
                $pdo->commit();
                exit;
            }
            
            $projectName = $sentProject['project_name'] ?? 'Sent Project';
            $projectSource = 'sent_projects';
            
            // Check permission
            $isRecipient = ($sentProject['to_department_id'] == $departmentId);
            $isSender = ($sentProject['from_department_id'] == $departmentId);
            
            if (!$isRecipient && !$isSender && $deletedByAdmin == 0) {
                echo json_encode(['success' => false, 'message' => 'Permission denied']);
                $pdo->commit();
                exit;
            }
            
            // ============================================================
            // !!! SOFT DELETE - UPDATE is_deleted TO 1 !!!
            // ============================================================
            $stmt = $pdo->prepare("
                UPDATE sent_projects 
                SET 
                    is_deleted = 1,
                    deleted_at = NOW()
                WHERE id = :id
            ");
            $stmt->execute(['id' => $projectId]);
            
            $affectedRows = $stmt->rowCount();
            error_log("DELETE PROJECT - Sent projects table affected rows: $affectedRows");
            
            $deleted = true;
            
            // Add to recycle bin
            addToRecycleBin($pdo, $projectId, 'sent_project', $projectName, $deletedByDepartment, $deletedByAdmin);
        }
    }
    
    // ============================================================
    // 3. ALSO DELETE DAILY WORK RECORDS ASSOCIATED WITH PROJECT
    // ============================================================
    if ($deleted) {
        // Delete daily work from dailywork table
        $dailyWorkStmt = $pdo->prepare("
            UPDATE dailywork 
            SET is_deleted = 1, deleted_at = NOW()
            WHERE project_id = ?
            AND (is_deleted = 0 OR is_deleted IS NULL)
        ");
        $dailyWorkStmt->execute([$projectId]);
        
        // Also delete sent daily work
        $sentDailyWorkStmt = $pdo->prepare("
            UPDATE sent_dailywork 
            SET is_deleted = 1, deleted_at = NOW()
            WHERE project_id = ?
            AND (is_deleted = 0 OR is_deleted IS NULL)
        ");
        $sentDailyWorkStmt->execute([$projectId]);
    }
    
    $pdo->commit();
    
    if ($deleted) {
        echo json_encode([
            'success' => true,
            'message' => 'Project deleted successfully',
            'project_name' => $projectName,
            'project_id' => $projectId,
            'source' => $projectSource,
            'affected_rows' => $affectedRows
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Project not found']);
    }
    
} catch(PDOException $e) {
    $pdo->rollBack();
    error_log('DELETE PROJECT Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage(),
        'error_code' => $e->getCode()
    ]);
}

// ============================================================
// HELPER FUNCTION: Add to recycle bin
// ============================================================
function addToRecycleBin($pdo, $itemId, $itemType, $itemName, $deletedByDepartment, $deletedByAdmin) {
    try {
        $checkBinStmt = $pdo->prepare("
            SELECT id FROM recycle_bin 
            WHERE item_id = ? AND item_type = ?
        ");
        $checkBinStmt->execute([$itemId, $itemType]);
        
        if (!$checkBinStmt->fetch()) {
            $stmt2 = $pdo->prepare("
                INSERT INTO recycle_bin 
                (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt2->execute([
                $itemId,
                $itemType,
                $itemName,
                $deletedByDepartment > 0 ? $deletedByDepartment : null,
                $deletedByAdmin
            ]);
            error_log("DELETE PROJECT - Added to recycle bin: $itemId, $itemType, $itemName");
        }
        return true;
    } catch(PDOException $e) {
        error_log('Recycle bin error: ' . $e->getMessage());
        return false;
    }
}
?>