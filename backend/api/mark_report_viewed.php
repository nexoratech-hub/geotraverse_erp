<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!$data || !isset($data->report_id)) {
    echo json_encode(['success' => false, 'message' => 'Report ID required']);
    exit;
}

$report_id = intval($data->report_id);
$is_admin = isset($data->is_admin) ? intval($data->is_admin) : 0;
$department_id = isset($data->department_id) ? intval($data->department_id) : null;

try {
    if ($is_admin == 1) {
        // Mark as viewed by admin
        $query = "UPDATE reports SET is_viewed_by_admin = 1 WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$report_id]);
        
        echo json_encode(['success' => true, 'message' => 'Report marked as viewed by admin']);
        
    } else if ($department_id) {
        // Mark as viewed by department
        $query = "UPDATE reports SET is_viewed_by_department = 1 WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$report_id]);
        
        echo json_encode(['success' => true, 'message' => 'Report marked as viewed by department']);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Admin or Department ID required']);
    }
    
} catch (PDOException $e) {
    error_log("Mark report viewed error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>