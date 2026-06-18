<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'geotraverse_erp';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$request_id = isset($input['request_id']) ? intval($input['request_id']) : 0;
$status = isset($input['status']) ? $input['status'] : '';
$reviewed_by = isset($input['reviewed_by']) ? $input['reviewed_by'] : 'Finance Manager';

if (!$request_id || !$status) {
    echo json_encode(['success' => false, 'message' => 'Request ID and status required']);
    exit;
}

// Valid statuses
$valid_statuses = ['pending', 'approved', 'cancelled', 'paid', 'partial'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

// Check if request exists
$stmt = $conn->prepare("SELECT * FROM fund_requests WHERE id = ?");
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();
$request = $result->fetch_assoc();
$stmt->close();

if (!$request) {
    echo json_encode(['success' => false, 'message' => 'Request not found']);
    exit;
}

// Update status
$stmt = $conn->prepare("UPDATE fund_requests SET status = ?, reviewed_by = ?, reviewed_at = NOW(), updated_at = NOW() WHERE id = ?");
$stmt->bind_param("ssi", $status, $reviewed_by, $request_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Request status updated successfully',
        'data' => [
            'id' => $request_id,
            'old_status' => $request['status'],
            'new_status' => $status,
            'reviewed_by' => $reviewed_by,
            'reviewed_at' => date('Y-m-d H:i:s')
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update status: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>