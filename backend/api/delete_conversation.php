<?php
// ============================================
// FILE: backend/api/delete_conversation.php
// SOFT DELETE PER DEPARTMENT
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
$conversation_id = isset($data['conversation_id']) ? (int)$data['conversation_id'] : null;
$user_id = isset($data['user_id']) ? (int)$data['user_id'] : null;
$department_id = isset($data['department_id']) ? (int)$data['department_id'] : null;

// Map department ID to column name
$department_column_map = [
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

// Super Admin user_id = 1 -> department_id = 1
if ($user_id == 1 && !$department_id) {
    $department_id = 1;
}

if (!$conversation_id) {
    echo json_encode(['success' => false, 'message' => 'conversation_id is required']);
    exit;
}

if (!$department_id || !isset($department_column_map[$department_id])) {
    echo json_encode(['success' => false, 'message' => 'Valid department_id is required']);
    exit;
}

$column_name = $department_column_map[$department_id];

try {
    // Check if conversation exists and belongs to this department
    $check_stmt = $conn->prepare("
        SELECT id, sender_dept, receiver_dept, subject FROM conversations 
        WHERE id = ? AND (sender_dept = ? OR receiver_dept = ?)
    ");
    $check_stmt->bind_param("iii", $conversation_id, $department_id, $department_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Conversation not found or access denied']);
        exit;
    }
    
    // Check if the column exists
    $col_check = $conn->query("SHOW COLUMNS FROM conversations LIKE '$column_name'");
    if ($col_check->num_rows === 0) {
        // Column doesn't exist, add it
        $conn->query("ALTER TABLE conversations ADD COLUMN $column_name TINYINT(1) DEFAULT 0");
    }
    
    // Mark as deleted for this specific department only
    $update_stmt = $conn->prepare("
        UPDATE conversations SET $column_name = 1, deleted_at = NOW() WHERE id = ?
    ");
    $update_stmt->bind_param("i", $conversation_id);
    $update_stmt->execute();
    
    // Add to recycle bin for tracking
    $recycle_stmt = $conn->prepare("
        INSERT INTO recycle_bin (original_table, original_id, deleted_data, deleted_by_department_id, deleted_by_admin, deleted_at) 
        VALUES ('conversations', ?, ?, ?, ?, NOW())
    ");
    $deleted_data = json_encode([
        'conversation_id' => $conversation_id,
        'deleted_by_department' => $department_id,
        'deleted_by_name' => $department_names[$department_id] ?? 'Unknown'
    ]);
    $is_admin = ($department_id == 1) ? 1 : 0;
    $dept_id_for_recycle = ($department_id == 1) ? null : $department_id;
    $recycle_stmt->bind_param("issi", $conversation_id, $deleted_data, $dept_id_for_recycle, $is_admin);
    $recycle_stmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'Conversation deleted from your view']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to delete conversation: ' . $e->getMessage()]);
} finally {
    if (isset($conn)) $conn->close();
}
?>