<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, DELETE, OPTIONS');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!$data || !isset($data->message_id)) {
    echo json_encode(['success' => false, 'message' => 'Message ID required']);
    exit;
}

$message_id = $data->message_id;
$department_id = isset($data->department_id) ? intval($data->department_id) : null;
$user_id = isset($data->user_id) ? intval($data->user_id) : null;

try {
    $db->beginTransaction();
    
    // Get message details
    $msgQuery = "SELECT * FROM messages WHERE id = ?";
    $msgStmt = $db->prepare($msgQuery);
    $msgStmt->execute([$message_id]);
    $message = $msgStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$message) {
        echo json_encode(['success' => false, 'message' => 'Message not found']);
        exit;
    }
    
    // Check if message is already deleted
    if ($message['sender_deleted'] == 1 && $message['receiver_deleted'] == 1) {
        echo json_encode(['success' => false, 'message' => 'Message already deleted']);
        exit;
    }
    
    // SOFT DELETE: Mark as deleted by the appropriate party
    if ($department_id) {
        // Delete by department
        if ($message['sender_dept'] == $department_id) {
            $updateQuery = "UPDATE messages SET sender_deleted = 1, deleted_at = NOW() WHERE id = ?";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->execute([$message_id]);
        } else if ($message['receiver_dept'] == $department_id) {
            $updateQuery = "UPDATE messages SET receiver_deleted = 1, deleted_at = NOW() WHERE id = ?";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->execute([$message_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'You are not authorized to delete this message']);
            exit;
        }
    } else if ($user_id) {
        // Delete by user
        if ($message['sender_id'] == $user_id) {
            $updateQuery = "UPDATE messages SET sender_deleted = 1, deleted_at = NOW() WHERE id = ?";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->execute([$message_id]);
        } else if ($message['receiver_id'] == $user_id) {
            $updateQuery = "UPDATE messages SET receiver_deleted = 1, deleted_at = NOW() WHERE id = ?";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->execute([$message_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'You are not authorized to delete this message']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Department ID or User ID required']);
        exit;
    }
    
    // Insert into recycle bin for tracking
    $recycleQuery = "INSERT INTO recycle_bin 
                    (original_table, original_id, deleted_data, deleted_by_department_id, deleted_by_user_id, deleted_by_admin, deleted_at) 
                    VALUES (?, ?, ?, ?, ?, 0, NOW())";
    $recycleStmt = $db->prepare($recycleQuery);
    $recycleStmt->execute([
        'messages',
        $message_id,
        json_encode($message),
        $department_id,
        $user_id
    ]);
    
    $db->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Message moved to recycle bin',
        'soft_deleted' => true
    ]);
    
} catch (PDOException $e) {
    $db->rollBack();
    error_log("Delete message error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>