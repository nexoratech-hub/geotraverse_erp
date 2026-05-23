<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'geotraverse_erp';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$conn->set_charset("utf8mb4");

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit;
}

$sender_dept = isset($data['sender_dept']) ? (int)$data['sender_dept'] : 0;
$receiver_dept = isset($data['receiver_dept']) ? (int)$data['receiver_dept'] : 0;
$message = isset($data['message']) ? trim($data['message']) : '';
$conversation_id = isset($data['conversation_id']) ? (int)$data['conversation_id'] : null;
$user_id = isset($data['user_id']) ? (int)$data['user_id'] : null;

if ($user_id == 1 && !$sender_dept) {
    $sender_dept = 1;
}

if (!$sender_dept || !$receiver_dept || !$message) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields: sender_dept, receiver_dept, message']);
    exit;
}

// Map department to delete column
$dept_column_map = [
    1 => 'deleted_by_super_admin',
    2 => 'deleted_by_finance',
    3 => 'deleted_by_sales',
    4 => 'deleted_by_manager',
    5 => 'deleted_by_secretary',
    6 => 'deleted_by_bricks',
    7 => 'deleted_by_aluminium',
    8 => 'deleted_by_town_planning',
    9 => 'deleted_by_architectural',
    10 => 'deleted_by_survey',
    11 => 'deleted_by_construction',
    12 => 'deleted_by_hatimiliki'
];

$sender_column = $dept_column_map[$sender_dept] ?? 'deleted_by_super_admin';

try {
    $conn->begin_transaction();
    
    // STEP 1: Check if conversation already exists between these departments
    $existing_conversation_id = null;
    
    if (!$conversation_id) {
        // Search for existing conversation between these two departments
        $search_query = "
            SELECT id FROM conversations 
            WHERE (sender_dept = ? AND receiver_dept = ?) 
               OR (sender_dept = ? AND receiver_dept = ?)
            LIMIT 1
        ";
        $search_stmt = $conn->prepare($search_query);
        $search_stmt->bind_param("iiii", $sender_dept, $receiver_dept, $receiver_dept, $sender_dept);
        $search_stmt->execute();
        $search_result = $search_stmt->get_result();
        
        if ($search_row = $search_result->fetch_assoc()) {
            $existing_conversation_id = $search_row['id'];
            $conversation_id = $existing_conversation_id;
            
            // UNDELETE - if this department had deleted it, restore it
            $undelete_query = "UPDATE conversations SET $sender_column = 0, deleted_at = NULL WHERE id = ?";
            $undelete_stmt = $conn->prepare($undelete_query);
            $undelete_stmt->bind_param("i", $conversation_id);
            $undelete_stmt->execute();
        }
    }
    
    // STEP 2: If no conversation exists, create a new one
    if (!$conversation_id) {
        $insert_conv = "
            INSERT INTO conversations (sender_dept, receiver_dept, subject, status, created_at, updated_at) 
            VALUES (?, ?, ?, 'active', NOW(), NOW())
        ";
        $subject = substr($message, 0, 100);
        $insert_stmt = $conn->prepare($insert_conv);
        $insert_stmt->bind_param("iis", $sender_dept, $receiver_dept, $subject);
        $insert_stmt->execute();
        $conversation_id = $conn->insert_id;
    }
    
    // STEP 3: Insert the message
    $insert_msg = "
        INSERT INTO messages (
            sender_dept, receiver_dept, conversation_id, sender_id, receiver_id, 
            message, is_read, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, 0, 'sent', NOW())
    ";
    $msg_stmt = $conn->prepare($insert_msg);
    $msg_stmt->bind_param("iiiiss", $sender_dept, $receiver_dept, $conversation_id, $sender_dept, $receiver_dept, $message);
    $msg_stmt->execute();
    $message_id = $conn->insert_id;
    
    // STEP 4: Update conversation updated_at timestamp
    $update_conv = "UPDATE conversations SET updated_at = NOW() WHERE id = ?";
    $update_stmt = $conn->prepare($update_conv);
    $update_stmt->bind_param("i", $conversation_id);
    $update_stmt->execute();
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Message sent successfully',
        'data' => [
            'conversation_id' => $conversation_id,
            'message_id' => $message_id,
            'is_new_conversation' => ($existing_conversation_id === null)
        ]
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} finally {
    $conn->close();
}
?>