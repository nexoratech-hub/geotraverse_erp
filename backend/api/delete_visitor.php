<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['id'])) {
    sendResponse(false, 'Visitor ID required');
}

try {
    $stmt = $pdo->prepare("DELETE FROM visitors WHERE id = :id");
    $stmt->execute(['id' => $data['id']]);
    
    sendResponse(true, 'Visitor record deleted');
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>