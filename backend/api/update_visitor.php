<?php
// update_visitor.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

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
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON data'
    ]);
    exit();
}

if (empty($input['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Visitor ID is required'
    ]);
    exit();
}

try {
    $query = "UPDATE visitors SET 
              name = :name,
              phone = :phone,
              department_to_visit = :department_to_visit,
              visit_date = :visit_date,
              visit_time = :visit_time,
              purpose = :purpose
              WHERE id = :id";
    
    $stmt = $pdo->prepare($query);
    
    $id = intval($input['id']);
    $name = trim($input['name']);
    $phone = isset($input['phone']) ? $input['phone'] : null;
    $department_to_visit = isset($input['department']) ? $input['department'] : null;
    $visit_date = isset($input['date']) && !empty($input['date']) ? $input['date'] : date('Y-m-d');
    $visit_time = isset($input['time']) && !empty($input['time']) ? $input['time'] : null;
    $purpose = isset($input['description']) ? $input['description'] : null;
    
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':department_to_visit', $department_to_visit);
    $stmt->bindParam(':visit_date', $visit_date);
    $stmt->bindParam(':visit_time', $visit_time);
    $stmt->bindParam(':purpose', $purpose);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Visitor updated successfully',
            'id' => $id
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update visitor'
        ]);
    }
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>