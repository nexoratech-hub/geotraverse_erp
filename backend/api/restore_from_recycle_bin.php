<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['recycle_id'])) {
    sendResponse(false, 'Recycle bin ID required');
}

try {
    // Get item info
    $stmt = $pdo->prepare("SELECT item_id, item_type FROM recycle_bin WHERE id = :id AND restored = 0");
    $stmt->execute(['id' => $data['recycle_id']]);
    $item = $stmt->fetch();
    
    if (!$item) {
        sendResponse(false, 'Item not found or already restored');
    }
    
    // Restore based on item type
    if ($item['item_type'] === 'project') {
        $stmt2 = $pdo->prepare("UPDATE projects SET is_deleted = 0, deleted_by_department = 0, deleted_by_admin = 0 WHERE id = :id");
        $stmt2->execute(['id' => $item['item_id']]);
    } elseif ($item['item_type'] === 'budget_request') {
        $stmt2 = $pdo->prepare("UPDATE budget_requests SET is_deleted = 0, deleted_by_department = 0, deleted_by_admin = 0 WHERE id = :id");
        $stmt2->execute(['id' => $item['item_id']]);
    } elseif ($item['item_type'] === 'report') {
        $stmt2 = $pdo->prepare("UPDATE reports SET is_deleted = 0, deleted_by_department = 0, deleted_by_admin = 0 WHERE id = :id");
        $stmt2->execute(['id' => $item['item_id']]);
    } elseif ($item['item_type'] === 'project_document') {
        $stmt2 = $pdo->prepare("UPDATE project_documents SET is_deleted = 0 WHERE id = :id");
        $stmt2->execute(['id' => $item['item_id']]);
    } elseif ($item['item_type'] === 'uploaded_report') {
        $stmt2 = $pdo->prepare("UPDATE uploaded_reports SET is_deleted = 0 WHERE id = :id");
        $stmt2->execute(['id' => $item['item_id']]);
    } elseif ($item['item_type'] === 'daily_work') {
        $stmt2 = $pdo->prepare("UPDATE daily_work SET is_deleted = 0 WHERE id = :id");
        $stmt2->execute(['id' => $item['item_id']]);
    }
    
    // Mark as restored in recycle bin
    $stmt3 = $pdo->prepare("UPDATE recycle_bin SET restored = 1, restored_at = NOW() WHERE id = :id");
    $stmt3->execute(['id' => $data['recycle_id']]);
    
    sendResponse(true, 'Item restored successfully', ['item_type' => $item['item_type']]);
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>