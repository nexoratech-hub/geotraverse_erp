<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$type = $data['type'] ?? 'expense';
$source = $data['source'] ?? '';
$amount = $data['amount'] ?? 0;
$transaction_date = $data['transaction_date'] ?? date('Y-m-d');
$status = $data['status'] ?? 'pending';
$paid_amount = $data['paid_amount'] ?? 0;
$description = $data['description'] ?? '';
$department_id = $data['department_id'] ?? 0;

if (empty($source) || $amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Source and amount required']);
    exit;
}

try {
    $query = "INSERT INTO transactions (type, source, amount, transaction_date, status, paid_amount, description, department_id, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssdssdsi', $type, $source, $amount, $transaction_date, $status, $paid_amount, $description, $department_id);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'Transaction added successfully', 'id' => $conn->insert_id]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>