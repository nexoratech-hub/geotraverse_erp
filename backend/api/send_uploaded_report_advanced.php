<?php
// backend/api/send_uploaded_report_advanced.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

error_reporting(0);
ini_set('display_errors', 0);
ob_clean();

$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['uploaded_report_id']) || !isset($input['to_department_id']) || !isset($input['from_department_id'])) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$reportId = (int)$input['uploaded_report_id'];
$toDeptId = (int)$input['to_department_id'];
$fromDeptId = (int)$input['from_department_id'];
$sentBy = isset($input['sent_by']) ? $input['sent_by'] : 'System';

// Get department names
$fromDeptName = 'Department ' . $fromDeptId;
$toDeptName = 'Department ' . $toDeptId;
try {
    $deptStmt = $pdo->prepare("SELECT name FROM departments WHERE id = ?");
    $deptStmt->execute([$fromDeptId]);
    $fromDeptName = $deptStmt->fetchColumn() ?: $fromDeptName;
    $deptStmt->execute([$toDeptId]);
    $toDeptName = $deptStmt->fetchColumn() ?: $toDeptName;
} catch(PDOException $e) {}

// Find the uploaded report
$stmt = $pdo->prepare("
    SELECT * FROM uploaded_reports 
    WHERE id = ? AND (is_deleted = 0 OR is_deleted IS NULL)
");
$stmt->execute([$reportId]);
$report = $stmt->fetch();

if (!$report) {
    // Check sent_uploaded_reports
    $stmt = $pdo->prepare("
        SELECT * FROM sent_uploaded_reports 
        WHERE id = ? AND (is_deleted = 0 OR is_deleted IS NULL)
    ");
    $stmt->execute([$reportId]);
    $sentReport = $stmt->fetch();
    
    if ($sentReport) {
        $reportData = json_decode($sentReport['uploaded_report_data'], true);
        $report = $reportData;
        $report['id'] = $sentReport['id'];
        $report['original_uploaded_report_id'] = $sentReport['original_uploaded_report_id'];
    }
}

if (!$report) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Uploaded report not found']);
    exit;
}

$originalReportId = $report['original_uploaded_report_id'] ?? $report['id'];

// Create copy data
$reportData = [
    'title' => $report['title'] ?? 'Untitled Uploaded Report',
    'description' => $report['description'] ?? '',
    'file_name' => $report['file_name'] ?? '',
    'file_path' => $report['file_path'] ?? '',
    'file_size' => $report['file_size'] ?? 0,
    'file_type' => $report['file_type'] ?? '',
    'period' => $report['period'] ?? 'monthly',
    'uploaded_by' => $report['uploaded_by'] ?? 'System',
    'created_at' => $report['created_at'] ?? date('Y-m-d H:i:s'),
    'department_id' => $report['department_id'] ?? $fromDeptId,
    'original_uploaded_report_id' => $originalReportId,
    'is_original' => 0,
    'is_sent_copy' => 1,
    'sent_from_department' => $fromDeptId,
    'sent_to_department' => $toDeptId,
    'sent_by' => $sentBy,
    'sent_at' => date('Y-m-d H:i:s'),
    'is_viewed_by_department' => 0,
    'forward_count' => ($report['forward_count'] ?? 0) + 1,
    'is_forward' => isset($report['sent_from_department']) && $report['sent_from_department'] != $fromDeptId ? 1 : 0
];

// Create new copy in sent_uploaded_reports
$stmt = $pdo->prepare("
    INSERT INTO sent_uploaded_reports (
        original_uploaded_report_id,
        uploaded_report_data,
        from_department_id,
        to_department_id,
        sent_by,
        sent_at,
        is_viewed,
        is_deleted,
        sent_count,
        is_sent,
        last_sent_at,
        from_department_name,
        to_department_name,
        uploaded_report_title,
        uploaded_report_period,
        uploaded_report_file,
        is_forward,
        forward_count,
        original_sender_department
    ) VALUES (
        :original_uploaded_report_id,
        :uploaded_report_data,
        :from_department_id,
        :to_department_id,
        :sent_by,
        NOW(),
        0,
        0,
        1,
        1,
        NOW(),
        :from_department_name,
        :to_department_name,
        :uploaded_report_title,
        :uploaded_report_period,
        :uploaded_report_file,
        :is_forward,
        :forward_count,
        :original_sender
    )
");

$stmt->execute([
    ':original_uploaded_report_id' => $originalReportId,
    ':uploaded_report_data' => json_encode($reportData),
    ':from_department_id' => $fromDeptId,
    ':to_department_id' => $toDeptId,
    ':sent_by' => $sentBy,
    ':from_department_name' => $fromDeptName,
    ':to_department_name' => $toDeptName,
    ':uploaded_report_title' => $reportData['title'],
    ':uploaded_report_period' => $reportData['period'],
    ':uploaded_report_file' => $reportData['file_name'],
    ':is_forward' => $reportData['is_forward'],
    ':forward_count' => $reportData['forward_count'],
    ':original_sender' => $report['sent_from_department'] ?? $fromDeptId
]);

$newSentId = $pdo->lastInsertId();

// Update original if it's the original
if (isset($report['is_original']) && $report['is_original'] == 1) {
    $stmt = $pdo->prepare("
        UPDATE uploaded_reports SET 
            sent_to_department = ?,
            sent_from_department = ?,
            is_sent = 1,
            sent_count = COALESCE(sent_count, 0) + 1,
            last_sent_at = NOW()
        WHERE id = ? AND is_original = 1
    ");
    $stmt->execute([$toDeptId, $fromDeptId, $reportId]);
}

// Add notification
try {
    $notifStmt = $pdo->prepare("
        INSERT INTO notifications (
            department_id,
            from_department_id,
            item_type,
            item_id,
            item_title,
            message,
            created_at,
            is_viewed
        ) VALUES (?, ?, 'sent_uploaded_report', ?, ?, ?, NOW(), 0)
    ");
    $message = "📁 Uploaded Report \"{$reportData['title']}\" sent from {$fromDeptName}";
    $notifStmt->execute([$toDeptId, $fromDeptId, $newSentId, $reportData['title'], $message]);
} catch(PDOException $e) {}

ob_clean();
echo json_encode([
    'success' => true,
    'message' => 'Uploaded report sent successfully with new unique copy',
    'data' => [
        'sent_id' => $newSentId,
        'original_uploaded_report_id' => $originalReportId,
        'from_department' => $fromDeptId,
        'to_department' => $toDeptId,
        'report_title' => $reportData['title'],
        'is_forward' => $reportData['is_forward'],
        'forward_count' => $reportData['forward_count'],
        'note' => 'This is a new independent copy with unique ID ' . $newSentId
    ]
]);
?>