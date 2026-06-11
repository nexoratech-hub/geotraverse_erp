<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['id'])) {
    sendResponse(false, 'Document ID required');
}

try {
    $stmt = $pdo->prepare("UPDATE project_documents SET is_deleted = 1, deleted_by = :deleted_by WHERE id = :id");
    $stmt->execute(['deleted_by' => $data['deleted_by'] ?? 'System', 'id' => $data['id']]);
    
    // Add to recycle bin
    $stmt2 = $pdo->prepare("INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id) VALUES (?, 'project_document', (SELECT title FROM project_documents WHERE id = ?), ?)");
    $stmt2->execute([$data['id'], $data['id'], $data['department_id'] ?? null]);
    
    sendResponse(true, 'Document moved to recycle bin');
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>