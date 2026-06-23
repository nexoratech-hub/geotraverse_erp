<?php
// delete_project_document.php - Delete Project Documents and Sent Project Documents

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

if (!$data || empty($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'Document ID required']);
    exit;
}

$docId = (int)$data['id'];
$departmentId = isset($data['department_id']) ? (int)$data['department_id'] : 0;
$deletedBy = isset($data['deleted_by']) ? $data['deleted_by'] : 'System';
$isAdmin = isset($data['is_admin']) && $data['is_admin'] ? 1 : 0;
$deletedByDepartment = isset($data['deleted_by_department']) ? (int)$data['deleted_by_department'] : $departmentId;
$deletedByAdmin = isset($data['deleted_by_admin']) ? (int)$data['deleted_by_admin'] : $isAdmin;

try {
    // ============================================================
    // 1. CHECK IF DOCUMENT EXISTS IN project_documents TABLE
    // ============================================================
    $checkStmt = $pdo->prepare("
        SELECT id, title, department_id, sent_from_department, sent_to_department 
        FROM project_documents 
        WHERE id = ?
        AND (is_deleted = 0 OR is_deleted IS NULL)
        AND (deleted_by_department = 0 OR deleted_by_department IS NULL)
        AND (deleted_by_admin = 0 OR deleted_by_admin IS NULL)
    ");
    $checkStmt->execute([$docId]);
    $doc = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($doc) {
        // Check permission
        $isOwner = ($doc['department_id'] == $departmentId);
        $isRecipient = ($doc['sent_to_department'] == $departmentId);
        
        if (!$isOwner && !$isRecipient && $deletedByAdmin == 0) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit;
        }
        
        // Soft delete
        $stmt = $pdo->prepare("
            UPDATE project_documents 
            SET 
                is_deleted = 1,
                deleted_by = :deleted_by,
                deleted_by_department = :dept_deleted,
                deleted_by_admin = :admin_deleted,
                deleted_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            'deleted_by' => $deletedBy,
            'dept_deleted' => $deletedByDepartment,
            'admin_deleted' => $deletedByAdmin,
            'id' => $docId
        ]);
        
        // Add to recycle bin
        addToRecycleBin($pdo, $docId, 'project_document', $doc['title'], $deletedByDepartment, $deletedByAdmin);
        
        echo json_encode([
            'success' => true,
            'message' => 'Document moved to recycle bin',
            'source' => 'project_documents_table'
        ]);
        exit;
    }
    
    // ============================================================
    // 2. CHECK IF DOCUMENT EXISTS IN sent_project_documents TABLE
    // ============================================================
    $checkSentStmt = $pdo->prepare("
        SELECT 
            id, 
            document_title, 
            from_department_id, 
            to_department_id
        FROM sent_project_documents 
        WHERE id = ?
        AND (is_deleted = 0 OR is_deleted IS NULL)
    ");
    $checkSentStmt->execute([$docId]);
    $sentDoc = $checkSentStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($sentDoc) {
        // Check permission
        $isRecipient = ($sentDoc['to_department_id'] == $departmentId);
        $isSender = ($sentDoc['from_department_id'] == $departmentId);
        
        if (!$isRecipient && !$isSender && $deletedByAdmin == 0) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit;
        }
        
        // Soft delete
        $stmt = $pdo->prepare("
            UPDATE sent_project_documents 
            SET 
                is_deleted = 1,
                deleted_by = :deleted_by,
                deleted_by_department = :dept_deleted,
                deleted_by_admin = :admin_deleted,
                deleted_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            'deleted_by' => $deletedBy,
            'dept_deleted' => $deletedByDepartment,
            'admin_deleted' => $deletedByAdmin,
            'id' => $docId
        ]);
        
        // Add to recycle bin
        addToRecycleBin($pdo, $docId, 'sent_project_document', $sentDoc['document_title'] ?? 'Sent Document', $deletedByDepartment, $deletedByAdmin);
        
        echo json_encode([
            'success' => true,
            'message' => 'Sent document moved to recycle bin',
            'source' => 'sent_project_documents_table'
        ]);
        exit;
    }
    
    // ============================================================
    // 3. DOCUMENT NOT FOUND
    // ============================================================
    echo json_encode(['success' => false, 'message' => 'Document not found']);
    
} catch(PDOException $e) {
    error_log('Delete document error: ' . $e->getMessage());
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