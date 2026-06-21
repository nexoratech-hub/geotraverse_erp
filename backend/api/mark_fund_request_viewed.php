<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'geotraverse_erp';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['request_id'])) {
    echo json_encode(['success' => false, 'message' => 'Request ID required']);
    exit();
}

$request_id = intval($data['request_id']);
$department_id = isset($data['department_id']) ? intval($data['department_id']) : 2;

$stmt = $conn->prepare("UPDATE fund_requests SET is_viewed_by_finance = 1, viewed_at = NOW() WHERE id = ?");
$stmt->bind_param("i", $request_id);

if ($stmt->execute()) {
    // Update notification
    $notifStmt = $conn->prepare("UPDATE notifications SET is_viewed = 1, viewed_at = NOW() WHERE item_type = 'fund_request' AND item_id = ? AND department_id = ?");
    $notifStmt->bind_param("ii", $request_id, $department_id);
    $notifStmt->execute();
    $notifStmt->close();
    
    echo json_encode(['success' => true, 'message' => 'Request marked as viewed']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to mark as viewed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>