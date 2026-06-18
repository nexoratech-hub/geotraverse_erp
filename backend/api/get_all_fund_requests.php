<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'geotraverse_erp';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

$sql = "SELECT 
            fr.*,
            d.name AS department_name
        FROM fund_requests fr
        LEFT JOIN departments d ON fr.department_id = d.id
        WHERE fr.is_deleted = 0
        ORDER BY 
            CASE 
                WHEN fr.status = 'pending' OR fr.status = 'Pending' THEN 0 
                WHEN fr.status = 'approved' OR fr.status = 'Approved' THEN 1 
                WHEN fr.status = 'paid' OR fr.status = 'Paid' THEN 2
                ELSE 3
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