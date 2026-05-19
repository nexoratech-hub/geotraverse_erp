<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, PUT');
header('Access-Control-Allow-Headers: Content-Type');

// Direct database connection (bypass config for testing)
$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;
    
    // Simple query to get all visitors
    $query = "SELECT * FROM visitors";
    
    if ($department_id > 0) {
        $query .= " WHERE department_id = " . $department_id;
    }
    
    $query .= " ORDER BY visit_date DESC, created_at DESC";
    
    $result = $pdo->query($query);
    $visitors = $result->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $visitors,
        'total' => count($visitors),
        'department_filter' => $department_id
    ]);
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $name = $data['name'] ?? '';
    $phone = $data['phone'] ?? '';
    $department_to_visit = $data['department'] ?? '';
    $visit_date = $data['date'] ?? date('Y-m-d');
    $visit_time = $data['time'] ?? date('H:i:s');
    $purpose = $data['description'] ?? '';
    $department_id = $data['department_id'] ?? 0;
    
    if (!$name) {
        echo json_encode(['success' => false, 'message' => 'Visitor name required']);
        exit;
    }
    
    $query = "INSERT INTO visitors (name, phone, department_to_visit, visit_date, visit_time, purpose, department_id, created_at) 
              VALUES (:name, :phone, :department_to_visit, :visit_date, :visit_time, :purpose, :department_id, NOW())";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':department_to_visit', $department_to_visit);
    $stmt->bindParam(':visit_date', $visit_date);
    $stmt->bindParam(':visit_time', $visit_time);
    $stmt->bindParam(':purpose', $purpose);
    $stmt->bindParam(':department_id', $department_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Visitor registered successfully', 'id' => $pdo->lastInsertId()]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to register visitor']);
    }
} elseif ($method === 'DELETE') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    $query = "DELETE FROM visitors WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Visitor deleted']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete visitor']);
    }
} elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? 0;
    
    $name = $data['name'] ?? '';
    $phone = $data['phone'] ?? '';
    $department_to_visit = $data['department'] ?? '';
    $visit_date = $data['date'] ?? date('Y-m-d');
    $visit_time = $data['time'] ?? date('H:i:s');
    $purpose = $data['description'] ?? '';
    
    $query = "UPDATE visitors SET 
              name = :name,
              phone = :phone,
              department_to_visit = :department_to_visit,
              visit_date = :visit_date,
              visit_time = :visit_time,
              purpose = :purpose
              WHERE id = :id";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':department_to_visit', $department_to_visit);
    $stmt->bindParam(':visit_date', $visit_date);
    $stmt->bindParam(':visit_time', $visit_time);
    $stmt->bindParam(':purpose', $purpose);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Visitor updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update visitor']);
    }
}
?>