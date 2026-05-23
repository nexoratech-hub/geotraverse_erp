<?php
// /geotraverse/backend/api/get_projects_universal.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($method === 'GET') {
    $department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;
    
    if ($department_id === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Department ID is required'
        ]);
        exit();
    }
    
    // Get projects for specific department
    $query = "SELECT p.*, d.name as department_name 
              FROM projects p 
              LEFT JOIN departments d ON p.department_id = d.id 
              WHERE p.department_id = :department_id 
              AND (p.deleted_by_department = 0 OR p.deleted_by_department IS NULL)
              AND (p.deleted_by_admin = 0 OR p.deleted_by_admin IS NULL)
              ORDER BY p.created_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':department_id', $department_id);
    $stmt->execute();
    
    $projects = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $projects[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'client_name' => $row['client_name'],
            'amount' => $row['amount'],
            'status' => $row['status'],
            'progress' => $row['progress'],
            'location' => $row['location'],
            'description' => $row['description'],
            'image' => $row['image'],
            'start_date' => $row['start_date'],
            'end_date' => $row['end_date'],
            'department_id' => $row['department_id'],
            'department_name' => $row['department_name'],
            'sent_from_dept' => $row['sent_from_dept'],
            'is_viewed_by_department' => $row['is_viewed_by_department'],
            'is_viewed_by_admin' => $row['is_viewed_by_admin'],
            'created_at' => $row['created_at']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $projects,
        'total' => count($projects),
        'department_id' => $department_id
    ]);
    
} elseif ($method === 'POST') {
    // Add new project
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid data'
        ]);
        exit();
    }
    
    $department_id = isset($data['department_id']) ? intval($data['department_id']) : 0;
    $name = isset($data['name']) ? trim($data['name']) : '';
    $client_name = isset($data['client_name']) ? trim($data['client_name']) : '';
    $amount = isset($data['amount']) ? floatval($data['amount']) : 0;
    $location = isset($data['location']) ? trim($data['location']) : '';
    $description = isset($data['description']) ? trim($data['description']) : '';
    $status = isset($data['status']) ? $data['status'] : 'pending';
    $progress = isset($data['progress']) ? intval($data['progress']) : 0;
    $start_date = isset($data['start_date']) ? $data['start_date'] : null;
    $end_date = isset($data['end_date']) ? $data['end_date'] : null;
    $image = isset($data['image']) ? $data['image'] : '';
    
    if (empty($name)) {
        echo json_encode([
            'success' => false,
            'message' => 'Project name is required'
        ]);
        exit();
    }
    
    $query = "INSERT INTO projects (name, client_name, amount, location, description, status, progress, start_date, end_date, department_id, image, created_at) 
              VALUES (:name, :client_name, :amount, :location, :description, :status, :progress, :start_date, :end_date, :department_id, :image, NOW())";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':client_name', $client_name);
    $stmt->bindParam(':amount', $amount);
    $stmt->bindParam(':location', $location);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':progress', $progress);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    $stmt->bindParam(':department_id', $department_id);
    $stmt->bindParam(':image', $image);
    
    if ($stmt->execute()) {
        $id = $db->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Project added successfully',
            'id' => $id
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to add project'
        ]);
    }
}
?>