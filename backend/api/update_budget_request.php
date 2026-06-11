<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$request_id = intval($data['id']);
$status = isset($data['status']) ? $data['status'] : 'pending';
$department_id = isset($data['department_id']) ? intval($data['department_id']) : 0;

try {
    // Add columns if not exist
    $conn->query("ALTER TABLE fund_requests ADD COLUMN IF NOT EXISTS is_viewed_by_finance TINYINT DEFAULT 0");
    $conn->query("ALTER TABLE fund_requests ADD COLUMN IF NOT EXISTS reviewed_at TIMESTAMP NULL");
    $conn->query("ALTER TABLE fund_requests ADD COLUMN IF NOT EXISTS reviewed_by VARCHAR(100) NULL");
    $conn->query("ALTER TABLE fund_requests ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP");
    
    $query = "UPDATE fund_requests 
              SET status = ?, 
                  reviewed_at = NOW(),
                  reviewed_by = 'Finance Manager',
                  updated_at = NOW()
              WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('si', $status, $request_id);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'Request updated successfully']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>