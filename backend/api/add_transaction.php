<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['source']) || empty($data['amount'])) {
    sendResponse(false, 'Source and amount required');
}

try {
    $stmt = $pdo->prepare("INSERT INTO transactions (type, source, amount, paid_amount, transaction_date, status, description, department_id, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $data['type'] ?? 'expense',
        $data['source'],
        $data['amount'],
        $data['paid_amount'] ?? $data['amount'],
        $data['transaction_date'] ?? date('Y-m-d'),
        $data['status'] ?? 'pending',
        $data['description'] ?? null,
        $data['department_id'] ?? 1,
        $data['created_by'] ?? 'System'
    ]);
    
    sendResponse(true, 'Transaction added successfully', ['id' => $pdo->lastInsertId()]);
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>