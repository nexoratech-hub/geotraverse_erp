<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Database connection
$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Get input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$document_id = isset($data['id']) ? intval($data['id']) : 0;
$department_id = isset($data['department_id']) ? intval($data['department_id']) : 0;
$is_deleted = isset($data['is_deleted']) ? intval($data['is_deleted']) : 1;

if ($document_id == 0) {
    echo json_encode(['success' => false, 'message' => 'Document ID required']);
    exit;
}

try {
    // ============================================================
    // 1. GET DOCUMENT DATA
    // ============================================================
    $stmt = $pdo->prepare("SELECT * FROM project_documents WHERE id = ?");
    $stmt->execute([$document_id]);
    $document = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$document) {
        echo json_encode(['success' => false, 'message' => 'Document not found']);
        exit;
    }

    // ============================================================
    // 2. CHECK IF DEPARTMENT CAN DELETE (OWNER OR RECEIVER)
    // ============================================================
    $is_owner = false;
    $is_receiver = false;
    
    if ($department_id > 0) {
        $is_owner = ($document['department_id'] == $department_id);
        $is_receiver = ($document['sent_to_department'] == $department_id);
    }
    
    $can_delete = ($department_id == 0 || $is_owner || $is_receiver);
    
    if (!$can_delete) {
        echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this document']);
        exit;
    }

    // ============================================================
    // 3. SOFT DELETE THE DOCUMENT
    // ============================================================
    $updateStmt = $pdo->prepare("
        UPDATE project_documents 
        SET 
            is_deleted = ?,
            deleted_at = NOW()
        WHERE id = ?
    ");
    $updateStmt->execute([$is_deleted, $document_id]);

    // ============================================================
    // 4. ADD TO RECYCLE BIN
    // ============================================================
    $recycleStmt = $pdo->prepare("
        INSERT INTO recycle_bin (
            item_id,
            item_type,
            item_name,
            deleted_by_department_id,
            deleted_at
        ) VALUES (
            ?, 'document', ?, ?, NOW()
        )
    ");
    
    $recycleStmt->execute([
        $document_id,
        $document['title'],
        $department_id > 0 ? $department_id : 0
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Document deleted successfully',
        'data' => [
            'document_id' => $document_id,
            'is_owner' => $is_owner,
            'is_receiver' => $is_receiver
        ]
    ]);

} catch(PDOException $e) {
    error_log("Delete document error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>