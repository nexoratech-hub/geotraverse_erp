<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['report_id'])) {
    sendResponse(false, 'Report ID required');
}

try {
    $stmt = $pdo->prepare("UPDATE reports SET is_deleted = 1, deleted_by_department = :dept_deleted, deleted_by_admin = :admin_deleted WHERE id = :id");
    
    $stmt->execute([
        'dept_deleted' => $data['department_id'] ? 1 : 0,
        'admin_deleted' => $data['is_admin'] ? 1 : 0,
        'id' => $data['report_id']
    ]);
    
    // Add to recycle bin
    $stmt2 = $pdo->prepare("INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin) VALUES (?, 'report', (SELECT title FROM reports WHERE id = ?), ?, ?)");
    $stmt2->execute([$data['report_id'], $data['report_id'], $data['department_id'] ?? null, $data['is_admin'] ?? 0]);
    
    sendResponse(true, 'Report moved to recycle bin');
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>