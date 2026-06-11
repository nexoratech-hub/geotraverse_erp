<?php
require_once 'config.php';

// Ensure JSON response is sent
header('Content-Type: application/json');

// Get input data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Debug: Log the received data
error_log("Add transaction received: " . $input);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data received']);
    exit;
}

if (empty($data['source']) || empty($data['amount'])) {
    echo json_encode(['success' => false, 'message' => 'Source and amount are required']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO transactions (type, source, amount, paid_amount, transaction_date, status, description, department_id, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $result = $stmt->execute([
        $data['type'] ?? 'expense',
        $data['source'],
        $data['amount'] ?? 0,
        $data['paid_amount'] ?? ($data['status'] === 'partial' ? ($data['paid_amount'] ?? 0) : ($data['amount'] ?? 0)),
        $data['transaction_date'] ?? date('Y-m-d'),
        $data['status'] ?? 'pending',
        $data['description'] ?? null,
        $data['department_id'] ?? 1,
        $data['created_by'] ?? 'System'
    ]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Transaction added successfully', 'id' => $pdo->lastInsertId()]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to insert transaction']);
    }
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>