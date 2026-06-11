<?php
// File: backend/api/mark_budget_request_viewed.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

session_start();

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['request_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$request_id = intval($data['request_id']);
$department_id = isset($data['department_id']) ? intval($data['department_id']) : 2; // Default Finance

try {
    // Check if column exists, if not add it
    $checkColumn = "SHOW COLUMNS FROM fund_requests LIKE 'is_viewed_by_finance'";
    $colResult = $conn->query($checkColumn);
    
    if ($colResult->num_rows === 0) {
        // Add the column
        $alterQuery = "ALTER TABLE fund_requests ADD COLUMN is_viewed_by_finance TINYINT DEFAULT 0";
        $conn->query($alterQuery);
    }
    
    // Mark as viewed by finance
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