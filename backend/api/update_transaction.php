<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['id'])) {
    sendResponse(false, 'Transaction ID required');
}

try {
    $stmt = $pdo->prepare("UPDATE transactions SET type = ?, source = ?, amount = ?, paid_amount = ?, transaction_date = ?, status = ?, description = ?, updated_by = ? WHERE id = ?");
    
    $stmt->execute([
        $data['type'] ?? 'expense',
        $data['source'],
        $data['amount'],
        $data['paid_amount'] ?? $data['amount'],
        $data['transaction_date'],
        $data['status'] ?? 'pending',
        $data['description'] ?? null,
        $data['updated_by'] ?? 'System',
        $data['id']
    ]);
    
    sendResponse(true, 'Transaction updated successfully');
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>