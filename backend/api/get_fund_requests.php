<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'geotraverse_erp';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Check if all=1 parameter is set
$all = isset($_GET['all']) ? $_GET['all'] : 0;

$sql = "SELECT 
            fr.*,
            d.name AS department_name
        FROM fund_requests fr
        LEFT JOIN departments d ON fr.department_id = d.id
        WHERE fr.is_deleted = 0";

// If all=1, show all departments, otherwise only finance
if ($all != 1) {
    $sql .= " AND fr.department_id = 2";
}

$sql .= " ORDER BY 
            CASE 
                WHEN fr.status = 'pending' OR fr.status = 'Pending' THEN 0 
                WHEN fr.status = 'approved' OR fr.status = 'Approved' THEN 1 
                ELSE 2 
            END,
            fr.id DESC";

$result = $conn->query($sql);
$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode([
    'success' => true,
    'data' => $data,
    'count' => count($data)
]);

$conn->close();
?>