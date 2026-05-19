<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents('php://input'), true);

$sender_dept = $data['sender_dept'] ?? 0;
$receiver_dept = $data['receiver_dept'] ?? 0;
$message = $data['message'] ?? '';
$sender_id = $data['sender_id'] ?? 0;
$subject = $data['subject'] ?? 'New Message';

if (!$sender_dept || !$receiver_dept || !$message) {
    echo json_encode(['success' => false, 'message' => 'Sender, receiver, and message required']);
    exit;
}

// Check if conversation exists between these departments
$convQuery = "SELECT c.id FROM conversations c
              INNER JOIN messages m ON c.id = m.conversation_id
              WHERE (m.sender_dept = :sender_dept AND m.receiver_dept = :receiver_dept)
              OR (m.sender_dept = :receiver_dept AND m.receiver_dept = :sender_dept)
              LIMIT 1";

$convStmt = $db->prepare($convQuery);
$convStmt->bindParam(':sender_dept', $sender_dept);
$convStmt->bindParam(':receiver_dept', $receiver_dept);
$convStmt->execute();

$conversation_id = $convStmt->fetchColumn();

if (!$conversation_id) {
    // Create new conversation
    $insertConv = "INSERT INTO conversations (user_id, admin_id, subject, created_at) VALUES (:user_id, :admin_id, :subject, NOW())";
    $convStmt2 = $db->prepare($insertConv);
    $admin_id = ($receiver_dept == 1) ? 1 : null;
    $convStmt2->bindParam(':user_id', $sender_id);
    $convStmt2->bindParam(':admin_id', $admin_id);
    $convStmt2->bindParam(':subject', $subject);
    $convStmt2->execute();
    $conversation_id = $db->lastInsertId();
}

// Insert message
$query = "INSERT INTO messages (sender_dept, receiver_dept, conversation_id, sender_id, receiver_id, message, is_read, created_at) 
          VALUES (:sender_dept, :receiver_dept, :conversation_id, :sender_id, :receiver_id, :message, 0, NOW())";

$stmt = $db->prepare($query);
$receiver_id = ($receiver_dept == 1) ? 1 : 0;
$stmt->bindParam(':sender_dept', $sender_dept);
$stmt->bindParam(':receiver_dept', $receiver_dept);
$stmt->bindParam(':conversation_id', $conversation_id);
$stmt->bindParam(':sender_id', $sender_id);
$stmt->bindParam(':receiver_id', $receiver_id);
$stmt->bindParam(':message', $message);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Message sent successfully', 'conversation_id' => $conversation_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send message']);
}
?>