<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "geotraverse_erp";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed', 'data' => []]);
    exit;
}

$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;

if ($department_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Department ID required', 'data' => []]);
    exit;
}

try {
    $sql = "SELECT n.*, 
                   d.name as from_department_name,
                   (SELECT name FROM departments WHERE id = n.department_id) as to_department_name
            FROM notifications n
            LEFT JOIN departments d ON d.id = n.from_department_id
            WHERE n.department_id = " . $department_id . "
            ORDER BY n.created_at DESC
            LIMIT 100";
    
    $result = $conn->query($sql);
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $notifications]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'data' => []]);
}

$conn->close();
?>