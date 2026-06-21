<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "geotraverse_erp";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$department_id = isset($data['department_id']) ? intval($data['department_id']) : 0;

if ($department_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Department ID required']);
    exit;
}

try {
    $stmt = $conn->prepare("
        UPDATE notifications 
        SET is_viewed = 1, viewed_at = NOW() 
        WHERE department_id = ? AND is_viewed = 0
    ");
    $stmt->bind_param("i", $department_id);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'updated' => $stmt->affected_rows]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>