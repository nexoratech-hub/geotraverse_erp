<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, DELETE, OPTIONS');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

// Log for debugging
error_log("Delete report received: " . print_r($data, true));

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit;
}

// Check for different parameter names that dashboards might send
$report_id = null;

if (isset($data->report_id)) {
    $report_id = intval($data->report_id);
} elseif (isset($data->id)) {
    $report_id = intval($data->id);
}

if (!$report_id) {
    echo json_encode(['success' => false, 'message' => 'Report ID required']);
    exit;
}

$department_id = isset($data->department_id) ? intval($data->department_id) : null;
$user_id = isset($data->user_id) ? intval($data->user_id) : null;
$is_admin = isset($data->is_admin) ? intval($data->is_admin) : 0;

// If department_id not provided, try to get from user_id
if (!$department_id && $user_id) {
    $userQuery = "SELECT department_id FROM users WHERE id = ?";
    $userStmt = $db->prepare($userQuery);
    $userStmt->execute([$user_id]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $department_id = $user['department_id'];
    }
}

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
    
    // SOFT DELETE based on who is deleting
    if ($is_admin == 1) {
        // Admin soft delete
        $updateQuery = "UPDATE reports SET deleted_by_admin = 1, deleted_at = NOW() WHERE id = ?";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->execute([$report_id]);
        
        $deleteType = 'admin';
        
    } else if ($department_id) {
        // Department soft delete - check if this department owns or received the report
        if ($report['department_id'] == $department_id || $report['sent_to_department'] == $department_id || $report['sent_from_department'] == $department_id) {
            $updateQuery = "UPDATE reports SET deleted_by_department = 1, deleted_at = NOW() WHERE id = ?";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->execute([$report_id]);
            $deleteType = 'department';
        } else {
            echo json_encode(['success' => false, 'message' => 'You are not authorized to delete this report']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Department ID or Admin flag required']);
        exit;
    }
    
    // Insert into recycle bin for tracking (optional, skip if table doesn't exist)
    try {
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
    } catch (PDOException $e) {
        // Recycle bin table might not exist, just log and continue
        error_log("Recycle bin insert failed: " . $e->getMessage());
    }
    
    $db->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Report moved to recycle bin',
        'soft_deleted' => true,
        'delete_type' => $deleteType,
        'report_id' => $report_id
    ]);
    
} catch (PDOException $e) {
    $db->rollBack();
    error_log("Delete report error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>