<?php
// File: backend/api/add_budget_request.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

session_start();

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$title = isset($data['title']) ? $data['title'] : '';
$type = isset($data['type']) ? $data['type'] : 'expense';
$source = isset($data['source']) ? $data['source'] : '';
$amount = isset($data['amount']) ? floatval($data['amount']) : 0;
$description = isset($data['description']) ? $data['description'] : '';
$department_id = isset($data['department_id']) ? intval($data['department_id']) : 0;
$requested_by = isset($data['requested_by']) ? $data['requested_by'] : '';

if (empty($title) && empty($source)) {
    echo json_encode(['success' => false, 'message' => 'Title is required']);
    exit;
}

if ($amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Amount must be greater than 0']);
    exit;
}

try {
    $finalTitle = !empty($title) ? $title : $source;
    $request_date = date('Y-m-d');
    
    $query = "INSERT INTO fund_requests (title, source, type, amount, description, department_id, requested_by, request_date, created_at, status) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'pending')";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sssdsiss', $finalTitle, $source, $type, $amount, $description, $department_id, $requested_by, $request_date);
    $stmt->execute();
    
    $new_id = $conn->insert_id;
    
    echo json_encode([
        'success' => true, 
        'message' => 'Budget request submitted successfully',
        'request_id' => $new_id
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>