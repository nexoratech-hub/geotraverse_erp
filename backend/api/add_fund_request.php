<?php
// Force JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'geotraverse_erp';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// If no JSON, try POST parameters
if (!$data) {
    $data = $_POST;
}

// Validate required fields
if (!$data || !isset($data['title']) || !isset($data['amount']) || !isset($data['department_id'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Required fields: title, amount, department_id',
        'received' => $data
    ]);
    exit();
}

// Sanitize input
$title = trim($data['title']);
$request_date = isset($data['request_date']) ? $data['request_date'] : date('Y-m-d');
$type = isset($data['type']) ? $data['type'] : 'expense';
$source = isset($data['source']) ? trim($data['source']) : $title;
$amount = floatval($data['amount']);
$description = isset($data['description']) ? trim($data['description']) : '';
$department_id = intval($data['department_id']);
$requested_by = isset($data['requested_by']) ? trim($data['requested_by']) : 'System';

// Insert into database
$stmt = $conn->prepare("INSERT INTO fund_requests (title, request_date, type, source, amount, description, department_id, requested_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database prepare failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param("ssssdsis", $title, $request_date, $type, $source, $amount, $description, $department_id, $requested_by);

if ($stmt->execute()) {
    $request_id = $conn->insert_id;
    
    // Add notification for finance department (department_id = 2)
    try {
        $notifStmt = $conn->prepare("INSERT INTO notifications (department_id, item_type, item_id, from_department_id, item_title, message, created_at) VALUES (2, 'fund_request', ?, ?, ?, CONCAT('New fund request from department ', ?), NOW())");
        if ($notifStmt) {
            $from_dept = $department_id;
            $notifStmt->bind_param("iiss", $request_id, $from_dept, $title, $from_dept);
            $notifStmt->execute();
            $notifStmt->close();
        }
    } catch (Exception $e) {
        // Notification failed, but request was added
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Fund request added successfully',
        'request_id' => $request_id,
        'data' => [
            'id' => $request_id,
            'title' => $title,
            'amount' => $amount,
            'department_id' => $department_id
        ]
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to add fund request: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>