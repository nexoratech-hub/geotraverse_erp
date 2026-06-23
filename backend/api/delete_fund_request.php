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
$deleted_by = isset($data['deleted_by']) ? trim($data['deleted_by']) : 'System';

$stmt = $conn->prepare("UPDATE fund_requests SET is_deleted = 1, deleted_at = NOW() WHERE id = ?");
$stmt->bind_param("i", $request_id);

if ($stmt->execute()) {
    // Add to recycle bin
    $title = "Fund Request ID: " . $request_id;
    $recycleStmt = $conn->prepare("INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin, deleted_at) VALUES (?, 'fund_request', ?, 2, 0, NOW())");
    $recycleStmt->bind_param("is", $request_id, $title);
    $recycleStmt->execute();
    $recycleStmt->close();
    
    echo json_encode(['success' => true, 'message' => 'Fund request deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>