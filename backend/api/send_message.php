<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
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

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$sender_dept = isset($input['sender_dept']) ? (int)$input['sender_dept'] : null;
$receiver_dept = isset($input['receiver_dept']) ? (int)$input['receiver_dept'] : null;
$conversation_id = isset($input['conversation_id']) ? (int)$input['conversation_id'] : null;
$message = isset($input['message']) ? trim($input['message']) : '';
$subject = isset($input['subject']) ? trim($input['subject']) : 'Message';
$sender_id = isset($input['sender_id']) ? (int)$input['sender_id'] : null;

if (!$sender_dept || !$receiver_dept || !$message) {
    echo json_encode(['success' => false, 'message' => 'sender_dept, receiver_dept, and message are required']);
    exit;
}

$department_names = [
    1 => 'Super Admin', 2 => 'Finance', 3 => 'Sales & Marketing',
    4 => 'Manager', 5 => 'Secretary', 6 => 'Bricks & Timber',
    7 => 'Aluminium', 8 => 'Town Planning', 9 => 'Architectural',
    10 => 'Survey', 11 => 'Construction', 12 => 'Hatimiliki'
];

// Map department to delete column
$delete_column_map = [
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

$sender_delete_column = $delete_column_map[$sender_dept] ?? 'deleted_by_super_admin';
$receiver_delete_column = $delete_column_map[$receiver_dept] ?? 'deleted_by_super_admin';

try {
    $conn->begin_transaction();
    
    // If conversation_id not provided, find or create conversation
    if (!$conversation_id) {
        // Check if conversation already exists between these departments
        $find_stmt = $conn->prepare("
            SELECT id FROM conversations 
            WHERE (sender_dept = ? AND receiver_dept = ?) 
               OR (sender_dept = ? AND receiver_dept = ?)
            LIMIT 1
        ");
        $find_stmt->bind_param("iiii", $sender_dept, $receiver_dept, $receiver_dept, $sender_dept);
        $find_stmt->execute();
        $find_result = $find_stmt->get_result();
        
        if ($find_row = $find_result->fetch_assoc()) {
            $conversation_id = $find_row['id'];
        } else {
            // Create new conversation
            $insert_stmt = $conn->prepare("
                INSERT INTO conversations (sender_dept, receiver_dept, subject, created_at, updated_at)
                VALUES (?, ?, ?, NOW(), NOW())
            ");
            $insert_stmt->bind_param("iis", $sender_dept, $receiver_dept, $subject);
            $insert_stmt->execute();
            $conversation_id = $conn->insert_id;
        }
    }
    
    // CRITICAL: Clear deleted flags for both departments when a new message is sent
    // This ensures the conversation reappears for both sides
    $clear_sender_stmt = $conn->prepare("UPDATE conversations SET $sender_delete_column = 0 WHERE id = ?");
    $clear_sender_stmt->bind_param("i", $conversation_id);
    $clear_sender_stmt->execute();
    
    $clear_receiver_stmt = $conn->prepare("UPDATE conversations SET $receiver_delete_column = 0 WHERE id = ?");
    $clear_receiver_stmt->bind_param("i", $conversation_id);
    $clear_receiver_stmt->execute();
    
    // Insert the message
    $insert_msg_stmt = $conn->prepare("
        INSERT INTO messages (conversation_id, sender_dept, receiver_dept, message, status, created_at)
        VALUES (?, ?, ?, ?, 'sent', NOW())
    ");
    $insert_msg_stmt->bind_param("iiis", $conversation_id, $sender_dept, $receiver_dept, $message);
    $insert_msg_stmt->execute();
    $message_id = $conn->insert_id;
    
    // Update conversation's updated_at timestamp
    $update_conv_stmt = $conn->prepare("UPDATE conversations SET updated_at = NOW() WHERE id = ?");
    $update_conv_stmt->bind_param("i", $conversation_id);
    $update_conv_stmt->execute();
    
    $conn->commit();
    
    // Get conversation details for response
    $conv_info_stmt = $conn->prepare("
        SELECT sender_dept, receiver_dept, subject 
        FROM conversations WHERE id = ?
    ");
    $conv_info_stmt->bind_param("i", $conversation_id);
    $conv_info_stmt->execute();
    $conv_info = $conv_info_stmt->get_result()->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'message' => 'Message sent successfully',
        'conversation_id' => $conversation_id,
        'message_id' => $message_id,
        'data' => [
            'conversation_id' => $conversation_id,
            'sender_dept' => $sender_dept,
            'receiver_dept' => $receiver_dept,
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s')
        ]
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Failed: ' . $e->getMessage()]);
} finally {
    if (isset($conn)) $conn->close();
}
?>