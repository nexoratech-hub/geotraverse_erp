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

if (!$data || !isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'Request ID required']);
    exit();
}

$id = intval($data['id']);
$title = isset($data['title']) ? trim($data['title']) : null;
$request_date = isset($data['request_date']) ? $data['request_date'] : null;
$type = isset($data['type']) ? $data['type'] : null;
$source = isset($data['source']) ? trim($data['source']) : null;
$amount = isset($data['amount']) ? floatval($data['amount']) : null;
$description = isset($data['description']) ? trim($data['description']) : null;
$status = isset($data['status']) ? trim($data['status']) : null;

$updates = [];
$params = [];
$types = "";

if ($title) { $updates[] = "title = ?"; $params[] = $title; $types .= "s"; }
if ($request_date) { $updates[] = "request_date = ?"; $params[] = $request_date; $types .= "s"; }
if ($type) { $updates[] = "type = ?"; $params[] = $type; $types .= "s"; }
if ($source) { $updates[] = "source = ?"; $params[] = $source; $types .= "s"; }
if ($amount !== null) { $updates[] = "amount = ?"; $params[] = $amount; $types .= "d"; }
if ($description !== null) { $updates[] = "description = ?"; $params[] = $description; $types .= "s"; }
if ($status) { $updates[] = "status = ?"; $params[] = $status; $types .= "s"; }

if (empty($updates)) {
    echo json_encode(['success' => false, 'message' => 'No fields to update']);
    exit();
}

$updates[] = "updated_at = NOW()";
$sql = "UPDATE fund_requests SET " . implode(", ", $updates) . " WHERE id = ?";
$params[] = $id;
$types .= "i";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Fund request updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>