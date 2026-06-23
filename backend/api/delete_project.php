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

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['project_id'])) {
    echo json_encode(['success' => false, 'message' => 'Project ID required']);
    exit;
}

$projectId = (int)$data['project_id'];
$departmentId = isset($data['department_id']) ? (int)$data['department_id'] : 0;
$isAdmin = isset($data['is_admin']) && $data['is_admin'] ? 1 : 0;
$deletedByDepartment = isset($data['deleted_by_department']) ? (int)$data['deleted_by_department'] : $departmentId;
$deletedByAdmin = isset($data['deleted_by_admin']) ? (int)$data['deleted_by_admin'] : $isAdmin;

try {
    // ============================================================
    // 1. CHECK IF PROJECT EXISTS IN projects TABLE
    // ============================================================
    $checkStmt = $pdo->prepare("
        SELECT id, name, department_id, sent_from_dept, sent_to_dept 
        FROM projects 
        WHERE id = ?
        AND (is_deleted = 0 OR is_deleted IS NULL)
        AND (deleted_by_department = 0 OR deleted_by_department IS NULL)
        AND (deleted_by_admin = 0 OR deleted_by_admin IS NULL)
    ");
    $checkStmt->execute([$projectId]);
    $project = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($project) {
        // Check permission
        $isOwner = ($project['department_id'] == $departmentId);
        $isRecipient = ($project['sent_to_dept'] == $departmentId);
        
        if (!$isOwner && !$isRecipient && $deletedByAdmin == 0) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit;
        }
        
        // Soft delete
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
        
        // Add to recycle bin
        addToRecycleBin($pdo, $projectId, 'project', $project['name'], $deletedByDepartment, $deletedByAdmin);
        
        echo json_encode([
            'success' => true,
            'message' => 'Project moved to recycle bin',
            'source' => 'projects_table'
        ]);
        exit;
    }
    
    // ============================================================
    // 2. CHECK IF PROJECT EXISTS IN sent_projects TABLE
    // ============================================================
    $checkSentStmt = $pdo->prepare("
        SELECT 
            id, 
            project_name, 
            from_department_id, 
            to_department_id
        FROM sent_projects 
        WHERE id = ?
        AND (is_deleted = 0 OR is_deleted IS NULL)
    ");
    $checkSentStmt->execute([$projectId]);
    $sentProject = $checkSentStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($sentProject) {
        // Check permission
        $isRecipient = ($sentProject['to_department_id'] == $departmentId);
        $isSender = ($sentProject['from_department_id'] == $departmentId);
        
        if (!$isRecipient && !$isSender && $deletedByAdmin == 0) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit;
        }
        
        // Soft delete
        $stmt = $pdo->prepare("
            UPDATE sent_projects 
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
        
        // Add to recycle bin
        addToRecycleBin($pdo, $projectId, 'sent_project', $sentProject['project_name'] ?? 'Sent Project', $deletedByDepartment, $deletedByAdmin);
        
        echo json_encode([
            'success' => true,
            'message' => 'Sent project moved to recycle bin',
            'source' => 'sent_projects_table'
        ]);
        exit;
    }
    
    echo json_encode(['success' => false, 'message' => 'Project not found']);
    
} catch(PDOException $e) {
    error_log('Delete project error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
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
        }
        return true;
    } catch(PDOException $e) {
        error_log('Recycle bin error: ' . $e->getMessage());
        return false;
    }
}
?>