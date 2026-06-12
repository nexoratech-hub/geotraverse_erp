<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage(), 'data' => []]);
    exit();
}

$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;

try {
    if ($department_id > 0) {
        $sql = "SELECT * FROM dailywork WHERE department_id = :department_id AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY date DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':department_id', $department_id);
    } else {
        $sql = "SELECT * FROM dailywork WHERE (is_deleted = 0 OR is_deleted IS NULL) ORDER BY date DESC";
        $stmt = $pdo->prepare($sql);
    }
    
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $results]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage(), 'data' => []]);
}
?>