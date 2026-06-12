<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection
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

$id = isset($data['id']) ? intval($data['id']) : 0;
$date = isset($data['date']) ? $data['date'] : '';
$project_id = isset($data['project_id']) ? intval($data['project_id']) : null;
$project_name = isset($data['project_name']) ? $data['project_name'] : '';
$work_description = isset($data['work_description']) ? $data['work_description'] : '';
$budget = isset($data['budget']) ? floatval($data['budget']) : 0;
$amount = isset($data['amount']) ? floatval($data['amount']) : 0;
$status = isset($data['status']) ? $data['status'] : 'pending';
$department_id = isset($data['department_id']) ? intval($data['department_id']) : 0;
$updated_by = isset($data['updated_by']) ? $data['updated_by'] : 'System';

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit();
}

try {
    $sql = "UPDATE dailywork SET 
                date = :date,
                project_id = :project_id,
                project_name = :project_name,
                work_description = :work_description,
                budget = :budget,
                amount = :amount,
                status = :status,
                department_id = :department_id,
                updated_by = :updated_by,
                updated_at = NOW()
            WHERE id = :id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':date', $date);
    $stmt->bindParam(':project_id', $project_id);
    $stmt->bindParam(':project_name', $project_name);
    $stmt->bindParam(':work_description', $work_description);
    $stmt->bindParam(':budget', $budget);
    $stmt->bindParam(':amount', $amount);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':department_id', $department_id);
    $stmt->bindParam(':updated_by', $updated_by);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Daily work updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update daily work']);
    }
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>