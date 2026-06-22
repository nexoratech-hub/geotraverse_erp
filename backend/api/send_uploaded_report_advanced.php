<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

// ============================================================
// DEBUG - Log everything
// ============================================================
error_log("=== SEND UPLOADED REPORT ===");
error_log("Full input: " . print_r($input, true));

// ============================================================
// GET REPORT ID - Look for 'id' first
// ============================================================
$reportId = 0;

// Try to get ID from various places - prioritize 'id'
if (isset($input['id'])) {
    $reportId = (int)$input['id'];
} elseif (isset($input['uploaded_report_id'])) {
    $reportId = (int)$input['uploaded_report_id'];
} elseif (isset($input['uploaded_report_data']['id'])) {
    $reportId = (int)$input['uploaded_report_data']['id'];
} elseif (isset($input['report_id'])) {
    $reportId = (int)$input['report_id'];
}

// If still 0, try to find any key that looks like ID
if ($reportId == 0 && is_array($input)) {
    foreach ($input as $key => $value) {
        if (strtolower($key) === 'id' || strtolower($key) === 'report_id' || strtolower($key) === 'uploaded_report_id') {
            if (is_numeric($value)) {
                $reportId = (int)$value;
                break;
            }
        }
    }
}

$toDeptId = isset($input['to_department_id']) ? (int)$input['to_department_id'] : 0;
$fromDeptId = isset($input['from_department_id']) ? (int)$input['from_department_id'] : 0;
$sentBy = isset($input['sent_by']) ? trim($input['sent_by']) : 'System';
$reportData = isset($input['uploaded_report_data']) ? $input['uploaded_report_data'] : [];

error_log("Final reportId: " . $reportId);
error_log("toDeptId: " . $toDeptId);
error_log("fromDeptId: " . $fromDeptId);

if ($reportId == 0) {
    echo json_encode([
        'success' => false,
        'message' => 'No report ID found. Please provide id.',
        'received_keys' => array_keys($input)
    ]);
    exit;
}

if ($toDeptId == 0 || $fromDeptId == 0) {
    echo json_encode(['success' => false, 'message' => 'Department IDs required']);
    exit;
}

// ============================================================
// FETCH REPORT FROM DATABASE using 'id' column
// ============================================================
try {
    $stmt = $pdo->prepare("SELECT * FROM uploaded_reports WHERE id = ?");
    $stmt->execute([$reportId]);
    $dbReport = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log("Database query result for id $reportId: " . print_r($dbReport, true));
    
    if (!$dbReport) {
        // Check if it exists but is deleted
        $stmt = $pdo->prepare("SELECT * FROM uploaded_reports WHERE id = ? AND is_deleted = 1");
        $stmt->execute([$reportId]);
        $deletedReport = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($deletedReport) {
            echo json_encode([
                'success' => false,
                'message' => 'This report has been deleted. Please restore it first.',
                'report_id' => $reportId,
                'is_deleted' => true
            ]);
            exit;
        }
        
        // Get all available reports for debugging
        $allStmt = $pdo->query("SELECT id, title, is_deleted FROM uploaded_reports");
        $allReports = $allStmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => false,
            'message' => 'Uploaded report not found with id: ' . $reportId,
            'available_reports' => $allReports
        ]);
        exit;
    }
    
    // Merge with provided data
    $reportData = array_merge($dbReport, $reportData);
    $reportData['sent_count'] = ($reportData['sent_count'] ?? 0) + 1;
    $reportData['is_sent'] = 1;
    
    error_log("Final report data: " . print_r($reportData, true));
    
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}

// ============================================================
// CHECK PERMISSION
// ============================================================
if ($fromDeptId != 1 && $reportData['department_id'] != $fromDeptId) {
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit;
}

// ============================================================
// GET DEPARTMENT NAMES
// ============================================================
$fromDeptName = 'Department ' . $fromDeptId;
$toDeptName = 'Department ' . $toDeptId;

