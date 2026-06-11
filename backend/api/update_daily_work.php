<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['id'])) {
    sendResponse(false, 'Daily work ID required');
}

try {
    $stmt = $pdo->prepare("UPDATE daily_work SET date = ?, work_description = ?, quantity_produced = ?, quantity_sold = ?, price_per_unit = ?, income = ?, expenses = ?, budget = ?, amount = ?, status = ?, updated_by = ? WHERE id = ?");
    
    $stmt->execute([
        $data['date'] ?? null,
        $data['work_description'] ?? null,
        $data['quantity_produced'] ?? 0,
        $data['quantity_sold'] ?? 0,
        $data['price_per_unit'] ?? 0,
        $data['income'] ?? 0,
        $data['expenses'] ?? 0,
        $data['budget'] ?? 0,
        $data['amount'] ?? 0,
        $data['status'] ?? 'pending',
        $data['updated_by'] ?? 'System',
        $data['id']
    ]);
    
    sendResponse(true, 'Daily work updated successfully');
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>