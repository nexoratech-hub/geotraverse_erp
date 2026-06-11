<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['id'])) {
    sendResponse(false, 'Employee ID required');
}

try {
    $stmt = $pdo->prepare("UPDATE employees SET is_deleted = 1 WHERE id = :id");
    $stmt->execute(['id' => $data['id']]);
    
    // Add to recycle bin
    $stmt2 = $pdo->prepare("INSERT INTO recycle_bin (item_id, item_type, item_name) VALUES (?, 'employee', (SELECT name FROM employees WHERE id = ?))");
    $stmt2->execute([$data['id'], $data['id']]);
    
    sendResponse(true, 'Employee moved to recycle bin');
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>