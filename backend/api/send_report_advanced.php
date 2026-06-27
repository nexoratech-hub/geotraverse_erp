<?php
// backend/api/send_report_advanced.php
// Send added/generated report to another department

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
if (!isset($input['report_id']) || !isset($input['to_department_id']) || !isset($input['from_department_id'])) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$reportId = (int)$input['report_id'];
$toDeptId = (int)$input['to_department_id'];
$fromDeptId = (int)$input['from_department_id'];
$sentBy = isset($input['sent_by']) ? $input['sent_by'] : 'System';
$isForward = isset($input['is_forward']) ? (int)$input['is_forward'] : 0;

// Get report data
$reportTitle = isset($input['report_title']) ? $input['report_title'] : 'Report';
$reportPeriod = isset($input['report_period']) ? $input['report_period'] : 'monthly';
$reportContent = isset($input['report_content']) ? $input['report_content'] : '';
$reportStatus = isset($input['report_status']) ? $input['report_status'] : 'draft';
$reportData = isset($input['report_data']) ? $input['report_data'] : [];

// If report_data is empty, try to fetch from database
if (empty($reportData)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM reports WHERE id = ? AND is_deleted = 0");
        $stmt->execute([$reportId]);
        $dbData = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($dbData) {
            $reportData = $dbData;
            $reportTitle = $dbData['title'] ?? $reportTitle;
            $reportPeriod = $dbData['period'] ?? $reportPeriod;
            $reportContent = $dbData['content'] ?? $reportContent;
            $reportStatus = $dbData['status'] ?? $reportStatus;
        }
    } catch(PDOException $e) {
        // Ignore
    }
}

// Ensure report_data is not empty
if (empty($reportData)) {
    $reportData = [
        'id' => $reportId,
        'title' => $reportTitle,
        'period' => $reportPeriod,
        'content' => $reportContent,
        'status' => $reportStatus,
        'created_by' => $sentBy,
        'created_at' => date('Y-m-d H:i:s')
    ];
}

$reportDataJson = json_encode($reportData);
if ($reportDataJson === false || $reportDataJson === null) {
    $reportDataJson = json_encode(['id' => $reportId, 'title' => $reportTitle]);
}

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
// CREATE TABLES IF NOT EXISTS (With all columns)
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
        `sent_count` int(11) DEFAULT 1,
        `is_sent` tinyint(4) DEFAULT 1,
        `last_sent_at` timestamp NULL DEFAULT NULL,
        `from_department_name` varchar(100) DEFAULT NULL,
        `to_department_name` varchar(100) DEFAULT NULL,
        `report_title` varchar(255) DEFAULT NULL,
        `report_period` varchar(50) DEFAULT NULL,
        `report_status` varchar(50) DEFAULT NULL,
        `is_forward` tinyint(4) DEFAULT 0,
        `forward_count` int(11) DEFAULT 0,
        `original_sender_department` int(11) DEFAULT NULL,
        `is_original` tinyint(4) DEFAULT 0,
        `is_sent_copy` tinyint(4) DEFAULT 1,
        PRIMARY KEY (`id`),
        KEY `idx_original_report` (`original_report_id`),
        KEY `idx_to_dept` (`to_department_id`)
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
    $checkStmt = $pdo->prepare("SELECT id, sent_count FROM sent_reports 
                                WHERE original_report_id = ? 
                                AND to_department_id = ? 
                                AND is_deleted = 0 
                                LIMIT 1");
    $checkStmt->execute([$reportId, $toDeptId]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        $updateStmt = $pdo->prepare("UPDATE sent_reports SET 
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
    $receiverCheck = $pdo->prepare("SELECT id FROM reports 
                                    WHERE original_report_id = ? 
                                    AND department_id = ? 
                                    AND is_sent_copy = 1 
                                    AND is_deleted = 0 
                                    LIMIT 1");
    $receiverCheck->execute([$reportId, $toDeptId]);
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
// SAVE TO sent_reports
// ============================================================
try {
    $stmt = $pdo->prepare("INSERT INTO sent_reports (
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
        original_sender_department,
        is_original,
        is_sent_copy
    ) VALUES (
        :report_id, :report_data, :from_dept, :to_dept,
        :sent_by, NOW(), 0, 0, 1, 1, NOW(),
        :from_name, :to_name, :title, :period, :status, 
        :is_forward, :forward_count, :original_sender, 0, 1
    )");
    
    $originalSender = isset($input['original_sender_department']) ? (int)$input['original_sender_department'] : $fromDeptId;
    
    $stmt->execute([
        ':report_id' => $reportId,
        ':report_data' => $reportDataJson,
        ':from_dept' => $fromDeptId,
        ':to_dept' => $toDeptId,
        ':sent_by' => $sentBy,
        ':from_name' => $fromDeptName,
        ':to_name' => $toDeptName,
        ':title' => $reportTitle,
        ':period' => $reportPeriod,
        ':status' => $reportStatus,
        ':is_forward' => $isForward,
        ':forward_count' => $isForward ? 1 : 0,
        ':original_sender' => $originalSender
    ]);
    
    $sentId = $pdo->lastInsertId();
    
    // ============================================================
    // CREATE COPY FOR RECEIVER IN reports TABLE
    // ============================================================
    $copyStmt = $pdo->prepare("INSERT INTO reports (
        title,
        period,
        content,
        status,
        department_id,
        created_by,
        created_at,
        is_original,
        is_sent_copy,
        original_report_id,
        sent_from_department,
        sent_to_department,
        is_viewed_by_department,
        is_deleted
    ) VALUES (
        :title, :period, :content, :status, :department_id,
        :created_by, NOW(), 0, 1, :original_id, :sent_from, :sent_to, 0, 0
    )");
    
    $copyStmt->execute([
        ':title' => $reportTitle,
        ':period' => $reportPeriod,
        ':content' => $reportContent,
        ':status' => $reportStatus,
        ':department_id' => $toDeptId,
        ':created_by' => $sentBy,
        ':original_id' => $reportId,
        ':sent_from' => $fromDeptId,
        ':sent_to' => $toDeptId
    ]);
    
    $copyId = $pdo->lastInsertId();
    
    // ============================================================
    // UPDATE ORIGINAL REPORT - Mark as sent
    // ============================================================
    try {
        $updateStmt = $pdo->prepare("UPDATE reports SET 
            sent_to_department = ?,
            sent_from_department = ?,
            is_sent = 1,
            sent_count = COALESCE(sent_count, 0) + 1,
            last_sent_at = NOW()
            WHERE id = ? AND is_original = 1");
        $updateStmt->execute([$toDeptId, $fromDeptId, $reportId]);
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
            ) VALUES (?, ?, 'report', ?, ?, ?, NOW(), 0)");
            
            $forwardMsg = $isForward ? ' (Forwarded)' : '';
            $message = "📊 Report \"{$reportTitle}\" sent from {$fromDeptName}{$forwardMsg}";
            $notifStmt->execute([$toDeptId, $fromDeptId, $reportId, $reportTitle, $message]);
        }
    } catch(PDOException $e) {
        // Ignore
    }
    
    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Report sent successfully' . ($isForward ? ' (Forwarded)' : ''),
        'sent_id' => $sentId,
        'copy_id' => $copyId,
        'original_id' => $reportId,
        'to_department' => $toDeptId,
        'to_department_name' => $toDeptName,
        'from_department' => $fromDeptId,
        'from_department_name' => $fromDeptName,
        'report_title' => $reportTitle,
        'is_forward' => $isForward,
        'note' => 'Copy created for receiver. Sender keeps original.'
    ]);

} catch(PDOException $e) {
    ob_clean();
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>