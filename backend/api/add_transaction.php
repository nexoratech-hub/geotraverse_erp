<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'geotraverse_erp';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$type = isset($input['type']) ? $input['type'] : '';
$source = isset($input['source']) ? $input['source'] : '';
$amount = isset($input['amount']) ? floatval($input['amount']) : 0;
$paid_amount = isset($input['paid_amount']) ? floatval($input['paid_amount']) : 0;
$transaction_date = isset($input['transaction_date']) ? $input['transaction_date'] : date('Y-m-d');
$status = isset($input['status']) ? $input['status'] : 'paid';
$description = isset($input['description']) ? $input['description'] : '';
$department_id = isset($input['department_id']) ? intval($input['department_id']) : 2;
$created_by = isset($input['created_by']) ? $input['created_by'] : 'Finance Manager';

if (!$type || !$source || $amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Type, source and amount required']);
    exit;
}

// Insert transaction
$stmt = $conn->prepare("INSERT INTO transactions (type, source, amount, paid_amount, transaction_date, status, description, department_id, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssddsssis", $type, $source, $amount, $paid_amount, $transaction_date, $status, $description, $department_id, $created_by);

if ($stmt->execute()) {
    $new_id = $conn->insert_id;
    echo json_encode([
        'success' => true,
        'message' => 'Transaction added successfully',
        'data' => ['id' => $new_id]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add transaction: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>