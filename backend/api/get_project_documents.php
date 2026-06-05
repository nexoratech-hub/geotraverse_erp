<?php
error_reporting(0);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once 'db_connect.php';

$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 1;

$query = "SELECT * FROM project_documents WHERE department_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $department_id);
$stmt->execute();
$result = $stmt->get_result();

$documents = [];
while ($row = $result->fetch_assoc()) {
    $documents[] = $row;
}

echo json_encode(['success' => true, 'data' => $documents]);
$stmt->close();
$conn->close();
?>