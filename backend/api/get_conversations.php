<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : null;
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;

if (!$department_id && !$user_id) {
    echo json_encode(['success' => false, 'message' => 'Department ID or User ID required', 'data' => []]);
    exit;
}

$departmentNames = [
    1 => 'Super Admin', 2 => 'Finance', 3 => 'Sales & Marketing', 4 => 'Manager',
    5 => 'Secretary', 6 => 'Bricks & Timber', 7 => 'Aluminium', 8 => 'Town Planning',
    9 => 'Architectural', 10 => 'Survey', 11 => 'Construction', 12 => 'Hatimiliki'
];

try {
    if ($department_id) {
        // Get conversations for a department
        $query = "SELECT 
                    c.id as conversation_id,
                    c.subject,
                    c.status,
                    c.created_at,
                    c.sender_dept,
                    c.receiver_dept,
                    MAX(m.created_at) as last_message_time,
                    (SELECT message FROM messages WHERE conversation_id = c.id AND sender_deleted = 0 AND receiver_deleted = 0 ORDER BY created_at DESC LIMIT 1) as last_message,
                    COUNT(CASE WHEN m.receiver_dept = ? AND m.is_read = 0 AND m.sender_deleted = 0 AND m.receiver_deleted = 0 THEN 1 END) as unread_count,
                    CASE 
                        WHEN c.sender_dept = ? THEN c.receiver_dept
                        ELSE c.sender_dept
                    END as other_department_id
                  FROM conversations c
                  LEFT JOIN messages m ON c.id = m.conversation_id AND m.sender_deleted = 0 AND m.receiver_deleted = 0
                  WHERE (c.sender_dept = ? OR c.receiver_dept = ?)
                    AND c.deleted_by_department = 0 
                    AND c.deleted_by_admin = 0
                  GROUP BY c.id
                  ORDER BY last_message_time DESC";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$department_id, $department_id, $department_id, $department_id]);
        $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($conversations as &$conv) {
            $otherId = $conv['other_department_id'];
            $conv['other_department_name'] = isset($departmentNames[$otherId]) ? $departmentNames[$otherId] : 'Department ' . $otherId;
            $conv['unread_count'] = intval($conv['unread_count']);
        }
        
        echo json_encode(['success' => true, 'data' => $conversations, 'department_id' => $department_id]);
        
    } else if ($user_id) {
        // Get conversations for a user
        $query = "SELECT 
                    c.id as conversation_id,
                    c.subject,
                    c.status,
                    c.created_at,
                    c.sender_dept,
                    c.receiver_dept,
                    MAX(m.created_at) as last_message_time,
                    (SELECT message FROM messages WHERE conversation_id = c.id AND sender_deleted = 0 AND receiver_deleted = 0 ORDER BY created_at DESC LIMIT 1) as last_message,
                    COUNT(CASE WHEN m.receiver_id = ? AND m.is_read = 0 AND m.sender_deleted = 0 AND m.receiver_deleted = 0 THEN 1 END) as unread_count,
                    CASE 
                        WHEN c.user_id = ? THEN c.admin_id
                        ELSE c.user_id
                    END as other_user_id,
                    CASE
                        WHEN c.sender_dept != ? AND c.sender_dept IS NOT NULL THEN c.sender_dept
                        WHEN c.receiver_dept != ? AND c.receiver_dept IS NOT NULL THEN c.receiver_dept
                        ELSE NULL
                    END as other_department_id
                  FROM conversations c
                  LEFT JOIN messages m ON c.id = m.conversation_id AND m.sender_deleted = 0 AND m.receiver_deleted = 0
                  WHERE (c.user_id = ? OR c.admin_id = ?)
                    AND c.deleted_by_user_id IS NULL
                  GROUP BY c.id
                  ORDER BY last_message_time DESC";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
        $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($conversations as &$conv) {
            $otherId = $conv['other_user_id'];
            $userQuery = "SELECT name, department_id FROM users WHERE id = ?";
            $userStmt = $db->prepare($userQuery);
            $userStmt->execute([$otherId]);
            $user = $userStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                $conv['other_department_name'] = isset($departmentNames[$user['department_id']]) ? $departmentNames[$user['department_id']] : 'Department';
                $conv['other_department_name'] .= ' - ' . $user['name'];
                $conv['other_department_id'] = $user['department_id'];
            } else if ($conv['other_department_id']) {
                $conv['other_department_name'] = isset($departmentNames[$conv['other_department_id']]) ? $departmentNames[$conv['other_department_id']] : 'Department ' . $conv['other_department_id'];
            } else {
                $conv['other_department_name'] = 'Unknown User';
                $conv['other_department_id'] = 0;
            }
            $conv['unread_count'] = intval($conv['unread_count']);
        }
        
        echo json_encode(['success' => true, 'data' => $conversations, 'user_id' => $user_id]);
    }
    
} catch (PDOException $e) {
    error_log("Get conversations error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'data' => []]);
}
?>