<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit;
}

if (!isset($data->title) || empty(trim($data->title))) {
    echo json_encode(['success' => false, 'message' => 'Title is required']);
    exit;
}

if (!isset($data->content) || empty(trim($data->content))) {
    echo json_encode(['success' => false, 'message' => 'Content is required']);
    exit;
}

$department_id = isset($data->department_id) ? intval($data->department_id) : 1;
$period = isset($data->period) ? $data->period : 'monthly';
$status = isset($data->status) ? $data->status : 'draft';

try {
    $query = "INSERT INTO reports (title, period, content, status, department_id, created_at) 
              VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $db->prepare($query);
    $stmt->execute([
        $data->title,
        $period,
        $data->content,
        $status,
        $department_id
    ]);
    
    $report_id = $db->lastInsertId();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Report added successfully',
        'report_id' => $report_id,
        'data' => ['id' => $report_id]
    ]);
    
} catch (PDOException $e) {
    error_log("Add report error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>