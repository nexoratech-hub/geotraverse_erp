<?php
// add_visitor.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

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
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit();
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON data'
    ]);
    exit();
}

// Validate required fields
if (empty($input['name'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Visitor name is required'
    ]);
    exit();
}

if (empty($input['department_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Department ID is required'
    ]);
    exit();
}

try {
    $query = "INSERT INTO visitors 
              (name, phone, department_to_visit, visit_date, visit_time, purpose, department_id, created_at)
              VALUES 
              (:name, :phone, :department_to_visit, :visit_date, :visit_time, :purpose, :department_id, NOW())";
    
    $stmt = $pdo->prepare($query);
    
    $name = trim($input['name']);
    $phone = isset($input['phone']) ? $input['phone'] : null;
    $department_to_visit = isset($input['department']) ? $input['department'] : null;
    $visit_date = isset($input['date']) && !empty($input['date']) ? $input['date'] : date('Y-m-d');
    $visit_time = isset($input['time']) && !empty($input['time']) ? $input['time'] : null;
    $purpose = isset($input['description']) ? $input['description'] : null;
    $department_id = intval($input['department_id']);
    
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':department_to_visit', $department_to_visit);
    $stmt->bindParam(':visit_date', $visit_date);
    $stmt->bindParam(':visit_time', $visit_time);
    $stmt->bindParam(':purpose', $purpose);
    $stmt->bindParam(':department_id', $department_id);
    
    if ($stmt->execute()) {
        $new_id = $pdo->lastInsertId();
        
        // Fetch the newly created visitor
        $fetch_query = "SELECT id, name, phone, department_to_visit, visit_date, visit_time, purpose, department_id, created_at
                        FROM visitors WHERE id = :id";
        $fetch_stmt = $pdo->prepare($fetch_query);
        $fetch_stmt->bindParam(':id', $new_id);
        $fetch_stmt->execute();
        $new_visitor = $fetch_stmt->fetch();
        
        echo json_encode([
            'success' => true,
            'message' => 'Visitor registered successfully',
            'data' => $new_visitor,
            'id' => $new_id
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to register visitor'
        ]);
    }
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>