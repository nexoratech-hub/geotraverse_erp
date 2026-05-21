<?php
// backend/api/add_report.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$database = new Database();
$conn = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Only POST method allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

$title = trim($input['title'] ?? '');
$period = $input['period'] ?? 'monthly';
$content = trim($input['content'] ?? '');
$department_id = isset($input['department_id']) ? intval($input['department_id']) : 1;
$status = $input['status'] ?? 'draft';

if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Title is required']);
    exit();
}

if (empty($content)) {
    echo json_encode(['success' false, 'message' => 'Content is required']);
    exit();
}

$stmt = $conn->prepare("INSERT INTO reports (title, period, content, department_id, status, created_at) 
                        VALUES (?, ?, ?, ?, ?, NOW())");
$stmt->execute([$title, $period, $content, $department_id, $status]);

$report_id = $conn->lastInsertId();

echo json_encode([
    'success' => true,
    'message' => 'Report added successfully',
    'report_id' => $report_id,
    'data' => [
        'id' => $report_id,
        'title' => $title,
        'period' => $period,
        'content' => $content,
        'department_id' => $department_id,
        'status' => $status
    ]
]);
?>