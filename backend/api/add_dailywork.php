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
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit();
}

$date = isset($data['date']) ? $data['date'] : '';
$project_id = isset($data['project_id']) ? intval($data['project_id']) : null;
$project_name = isset($data['project_name']) ? $data['project_name'] : '';
$work_description = isset($data['work_description']) ? $data['work_description'] : '';
$budget = isset($data['budget']) ? floatval($data['budget']) : 0;
$amount = isset($data['amount']) ? floatval($data['amount']) : 0;
$status = isset($data['status']) ? $data['status'] : 'pending';
$department_id = isset($data['department_id']) ? intval($data['department_id']) : 0;
$created_by = isset($data['created_by']) ? $data['created_by'] : 'System';

try {
    $sql = "INSERT INTO dailywork (date, project_id, project_name, work_description, budget, amount, status, department_id, created_by, created_at) 
            VALUES (:date, :project_id, :project_name, :work_description, :budget, :amount, :status, :department_id, :created_by, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':date', $date);
    $stmt->bindParam(':project_id', $project_id);
    $stmt->bindParam(':project_name', $project_name);
    $stmt->bindParam(':work_description', $work_description);
    $stmt->bindParam(':budget', $budget);
    $stmt->bindParam(':amount', $amount);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':department_id', $department_id);
    $stmt->bindParam(':created_by', $created_by);
    
    if ($stmt->execute()) {
        $newId = $pdo->lastInsertId();
        echo json_encode(['success' => true, 'message' => 'Daily work added successfully', 'id' => $newId]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add daily work']);
    }
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>