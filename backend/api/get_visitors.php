<?php
// get_visitors.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database configuration
$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage(),
        'data' => []
    ]);
    exit();
}

// Get department_id from query parameter
$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;

try {
    // Base query - get visitors for the specified department
    if ($department_id > 0) {
        $query = "SELECT id, name, phone, department_to_visit, visit_date, visit_time, purpose, department_id, created_at
                  FROM visitors 
                  WHERE department_id = :department_id
                  ORDER BY visit_date DESC, created_at DESC";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':department_id', $department_id, PDO::PARAM_INT);
    } else {
        $query = "SELECT id, name, phone, department_to_visit, visit_date, visit_time, purpose, department_id, created_at
                  FROM visitors 
                  ORDER BY visit_date DESC, created_at DESC";
        $stmt = $pdo->prepare($query);
    }
    
    $stmt->execute();
    $visitors = $stmt->fetchAll();
    
    // Format data for frontend
    foreach ($visitors as &$visitor) {
        $visitor['created_at'] = $visitor['created_at'];
        // Format time if needed
        if ($visitor['visit_time']) {
            $visitor['visit_time'] = date('H:i', strtotime($visitor['visit_time']));
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $visitors,
        'count' => count($visitors),
        'message' => 'Visitors retrieved successfully'
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>