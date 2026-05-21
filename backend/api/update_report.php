<?php
// backend/api/update_report.php
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

$report_id = isset($input['id']) ? intval($input['id']) : (isset($input['report_id']) ? intval($input['report_id']) : 0);
$title = trim($input['title'] ?? '');
$period = $input['period'] ?? 'monthly';
$content = trim($input['content'] ?? '');

if (!$report_id) {
    echo json_encode(['success' => false, 'message' => 'Report ID is required']);
    exit();
}

$stmt = $conn->prepare("UPDATE reports SET title = ?, period = ?, content = ? WHERE id = ?");
$stmt->execute([$title, $period, $content, $report_id]);

if ($stmt->rowCount() > 0 || true) {
    echo json_encode(['success' => true, 'message' => 'Report updated successfully']);
} else {
    echo json_encode(['success' => true, 'message' => 'No changes made']);
}
?>