<?php
// add_report.php - Kuongeza report mpya

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB Connection Error: ' . $e->getMessage()]);
    exit;
}

// Get raw input
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

// If JSON fails, try FormData
if (!$data) {
    // FormData from POST
    $data = $_POST;
}

// Log for debugging
error_log("add_report.php - Received data: " . print_r($data, true));

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit;
}

$title = isset($data['title']) ? trim($data['title']) : '';
$period = isset($data['period']) ? $data['period'] : 'monthly';
$content = isset($data['content']) ? trim($data['content']) : '';
$department_id = isset($data['department_id']) ? (int)$data['department_id'] : 0;
$status = isset($data['status']) ? $data['status'] : 'draft';
$created_by = isset($data['created_by']) ? $data['created_by'] : 'System';
$is_viewed_by_department = isset($data['is_viewed_by_department']) ? (int)$data['is_viewed_by_department'] : 1;
$sent_from_department = isset($data['sent_from_department']) ? (int)$data['sent_from_department'] : $department_id;

if (!$title || !$content || !$department_id) {
    echo json_encode([
        'success' => false, 
        'message' => 'Missing required fields: title, content, department_id',
        'received' => ['title' => $title, 'content' => $content, 'department_id' => $department_id]
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO reports (
            title, 
            period, 
            content, 
            department_id, 
            status, 
            created_by, 
            is_viewed_by_department, 
            sent_from_department,
            created_at,
            updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    
    $stmt->execute([
        $title,
        $period,
        $content,
        $department_id,
        $status,
        $created_by,
        $is_viewed_by_department,
        $sent_from_department
    ]);
    
    $reportId = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Report added successfully',
        'id' => $reportId,
        'data' => [
            'title' => $title,
            'period' => $period,
            'department_id' => $department_id,
            'created_by' => $created_by
        ]
    ]);
    
} catch(PDOException $e) {
    error_log("add_report.php - Database error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>