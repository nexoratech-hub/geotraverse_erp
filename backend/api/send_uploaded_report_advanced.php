<?php
// backend/api/send_uploaded_report_advanced.php
// Send uploaded report - ALLOWS FORWARDING
// Sender keeps their existing copy, Receiver gets new copy

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

if (!$input) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Required fields
if (!isset($input['original_uploaded_report_id']) || !isset($input['to_department_id']) || !isset($input['from_department_id'])) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$originalReportId = (int)$input['original_uploaded_report_id'];
$toDeptId = (int)$input['to_department_id'];
$fromDeptId = (int)$input['from_department_id'];
$sentBy = isset($input['sent_by']) ? $input['sent_by'] : 'System';
$isForward = isset($input['is_forward']) ? (int)$input['is_forward'] : 0;

// Get sender department name
$fromDeptName = 'Department ' . $fromDeptId;
$toDeptName = 'Department ' . $toDeptId;
try {
    $deptStmt = $pdo->prepare("SELECT name FROM departments WHERE id = ?");
    $deptStmt->execute([$fromDeptId]);
    $fromDeptName = $deptStmt->fetchColumn() ?: $fromDeptName;
    $deptStmt->execute([$toDeptId]);
    $toDeptName = $deptStmt->fetchColumn() ?: $toDeptName;
} catch(PDOException $e) {
    // Ignore
}

// ============================================================
// GET UPLOADED REPORT DATA
// ============================================================
$reportData = [];
$reportTitle = 'Uploaded Report';
$reportFile = '';
$reportPeriod = 'monthly';

// 1. Try to get from input
if (isset($input['uploaded_report_data'])) {
    if (is_array($input['uploaded_report_data'])) {
        $reportData = $input['uploaded_report_data'];
    } elseif (is_string($input['uploaded_report_data'])) {
        $reportData = json_decode($input['uploaded_report_data'], true);
        if (!is_array($reportData)) {
            $reportData = [];
        }
    }
}

// 2. Try to get metadata from input
if (isset($input['uploaded_report_title'])) {
    $reportTitle = $input['uploaded_report_title'];
}
if (isset($input['uploaded_report_file'])) {
    $reportFile = $input['uploaded_report_file'];
}
if (isset($input['uploaded_report_period'])) {
    $reportPeriod = $input['uploaded_report_period'];
}

// 3. If empty, try to fetch from database
if (empty($reportData)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM uploaded_reports WHERE id = ? AND is_deleted = 0");
        $stmt->execute([$originalReportId]);
        $dbData = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($dbData) {
            $reportData = $dbData;
            $reportTitle = $dbData['title'] ?? $reportTitle;
            $reportFile = $dbData['file_name'] ?? $reportFile;
            $reportPeriod = $dbData['period'] ?? $reportPeriod;
        }
    } catch(PDOException $e) {
        // Ignore
    }
}

// 4. If still empty, check if this is a copy (forward)
if (empty($reportData)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM uploaded_reports 
                               WHERE original_uploaded_report_id = ? 
                               AND department_id = ? 
                               AND is_sent_copy = 1 
                               AND is_deleted = 0 
                               LIMIT 1");
        $stmt->execute([$originalReportId, $fromDeptId]);
        $copyData = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($copyData) {
            $reportData = $copyData;
            $reportTitle = $copyData['title'] ?? $reportTitle;
            $reportFile = $copyData['file_name'] ?? $reportFile;
            $reportPeriod = $copyData['period'] ?? $reportPeriod;
        }
    } catch(PDOException $e) {
        // Ignore
    }
}

// 5. Ensure report_data is not empty
if (empty($reportData)) {
    $reportData = [
        'id' => $originalReportId,
        'title' => $reportTitle,
        'file_name' => $reportFile,
        'period' => $reportPeriod,
        'uploaded_by' => $sentBy,
        'created_at' => date('Y-m-d H:i:s')
    ];
}

$reportDataJson = json_encode($reportData);
if ($reportDataJson === false || $reportDataJson === null) {
    $reportDataJson = json_encode(['id' => $originalReportId, 'title' => $reportTitle]);
}

