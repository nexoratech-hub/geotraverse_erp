<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit;
}

$message_id = isset($data->message_id) ? intval($data->message_id) : null;
$department_id = isset($data->department_id) ? intval($data->department_id) : null;
$conversation_id = isset($data->conversation_id) ? intval($data->conversation_id) : null;
$user_id = isset($data->user_id) ? intval($data->user_id) : null;

try {
    if ($message_id) {
        // Mark single message as read
        $query = "UPDATE messages SET is_read = 1, read_at = NOW() WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$message_id]);
        
        echo json_encode(['success' => true, 'message' => 'Message marked as read']);
        
    } else if ($conversation_id && $department_id) {
        // Mark all messages in conversation as read for this department
        $query = "UPDATE messages SET is_read = 1, read_at = NOW() 
                  WHERE conversation_id = ? AND receiver_dept = ? AND is_read = 0";
        $stmt = $db->prepare($query);
        $stmt->execute([$conversation_id, $department_id]);
        
        echo json_encode(['success' => true, 'message' => 'All messages marked as read']);
        
    } else if ($conversation_id && $user_id) {
        // Mark all messages in conversation as read for this user
        $query = "UPDATE messages SET is_read = 1, read_at = NOW() 
                  WHERE conversation_id = ? AND receiver_id = ? AND is_read = 0";
        $stmt = $db->prepare($query);
        $stmt->execute([$conversation_id, $user_id]);
        
        echo json_encode(['success' => true, 'message' => 'All messages marked as read']);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Message ID or Conversation ID with Department/User ID required']);
    }
    
} catch (PDOException $e) {
    error_log("Mark message read error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>