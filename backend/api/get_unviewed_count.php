<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "geotraverse_erp";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed', 'count' => 0]);
    exit;
}

$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;

if ($department_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Department ID required', 'count' => 0]);
    exit;
}

try {
    $sql = "SELECT COUNT(*) as count 
            FROM notifications 
            WHERE department_id = " . $department_id . " AND is_viewed = 0";
    
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    
    echo json_encode(['success' => true, 'count' => intval($row['count'])]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'count' => 0]);
}

$conn->close();
?>