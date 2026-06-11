<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get query parameters
$department_id = isset($_GET['department_id']) ? $_GET['department_id'] : null;
$work_type = isset($_GET['work_type']) ? $_GET['work_type'] : null;
$id = isset($_GET['id']) ? $_GET['id'] : null;

// Build query
$query = "SELECT * FROM daily_work WHERE (is_deleted = 0 OR is_deleted IS NULL)";
$params = [];

if ($department_id) {
    $query .= " AND department_id = :department_id";
    $params[':department_id'] = $department_id;
}

if ($work_type) {
    $query .= " AND work_type = :work_type";
    $params[':work_type'] = $work_type;
}

if ($id) {
    $query .= " AND id = :id";
    $params[':id'] = $id;
}

$query .= " ORDER BY date DESC";

// Prepare and execute
$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'data' => $results]);
?>