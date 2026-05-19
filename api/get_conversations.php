<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;
    
    if ($department_id === 0) {
        echo json_encode(['success' => false, 'message' => 'Department ID required']);
        exit;
    }
    
    // Get all unique conversations for this department
    $query = "SELECT 
                c.id as conversation_id,
                c.subject,
                CASE 
                    WHEN m.sender_dept = :dept_id THEN m.receiver_dept
                    ELSE m.sender_dept
                END as other_department_id,
                d.name as other_department_name,
                COUNT(CASE WHEN m.receiver_dept = :dept_id AND m.is_read = 0 THEN 1 END) as unread_count,
                MAX(m.created_at) as last_message_time,
                (SELECT message FROM messages m2 
                 WHERE m2.conversation_id = c.id 
                 AND ((m2.sender_dept = :dept_id AND m2.sender_deleted = 0) OR (m2.receiver_dept = :dept_id AND m2.receiver_deleted = 0))
                 ORDER BY m2.created_at DESC LIMIT 1) as last_message
            FROM conversations c
            INNER JOIN messages m ON c.id = m.conversation_id
            LEFT JOIN departments d ON d.id = CASE 
                WHEN m.sender_dept = :dept_id THEN m.receiver_dept
                ELSE m.sender_dept
            END
            WHERE (m.sender_dept = :dept_id OR m.receiver_dept = :dept_id)
            AND ((m.sender_dept = :dept_id AND m.sender_deleted = 0) OR (m.receiver_dept = :dept_id AND m.receiver_deleted = 0))
            GROUP BY c.id, c.subject, other_department_id, other_department_name
            ORDER BY last_message_time DESC";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':dept_id', $department_id);
    $stmt->execute();
    
    $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $conversations
    ]);
}
?>