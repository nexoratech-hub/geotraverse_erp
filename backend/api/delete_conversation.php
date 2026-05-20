<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, DELETE, OPTIONS');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit;
}

$conversation_id = isset($data->conversation_id) ? intval($data->conversation_id) : null;
$department_id = isset($data->department_id) ? intval($data->department_id) : null;
$user_id = isset($data->user_id) ? intval($data->user_id) : null;

if (!$conversation_id) {
    echo json_encode(['success' => false, 'message' => 'Conversation ID required']);
    exit;
}

try {
    $db->beginTransaction();
    
    // Get conversation details
    $convQuery = "SELECT * FROM conversations WHERE id = ?";
    $convStmt = $db->prepare($convQuery);
    $convStmt->execute([$conversation_id]);
    $conversation = $convStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$conversation) {
        echo json_encode(['success' => false, 'message' => 'Conversation not found']);
        exit;
    }
    
    // Get all messages in this conversation for recycle bin
    $msgQuery = "SELECT * FROM messages WHERE conversation_id = ?";
    $msgStmt = $db->prepare($msgQuery);
    $msgStmt->execute([$conversation_id]);
    $messages = $msgStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // SOFT DELETE: Mark conversation as deleted
    if ($department_id) {
        // Check if this department is part of the conversation
        if ($conversation['sender_dept'] == $department_id || $conversation['receiver_dept'] == $department_id) {
            $updateQuery = "UPDATE conversations SET deleted_by_department = 1, deleted_by_user_id = NULL, deleted_at = NOW() WHERE id = ?";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->execute([$conversation_id]);
            
            // Soft delete all messages for this department
            $updateMsgQuery = "UPDATE messages SET 
                              sender_deleted = CASE WHEN sender_dept = ? THEN 1 ELSE sender_deleted END,
                              receiver_deleted = CASE WHEN receiver_dept = ? THEN 1 ELSE receiver_deleted END,
                              deleted_at = NOW()
                              WHERE conversation_id = ?";
            $updateMsgStmt = $db->prepare($updateMsgQuery);
            $updateMsgStmt->execute([$department_id, $department_id, $conversation_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'You are not authorized to delete this conversation']);
            exit;
        }
    } else if ($user_id) {
        // Check if user is part of the conversation
        if ($conversation['user_id'] == $user_id || $conversation['admin_id'] == $user_id) {
            $updateQuery = "UPDATE conversations SET deleted_by_user_id = ?, deleted_at = NOW() WHERE id = ?";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->execute([$user_id, $conversation_id]);
            
            // Soft delete all messages for this user
            $updateMsgQuery = "UPDATE messages SET 
                              sender_deleted = CASE WHEN sender_id = ? THEN 1 ELSE sender_deleted END,
                              receiver_deleted = CASE WHEN receiver_id = ? THEN 1 ELSE receiver_deleted END,
                              deleted_at = NOW()
                              WHERE conversation_id = ?";
            $updateMsgStmt = $db->prepare($updateMsgQuery);
            $updateMsgStmt->execute([$user_id, $user_id, $conversation_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'You are not authorized to delete this conversation']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Department ID or User ID required']);
        exit;
    }
    
    // Insert conversation into recycle bin
    $recycleQuery = "INSERT INTO recycle_bin 
                    (original_table, original_id, deleted_data, deleted_by_department_id, deleted_by_user_id, deleted_by_admin, deleted_at) 
                    VALUES (?, ?, ?, ?, ?, 0, NOW())";
    $recycleStmt = $db->prepare($recycleQuery);
    $recycleStmt->execute([
        'conversations',
        $conversation_id,
        json_encode($conversation),
        $department_id,
        $user_id
    ]);
    
    $db->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Conversation moved to recycle bin',
        'soft_deleted' => true
    ]);
    
} catch (PDOException $e) {
    $db->rollBack();
    error_log("Delete conversation error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>