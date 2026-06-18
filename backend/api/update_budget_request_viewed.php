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
$viewed_by = isset($input['viewed_by']) ? intval($input['viewed_by']) : 2;

if (!$request_id) {
    echo json_encode(['success' => false, 'message' => 'Request ID required']);
    exit;
}

$stmt = $conn->prepare("UPDATE fund_requests SET is_viewed_by_finance = 1, viewed_at = NOW() WHERE id = ?");
$stmt->bind_param("i", $request_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Request marked as viewed']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>