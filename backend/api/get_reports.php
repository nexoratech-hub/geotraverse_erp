<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$department_id = isset($_GET['department_id']) ? (int)$_GET['department_id'] : 0;
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$report_id = isset($_GET['report_id']) ? (int)$_GET['report_id'] : 0;

try {
    if ($report_id > 0) {
        $query = "SELECT r.*, d.name as department_name 
                  FROM reports r
                  LEFT JOIN departments d ON r.department_id = d.id
                  WHERE r.id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$report_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'data' => $stmt->fetch(PDO::FETCH_ASSOC)]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Report not found']);
        }
        exit();
    }
    
    // Get all reports for department
    $query = "SELECT r.*, d.name as department_name 
              FROM reports r
              LEFT JOIN departments d ON r.department_id = d.id
              WHERE r.deleted_by_admin = 0 AND r.deleted_by_department = 0
              ORDER BY r.created_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Filter by department if specified
    if ($department_id > 0) {
        $reports = array_filter($reports, function($r) use ($department_id) {
            return $r['department_id'] == $department_id;
        });
        $reports = array_values($reports);
    }
    
    echo json_encode([
        'success' => true,
        'data' => $reports,
        'total' => count($reports),
        'department_id' => $department_id
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>