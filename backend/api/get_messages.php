<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$conversation_id = isset($_GET['conversation_id']) ? intval($_GET['conversation_id']) : null;
$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : null;
$from_department = isset($_GET['from_department']) ? intval($_GET['from_department']) : null;
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;

$departmentNames = [
    1 => 'Super Admin', 2 => 'Finance', 3 => 'Sales & Marketing', 4 => 'Manager',
    5 => 'Secretary', 6 => 'Bricks & Timber', 7 => 'Aluminium', 8 => 'Town Planning',
    9 => 'Architectural', 10 => 'Survey', 11 => 'Construction', 12 => 'Hatimiliki'
];

try {
    if ($conversation_id) {
        // Get messages for a specific conversation
        $query = "SELECT 
                    m.id,
                    m.message,
                    m.is_read,
                    m.status,
                    m.created_at,
                    m.sender_dept,
                    m.receiver_dept,
                    m.sender_id,
                    m.receiver_id,
                    m.sender_deleted,
                    m.receiver_deleted,
                    u_sender.name as sender_name,
                    u_receiver.name as receiver_name,
                    d_sender.name as sender_department_name,
                    d_receiver.name as receiver_department_name
                  FROM messages m
                  LEFT JOIN users u_sender ON m.sender_id = u_sender.id
                  LEFT JOIN users u_receiver ON m.receiver_id = u_receiver.id
                  LEFT JOIN departments d_sender ON m.sender_dept = d_sender.id
                  LEFT JOIN departments d_receiver ON m.receiver_dept = d_receiver.id
                  WHERE m.conversation_id = ?
                    AND m.sender_deleted = 0 
                    AND m.receiver_deleted = 0
                  ORDER BY m.created_at ASC";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$conversation_id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Mark messages as read if user_id provided
        if ($user_id) {
            $updateQuery = "UPDATE messages SET is_read = 1, read_at = NOW() 
                           WHERE conversation_id = ? AND receiver_id = ? AND is_read = 0";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->execute([$conversation_id, $user_id]);
        } else if ($department_id) {
            $updateQuery = "UPDATE messages SET is_read = 1, read_at = NOW() 
                           WHERE conversation_id = ? AND receiver_dept = ? AND is_read = 0";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->execute([$conversation_id, $department_id]);
        }
        
        // Add department names for display
        foreach ($messages as &$msg) {
            if ($msg['sender_dept']) {
                $msg['sender_department'] = isset($departmentNames[$msg['sender_dept']]) ? $departmentNames[$msg['sender_dept']] : 'Department ' . $msg['sender_dept'];
            } else if ($msg['sender_name']) {
                $msg['sender_department'] = $msg['sender_name'];
            } else {
                $msg['sender_department'] = 'Unknown';
            }
        }
        
        echo json_encode(['success' => true, 'data' => $messages]);
        
    } else if ($department_id && $from_department) {
        // Get messages between two departments
        $query = "SELECT 
                    m.id,
                    m.message,
                    m.is_read,
                    m.status,
                    m.created_at,
                    m.sender_dept,
                    m.receiver_dept,
                    m.sender_id,
                    m.receiver_id,
                    m.sender_deleted,
                    m.receiver_deleted
                  FROM messages m
                  WHERE ((m.sender_dept = ? AND m.receiver_dept = ?) OR (m.sender_dept = ? AND m.receiver_dept = ?))
                    AND m.sender_deleted = 0 
                    AND m.receiver_deleted = 0
                  ORDER BY m.created_at ASC";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$department_id, $from_department, $from_department, $department_id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $messages]);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Conversation ID or department parameters required', 'data' => []]);
    }
    
} catch (PDOException $e) {
    error_log("Get messages error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'data' => []]);
}
?>