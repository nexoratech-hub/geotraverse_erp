<?php
/**
 * API: Send Report to Another Department
 * Method: POST
 * 
 * Parameters:
 *   - report_id (required) - ID of report to send
 *   - to_department_id (required) - Department receiving the report
 *   - from_department_id (optional) - Department sending the report
 *   - department_id (optional) - Alternative for from_department_id
 *   - user_id (optional) - User sending the report
 * 
 * Response: { "success": true, "message": "Report sent successfully" }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

$report_id = isset($data['report_id']) ? intval($data['report_id']) : 0;

if ($report_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Report ID is required']);
    exit;
}

// Determine receiver department (to_department_id)
$to_department_id = 0;
if (isset($data['to_department_id'])) {
    $to_department_id = intval($data['to_department_id']);
} elseif (isset($data['department_id']) && !isset($data['from_department_id'])) {
    // For Finance dashboard pattern: department_id is the receiver
    $to_department_id = intval($data['department_id']);
}

if ($to_department_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'To department ID is required']);
    exit;
}

// Determine sender department (from_department_id)
$from_department_id = null;

if (isset($data['from_department_id'])) {
    $from_department_id = intval($data['from_department_id']);
} elseif (isset($data['department_id']) && isset($data['to_department_id'])) {
    // For Manager pattern: department_id is the sender
    $from_department_id = intval($data['department_id']);
} elseif (isset($data['user_id'])) {
    // For Super Admin pattern: get department from user
    $user_id = intval($data['user_id']);
    $userQuery = "SELECT department_id FROM users WHERE id = :id";
    $userStmt = $db->prepare($userQuery);
    $userStmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $userStmt->execute();
    if ($userStmt->rowCount() > 0) {
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);
        $from_department_id = $user['department_id'];
    }
}

try {
    // Get the original report
    $getQuery = "SELECT id, title, period, content, status, department_id FROM reports WHERE id = :id AND deleted_by_admin = 0 AND deleted_by_department = 0";
    $getStmt = $db->prepare($getQuery);
    $getStmt->bindParam(':id', $report_id, PDO::PARAM_INT);
    $getStmt->execute();
    
    if ($getStmt->rowCount() == 0) {
        echo json_encode(['success' => false, 'message' => 'Report not found or has been deleted']);
        exit;
    }
    
    $report = $getStmt->fetch(PDO::FETCH_ASSOC);
    
    // If from_department_id still not set, use report's original department
    if ($from_department_id === null) {
        $from_department_id = $report['department_id'];
    }
    
    // Check if report already sent to this department (using sent_from_dept and sent_to_department)
    $checkDuplicate = "SELECT id FROM reports WHERE sent_to_department = :to_dept AND sent_from_dept = :from_dept AND title = :title AND deleted_by_department = 0";
    $checkStmt = $db->prepare($checkDuplicate);
    $checkStmt->bindParam(':to_dept', $to_department_id, PDO::PARAM_INT);
    $checkStmt->bindParam(':from_dept', $from_department_id, PDO::PARAM_INT);
    $checkStmt->bindParam(':title', $report['title'], PDO::PARAM_STR);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Report has already been sent to this department',
            'already_sent' => true
        ]);
        exit;
    }
    
    // Insert a copy as a sent report to the target department
    // Using correct column names from your database: sent_from_dept, sent_to_department
    $insertQuery = "INSERT INTO reports (title, period, content, status, department_id, sent_from_dept, sent_to_department, is_viewed_by_department, created_at) 
                    VALUES (:title, :period, :content, 'sent', :to_dept, :from_dept, :to_dept, 0, NOW())";
    
    $insertStmt = $db->prepare($insertQuery);
    $insertStmt->bindParam(':title', $report['title'], PDO::PARAM_STR);
    $insertStmt->bindParam(':period', $report['period'], PDO::PARAM_STR);
    $insertStmt->bindParam(':content', $report['content'], PDO::PARAM_STR);
    $insertStmt->bindParam(':to_dept', $to_department_id, PDO::PARAM_INT);
    $insertStmt->bindParam(':from_dept', $from_department_id, PDO::PARAM_INT);
    
    if ($insertStmt->execute()) {
        // Also update original report status to 'sent' if not already
        if ($report['status'] !== 'sent') {
            $updateQuery = "UPDATE reports SET status = 'sent' WHERE id = :id";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->bindParam(':id', $report_id, PDO::PARAM_INT);
            $updateStmt->execute();
        }
        
        // Get department name for response message
        $deptQuery = "SELECT name FROM departments WHERE id = :id";
        $deptStmt = $db->prepare($deptQuery);
        $deptStmt->bindParam(':id', $to_department_id, PDO::PARAM_INT);
        $deptStmt->execute();
        $deptName = $deptStmt->rowCount() > 0 ? $deptStmt->fetch(PDO::FETCH_ASSOC)['name'] : 'Department ' . $to_department_id;
        
        echo json_encode([
            'success' => true,
            'message' => "Report sent successfully to {$deptName}",
            'data' => [
                'report_id' => $report_id,
                'to_department_id' => $to_department_id,
                'to_department_name' => $deptName,
                'sent_at' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        $errorInfo = $insertStmt->errorInfo();
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to send report - database insert error: ' . $errorInfo[2]
        ]);
    }
} catch (PDOException $e) {
    error_log("send_report.php error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>