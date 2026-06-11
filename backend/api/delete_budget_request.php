<?php
// File: backend/api/delete_budget_request.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

session_start();

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$request_id = intval($data['id']);
$department_id = isset($data['department_id']) ? intval($data['department_id']) : 0;
$is_admin = isset($data['is_admin']) ? intval($data['is_admin']) : 0;

try {
    $query = "UPDATE fund_requests SET is_deleted = 1, deleted_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $request_id);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'Request deleted successfully']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>