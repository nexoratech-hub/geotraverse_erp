<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

// Log received data for debugging
error_log("Send report received: " . print_r($data, true));

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit;
}

if (!isset($data->report_id)) {
    echo json_encode(['success' => false, 'message' => 'Report ID required']);
    exit;
}

if (!isset($data->to_department_id)) {
    echo json_encode(['success' => false, 'message' => 'Destination department ID required']);
    exit;
}

$report_id = intval($data->report_id);
$to_department_id = intval($data->to_department_id);
$from_department_id = isset($data->from_department_id) ? intval($data->from_department_id) : null;

// If from_department_id not provided, try to get it from the report
if (!$from_department_id) {
    $checkQuery = "SELECT department_id FROM reports WHERE id = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$report_id]);
    $reportCheck = $checkStmt->fetch(PDO::FETCH_ASSOC);
    if ($reportCheck) {
        $from_department_id = $reportCheck['department_id'];
    }
}

if (!$from_department_id) {
    echo json_encode(['success' => false, 'message' => 'From department ID could not be determined']);
    exit;
}

$departmentNames = [
    1 => 'Super Admin', 2 => 'Finance', 3 => 'Sales & Marketing', 4 => 'Manager',
    5 => 'Secretary', 6 => 'Bricks & Timber', 7 => 'Aluminium', 8 => 'Town Planning',
    9 => 'Architectural', 10 => 'Survey', 11 => 'Construction', 12 => 'Hatimiliki'
];
$fromDeptName = isset($departmentNames[$from_department_id]) ? $departmentNames[$from_department_id] : 'Department ' . $from_department_id;
$toDeptName = isset($departmentNames[$to_department_id]) ? $departmentNames[$to_department_id] : 'Department ' . $to_department_id;

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
    
    // Create a copy of the report for the target department (if sending to different department)
    if ($to_department_id != $from_department_id) {
        // Insert a new report entry for the target department
        $insertQuery = "INSERT INTO reports (title, period, content, status, department_id, sent_from_department, sent_to_department, is_viewed_by_department, created_at) 
                        VALUES (?, ?, ?, 'sent', ?, ?, ?, 0, NOW())";
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->execute([
            $report['title'],
            $report['period'],
            $report['content'],
            $to_department_id,
            $from_department_id,
            $to_department_id
        ]);
        
        $new_report_id = $db->lastInsertId();
        
        // Update original report status
        $updateQuery = "UPDATE reports SET status = 'sent', sent_to_department = ? WHERE id = ?";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->execute([$to_department_id, $report_id]);
        
        $sentReportId = $new_report_id;
    } else {
        // Just update the existing report
        $updateQuery = "UPDATE reports SET status = 'sent', sent_to_department = ? WHERE id = ?";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->execute([$to_department_id, $report_id]);
        $sentReportId = $report_id;
    }
    
    // Create message content for notification
    $messageText = "📊 NEW REPORT RECEIVED\n\n";
    $messageText .= "From: " . $fromDeptName . "\n";
    $messageText .= "To: " . $toDeptName . "\n";
    $messageText .= "Title: " . $report['title'] . "\n";
    $messageText .= "Period: " . $report['period'] . "\n";
    $messageText .= "Sent: " . date('Y-m-d H:i:s') . "\n\n";
    $messageText .= "Please login to view this report in the Reports section.\n\n";
    $messageText .= "Report Content Preview:\n";
    $messageText .= "----------------------------------------\n";
    $messageText .= substr($report['content'], 0, 500);
    if (strlen($report['content']) > 500) $messageText .= "...";
    $messageText .= "\n----------------------------------------";
    
    // Find or create conversation between departments
    $convQuery = "SELECT id FROM conversations 
                  WHERE (sender_dept = ? AND receiver_dept = ?) 
                  OR (sender_dept = ? AND receiver_dept = ?)
                  AND deleted_by_department = 0 AND deleted_by_admin = 0
                  LIMIT 1";
    $convStmt = $db->prepare($convQuery);
    $convStmt->execute([$from_department_id, $to_department_id, $to_department_id, $from_department_id]);
    
    if ($convStmt->rowCount() > 0) {
        $convRow = $convStmt->fetch(PDO::FETCH_ASSOC);
        $conversation_id = $convRow['id'];
    } else {
        // Get user IDs for both departments
        $userQuery = "SELECT id FROM users WHERE department_id = ? AND is_active = 1 LIMIT 1";
        $userStmt = $db->prepare($userQuery);
        $userStmt->execute([$from_department_id]);
        $senderUser = $userStmt->fetch(PDO::FETCH_ASSOC);
        $sender_user_id = $senderUser ? $senderUser['id'] : 1;
        
        $userStmt2 = $db->prepare($userQuery);
        $userStmt2->execute([$to_department_id]);
        $receiverUser = $userStmt2->fetch(PDO::FETCH_ASSOC);
        $receiver_user_id = $receiverUser ? $receiverUser['id'] : 1;
        
        $subject = "Report: " . substr($report['title'], 0, 100);
        $createConv = "INSERT INTO conversations (user_id, admin_id, sender_dept, receiver_dept, subject, status, created_at, updated_at) 
                       VALUES (?, ?, ?, ?, ?, 'active', NOW(), NOW())";
        $createStmt = $db->prepare($createConv);
        $createStmt->execute([$sender_user_id, $receiver_user_id, $from_department_id, $to_department_id, $subject]);
        $conversation_id = $db->lastInsertId();
    }
    
    // Send notification message
    $msgQuery = "INSERT INTO messages 
                 (sender_dept, receiver_dept, conversation_id, sender_id, receiver_id, message, is_read, status, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, 0, 'sent', NOW())";
    $msgStmt = $db->prepare($msgQuery);
    $msgStmt->execute([
        $from_department_id, 
        $to_department_id, 
        $conversation_id, 
        1, 
        1, 
        $messageText
    ]);
    
    $db->commit();
    
    error_log("Report sent successfully: report_id=$report_id, from=$from_department_id, to=$to_department_id");
    
    echo json_encode([
        'success' => true, 
        'message' => 'Report sent successfully to ' . $toDeptName,
        'report_id' => $sentReportId,
        'from_department' => $from_department_id,
        'to_department' => $to_department_id
    ]);
    
} catch (PDOException $e) {
    $db->rollBack();
    error_log("Send report error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>