<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['report_id'])) {
    sendResponse(false, 'Report ID required');
}

try {
    $stmt = $pdo->prepare("UPDATE reports SET is_viewed_by_department = 1, is_viewed_by_admin = 1 WHERE id = :id");
    $stmt->execute(['id' => $data['report_id']]);
    
    sendResponse(true, 'Report marked as viewed');
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>