<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['id'])) {
    sendResponse(false, 'Report ID required');
}

try {
    $stmt = $pdo->prepare("UPDATE reports SET title = ?, period = ?, content = ?, updated_by = ? WHERE id = ?");
    
    $stmt->execute([
        $data['title'] ?? null,
        $data['period'] ?? 'monthly',
        $data['content'] ?? null,
        $data['updated_by'] ?? 'System',
        $data['id']
    ]);
    
    sendResponse(true, 'Report updated successfully');
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>