// ============================================================
// CREATE TABLES IF NOT EXISTS
// ============================================================
try {
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `sent_uploaded_reports` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `original_uploaded_report_id` int(11) NOT NULL,
        `uploaded_report_data` longtext NOT NULL,
        `from_department_id` int(11) NOT NULL,
        `to_department_id` int(11) NOT NULL,
        `sent_by` varchar(100) DEFAULT NULL,
        `sent_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `is_viewed` tinyint(4) DEFAULT 0,
        `viewed_at` timestamp NULL DEFAULT NULL,
        `is_deleted` tinyint(4) DEFAULT 0,
        `deleted_at` timestamp NULL DEFAULT NULL,
        `sent_count` int(11) DEFAULT 1,
        `is_sent` tinyint(4) DEFAULT 1,
        `last_sent_at` timestamp NULL DEFAULT NULL,
        `from_department_name` varchar(100) DEFAULT NULL,
        `to_department_name` varchar(100) DEFAULT NULL,
        `uploaded_report_title` varchar(255) DEFAULT NULL,
        `uploaded_report_period` varchar(50) DEFAULT NULL,
        `uploaded_report_file` varchar(255) DEFAULT NULL,
        `is_original` tinyint(4) DEFAULT 0,
        `is_sent_copy` tinyint(4) DEFAULT 1,
        PRIMARY KEY (`id`),
        KEY `idx_original_report` (`original_uploaded_report_id`),
        KEY `idx_to_dept` (`to_department_id`),
        KEY `idx_from_dept` (`from_department_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch(PDOException $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Table creation failed: ' . $e->getMessage()]);
    exit;
}

// ============================================================
// CHECK IF ALREADY SENT TO THIS DEPARTMENT
// ============================================================
try {
    $checkStmt = $pdo->prepare("SELECT id, sent_count FROM sent_uploaded_reports 
                                WHERE original_uploaded_report_id = ? 
                                AND to_department_id = ? 
                                AND is_deleted = 0 
                                LIMIT 1");
    $checkStmt->execute([$originalReportId, $toDeptId]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        $updateStmt = $pdo->prepare("UPDATE sent_uploaded_reports SET 
            sent_count = sent_count + 1,
            last_sent_at = NOW(),
            is_sent = 1
            WHERE id = ?");
        $updateStmt->execute([$existing['id']]);
        
        ob_clean();
        echo json_encode([
            'success' => true,
            'message' => 'Report already sent. Updated sent count.',
            'sent_id' => $existing['id'],
            'already_sent' => true,
            'sent_count' => $existing['sent_count'] + 1
        ]);
        exit;
    }
} catch(PDOException $e) {
    // Ignore
}

// ============================================================
// CHECK IF RECEIVER ALREADY HAS A COPY
// ============================================================
try {
    $receiverCheck = $pdo->prepare("SELECT id FROM uploaded_reports 
                                    WHERE original_uploaded_report_id = ? 
                                    AND department_id = ? 
                                    AND is_sent_copy = 1 
                                    AND is_deleted = 0 
                                    LIMIT 1");
    $receiverCheck->execute([$originalReportId, $toDeptId]);
    $existingCopy = $receiverCheck->fetch(PDO::FETCH_ASSOC);
    
    if ($existingCopy) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => '⚠️ This department already has a copy of this report.',
            'error_code' => 'COPY_ALREADY_EXISTS',
            'copy_id' => $existingCopy['id'],
            'to_department' => $toDeptId
        ]);
        exit;
    }
} catch(PDOException $e) {
    // Ignore
}

// ============================================================
// SAVE TO sent_uploaded_reports
// ============================================================
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
        uploaded_report_file,
        is_original,
        is_sent_copy
    ) VALUES (
        :original_id, :report_data, :from_dept, :to_dept,
        :sent_by, NOW(), 0, 0, 1, 1, NOW(),
        :from_name, :to_name, :title, :period, :file, 0, 1
    )");
    
    $stmt->execute([
        ':original_id' => $originalReportId,
        ':report_data' => $reportDataJson,
        ':from_dept' => $fromDeptId,
        ':to_dept' => $toDeptId,
        ':sent_by' => $sentBy,
        ':from_name' => $fromDeptName,
        ':to_name' => $toDeptName,
        ':title' => $reportTitle,
        ':period' => $reportPeriod,
        ':file' => $reportFile
    ]);
    
    $sentId = $pdo->lastInsertId();
    
    // ============================================================
    // CREATE COPY FOR RECEIVER IN uploaded_reports
    // ============================================================
    $copyStmt = $pdo->prepare("INSERT INTO uploaded_reports (
        title,
        description,
        file_name,
        file_path,
        file_size,
        file_type,
        period,
        uploaded_by,
        department_id,
        created_at,
        is_original,
        is_sent_copy,
        original_uploaded_report_id,
        sent_from_department,
        sent_to_department,
        is_viewed_by_department,
        is_deleted
    ) VALUES (
        :title, :description, :file_name, :file_path, :file_size,
        :file_type, :period, :uploaded_by, :department_id, NOW(),
        0, 1, :original_id, :sent_from, :sent_to, 0, 0
    )");
    
    $copyStmt->execute([
        ':title' => $reportTitle,
        ':description' => $reportData['description'] ?? '',
        ':file_name' => $reportFile,
        ':file_path' => $reportData['file_path'] ?? '',
        ':file_size' => $reportData['file_size'] ?? 0,
        ':file_type' => $reportData['file_type'] ?? '',
        ':period' => $reportPeriod,
        ':uploaded_by' => $sentBy,
        ':department_id' => $toDeptId,
        ':original_id' => $originalReportId,
        ':sent_from' => $fromDeptId,
        ':sent_to' => $toDeptId
    ]);
    
    $copyId = $pdo->lastInsertId();
    
    // ============================================================
    // SENDER KEEPS THEIR COPY - NO NEW COPY CREATED
    // ============================================================
    
    // ============================================================
    // UPDATE SENDER'S COPY - Mark as forwarded
    // ============================================================
    try {
        $updateCopyStmt = $pdo->prepare("UPDATE uploaded_reports SET 
            sent_to_department = ?,
            sent_from_department = ?,
            is_sent = 1,
            sent_count = COALESCE(sent_count, 0) + 1,
            last_sent_at = NOW()
            WHERE original_uploaded_report_id = ? 
            AND department_id = ? 
            AND is_sent_copy = 1");
        $updateCopyStmt->execute([$toDeptId, $fromDeptId, $originalReportId, $fromDeptId]);
    } catch(PDOException $e) {
        // Ignore
    }
    
    // ============================================================
    // UPDATE ORIGINAL UPLOADED REPORT - Mark as sent
    // ============================================================
    try {
        $updateStmt = $pdo->prepare("UPDATE uploaded_reports SET 
            sent_to_department = ?,
            sent_from_department = ?,
            is_sent = 1,
            sent_count = COALESCE(sent_count, 0) + 1,
            last_sent_at = NOW()
            WHERE id = ? AND is_original = 1");
        $updateStmt->execute([$toDeptId, $fromDeptId, $originalReportId]);
    } catch(PDOException $e) {
        // Ignore
    }
    
    // ============================================================
    // ADD NOTIFICATION FOR RECIPIENT
    // ============================================================
    try {
        $notifCheck = $pdo->query("SHOW TABLES LIKE 'notifications'");
        if ($notifCheck->rowCount() > 0) {
            $notifStmt = $pdo->prepare("INSERT INTO notifications (
                department_id,
                from_department_id,
                item_type,
                item_id,
                item_title,
                message,
                created_at,
                is_viewed
            ) VALUES (?, ?, 'uploaded_report', ?, ?, ?, NOW(), 0)");
            
            $forwardMsg = $isForward ? ' (Forwarded)' : '';
            $message = "📁 Uploaded report \"{$reportTitle}\" sent from {$fromDeptName}{$forwardMsg}";
            $notifStmt->execute([$toDeptId, $fromDeptId, $originalReportId, $reportTitle, $message]);
        }
    } catch(PDOException $e) {
        // Ignore
    }
    
    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Uploaded report sent successfully' . ($isForward ? ' (Forwarded)' : ''),
        'sent_id' => $sentId,
        'copy_id' => $copyId,
        'original_id' => $originalReportId,
        'to_department' => $toDeptId,
        'to_department_name' => $toDeptName,
        'from_department' => $fromDeptId,
        'from_department_name' => $fromDeptName,
        'report_title' => $reportTitle,
        'is_forward' => $isForward,
        'note' => 'Copy created for receiver only. Sender keeps their existing copy.'
    ]);

} catch(PDOException $e) {
    ob_clean();
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>