<?php
// mark_document_viewed.php - FIXED VERSION

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

error_reporting(0);
ini_set('display_errors', 0);

$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['document_id'])) {
    echo json_encode(['success' => false, 'message' => 'Document ID required']);
    exit;
}

$docId = (int)$input['document_id'];
$deptId = isset($input['department_id']) ? (int)$input['department_id'] : 0;

try {
    // ============================================================
    // STEP 1: Update sent_documents - SET viewed
    // ============================================================
    $stmt = $pdo->prepare("UPDATE sent_documents SET 
        is_viewed = 1, 
        is_viewed_by_department = 1,
        viewed_at = NOW()
        WHERE id = ?");
    $stmt->execute([$docId]);
    $rowsAffected = $stmt->rowCount();
    
    // ============================================================
    // STEP 2: Also update project_documents if exists
    // ============================================================
    $stmt2 = $pdo->prepare("UPDATE project_documents SET 
        is_viewed_by_department = 1
        WHERE id = ?");
    $stmt2->execute([$docId]);
    
    // ============================================================
    // STEP 3: VERIFY - Check if update worked
    // ============================================================
    $verifyStmt = $pdo->prepare("SELECT 
        id, 
        is_viewed, 
        is_viewed_by_department, 
        viewed_at 
        FROM sent_documents 
        WHERE id = ?");
    $verifyStmt->execute([$docId]);
    $result = $verifyStmt->fetch(PDO::FETCH_ASSOC);
    
    // ============================================================
    // STEP 4: Return response
    // ============================================================
    if ($rowsAffected > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Document marked as viewed',
            'document_id' => $docId,
            'rows_affected' => $rowsAffected,
            'current_status' => $result
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Document not found or already viewed',
            'document_id' => $docId,
            'rows_affected' => $rowsAffected
        ]);
    }
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Update failed: ' . $e->getMessage()
    ]);
}
?>