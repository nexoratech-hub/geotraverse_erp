<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['id'])) {
    sendResponse(false, 'Report ID required');
}

try {
    // Get file path first
    $stmt2 = $pdo->prepare("SELECT file_path FROM uploaded_reports WHERE id = :id");
    $stmt2->execute(['id' => $data['id']]);
    $report = $stmt2->fetch();
    
    $stmt = $pdo->prepare("UPDATE uploaded_reports SET is_deleted = 1, deleted_by = :deleted_by WHERE id = :id");
    $stmt->execute(['deleted_by' => $data['deleted_by'] ?? 'System', 'id' => $data['id']]);
    
    // Add to recycle bin
    $stmt3 = $pdo->prepare("INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id) VALUES (?, 'uploaded_report', (SELECT title FROM uploaded_reports WHERE id = ?), ?)");
    $stmt3->execute([$data['id'], $data['id'], $data['department_id'] ?? null]);
    
    sendResponse(true, 'Uploaded report moved to recycle bin');
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>