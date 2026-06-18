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
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$request_id = isset($input['request_id']) ? intval($input['request_id']) : 0;
$deleted_by = isset($input['deleted_by']) ? $input['deleted_by'] : 'Finance Manager';

if (!$request_id) {
    echo json_encode(['success' => false, 'message' => 'Request ID required']);
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

// Soft delete
$stmt = $conn->prepare("UPDATE fund_requests SET is_deleted = 1, deleted_at = NOW() WHERE id = ?");
$stmt->bind_param("i", $request_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Request deleted successfully',
        'data' => ['id' => $request_id]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>