try {
    $deptStmt = $pdo->prepare("SELECT name FROM departments WHERE id = ?");
    $deptStmt->execute([$fromDeptId]);
    $result = $deptStmt->fetch(PDO::FETCH_ASSOC);
    if ($result) $fromDeptName = $result['name'];
    
    $deptStmt->execute([$toDeptId]);
    $result = $deptStmt->fetch(PDO::FETCH_ASSOC);
    if ($result) $toDeptName = $result['name'];
} catch(PDOException $e) {}

$reportTitle = $reportData['title'] ?? 'Untitled Uploaded Report';
$reportPeriod = $reportData['period'] ?? 'monthly';
$reportFile = $reportData['file_name'] ?? '';

error_log("Sending: " . $reportTitle . " (ID: " . $reportId . ") to " . $toDeptName);

// ============================================================
// CREATE TABLE IF NOT EXISTS
// ============================================================
try {
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'sent_uploaded_reports'");
    if ($tableCheck->rowCount() == 0) {
        $createTable = "
        CREATE TABLE IF NOT EXISTS `sent_uploaded_reports` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `original_uploaded_report_id` int(11) NOT NULL,
            `uploaded_report_data` longtext DEFAULT NULL,
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
            `uploaded_report_title` varchar(255) DEFAULT NULL,
            `uploaded_report_period` varchar(50) DEFAULT NULL,
            `uploaded_report_file` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $pdo->exec($createTable);
        error_log("Created sent_uploaded_reports table");
    }
} catch(PDOException $e) {
    error_log("Table creation error: " . $e->getMessage());
}

// ============================================================
// INSERT INTO sent_uploaded_reports
// ============================================================
$reportDataJson = json_encode($reportData);

try {
    $stmt = $pdo->prepare("INSERT INTO sent_uploaded_reports (
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
        uploaded_report_file
    ) VALUES (?, ?, ?, ?, ?, NOW(), 0, 0, 1, 1, NOW(), ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $reportId,
        $reportDataJson,
        $fromDeptId,
        $toDeptId,
        $sentBy,
        $fromDeptName,
        $toDeptName,
        $reportTitle,
        $reportPeriod,
        $reportFile
    ]);
    
    $sentId = $pdo->lastInsertId();
    error_log("Inserted into sent_uploaded_reports with ID: " . $sentId);
    
    // ============================================================
    // UPDATE ORIGINAL uploaded_reports
    // ============================================================
    try {
        $updateStmt = $pdo->prepare("UPDATE uploaded_reports SET 
            sent_to_department = ?,
            sent_from_department = ?,
            is_viewed_by_department = 0,
            sent_count = sent_count + 1,
            is_sent = 1,
            last_sent_at = NOW()
            WHERE id = ?");
        $updateStmt->execute([$toDeptId, $fromDeptId, $reportId]);
        error_log("Updated uploaded_reports for ID: " . $reportId);
    } catch(PDOException $e) {
        error_log("Update original error: " . $e->getMessage());
    }
    
    // ============================================================
    // ADD NOTIFICATION
    // ============================================================
    try {
        $notifStmt = $pdo->prepare("
            INSERT INTO notifications (
                department_id, from_department_id, item_type, 
                item_id, item_title, message, is_viewed, created_at
            ) VALUES (?, ?, 'uploaded_report', ?, ?, ?, 0, NOW())
        ");
        $notifStmt->execute([
            $toDeptId,
            $fromDeptId,
            $reportId,
            $reportTitle,
            "Uploaded report \"{$reportTitle}\" sent from {$fromDeptName}"
        ]);
        error_log("Added notification for department: " . $toDeptId);
    } catch(PDOException $e) {
        error_log("Notification error: " . $e->getMessage());
    }
    
    // ============================================================
    // SUCCESS
    // ============================================================
    echo json_encode([
        'success' => true,
        'message' => 'Uploaded report sent successfully',
        'sent_id' => $sentId,
        'uploaded_report_id' => $reportId,
        'to_department' => $toDeptId,
        'to_department_name' => $toDeptName,
        'from_department' => $fromDeptId,
        'from_department_name' => $fromDeptName,
        'report_title' => $reportTitle
    ]);
    
} catch(PDOException $e) {
    error_log("Insert error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>