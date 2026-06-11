<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['id'])) {
    sendResponse(false, 'Campaign ID required');
}

try {
    $stmt = $pdo->prepare("UPDATE marketing_campaigns SET is_deleted = 1, deleted_by_department = :dept_deleted, deleted_by_admin = :admin_deleted WHERE id = :id");
    
    $stmt->execute([
        'dept_deleted' => $data['deleted_by_department'] ?? 0,
        'admin_deleted' => $data['deleted_by_admin'] ?? 0,
        'id' => $data['id']
    ]);
    
    // Add to recycle bin
    $stmt2 = $pdo->prepare("INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin) VALUES (?, 'marketing_campaign', (SELECT campaign_name FROM marketing_campaigns WHERE id = ?), ?, ?)");
    $stmt2->execute([$data['id'], $data['id'], $data['deleted_by_department'] ?? null, $data['deleted_by_admin'] ?? 0]);
    
    sendResponse(true, 'Marketing campaign moved to recycle bin');
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>