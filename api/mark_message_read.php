<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

require_once '../config/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$message_id = isset($data['message_id']) ? intval($data['message_id']) : 0;

if (!$message_id) {
    echo json_encode(['success' => false, 'message' => 'Message ID required']);
    exit;
}

$stmt = $conn->prepare("UPDATE messages SET is_read = 1, read_at = NOW() WHERE id = ?");
$stmt->bind_param("i", $message_id);
$stmt->execute();

echo json_encode(['success' => true]);
?>