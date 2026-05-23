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

// Get parameters
$department_id = isset($_GET['department_id']) ? (int)$_GET['department_id'] : null;
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

if ($user_id == 1 && !$department_id) {
    $department_id = 1;
}

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

if (!$department_id) {
    echo json_encode(['success' => false, 'message' => 'department_id is required']);
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

$delete_column = $delete_column_map[$department_id] ?? 'deleted_by_super_admin';

try {
    // Get ALL conversations where this department is involved and not deleted by them
    $query = "
        SELECT 
            c.id as conversation_id,
            c.sender_dept,
            c.receiver_dept,
            c.subject,
            c.status,
            c.created_at,
            c.updated_at,
            CASE 
                WHEN c.sender_dept = ? THEN c.receiver_dept
                ELSE c.sender_dept
            END as other_department_id
        FROM conversations c
        WHERE (c.sender_dept = ? OR c.receiver_dept = ?)
        AND (c.$delete_column != 1 OR c.$delete_column IS NULL)
        ORDER BY c.updated_at DESC
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiiii", $department_id, $department_id, $department_id, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $conversations = [];
    $total_unread = 0;
    
    while ($row = $result->fetch_assoc()) {
        $other_dept_id = $row['other_department_id'];
        $other_dept_name = $department_names[$other_dept_id] ?? 'Unknown';
        
        // Get latest message from THIS conversation (not deleted by current user)
        $msg_query = "
            SELECT message, created_at 
            FROM messages 
            WHERE conversation_id = ? 
            AND ((sender_dept = ? AND sender_deleted != 1) OR (receiver_dept = ? AND receiver_deleted != 1))
            ORDER BY created_at DESC 
            LIMIT 1
        ";
        $msg_stmt = $conn->prepare($msg_query);
        $msg_stmt->bind_param("iii", $row['conversation_id'], $department_id, $department_id);
        $msg_stmt->execute();
        $msg_result = $msg_stmt->get_result();
        $latest_msg = $msg_result->fetch_assoc();
        
        $last_message = $latest_msg['message'] ?? 'No messages yet';
        $last_message_time = $latest_msg['created_at'] ?? $row['created_at'];
        
        // Get unread count for this conversation
        $unread_stmt = $conn->prepare("
            SELECT COUNT(*) as unread 
            FROM messages 
            WHERE conversation_id = ? 
            AND receiver_dept = ? 
            AND is_read = 0
            AND receiver_deleted != 1
        ");
        $unread_stmt->bind_param("ii", $row['conversation_id'], $department_id);
        $unread_stmt->execute();
        $unread_result = $unread_stmt->get_result();
        $unread_data = $unread_result->fetch_assoc();
        $unread_count = $unread_data['unread'] ?? 0;
        
        $total_unread += $unread_count;
        
        $conversations[] = [
            'conversation_id' => (int)$row['conversation_id'],
            'other_department_id' => $other_dept_id,
            'other_department_name' => $other_dept_name,
            'subject' => $row['subject'] ?? 'No subject',
            'last_message' => $last_message,
            'last_message_time' => $last_message_time,
            'unread_count' => $unread_count,
            'status' => $row['status'] ?? 'active',
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $conversations,
        'total_unread' => $total_unread,
        'total_count' => count($conversations)
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed: ' . $e->getMessage()]);
} finally {
    if (isset($conn)) $conn->close();
}
?>