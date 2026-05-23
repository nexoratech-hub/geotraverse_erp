<?php
// ============================================
// FILE: backend/api/send_message.php
// FINAL VERSION - Inakubali vigezo vyote
// ============================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
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

// Get input data
$data = [];
$raw_input = file_get_contents('php://input');
$json_data = json_decode($raw_input, true);

if ($json_data) {
    $data = $json_data;
} elseif (!empty($_POST)) {
    $data = $_POST;
} elseif (!empty($_GET)) {
    $data = $_GET;
}

// ============================================
// FUNCTION to get department from user_id
// ============================================
function getDeptFromUserId($conn, $user_id) {
    $stmt = $conn->prepare("SELECT department_id FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['department_id'];
    }
    return null;
}

// ============================================
// GET SENDER DEPARTMENT
// ============================================
$sender_dept = null;

if (isset($data['sender_dept'])) {
    $sender_dept = (int)$data['sender_dept'];
} elseif (isset($data['from_department_id'])) {
    $sender_dept = (int)$data['from_department_id'];
} elseif (isset($data['user_id'])) {
    $user_id = (int)$data['user_id'];
    $sender_dept = getDeptFromUserId($conn, $user_id);
    if ($user_id == 1 && !$sender_dept) $sender_dept = 1;
} elseif (isset($data['department_id']) && !isset($data['receiver_dept']) && !isset($data['to_department_id'])) {
    $sender_dept = (int)$data['department_id'];
}

// ============================================
// GET RECEIVER DEPARTMENT
// ============================================
$receiver_dept = null;

if (isset($data['receiver_dept'])) {
    $receiver_dept = (int)$data['receiver_dept'];
} elseif (isset($data['to_department_id'])) {
    $receiver_dept = (int)$data['to_department_id'];
} elseif (isset($data['department_id']) && isset($data['user_id']) && $data['user_id'] == 1) {
    $receiver_dept = (int)$data['department_id'];
} elseif (isset($data['msgTo'])) {
    $receiver_dept = (int)$data['msgTo'];
} elseif (isset($data['to'])) {
    $receiver_dept = (int)$data['to'];
}

// ============================================
// GET CONVERSATION ID
// ============================================
$conversation_id = isset($data['conversation_id']) ? (int)$data['conversation_id'] : null;

// ============================================
// GET MESSAGE
// ============================================
$message = '';
if (isset($data['message'])) {
    $message = trim($data['message']);
} elseif (isset($data['msg'])) {
    $message = trim($data['msg']);
}

$subject = isset($data['subject']) ? trim($data['subject']) : 'New Message';

// ============================================
// IF CONVERSATION_ID EXISTS BUT NO RECEIVER, GET FROM CONVERSATION
// ============================================
if ($conversation_id && !$receiver_dept && $sender_dept) {
    $conv_stmt = $conn->prepare("SELECT sender_dept, receiver_dept FROM conversations WHERE id = ?");
    $conv_stmt->bind_param("i", $conversation_id);
    $conv_stmt->execute();
    $conv_result = $conv_stmt->get_result();
    if ($conv_row = $conv_result->fetch_assoc()) {
        if ($conv_row['sender_dept'] == $sender_dept) {
            $receiver_dept = $conv_row['receiver_dept'];
        } else {
            $receiver_dept = $conv_row['sender_dept'];
        }
    }
}

// ============================================
// VALIDATION
// ============================================

if (!$sender_dept) {
    echo json_encode([
        'success' => false, 
        'message' => 'Unable to identify sender department',
        'debug' => $data
    ]);
    exit;
}

if (!$receiver_dept) {
    echo json_encode([
        'success' => false, 
        'message' => 'Please select a valid department to send message. Valid departments: 1-12',
        'debug' => $data
    ]);
    exit;
}

if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
    exit;
}

if ($sender_dept == $receiver_dept) {
    echo json_encode(['success' => false, 'message' => 'Cannot send message to the same department']);
    exit;
}

$department_names = [
    1 => 'Super Admin', 2 => 'Finance', 3 => 'Sales & Marketing',
    4 => 'Manager', 5 => 'Secretary', 6 => 'Bricks & Timber',
    7 => 'Aluminium', 8 => 'Town Planning', 9 => 'Architectural',
    10 => 'Survey', 11 => 'Construction', 12 => 'Hatimiliki'
];

try {
    // FIND OR CREATE CONVERSATION
    if (!$conversation_id) {
        $find_stmt = $conn->prepare("
            SELECT id FROM conversations 
            WHERE (sender_dept = ? AND receiver_dept = ?) 
               OR (sender_dept = ? AND receiver_dept = ?)
            AND deleted_by_department != 1 AND deleted_by_admin != 1
            LIMIT 1
        ");
        $find_stmt->bind_param("iiii", $sender_dept, $receiver_dept, $receiver_dept, $sender_dept);
        $find_stmt->execute();
        $find_result = $find_stmt->get_result();
        
        if ($find_result->num_rows > 0) {
            $row = $find_result->fetch_assoc();
            $conversation_id = $row['id'];
        } else {
            $insert_conv = $conn->prepare("
                INSERT INTO conversations (sender_dept, receiver_dept, subject, status, created_at, updated_at) 
                VALUES (?, ?, ?, 'active', NOW(), NOW())
            ");
            $insert_conv->bind_param("iis", $sender_dept, $receiver_dept, $subject);
            $insert_conv->execute();
            $conversation_id = $conn->insert_id;
        }
    }
    
    if (!$conversation_id) {
        throw new Exception("Failed to create or find conversation");
    }
    
    // INSERT MESSAGE
    $insert_msg = $conn->prepare("
        INSERT INTO messages (conversation_id, sender_dept, receiver_dept, message, is_read, status, created_at) 
        VALUES (?, ?, ?, ?, 0, 'sent', NOW())
    ");
    $insert_msg->bind_param("iiis", $conversation_id, $sender_dept, $receiver_dept, $message);
    $insert_msg->execute();
    
    // UPDATE CONVERSATION
    $update_conv = $conn->prepare("UPDATE conversations SET updated_at = NOW() WHERE id = ?");
    $update_conv->bind_param("i", $conversation_id);
    $update_conv->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Message sent successfully',
        'data' => [
            'message_id' => $conn->insert_id,
            'conversation_id' => $conversation_id,
            'from_department' => $sender_dept,
            'from_department_name' => $department_names[$sender_dept],
            'to_department' => $receiver_dept,
            'to_department_name' => $department_names[$receiver_dept],
            'message' => $message
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed: ' . $e->getMessage()]);
} finally {
    $conn->close();
}
?>