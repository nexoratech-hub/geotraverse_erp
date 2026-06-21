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

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    $data = $_POST;
}

if (!$data || !isset($data['request_id'])) {
    echo json_encode(['success' => false, 'message' => 'Request ID required']);
    exit();
}

$request_id = intval($data['request_id']);
$reviewed_by = isset($data['reviewed_by']) ? trim($data['reviewed_by']) : 'Finance Manager';

$stmt = $conn->prepare("SELECT * FROM fund_requests WHERE id = ? AND is_deleted = 0");
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Request not found']);
    $stmt->close();
    $conn->close();
    exit();
}

$request = $result->fetch_assoc();
$stmt->close();

if ($request['status'] !== 'pending') {
    echo json_encode([
        'success' => false, 
        'message' => 'Request is already ' . $request['status']
    ]);
    $conn->close();
    exit();
}

$updateStmt = $conn->prepare("UPDATE fund_requests SET status = 'cancelled', reviewed_by = ?, reviewed_at = NOW(), is_viewed_by_finance = 1, viewed_at = NOW() WHERE id = ?");
$updateStmt->bind_param("si", $reviewed_by, $request_id);

if ($updateStmt->execute()) {
    // Add notification
    try {
        $notifStmt = $conn->prepare("INSERT INTO notifications (department_id, item_type, item_id, from_department_id, item_title, message, created_at) VALUES (?, 'fund_request', ?, 2, ?, CONCAT('Your fund request \"', ?, '\" has been CANCELLED'), NOW())");
        if ($notifStmt) {
            $notifStmt->bind_param("iiss", $request['department_id'], $request_id, $request['title'], $request['title']);
            $notifStmt->execute();
            $notifStmt->close();
        }
    } catch (Exception $e) {
        // Notification failed
    }
    
    echo json_encode(['success' => true, 'message' => 'Request cancelled successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to cancel request: ' . $updateStmt->error]);
}

$updateStmt->close();
$conn->close();
?>