<?php
// ============================================
// FILE: backend/api/delete_message.php
// ============================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection
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

// Get input data
$data = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw_input = file_get_contents('php://input');
    $data = json_decode($raw_input, true);
    if (!$data) {
        $data = $_POST;
    }
} else {
    $data = $_GET;
}

// Get parameters
$message_id = isset($data['message_id']) ? (int)$data['message_id'] : null;
$user_id = isset($data['user_id']) ? (int)$data['user_id'] : null;
$department_id = isset($data['department_id']) ? (int)$data['department_id'] : null;

// Super Admin user_id = 1
if ($user_id == 1 && !$department_id) {
    $department_id = 1;
}

if (!$message_id) {
    echo json_encode(['success' => false, 'message' => 'message_id is required']);
    exit;
}

if (!$department_id) {
    echo json_encode(['success' => false, 'message' => 'department_id or user_id is required']);
    exit;
}

try {
    // First, check who the sender and receiver are
    $check_stmt = $conn->prepare("
        SELECT sender_dept, receiver_dept FROM messages WHERE id = ?
    ");
    $check_stmt->bind_param("i", $message_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Message not found']);
        exit;
    }
    
    $msg = $result->fetch_assoc();
    
    // Soft delete based on who is deleting
    if ($msg['sender_dept'] == $department_id) {
        // Sender is deleting - mark sender_deleted = 1
        $update_stmt = $conn->prepare("
            UPDATE messages SET sender_deleted = 1, deleted_at = NOW() WHERE id = ?
        ");
    } else {
        // Receiver is deleting - mark receiver_deleted = 1
        $update_stmt = $conn->prepare("
            UPDATE messages SET receiver_deleted = 1, deleted_at = NOW() WHERE id = ?
        ");
    }
    
    $update_stmt->bind_param("i", $message_id);
    $update_stmt->execute();
    
    // Also add to recycle bin for Super Admin
    if ($department_id == 1) {
        $recycle_stmt = $conn->prepare("
            INSERT INTO recycle_bin (original_table, original_id, deleted_data, deleted_by_department_id, deleted_by_admin, deleted_at) 
            SELECT 'messages', ?, JSON_OBJECT('message', message, 'sender_dept', sender_dept, 'receiver_dept', receiver_dept), ?, 1, NOW()
            FROM messages WHERE id = ?
        ");
        $recycle_stmt->bind_param("iii", $message_id, $department_id, $message_id);
        $recycle_stmt->execute();
    }
    
    echo json_encode(['success' => true, 'message' => 'Message deleted successfully']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to delete message: ' . $e->getMessage()]);
} finally {
    if (isset($conn)) $conn->close();
}
?>