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

if (!$data || !isset($data['request_id']) || !isset($data['status'])) {
    echo json_encode(['success' => false, 'message' => 'Request ID and status required']);
    exit();
}

$request_id = intval($data['request_id']);
$status = trim($data['status']);
$reviewed_by = isset($data['reviewed_by']) ? trim($data['reviewed_by']) : 'Finance Manager';

$allowed_statuses = ['pending', 'approved', 'cancelled', 'paid', 'partial'];
if (!in_array($status, $allowed_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

$stmt = $conn->prepare("UPDATE fund_requests SET status = ?, reviewed_by = ?, reviewed_at = NOW(), is_viewed_by_finance = 1, viewed_at = NOW() WHERE id = ?");
$stmt->bind_param("si", $status, $request_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Status updated to: ' . $status]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update status: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>