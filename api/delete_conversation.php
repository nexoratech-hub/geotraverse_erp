<?php
// ==================== conversations/delete_conversation.php ====================
require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['conversation_id']) || empty($data['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

try {
    $user_stmt = $pdo->prepare("SELECT department_id FROM users WHERE id = ?");
    $user_stmt->execute([$data['user_id']]);
    $user = $user_stmt->fetch();
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit();
    }
    
    $dept_id = $user['department_id'];
    
    $msg_stmt = $pdo->prepare("
        SELECT m.id FROM messages m
        JOIN conversations c ON (m.sender_dept = c.department_a OR m.receiver_dept = c.department_a)
        WHERE c.id = ?
    ");
    $msg_stmt->execute([$data['conversation_id']]);
    $messages = $msg_stmt->fetchAll();
    
    foreach ($messages as $msg) {
        $del_stmt = $pdo->prepare("DELETE FROM message_status WHERE message_id = ? AND user_id = ?");
        $del_stmt->execute([$msg['id'], $data['user_id']]);
        
        $check_stmt = $pdo->prepare("SELECT COUNT(*) as count FROM message_status WHERE message_id = ?");
        $check_stmt->execute([$msg['id']]);
        $count = $check_stmt->fetch();
        
        if ($count['count'] == 0) {
            $del_msg = $pdo->prepare("DELETE FROM messages WHERE id = ?");
            $del_msg->execute([$msg['id']]);
        }
    }
    
    $conv_stmt = $pdo->prepare("DELETE FROM conversations WHERE id = ?");
    $conv_stmt->execute([$data['conversation_id']]);
    
    echo json_encode(['success' => true, 'message' => 'Conversation deleted']);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>