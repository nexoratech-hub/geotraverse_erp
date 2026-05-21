<?php
// backend/api/delete_report.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$database = new Database();
$conn = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] != 'POST' && $_SERVER['REQUEST_METHOD'] != 'DELETE') {
    echo json_encode(['success' => false, 'message' => 'Only POST/DELETE method allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

$report_id = isset($input['report_id']) ? intval($input['report_id']) : (isset($input['id']) ? intval($input['id']) : 0);
$department_id = isset($input['department_id']) ? intval($input['department_id']) : 0;
$user_id = isset($input['user_id']) ? intval($input['user_id']) : 0;
$is_admin = isset($input['is_admin']) ? intval($input['is_admin']) : 0;

// Soft delete parameters
$deleted_by_department = isset($input['deleted_by_department']) ? intval($input['deleted_by_department']) : 0;
$deleted_by_admin = isset($input['deleted_by_admin']) ? intval($input['deleted_by_admin']) : 0;

if (!$report_id) {
    echo json_encode(['success' => false, 'message' => 'Report ID is required']);
    exit();
}

// Check if report exists
$checkStmt = $conn->prepare("SELECT * FROM reports WHERE id = ?");
$checkStmt->execute([$report_id]);
$report = $checkStmt->fetch(PDO::FETCH_ASSOC);

if (!$report) {
    echo json_encode(['success' => false, 'message' => 'Report not found']);
    exit();
}

// SOFT DELETE - only hide from the requesting department/admin
if ($is_admin == 1) {
    // Admin soft delete - hide from admin view only
    $stmt = $conn->prepare("UPDATE reports SET deleted_by_admin = 1, deleted_by_user_id = ?, deleted_at = NOW() WHERE id = ?");
    $stmt->execute([$user_id, $report_id]);
    echo json_encode(['success' => true, 'message' => 'Report hidden from Admin view (other departments can still see it)']);
} 
else if ($department_id > 0) {
    // Department soft delete - hide from this department only
    $stmt = $conn->prepare("UPDATE reports SET deleted_by_department = 1, deleted_by_department_id = ?, deleted_at = NOW() WHERE id = ?");
    $stmt->execute([$department_id, $report_id]);
    echo json_encode(['success' => true, 'message' => 'Report hidden from your department view']);
}
else {
    echo json_encode(['success' => false, 'message' => 'Invalid deletion parameters']);
}
?>