<?php
// ==================== conversations/get_conversations.php ====================
require_once '../config/database.php';

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'User ID required', 'data' => []]);
    exit();
}

try {
    $user_stmt = $pdo->prepare("SELECT department_id FROM users WHERE id = ?");
    $user_stmt->execute([$user_id]);
    $user = $user_stmt->fetch();
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found', 'data' => []]);
        exit();
    }
    
    $dept_id = $user['department_id'];
    
    $stmt = $pdo->prepare("
        SELECT 
            c.id as conversation_id,
            CASE 
                WHEN c.department_a = ? THEN c.department_b
                ELSE c.department_a
            END as other_department_id,
            d.name as other_department_name,
            c.last_message,
            c.last_message_time,
            (
                SELECT COUNT(*) 
                FROM messages m
                JOIN message_status ms ON m.id = ms.message_id
                WHERE m.receiver_dept = ? AND ms.user_id = ? AND ms.is_read = 0
            ) as unread_count
        FROM conversations c
        JOIN departments d ON d.id = CASE 
            WHEN c.department_a = ? THEN c.department_b
            ELSE c.department_a
        END
        WHERE c.department_a = ? OR c.department_b = ?
        ORDER BY c.last_message_time DESC
    ");
    
    $stmt->execute([$dept_id, $dept_id, $user_id, $dept_id, $dept_id, $dept_id]);
    $conversations = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'data' => $conversations]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'data' => []]);
}
?>