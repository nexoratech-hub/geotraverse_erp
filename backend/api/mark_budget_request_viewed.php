<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['request_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$request_id = intval($data['request_id']);

try {
    $conn->query("ALTER TABLE fund_requests ADD COLUMN IF NOT EXISTS is_viewed_by_finance TINYINT DEFAULT 0");
    $conn->query("ALTER TABLE fund_requests ADD COLUMN IF NOT EXISTS viewed_at TIMESTAMP NULL");
    
    $query = "UPDATE fund_requests SET is_viewed_by_finance = 1, viewed_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $request_id);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'Request marked as viewed']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>