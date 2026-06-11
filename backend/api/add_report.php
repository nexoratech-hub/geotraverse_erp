<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['title']) || empty($data['content'])) {
    sendResponse(false, 'Title and content required');
}

try {
    $stmt = $pdo->prepare("INSERT INTO reports (title, period, content, status, department_id, created_by) VALUES (?, ?, ?, 'draft', ?, ?)");
    
    $stmt->execute([
        $data['title'],
        $data['period'] ?? 'monthly',
        $data['content'],
        $data['department_id'] ?? 1,
        $data['created_by'] ?? 'System'
    ]);
    
    sendResponse(true, 'Report added successfully', ['id' => $pdo->lastInsertId()]);
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>