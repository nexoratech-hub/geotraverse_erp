<?php
// ============================================
// FILE: backend/api/get_messages.php
// ============================================

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

// Get parameters
$conversation_id = isset($_GET['conversation_id']) ? (int)$_GET['conversation_id'] : null;
$department_id = isset($_GET['department_id']) ? (int)$_GET['department_id'] : null;
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

// If user_id is provided, get department from users table
if ($user_id && !$department_id) {
    $user_stmt = $conn->prepare("SELECT department_id FROM users WHERE id = ?");
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    if ($user_row = $user_result->fetch_assoc()) {
        $department_id = $user_row['department_id'];
    }
}

if (!$conversation_id || !$department_id) {
    echo json_encode(['success' => false, 'message' => 'conversation_id and department_id/user_id are required']);
    exit;
}

$department_names = [
    1 => 'Super Admin', 2 => 'Finance', 3 => 'Sales & Marketing',
    4 => 'Manager', 5 => 'Secretary', 6 => 'Bricks & Timber',
    7 => 'Aluminium', 8 => 'Town Planning', 9 => 'Architectural',
    10 => 'Survey', 11 => 'Construction', 12 => 'Hatimiliki'
];

try {
    // Get messages
    $query = "
        SELECT 
            m.id,
            m.conversation_id,
            m.sender_dept,
            m.receiver_dept,
            m.message,
            m.is_read,
            m.status,
            m.created_at,
            CASE 
                WHEN m.sender_dept = 1 THEN 'Super Admin'
                WHEN m.sender_dept = 2 THEN 'Finance'
                WHEN m.sender_dept = 3 THEN 'Sales & Marketing'
                WHEN m.sender_dept = 4 THEN 'Manager'
                WHEN m.sender_dept = 5 THEN 'Secretary'
                WHEN m.sender_dept = 6 THEN 'Bricks & Timber'
                WHEN m.sender_dept = 7 THEN 'Aluminium'
                WHEN m.sender_dept = 8 THEN 'Town Planning'
                WHEN m.sender_dept = 9 THEN 'Architectural'
                WHEN m.sender_dept = 10 THEN 'Survey'
                WHEN m.sender_dept = 11 THEN 'Construction'
                WHEN m.sender_dept = 12 THEN 'Hatimiliki'
                ELSE 'Unknown'
            END as sender_dept_name
        FROM messages m
        WHERE m.conversation_id = ?
        AND ((m.sender_dept = ? AND m.sender_deleted != 1) OR (m.receiver_dept = ? AND m.receiver_deleted != 1))
        ORDER BY m.created_at ASC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $conversation_id, $department_id, $department_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $messages = [];
    $unread_ids = [];
    
    while ($row = $result->fetch_assoc()) {
        $is_sent_by_current = ($row['sender_dept'] == $department_id);
        
        if (!$is_sent_by_current && $row['is_read'] == 0) {
            $unread_ids[] = $row['id'];
        }
        
        $messages[] = [
            'id' => (int)$row['id'],
            'conversation_id' => (int)$row['conversation_id'],
            'sender_dept' => (int)$row['sender_dept'],
            'sender_dept_name' => $row['sender_dept_name'],
            'receiver_dept' => (int)$row['receiver_dept'],
            'message' => $row['message'],
            'is_read' => (int)$row['is_read'],
            'is_sent_by_current' => $is_sent_by_current,
            'status' => $row['status'],
            'created_at' => $row['created_at']
        ];
    }
    
    // Mark unread messages as read
    if (!empty($unread_ids)) {
        $placeholders = implode(',', array_fill(0, count($unread_ids), '?'));
        $update_stmt = $conn->prepare("UPDATE messages SET is_read = 1, read_at = NOW() WHERE id IN ($placeholders)");
        $update_stmt->bind_param(str_repeat('i', count($unread_ids)), ...$unread_ids);
        $update_stmt->execute();
    }
    
    echo json_encode([
        'success' => true,
        'data' => $messages,
        'total_count' => count($messages)
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed: ' . $e->getMessage()]);
} finally {
    if (isset($conn)) $conn->close();
}
?>