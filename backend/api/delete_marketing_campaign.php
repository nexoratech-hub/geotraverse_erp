<?php
// C:\xampp\htdocs\geotraverse\backend\api\delete_marketing_campaign.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = isset($data['id']) ? intval($data['id']) : (isset($_GET['id']) ? intval($_GET['id']) : 0);
    $department_id = isset($data['department_id']) ? intval($data['department_id']) : 0;
} else {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;
}

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Campaign ID is required']);
    exit;
}

try {
    // Soft delete - mark as deleted
    $query = "UPDATE marketing_campaigns SET 
              deleted_by_department = 1, 
              deleted_at = NOW() 
              WHERE id = :id";
    
    if ($department_id > 0) {
        $query .= " AND department_id = :department_id";
    }
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    if ($department_id > 0) {
        $stmt->bindParam(':department_id', $department_id);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Campaign deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete campaign']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>