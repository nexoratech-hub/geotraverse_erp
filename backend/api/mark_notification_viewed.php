<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "geotraverse_erp";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['notification_id'])) {
    echo json_encode(['success' => false, 'message' => 'Notification ID required']);
    exit;
}

$notification_id = intval($data['notification_id']);

try {
    $stmt = $conn->prepare("
        UPDATE notifications 
        SET is_viewed = 1, viewed_at = NOW() 
        WHERE id = ? AND is_viewed = 0
    ");
    $stmt->bind_param("i", $notification_id);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>