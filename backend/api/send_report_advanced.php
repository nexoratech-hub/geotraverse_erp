<?php
// backend/api/send_report_advanced.php

// ============================================================
// ERROR REPORTING
// ============================================================
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// ============================================================
// HEADERS
// ============================================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ============================================================
// DATABASE CONNECTION
// ============================================================
try {
    $pdo = new PDO("mysql:host=localhost;dbname=geotraverse_erp;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit;
}

// ============================================================
// GET INPUT
// ============================================================
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON input'
    ]);
    exit;
}

// ============================================================
// VALIDATE REQUIRED FIELDS
// ============================================================
if (!isset($input['report_id']) || !isset($input['to_department_id']) || !isset($input['from_department_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields: report_id, to_department_id, from_department_id'
    ]);
    exit;
}

$reportId = (int)$input['report_id'];
$toDeptId = (int)$input['to_department_id'];
$fromDeptId = (int)$input['from_department_id'];
$sentBy = isset($input['sent_by']) ? $input['sent_by'] : 'System';

// ============================================================
// GET DEPARTMENT NAMES
// ============================================================
$fromDeptName = 'Department ' . $fromDeptId;
$toDeptName = 'Department ' . $toDeptId;

try {
    $deptStmt = $pdo->prepare("SELECT name FROM departments WHERE id = ?");
    $deptStmt->execute([$fromDeptId]);
    $result = $deptStmt->fetch();
    if ($result) $fromDeptName = $result['name'];
    
    $deptStmt->execute([$toDeptId]);
    $result = $deptStmt->fetch();
    if ($result) $toDeptName = $result['name'];
} catch (PDOException $e) {
    // Use default names if department names can't be fetched
}

// ============================================================
// FIND THE REPORT
// ============================================================
$reportData = null;
$originalReportId = $reportId;
$isForward = 0;
$originalSender = null;
$forwardCount = 0;

// Check if this is an original report
try {
    $stmt = $pdo->prepare("
        SELECT * FROM reports 
        WHERE id = ? AND (is_deleted = 0 OR is_deleted IS NULL)
    ");
    $stmt->execute([$reportId]);
    $report = $stmt->fetch();
    
    if ($report) {
        $reportData = $report;
        $originalReportId = $report['id'];
        $isForward = 0;
    }
} catch (PDOException $e) {
    // Ignore, try sent_reports
}

// If not found in reports, check sent_reports
if (!$reportData) {
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM sent_reports 
            WHERE id = ? AND (is_deleted = 0 OR is_deleted IS NULL)
        ");
        $stmt->execute([$reportId]);
        $sentReport = $stmt->fetch();
        
        if ($sentReport) {
            $reportData = json_decode($sentReport['report_data'], true);
            if ($reportData) {
                $originalReportId = $sentReport['original_report_id'] ?? $reportId;
                $isForward = 1;
                $originalSender = $sentReport['from_department_id'] ?? $fromDeptId;
                $forwardCount = ($sentReport['sent_count'] ?? 0) + 1;
            }
        }
    } catch (PDOException $e) {
        // Ignore
    }
}

// If still not found, return error
if (!$reportData) {
    echo json_encode([
        'success' => false,
        'message' => 'Report not found'
    ]);
    exit;
}

// ============================================================
// BUILD REPORT DATA FOR SENDING
// ============================================================
$reportTitle = $reportData['title'] ?? 'Untitled Report';
$reportPeriod = $reportData['period'] ?? 'monthly';
$reportContent = $reportData['content'] ?? '';
$reportStatus = $reportData['status'] ?? 'draft';
$createdBy = $reportData['created_by'] ?? 'System';
$createdAt = $reportData['created_at'] ?? date('Y-m-d H:i:s');

$sendData = [
    'title' => $reportTitle,
    'period' => $reportPeriod,
    'content' => $reportContent,
    'status' => $reportStatus,
    'created_by' => $createdBy,
    'created_at' => $createdAt,
    'original_report_id' => $originalReportId,
    'sent_from_department' => $fromDeptId,
    'sent_to_department' => $toDeptId,
    'sent_by' => $sentBy,
    'sent_at' => date('Y-m-d H:i:s'),
    'is_original' => 0,
    'is_sent_copy' => 1,
    'is_forward' => $isForward,
    'forward_count' => $forwardCount,
    'original_sender' => $originalSender
];

