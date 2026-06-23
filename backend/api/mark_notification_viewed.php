<?php
// ============================================================
// mark_notification_viewed.php - Kuweka notification kama imeonekana
// ============================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$department_id = isset($data['department_id']) ? intval($data['department_id']) : 0;
$item_type = isset($data['item_type']) ? $data['item_type'] : '';
$item_id = isset($data['item_id']) ? intval($data['item_id']) : 0;
$notification_id = isset($data['notification_id']) ? intval($data['notification_id']) : 0;

if (!$department_id) {
    echo json_encode(['success' => false, 'message' => 'Department ID required']);
    exit;
}

try {
    $db = getDB();
    
    if ($notification_id) {
        // Mark specific notification by ID
        $stmt = $db->prepare("
            UPDATE notifications 
            SET is_viewed = 1, updated_at = NOW() 
            WHERE id = ? AND department_id = ?
        ");
        $stmt->execute([$notification_id, $department_id]);
        
        $affected = $stmt->rowCount();
        
        echo json_encode([
            'success' => true,
            'message' => 'Notification marked as viewed',
            'affected' => $affected
        ]);
        
    } else if ($item_type && $item_id) {
        // Mark notification by item_type and item_id
        $stmt = $db->prepare("
            UPDATE notifications 
            SET is_viewed = 1, updated_at = NOW() 
            WHERE department_id = ? AND item_type = ? AND item_id = ? AND is_viewed = 0
        ");
        $stmt->execute([$department_id, $item_type, $item_id]);
        
        $affected = $stmt->rowCount();
        
        echo json_encode([
            'success' => true,
            'message' => 'Notification marked as viewed',
            'affected' => $affected
        ]);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Either notification_id or (item_type + item_id) required']);
    }
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>