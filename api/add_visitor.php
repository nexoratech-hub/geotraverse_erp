<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents('php://input'), true);

$name = $data['name'] ?? '';
$phone = $data['phone'] ?? '';
$department = $data['department'] ?? '';
$date = $data['date'] ?? date('Y-m-d');
$time = $data['time'] ?? date('H:i:s');
$description = $data['description'] ?? '';
$department_id = $data['department_id'] ?? 0;

if (!$name) {
    echo json_encode(['success' => false, 'message' => 'Visitor name required']);
    exit;
}

$query = "INSERT INTO visitors (name, phone, department_to_visit, visit_date, visit_time, purpose, department_id) 
          VALUES (:name, :phone, :department, :date, :time, :description, :department_id)";

$stmt = $db->prepare($query);
$stmt->bindParam(':name', $name);
$stmt->bindParam(':phone', $phone);
$stmt->bindParam(':department', $department);
$stmt->bindParam(':date', $date);
$stmt->bindParam(':time', $time);
$stmt->bindParam(':description', $description);
$stmt->bindParam(':department_id', $department_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Visitor registered successfully', 'id' => $db->lastInsertId()]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to register visitor']);
}
?>