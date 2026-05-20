<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

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
$toDeptName = isset($departmentNames[$to_department_id]) ? $departmentNames[$to_department_id] : 'Department ' . $to_department_id;

try {
    $db->beginTransaction();
    
    // Get original report details
    $reportQuery = "SELECT * FROM reports WHERE id = ? AND deleted_by_department = 0 AND deleted_by_admin = 0";
    $reportStmt = $db->prepare($reportQuery);
    $reportStmt->execute([$report_id]);
    $original_report = $reportStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$original_report) {
        echo json_encode(['success' => false, 'message' => 'Report not found']);
        exit;
    }
    
    // Check if report already exists for target department
    $checkExisting = "SELECT id FROM reports 
                      WHERE title = ? AND sent_from_department = ? AND sent_to_department = ? 
                      AND deleted_by_department = 0 AND deleted_by_admin = 0";
    $checkStmt = $db->prepare($checkExisting);
    $checkStmt->execute([$original_report['title'], $from_department_id, $to_department_id]);
    
    if ($checkStmt->rowCount() > 0) {
        // Report already sent, just update status
        $existingReport = $checkStmt->fetch(PDO::FETCH_ASSOC);
        $updateQuery = "UPDATE reports SET status = 'sent', updated_at = NOW() WHERE id = ?";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->execute([$existingReport['id']]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Report already sent to ' . $toDeptName,
            'report_id' => $existingReport['id']
        ]);
        $db->commit();
        exit;
    }
    
    // Create a COPY of the report for target department (NO message sending)
    $insertQuery = "INSERT INTO reports 
                    (title, period, content, status, department_id, sent_from_department, sent_to_department, is_viewed_by_department, created_at) 
                    VALUES (?, ?, ?, 'sent', ?, ?, ?, 0, NOW())";
    $insertStmt = $db->prepare($insertQuery);
    $insertStmt->execute([
        $original_report['title'],
        $original_report['period'],
        $original_report['content'],
        $to_department_id,
        $from_department_id,
        $to_department_id
    ]);
    
    $new_report_id = $db->lastInsertId();
    
    // Update original report status
    $updateQuery = "UPDATE reports SET status = 'sent', sent_to_department = ? WHERE id = ?";
    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->execute([$to_department_id, $report_id]);
    
    $db->commit();
    
    error_log("Report sent successfully: report_id=$report_id, from=$from_department_id, to=$to_department_id, new_report_id=$new_report_id");
    
    echo json_encode([
        'success' => true, 
        'message' => 'Report sent successfully to ' . $toDeptName,
        'report_id' => $new_report_id,
        'original_report_id' => $report_id,
        'from_department' => $from_department_id,
        'to_department' => $to_department_id
    ]);
    
} catch (PDOException $e) {
    $db->rollBack();
    error_log("Send report error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>