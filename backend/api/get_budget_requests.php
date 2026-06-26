<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once '../config/database.php';

$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : null;
$all = isset($_GET['all']) ? $_GET['all'] : false;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;

$conn = getConnection();

if ($all) {
    $stmt = $conn->prepare("SELECT fr.*, d.name as department_name 
        FROM fund_requests fr 
        LEFT JOIN departments d ON fr.department_id = d.id 
        WHERE fr.is_deleted = 0 
        ORDER BY fr.created_at DESC 
        LIMIT ?");
    $stmt->bind_param("i", $limit);
} else {
    $stmt = $conn->prepare("SELECT fr.*, d.name as department_name 
        FROM fund_requests fr 
        LEFT JOIN departments d ON fr.department_id = d.id 
        WHERE fr.department_id = ? AND fr.is_deleted = 0 
        ORDER BY fr.created_at DESC 
        LIMIT ?");
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