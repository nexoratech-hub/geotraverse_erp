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
    $conversation_id = isset($_GET['conversation_id']) ? intval($_GET['conversation_id']) : 0;
    
    if ($department_id === 0 && $conversation_id === 0) {
        echo json_encode(['success' => false, 'message' => 'Department ID or Conversation ID required']);
        exit;
    }
    
    if ($conversation_id > 0) {
        // Get messages for specific conversation
        $query = "SELECT m.*, 
                  d1.name as sender_department_name,
                  d2.name as receiver_department_name
                  FROM messages m
                  LEFT JOIN departments d1 ON m.sender_dept = d1.id
                  LEFT JOIN departments d2 ON m.receiver_dept = d2.id
                  WHERE m.conversation_id = :conversation_id
                  ORDER BY m.created_at ASC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':conversation_id', $conversation_id);
    } else {
        // Get messages between current department and the other department
        $query = "SELECT m.*, 
                  d1.name as sender_department_name,
                  d2.name as receiver_department_name
                  FROM messages m
                  LEFT JOIN departments d1 ON m.sender_dept = d1.id
                  LEFT JOIN departments d2 ON m.receiver_dept = d2.id
                  WHERE (m.sender_dept = :dept_id OR m.receiver_dept = :dept_id)
                  AND ((m.sender_dept = :dept_id AND m.sender_deleted = 0) OR (m.receiver_dept = :dept_id AND m.receiver_deleted = 0))
                  ORDER BY m.created_at ASC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':dept_id', $department_id);
    }
    
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $messages
    ]);
}
?>