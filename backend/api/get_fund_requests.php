<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'geotraverse_erp';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : null;
$all = isset($_GET['all']) ? $_GET['all'] : false;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;

if ($all || !$department_id) {
    $stmt = $conn->prepare("SELECT fr.*, d.name as department_name FROM fund_requests fr LEFT JOIN departments d ON fr.department_id = d.id WHERE fr.is_deleted = 0 ORDER BY fr.created_at DESC LIMIT ?");
    $stmt->bind_param("i", $limit);
} else {
    $stmt = $conn->prepare("SELECT fr.*, d.name as department_name FROM fund_requests fr LEFT JOIN departments d ON fr.department_id = d.id WHERE fr.department_id = ? AND fr.is_deleted = 0 ORDER BY fr.created_at DESC LIMIT ?");
    $stmt->bind_param("ii", $department_id, $limit);
}

$stmt->execute();
$result = $stmt->get_result();

$requests = [];
while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}

echo json_encode(['success' => true, 'data' => $requests, 'count' => count($requests)]);

$stmt->close();
$conn->close();
?>