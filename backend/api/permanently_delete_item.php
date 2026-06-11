<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['recycle_id'])) {
    sendResponse(false, 'Recycle bin ID required');
}

try {
    $stmt = $pdo->prepare("DELETE FROM recycle_bin WHERE id = :id");
    $stmt->execute(['id' => $data['recycle_id']]);
    
    sendResponse(true, 'Item permanently deleted from recycle bin');
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>