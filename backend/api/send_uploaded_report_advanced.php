<?php
// ============================================================
// send_uploaded_report_advanced.php - COMPLETE FIX
// ============================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

function logMessage($msg) {
    $log = date('Y-m-d H:i:s') . " - " . $msg . "\n";
    file_put_contents(__DIR__ . '/error_log.txt', $log, FILE_APPEND);
    error_log($msg);
}

logMessage("========================================");
logMessage("📤 send_uploaded_report_advanced.php called");

// ============================================================
// DATABASE CONNECTION
// ============================================================
$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    logMessage("✅ Database connected");
} catch(PDOException $e) {
    logMessage("❌ DB Connection failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// ============================================================
// GET INPUT
// ============================================================
$rawInput = file_get_contents('php://input');
logMessage("📤 RAW INPUT: " . $rawInput);

$input = json_decode($rawInput, true);

if (!$input) {
    logMessage("❌ Invalid JSON input");
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

logMessage("📤 PARSED INPUT: " . json_encode($input));

// ============================================================
// EXTRACT DATA - MATCHING sent_uploaded_reports TABLE
// ============================================================
$originalUploadedReportId = isset($input['original_uploaded_report_id']) ? (int)$input['original_uploaded_report_id'] : 0;
$uploadedReportData = isset($input['uploaded_report_data']) ? $input['uploaded_report_data'] : '';
$fromDepartmentId = isset($input['from_department_id']) ? (int)$input['from_department_id'] : 0;
$toDepartmentId = isset($input['to_department_id']) ? (int)$input['to_department_id'] : 0;
$sentBy = isset($input['sent_by']) ? $input['sent_by'] : 'System';
$fromDepartmentName = isset($input['from_department_name']) ? $input['from_department_name'] : 'Super Admin';
$toDepartmentName = isset($input['to_department_name']) ? $input['to_department_name'] : 'Department';
$uploadedReportTitle = isset($input['uploaded_report_title']) ? $input['uploaded_report_title'] : 'Uploaded Report';
$uploadedReportPeriod = isset($input['uploaded_report_period']) ? $input['uploaded_report_period'] : 'monthly';
$uploadedReportFile = isset($input['uploaded_report_file']) ? $input['uploaded_report_file'] : '';

logMessage("📊 Data: original_id=$originalUploadedReportId, from=$fromDepartmentId, to=$toDepartmentId, title=$uploadedReportTitle");

if ($originalUploadedReportId <= 0 || $toDepartmentId <= 0 || $fromDepartmentId <= 0) {
    logMessage("❌ Missing required fields");
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields',
        'debug' => [
            'original_uploaded_report_id' => $originalUploadedReportId,
            'to_department_id' => $toDepartmentId,
            'from_department_id' => $fromDepartmentId
        ]
    ]);
    exit;
}

try {
    // ============================================================
    // STEP 1: CHECK IF ORIGINAL REPORT EXISTS
    // ============================================================
    $checkOriginal = $pdo->prepare("SELECT * FROM uploaded_reports WHERE id = ? AND is_deleted != 1");
    $checkOriginal->execute([$originalUploadedReportId]);
    $originalReport = $checkOriginal->fetch();
    
    if (!$originalReport) {
        logMessage("❌ Original report not found: $originalUploadedReportId");
        echo json_encode(['success' => false, 'message' => 'Original report not found']);
        exit;
    }
    
    logMessage("✅ Original report found: ID=" . $originalReport['id'] . ", Title=" . $originalReport['title']);

    // ============================================================
    // STEP 2: CHECK IF ALREADY SENT TO THIS DEPARTMENT
    // ============================================================
    $checkStmt = $pdo->prepare("SELECT id FROM sent_uploaded_reports 
        WHERE original_uploaded_report_id = ? AND to_department_id = ? AND is_deleted = 0");
    $checkStmt->execute([$originalUploadedReportId, $toDepartmentId]);
    $existing = $checkStmt->fetch();
    
    if ($existing) {
        logMessage("⚠️ Already sent to department $toDepartmentId, updating...");
    }

    // ============================================================
    // STEP 3: PREPARE DATA FOR INSERT
    // ============================================================
    // If uploaded_report_data is not provided, build it from original report
    if (empty($uploadedReportData) || $uploadedReportData === 'null') {
        $reportData = [
            'id' => $originalReport['id'],
            'title' => $originalReport['title'],
            'description' => $originalReport['description'] ?? '',
            'file_name' => $originalReport['file_name'],
            'file_path' => $originalReport['file_path'],
            'file_size' => $originalReport['file_size'] ?? 0,
            'file_type' => $originalReport['file_type'] ?? '',
            'period' => $originalReport['period'] ?? 'monthly',
            'uploaded_by' => $originalReport['uploaded_by'],
            'department_id' => $fromDepartmentId,
            'created_at' => $originalReport['created_at'],
            'from_department_id' => $fromDepartmentId,
            'to_department_id' => $toDepartmentId,
            'sent_by' => $sentBy,
            'sent_at' => date('Y-m-d H:i:s'),
            'from_department_name' => $fromDepartmentName,
            'to_department_name' => $toDepartmentName
        ];
        $uploadedReportData = json_encode($reportData, JSON_UNESCAPED_UNICODE);
        logMessage("📄 Built report data from original");
    }

    // ============================================================
    // STEP 4: INSERT OR UPDATE
    // ============================================================
    if ($existing) {
        // UPDATE existing record
        $updateSql = "UPDATE sent_uploaded_reports SET 
            uploaded_report_data = ?,
            sent_by = ?,
            sent_at = NOW(),
            is_viewed = 0,
            sent_count = sent_count + 1,
            is_sent = 1,
            last_sent_at = NOW(),
            from_department_name = ?,
            to_department_name = ?,
            uploaded_report_title = ?,
            uploaded_report_period = ?,
            uploaded_report_file = ?
            WHERE id = ?";
        
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([
            $uploadedReportData,
            $sentBy,
            $fromDepartmentName,
            $toDepartmentName,
            $uploadedReportTitle,
            $uploadedReportPeriod,
            $uploadedReportFile,
            $existing['id']
        ]);
        $sentId = $existing['id'];
        logMessage("🔄 Updated existing record ID: $sentId");
    } else {
        // INSERT new record
        $insertSql = "INSERT INTO sent_uploaded_reports (
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
        ) VALUES (
            ?, ?, ?, ?, ?, NOW(), 0, 0, 1, 1, NOW(), ?, ?, ?, ?, ?
        )";
        
        logMessage("📝 Insert SQL: $insertSql");
        
        $insertStmt = $pdo->prepare($insertSql);
        $insertResult = $insertStmt->execute([
            $originalUploadedReportId,
            $uploadedReportData,
            $fromDepartmentId,
            $toDepartmentId,
            $sentBy,
            $fromDepartmentName,
            $toDepartmentName,
            $uploadedReportTitle,
            $uploadedReportPeriod,
            $uploadedReportFile
        ]);
        
        if (!$insertResult) {
            $errorInfo = $insertStmt->errorInfo();
            logMessage("❌ Insert failed: " . print_r($errorInfo, true));
            throw new Exception("Insert failed: " . $errorInfo[2]);
        }
        
        $sentId = $pdo->lastInsertId();
        logMessage("✅ INSERTED new record ID: $sentId, Rows affected: " . $insertStmt->rowCount());
    }

    // ============================================================
    // STEP 5: UPDATE ORIGINAL REPORT
    // ============================================================
    try {
        $updateOriginal = $pdo->prepare("UPDATE uploaded_reports SET 
            sent_to_department = ?,
            sent_from_department = ?,
            is_sent = 1,
            sent_count = COALESCE(sent_count, 0) + 1,
            last_sent_at = NOW()
            WHERE id = ?");
        $updateOriginal->execute([$toDepartmentId, $fromDepartmentId, $originalUploadedReportId]);
        logMessage("✅ Original report updated, rows affected: " . $updateOriginal->rowCount());
    } catch(PDOException $e) {
        logMessage("⚠️ Could not update original: " . $e->getMessage());
    }

    // ============================================================
    // STEP 6: VERIFY THE INSERT
    // ============================================================
    $verifyStmt = $pdo->prepare("SELECT * FROM sent_uploaded_reports WHERE id = ?");
    $verifyStmt->execute([$sentId]);
    $verified = $verifyStmt->fetch();
    
    if ($verified) {
        logMessage("✅ VERIFIED: Record exists in sent_uploaded_reports with ID: $sentId");
        logMessage("✅ Verified data: " . json_encode($verified));
    } else {
        logMessage("❌ VERIFICATION FAILED: Record not found in sent_uploaded_reports!");
    }

    // ============================================================
    // STEP 7: ADD NOTIFICATION
    // ============================================================
    try {
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
        
        $message = "📄 Uploaded Report \"{$uploadedReportTitle}\" sent from {$fromDepartmentName} to {$toDepartmentName}";
        $notifStmt->execute([$toDepartmentId, $fromDepartmentId, $originalUploadedReportId, $uploadedReportTitle, $message]);
        logMessage("✅ Notification added for department: $toDepartmentId");
    } catch(PDOException $e) {
        logMessage("⚠️ Could not add notification: " . $e->getMessage());
    }

    // ============================================================
    // STEP 8: RETURN SUCCESS
    // ============================================================
    $response = [
        'success' => true,
        'message' => 'Uploaded report sent successfully',
        'sent_id' => $sentId,
        'original_report_id' => $originalUploadedReportId,
        'to_department' => $toDepartmentId,
        'to_department_name' => $toDepartmentName,
        'from_department' => $fromDepartmentId,
        'from_department_name' => $fromDepartmentName,
        'report_title' => $uploadedReportTitle,
        'verified' => $verified ? true : false
    ];
    
    logMessage("📤 RESPONSE: " . json_encode($response));
    echo json_encode($response);

} catch(PDOException $e) {
    logMessage("❌ PDO Exception: " . $e->getMessage());
    logMessage("❌ SQL State: " . $e->getCode());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'sql_state' => $e->getCode()
    ]);
} catch(Exception $e) {
    logMessage("❌ Exception: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>