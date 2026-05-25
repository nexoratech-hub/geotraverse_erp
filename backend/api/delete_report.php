<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit;
}

// Get parameters - support both naming conventions
$report_id = isset($data->report_id) ? intval($data->report_id) : 0;
$id = isset($data->id) ? intval($data->id) : 0;
$department_id = isset($data->department_id) ? intval($data->department_id) : 0;
$is_admin = isset($data->is_admin) ? intval($data->is_admin) : 0;

// Use whichever ID is provided
$final_report_id = $report_id > 0 ? $report_id : $id;

if ($final_report_id == 0) {
    echo json_encode(['success' => false, 'message' => 'Report ID required']);
    exit;
}

try {
    // Check if report exists
    $check_stmt = $db->prepare("SELECT id, department_id FROM reports WHERE id = ?");
    $check_stmt->execute([$final_report_id]);
    
    if ($check_stmt->rowCount() == 0) {
        echo json_encode(['success' => false, 'message' => 'Report not found']);
        exit;
    }
    
    $report = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Determine if this is a department delete or admin delete
    if ($is_admin == 1) {
        // Admin delete - set deleted_by_admin = 1
        $update_stmt = $db->prepare("UPDATE reports SET deleted_by_admin = 1, deleted_at = NOW() WHERE id = ?");
        $update_stmt->execute([$final_report_id]);
        
        echo json_encode(['success' => true, 'message' => 'Report deleted by admin']);
    } else if ($department_id > 0) {
        // Department soft delete - set deleted_by_department = 1
        $update_stmt = $db->prepare("UPDATE reports SET deleted_by_department = 1, deleted_at = NOW() WHERE id = ?");
        $update_stmt->execute([$final_report_id]);
        
        echo json_encode(['success' => true, 'message' => 'Report deleted from department view']);
    } else {
        // Default - soft delete by department
        $update_stmt = $db->prepare("UPDATE reports SET deleted_by_department = 1, deleted_at = NOW() WHERE id = ?");
        $update_stmt->execute([$final_report_id]);
        
        echo json_encode(['success' => true, 'message' => 'Report deleted successfully']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>