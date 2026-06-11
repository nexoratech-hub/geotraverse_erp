<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'config/db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

// Required fields
$required = ['date', 'project_name', 'work_type', 'department_id', 'created_by'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        echo json_encode(['success' => false, 'message' => "Missing field: $field"]);
        exit();
    }
}

// Prepare data
$date = $data['date'];
$project_name = $data['project_name'];
$work_type = $data['work_type'];
$work_description = $data['work_description'] ?? '';
$quantity_produced = $data['quantity_produced'] ?? 0;
$quantity_sold = $data['quantity_sold'] ?? 0;
$price_per_unit = $data['price_per_unit'] ?? 0;
$total_amount = $data['total_amount'] ?? ($quantity_sold * $price_per_unit);
$income = $data['income'] ?? $total_amount;
$expenses = $data['expenses'] ?? 0;
$payment_status = $data['payment_status'] ?? 'pending';
$partial_amount = $data['partial_amount'] ?? 0;
$status = $data['status'] ?? $payment_status;
$department_id = $data['department_id'];
$created_by = $data['created_by'];

$query = "INSERT INTO daily_work (date, project_name, work_type, work_description, 
          quantity_produced, quantity_sold, price_per_unit, total_amount, income, 
          expenses, payment_status, partial_amount, status, department_id, created_by, created_at) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($query);
$stmt->bind_param("ssssiiidddsssiss", 
    $date, $project_name, $work_type, $work_description,
    $quantity_produced, $quantity_sold, $price_per_unit, $total_amount, $income,
    $expenses, $payment_status, $partial_amount, $status, $department_id, $created_by
);

if ($stmt->execute()) {
    $id = $conn->insert_id;
    echo json_encode(['success' => true, 'message' => 'Daily work added', 'id' => $id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>