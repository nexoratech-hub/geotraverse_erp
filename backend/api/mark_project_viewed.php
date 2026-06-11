<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['project_id'])) {
    sendResponse(false, 'Project ID required');
}

try {
    $stmt = $pdo->prepare("UPDATE projects SET is_viewed_by_department = 1, is_viewed_by_admin = 1 WHERE id = :id");
    $stmt->execute(['id' => $data['project_id']]);
    
    sendResponse(true, 'Project marked as viewed');
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>