// ============================================================
// CHECK IF TABLE EXISTS, CREATE IF NOT
// ============================================================
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `sent_reports` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `original_report_id` int(11) NOT NULL,
            `report_data` longtext NOT NULL,
            `from_department_id` int(11) NOT NULL,
            `to_department_id` int(11) NOT NULL,
            `sent_by` varchar(100) DEFAULT NULL,
            `sent_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `is_viewed` tinyint(4) DEFAULT 0,
            `viewed_at` timestamp NULL DEFAULT NULL,
            `is_deleted` tinyint(4) DEFAULT 0,
            `deleted_at` timestamp NULL DEFAULT NULL,
            `sent_count` int(11) DEFAULT 0,
            `is_sent` tinyint(4) DEFAULT 0,
            `last_sent_at` timestamp NULL DEFAULT NULL,
            `from_department_name` varchar(100) DEFAULT NULL,
            `to_department_name` varchar(100) DEFAULT NULL,
            `report_title` varchar(255) DEFAULT NULL,
            `report_period` varchar(50) DEFAULT NULL,
            `report_status` varchar(50) DEFAULT NULL,
            `is_forward` tinyint(4) DEFAULT 0,
            `forward_count` int(11) DEFAULT 0,
            `original_sender_department` int(11) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_original_report` (`original_report_id`),
            KEY `idx_to_dept` (`to_department_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
} catch (PDOException $e) {
    // Table might already exist
}

// ============================================================
// CHECK IF ALREADY SENT TO THIS DEPARTMENT (AVOID DUPLICATES)
// ============================================================
try {
    $checkStmt = $pdo->prepare("
        SELECT id, sent_count FROM sent_reports 
        WHERE original_report_id = ? AND to_department_id = ? AND is_deleted = 0
    ");
    $checkStmt->execute([$originalReportId, $toDeptId]);
    $existing = $checkStmt->fetch();
} catch (PDOException $e) {
    $existing = false;
}

// ============================================================
// INSERT OR UPDATE SENT REPORT
// ============================================================
try {
    if ($existing) {
        // Update existing sent report
        $stmt = $pdo->prepare("
            UPDATE sent_reports SET 
                report_data = ?,
                from_department_id = ?,
                sent_by = ?,
                sent_at = NOW(),
                is_viewed = 0,
                sent_count = sent_count + 1,
                is_sent = 1,
                last_sent_at = NOW(),
                from_department_name = ?,
                to_department_name = ?,
                report_title = ?,
                report_period = ?,
                report_status = ?,
                is_forward = ?,
                forward_count = ?,
                original_sender_department = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            json_encode($sendData, JSON_UNESCAPED_UNICODE),
            $fromDeptId,
            $sentBy,
            $fromDeptName,
            $toDeptName,
            $reportTitle,
            $reportPeriod,
            $reportStatus,
            $isForward,
            $forwardCount,
            $originalSender,
            $existing['id']
        ]);
        $sentId = $existing['id'];
    } else {
        // Insert new sent report
        $stmt = $pdo->prepare("
            INSERT INTO sent_reports (
                original_report_id,
                report_data,
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
                report_title,
                report_period,
                report_status,
                is_forward,
                forward_count,
                original_sender_department
            ) VALUES (
                ?, ?, ?, ?, ?, NOW(), 0, 0, 1, 1, NOW(), ?, ?, ?, ?, ?, ?, ?, ?
            )
        ");
        
        $stmt->execute([
            $originalReportId,
            json_encode($sendData, JSON_UNESCAPED_UNICODE),
            $fromDeptId,
            $toDeptId,
            $sentBy,
            $fromDeptName,
            $toDeptName,
            $reportTitle,
            $reportPeriod,
            $reportStatus,
            $isForward,
            $forwardCount,
            $originalSender
        ]);
        $sentId = $pdo->lastInsertId();
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save sent report: ' . $e->getMessage()
    ]);
    exit;
}

// ============================================================
// UPDATE ORIGINAL REPORT STATUS
// ============================================================
try {
    $stmt = $pdo->prepare("
        UPDATE reports SET 
            sent_to_department = ?,
            sent_from_department = ?,
            is_sent = 1,
            sent_count = COALESCE(sent_count, 0) + 1,
            last_sent_at = NOW()
        WHERE id = ? AND is_original = 1
    ");
    $stmt->execute([$toDeptId, $fromDeptId, $originalReportId]);
} catch (PDOException $e) {
    // Ignore if report doesn't exist or isn't original
}

// ============================================================
// ADD NOTIFICATION
// ============================================================
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
        ) VALUES (?, ?, 'report', ?, ?, ?, NOW(), 0)
    ");
    
    $message = "📊 Report \"{$reportTitle}\" sent from {$fromDeptName}";
    $notifStmt->execute([$toDeptId, $fromDeptId, $sentId, $reportTitle, $message]);
} catch (PDOException $e) {
    // Ignore if notifications table doesn't exist
}

// ============================================================
// SUCCESS RESPONSE
// ============================================================
echo json_encode([
    'success' => true,
    'message' => 'Report sent successfully',
    'data' => [
        'sent_id' => $sentId,
        'original_report_id' => $originalReportId,
        'from_department_id' => $fromDeptId,
        'from_department_name' => $fromDeptName,
        'to_department_id' => $toDeptId,
        'to_department_name' => $toDeptName,
        'report_title' => $reportTitle,
        'is_forward' => $isForward,
        'forward_count' => $forwardCount
    ]
]);
?>