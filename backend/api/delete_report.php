<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, DELETE, OPTIONS');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!$data || !isset($data->report_id)) {
    echo json_encode(['success' => false, 'message' => 'Report ID required']);
    exit;
}

$report_id = intval($data->report_id);
$department_id = isset($data->department_id) ? intval($data->department_id) : null;
$user_id = isset($data->user_id) ? intval($data->user_id) : null;
$is_admin = isset($data->is_admin) ? intval($data->is_admin) : 0;

try {
    $db->beginTransaction();
    
    // Get report details
    $reportQuery = "SELECT * FROM reports WHERE id = ?";
    $reportStmt = $db->prepare($reportQuery);
    $reportStmt->execute([$report_id]);
    $report = $reportStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$report) {
        echo json_encode(['success' => false, 'message' => 'Report not found']);
        exit;
    }
    
    if ($is_admin == 1) {
        // Admin soft delete
        $updateQuery = "UPDATE reports SET deleted_by_admin = 1, deleted_at = NOW() WHERE id = ?";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->execute([$report_id]);
        
    } else if ($department_id) {
        // Department soft delete
        $updateQuery = "UPDATE reports SET deleted_by_department = 1, deleted_at = NOW() WHERE id = ?";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->execute([$report_id]);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Department ID or Admin flag required']);
        exit;
    }
    
    // Insert into recycle bin
    $recycleQuery = "INSERT INTO recycle_bin 
                    (original_table, original_id, deleted_data, deleted_by_department_id, deleted_by_user_id, deleted_by_admin, deleted_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $recycleStmt = $db->prepare($recycleQuery);
    $recycleStmt->execute([
        'reports',
        $report_id,
        json_encode($report),
        $department_id,
        $user_id,
        $is_admin
    ]);
    
    $db->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Report moved to recycle bin',
        'soft_deleted' => true
    ]);
    
} catch (PDOException $e) {
    $db->rollBack();
    error_log("Delete report error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>