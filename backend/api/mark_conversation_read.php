<?php
// /geotraverse/backend/api/mark_conversation_read_universal.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid data'
        ]);
        exit();
    }
    
    $conversation_id = isset($data['conversation_id']) ? intval($data['conversation_id']) : 0;
    $department_id = isset($data['department_id']) ? intval($data['department_id']) : 0;
    $user_id = isset($data['user_id']) ? intval($data['user_id']) : 0;
    
    if ($conversation_id === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Conversation ID is required'
        ]);
        exit();
    }
    
    if ($department_id > 0) {
        // Mark all messages in conversation as read for this department
        $query = "UPDATE messages 
                  SET is_read = 1, read_at = NOW(), status = 'read' 
                  WHERE conversation_id = :conv_id 
                  AND receiver_dept = :dept_id 
                  AND is_read = 0";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':conv_id', $conversation_id);
        $stmt->bindParam(':dept_id', $department_id);
        
    } elseif ($user_id > 0) {
        // Mark all messages in conversation as read for this user
        $query = "UPDATE messages 
                  SET is_read = 1, read_at = NOW(), status = 'read' 
                  WHERE conversation_id = :conv_id 
                  AND receiver_id = :user_id 
                  AND is_read = 0";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':conv_id', $conversation_id);
        $stmt->bindParam(':user_id', $user_id);
        
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Department ID or User ID is required'
        ]);
        exit();
    }
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'All messages marked as read'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to mark messages as read'
        ]);
    }
}
?>