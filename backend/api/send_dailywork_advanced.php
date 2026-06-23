<?php
// send_dailywork_advanced.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include_once '../config/database.php';
include_once '../config/auth.php';

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

$user = $auth->validateToken();
if (!$user) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$required = ['dailywork_id', 'to_department_id', 'from_department_id'];
foreach ($required as $field) {
    if (!isset($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing field: $field"]);
        exit;
    }
}

$dailywork_id = intval($data['dailywork_id']);
$to_department_id = intval($data['to_department_id']);
$from_department_id = intval($data['from_department_id']);
$sent_by = isset($data['sent_by']) ? $data['sent_by'] : $user['name'] ?? 'System';
$from_department_name = isset($data['from_department_name']) ? $data['from_department_name'] : null;

// Get daily work data from dailywork table
$query = "SELECT * FROM dailywork WHERE id = ? AND is_deleted = 0";
$stmt = $db->prepare($query);
$stmt->execute([$dailywork_id]);
$dailywork = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dailywork) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Daily work record not found']);
    exit;
}

// Get department names
$query = "SELECT name FROM departments WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$from_department_id]);
$from_dept = $stmt->fetch(PDO::FETCH_ASSOC);
$from_name = $from_dept ? $from_dept['name'] : $from_department_name ?? 'Unknown';

$stmt->execute([$to_department_id]);
$to_dept = $stmt->fetch(PDO::FETCH_ASSOC);
$to_name = $to_dept ? $to_dept['name'] : 'Unknown';

// Build daily work data JSON
$dailywork_data = [
    'id' => $dailywork['id'],
    'date' => $dailywork['date'],
    'project_id' => $dailywork['project_id'],
    'project_name' => $dailywork['project_name'],
    'work_description' => $dailywork['work_description'],
    'budget' => $dailywork['budget'],
    'amount' => $dailywork['amount'],
    'status' => $dailywork['status'],
    'department_id' => $dailywork['department_id'],
    'created_by' => $dailywork['created_by'],
    'created_at' => $dailywork['created_at'],
    'sent_count' => ($dailywork['sent_count'] ?? 0) + 1,
    'is_sent' => 1,
    'last_sent_at' => date('Y-m-d H:i:s')
];

// Insert into sent_dailywork table
$query = "INSERT INTO sent_dailywork (
    original_dailywork_id,
    dailywork_data,
    from_department_id,
    to_department_id,
    sent_by,
    sent_at,
    is_viewed,
    is_deleted,
    sent_count,
    is_sent,
    last_sent_at,
    from_department_name,
    to_department_name,
    dailywork_project_name,
    dailywork_date,
    dailywork_amount
) VALUES (?, ?, ?, ?, ?, NOW(), 0, 0, ?, 1, NOW(), ?, ?, ?, ?, ?)";

$stmt = $db->prepare($query);
$stmt->execute([
    $dailywork_id,
    json_encode($dailywork_data),
    $from_department_id,
    $to_department_id,
    $sent_by,
    ($dailywork['sent_count'] ?? 0) + 1,
    $from_name,
    $to_name,
    $dailywork['project_name'],
    $dailywork['date'],
    $dailywork['amount']
]);

$sent_id = $db->lastInsertId();

// Update original daily work record
$query = "UPDATE dailywork SET 
    sent_to_department = ?,
    sent_from_department = ?,
    is_sent = 1,
    sent_count = COALESCE(sent_count, 0) + 1,
    last_sent_at = NOW(),
    is_viewed_by_department = 0
WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$to_department_id, $from_department_id, $dailywork_id]);

// Add notification for recipient
$query = "INSERT INTO notifications (
    department_id,
    from_department_id,
    item_type,
    item_id,
    item_title,
    message,
    created_at,
    is_viewed
) VALUES (?, ?, 'dailywork', ?, ?, ?, NOW(), 0)";
$stmt = $db->prepare($query);
$message = "📋 Daily work \"{$dailywork['project_name']}\" sent from {$from_name}";
$stmt->execute([
    $to_department_id,
    $from_department_id,
    $dailywork_id,
    $dailywork['project_name'],
    $message
]);

echo json_encode([
    'success' => true,
    'message' => 'Daily work sent successfully',
    'data' => [
        'sent_id' => $sent_id,
        'sent_at' => date('Y-m-d H:i:s')
    ]
]);
?>