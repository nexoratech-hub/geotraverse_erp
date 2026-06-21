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

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$department_id = isset($data['department_id']) ? intval($data['department_id']) : 0;
$item_type = isset($data['item_type']) ? $data['item_type'] : '';
$item_id = isset($data['item_id']) ? intval($data['item_id']) : 0;
$from_department_id = isset($data['from_department_id']) ? intval($data['from_department_id']) : 0;
$item_title = isset($data['item_title']) ? $data['item_title'] : '';
$message = isset($data['message']) ? $data['message'] : '';

if ($department_id <= 0 || empty($item_type) || $item_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $stmt = $conn->prepare("
        INSERT INTO notifications (department_id, item_type, item_id, from_department_id, item_title, message, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("isiiis", $department_id, $item_type, $item_id, $from_department_id, $item_title, $message);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>