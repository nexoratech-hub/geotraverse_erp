<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit;
}

if (!isset($data->message) || empty(trim($data->message))) {
    echo json_encode(['success' => false, 'message' => 'Message is required']);
    exit;
}

try {
    $db->beginTransaction();
    
    $sender_department_id = null;
    $sender_user_id = null;
    $receiver_department_id = null;
    $receiver_user_id = null;
    $conversation_id = null;
    
    // CASE 1: Sending from department to department (preferred)
    if (isset($data->from_department_id) && isset($data->to_department_id)) {
        $sender_department_id = $data->from_department_id;
        $receiver_department_id = $data->to_department_id;
        
        // Get user IDs for departments
        $userQuery = "SELECT id FROM users WHERE department_id = ? AND is_active = 1 LIMIT 1";
        $userStmt = $db->prepare($userQuery);
        $userStmt->execute([$sender_department_id]);
        $senderUser = $userStmt->fetch(PDO::FETCH_ASSOC);
        $sender_user_id = $senderUser ? $senderUser['id'] : 1;
        
        $userStmt2 = $db->prepare($userQuery);
        $userStmt2->execute([$receiver_department_id]);
        $receiverUser = $userStmt2->fetch(PDO::FETCH_ASSOC);
        $receiver_user_id = $receiverUser ? $receiverUser['id'] : 1;
        
        // Find existing conversation between these departments
        $convQuery = "SELECT id FROM conversations 
                      WHERE (sender_dept = ? AND receiver_dept = ?) 
                      OR (sender_dept = ? AND receiver_dept = ?)
                      AND deleted_by_department = 0 AND deleted_by_admin = 0
                      LIMIT 1";
        $convStmt = $db->prepare($convQuery);
        $convStmt->execute([$sender_department_id, $receiver_department_id, $receiver_department_id, $sender_department_id]);
        
        if ($convStmt->rowCount() > 0) {
            $convRow = $convStmt->fetch(PDO::FETCH_ASSOC);
            $conversation_id = $convRow['id'];
        } else {
            // Create new conversation
            $subject = isset($data->subject) ? $data->subject : 'Message from Department ' . $sender_department_id;
            $createConv = "INSERT INTO conversations (user_id, admin_id, sender_dept, receiver_dept, subject, status, created_at, updated_at) 
                           VALUES (?, ?, ?, ?, ?, 'active', NOW(), NOW())";
            $createStmt = $db->prepare($createConv);
            $createStmt->execute([$sender_user_id, $receiver_user_id, $sender_department_id, $receiver_department_id, $subject]);
            $conversation_id = $db->lastInsertId();
        }
        
        // Insert message
        $query = "INSERT INTO messages 
                  (sender_dept, receiver_dept, conversation_id, sender_id, receiver_id, message, is_read, status, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, 0, 'sent', NOW())";
        $stmt = $db->prepare($query);
        $stmt->execute([
            $sender_department_id, 
            $receiver_department_id, 
            $conversation_id, 
            $sender_user_id, 
            $receiver_user_id, 
            $data->message
        ]);
        
        $message_id = $db->lastInsertId();
        $db->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Message sent successfully',
            'message_id' => $message_id,
            'conversation_id' => $conversation_id
        ]);
        exit;
    }
    
    // CASE 2: Sending from user to department
    else if (isset($data->sender_id) && isset($data->receiver_department_id)) {
        $sender_user_id = $data->sender_id;
        $receiver_department_id = $data->receiver_department_id;
        
        // Get sender's department
        $deptQuery = "SELECT department_id FROM users WHERE id = ?";
        $deptStmt = $db->prepare($deptQuery);
        $deptStmt->execute([$sender_user_id]);
        $senderDept = $deptStmt->fetch(PDO::FETCH_ASSOC);
        $sender_department_id = $senderDept ? $senderDept['department_id'] : null;
        
        // Get receiver user
        $userQuery = "SELECT id FROM users WHERE department_id = ? AND is_active = 1 LIMIT 1";
        $userStmt = $db->prepare($userQuery);
        $userStmt->execute([$receiver_department_id]);
        $receiverUser = $userStmt->fetch(PDO::FETCH_ASSOC);
        $receiver_user_id = $receiverUser ? $receiverUser['id'] : 1;
        
        // Find existing conversation
        $convQuery = "SELECT id FROM conversations 
                      WHERE (user_id = ? AND admin_id = ?) 
                      OR (user_id = ? AND admin_id = ?)
                      AND deleted_by_department = 0 AND deleted_by_admin = 0
                      LIMIT 1";
        $convStmt = $db->prepare($convQuery);
        $convStmt->execute([$sender_user_id, $receiver_user_id, $receiver_user_id, $sender_user_id]);
        
        if ($convStmt->rowCount() > 0) {
            $convRow = $convStmt->fetch(PDO::FETCH_ASSOC);
            $conversation_id = $convRow['id'];
            
            // Update department info if not set
            $updateConv = "UPDATE conversations SET sender_dept = ?, receiver_dept = ? WHERE id = ?";
            $updateStmt = $db->prepare($updateConv);
            $updateStmt->execute([$sender_department_id, $receiver_department_id, $conversation_id]);
        } else {
            $subject = isset($data->subject) ? $data->subject : 'New Message';
            $createConv = "INSERT INTO conversations (user_id, admin_id, sender_dept, receiver_dept, subject, status, created_at, updated_at) 
                           VALUES (?, ?, ?, ?, ?, 'active', NOW(), NOW())";
            $createStmt = $db->prepare($createConv);
            $createStmt->execute([$sender_user_id, $receiver_user_id, $sender_department_id, $receiver_department_id, $subject]);
            $conversation_id = $db->lastInsertId();
        }
        
        // Insert message
        $query = "INSERT INTO messages 
                  (sender_dept, receiver_dept, conversation_id, sender_id, receiver_id, message, is_read, status, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, 0, 'sent', NOW())";
        $stmt = $db->prepare($query);
        $stmt->execute([
            $sender_department_id, 
            $receiver_department_id, 
            $conversation_id, 
            $sender_user_id, 
            $receiver_user_id, 
            $data->message
        ]);
        
        $db->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Message sent successfully',
            'message_id' => $db->lastInsertId(),
            'conversation_id' => $conversation_id
        ]);
        exit;
    }
    
    // CASE 3: Using conversation_id only
    else if (isset($data->conversation_id) && isset($data->message) && isset($data->user_id)) {
        $sender_user_id = $data->user_id;
        $message = $data->message;
        $conversation_id = $data->conversation_id;
        
        // Get conversation details
        $convQuery = "SELECT * FROM conversations WHERE id = ?";
        $convStmt = $db->prepare($convQuery);
        $convStmt->execute([$conversation_id]);
        $conversation = $convStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$conversation) {
            echo json_encode(['success' => false, 'message' => 'Conversation not found']);
            exit;
        }
        
        $receiver_user_id = ($conversation['user_id'] == $sender_user_id) ? $conversation['admin_id'] : $conversation['user_id'];
        $sender_department_id = $conversation['sender_dept'];
        $receiver_department_id = $conversation['receiver_dept'];
        
        $query = "INSERT INTO messages 
                  (sender_dept, receiver_dept, conversation_id, sender_id, receiver_id, message, is_read, status, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, 0, 'sent', NOW())";
        $stmt = $db->prepare($query);
        $stmt->execute([
            $sender_department_id,
            $receiver_department_id,
            $conversation_id, 
            $sender_user_id, 
            $receiver_user_id, 
            $message
        ]);
        
        $db->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Message sent successfully',
            'message_id' => $db->lastInsertId()
        ]);
        exit;
    }
    
    else {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters. Need from_department_id+to_department_id or sender_id+receiver_department_id or conversation_id+user_id+message']);
        exit;
    }
    
} catch (PDOException $e) {
    $db->rollBack();
    error_log("Send message error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>