<?php
// ============================================================
// add_notification.php - Kuongeza notification
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
$from_department_id = isset($data['from_department_id']) ? intval($data['from_department_id']) : 0;
$item_type = isset($data['item_type']) ? $data['item_type'] : '';
$item_id = isset($data['item_id']) ? intval($data['item_id']) : 0;
$item_title = isset($data['item_title']) ? $data['item_title'] : '';
$message = isset($data['message']) ? $data['message'] : '';

if (!$department_id || !$item_type || !$item_id) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $db = getDB();
    
    // Check if notification already exists
    $checkStmt = $db->prepare("
        SELECT id FROM notifications 
        WHERE department_id = ? AND item_type = ? AND item_id = ? AND is_viewed = 0
    ");
    $checkStmt->execute([$department_id, $item_type, $item_id]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        echo json_encode(['success' => true, 'message' => 'Notification already exists', 'id' => $existing['id']]);
        exit;
    }
    
    // Insert notification
    $stmt = $db->prepare("
        INSERT INTO notifications (
            department_id, 
            from_department_id, 
            item_type, 
            item_id, 
            item_title, 
            message, 
            is_viewed, 
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, 0, NOW())
    ");
    
    $stmt->execute([
        $department_id,
        $from_department_id,
        $item_type,
        $item_id,
        $item_title,
        $message
    ]);
    
    $notificationId = $db->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Notification added successfully',
        'id' => $notificationId
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>