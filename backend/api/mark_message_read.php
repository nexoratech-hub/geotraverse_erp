<?php
// /geotraverse/backend/api/mark_message_read_universal.php
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
    
    $message_id = isset($data['message_id']) ? intval($data['message_id']) : 0;
    $department_id = isset($data['department_id']) ? intval($data['department_id']) : 0;
    $user_id = isset($data['user_id']) ? intval($data['user_id']) : 0;
    
    if ($message_id === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Message ID is required'
        ]);
        exit();
    }
    
    // Mark message as read
    $query = "UPDATE messages SET is_read = 1, read_at = NOW(), status = 'read' WHERE id = :msg_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':msg_id', $message_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Message marked as read'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to mark message as read'
        ]);
    }
}
?>