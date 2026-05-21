<?php
// backend/api/send_message.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$database = new Database();
$conn = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Only POST method allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

// Support multiple parameter formats
$from_department_id = $input['from_department_id'] ?? $input['sender_dept'] ?? null;
$to_department_id = $input['to_department_id'] ?? $input['receiver_dept'] ?? null;
$sender_id = $input['sender_id'] ?? null;
$receiver_id = $input['receiver_id'] ?? null;
$conversation_id = $input['conversation_id'] ?? null;
$user_id = $input['user_id'] ?? null;
$message_text = $input['message'] ?? $input['msg'] ?? null;
$subject = $input['subject'] ?? 'Message';

if (!$message_text) {
    echo json_encode(['success' => false, 'message' => 'Message content is required']);
    exit();
}

// CASE 1: Using from_department_id + to_department_id
if ($from_department_id && $to_department_id) {
    $stmt = $conn->prepare("INSERT INTO messages (sender_dept, receiver_dept, message, is_read, status, created_at) 
                           VALUES (?, ?, ?, 0, 'sent', NOW())");
    $stmt->execute([$from_department_id, $to_department_id, $message_text]);
    
    echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
}
// CASE 2: Using sender_id + receiver_department_id
else if ($sender_id && $to_department_id) {
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_dept, message, is_read, status, created_at) 
                           VALUES (?, ?, ?, 0, 'sent', NOW())");
    $stmt->execute([$sender_id, $to_department_id, $message_text]);
    
    echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
}
// CASE 3: Using conversation_id + user_id + message
else if ($conversation_id && $user_id && $message_text) {
    // Get the other department from conversation
    $convStmt = $conn->prepare("SELECT sender_dept, receiver_dept FROM conversations WHERE id = ?");
    $convStmt->execute([$conversation_id]);
    $conv = $convStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($conv) {
        // Determine which side the user is on
        $stmt = $conn->prepare("INSERT INTO messages (conversation_id, sender_id, message, is_read, status, created_at) 
                               VALUES (?, ?, ?, 0, 'sent', NOW())");
        $stmt->execute([$conversation_id, $user_id, $message_text]);
        
        echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Conversation not found']);
    }
}
else {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid parameters. Need from_department_id+to_department_id or sender_id+receiver_department_id or conversation_id+user_id+message'
    ]);
}
?>