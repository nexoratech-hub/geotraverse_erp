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

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$conversation_id = isset($input['conversation_id']) ? (int)$input['conversation_id'] : null;
$department_id = isset($input['department_id']) ? (int)$input['department_id'] : null;
$user_id = isset($input['user_id']) ? (int)$input['user_id'] : null;

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
    echo json_encode(['success' => false, 'message' => 'conversation_id and department_id are required']);
    exit;
}

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

$delete_column = $delete_column_map[$department_id] ?? 'deleted_by_super_admin';

try {
    // Set the deleted flag for this department
    $update_stmt = $conn->prepare("UPDATE conversations SET $delete_column = 1, updated_at = NOW() WHERE id = ?");
    $update_stmt->bind_param("i", $conversation_id);
    $update_stmt->execute();
    
    // Also mark all messages in this conversation as deleted for this department
    $delete_msgs_stmt = $conn->prepare("
        UPDATE messages 
        SET deleted_by_sender = 1 
        WHERE conversation_id = ? AND sender_dept = ?
    ");
    $delete_msgs_stmt->bind_param("ii", $conversation_id, $department_id);
    $delete_msgs_stmt->execute();
    
    // Check if the conversation is now considered "hidden" for this department
    // Get the conversation to see if it should be completely removed from view
    $check_stmt = $conn->prepare("
        SELECT * FROM conversations WHERE id = ?
    ");
    $check_stmt->bind_param("i", $conversation_id);
    $check_stmt->execute();
    $conv = $check_stmt->get_result()->fetch_assoc();
    
    // Determine if the conversation should be permanently hidden
    $should_hide = true;
    
    // If the other department still has it, we keep the conversation record but flagged as deleted
    // Get the other department
    $other_dept = ($conv['sender_dept'] == $department_id) ? $conv['receiver_dept'] : $conv['sender_dept'];
    $other_delete_column = $delete_column_map[$other_dept] ?? 'deleted_by_super_admin';
    
    $other_check = $conn->prepare("SELECT $other_delete_column as other_deleted FROM conversations WHERE id = ?");
    $other_check->bind_param("i", $conversation_id);
    $other_check->execute();
    $other_result = $other_check->get_result()->fetch_assoc();
    
    $other_deleted = $other_result['other_deleted'] ?? 0;
    
    // If both departments have deleted the conversation, we can soft-delete it completely
    if ($other_deleted == 1) {
        $hide_stmt = $conn->prepare("UPDATE conversations SET status = 'deleted' WHERE id = ?");
        $hide_stmt->bind_param("i", $conversation_id);
        $hide_stmt->execute();
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Conversation deleted successfully',
        'conversation_id' => $conversation_id,
        'hidden' => true
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed: ' . $e->getMessage()]);
} finally {
    if (isset($conn)) $conn->close();
}
?>