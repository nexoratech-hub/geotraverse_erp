<?php
// send_uploaded_report_advanced.php - FIXED: Save to sent_uploaded_reports table

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 0);

$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Required fields
if (!isset($input['uploaded_report_id']) && !isset($input['document_id'])) {
    echo json_encode(['success' => false, 'message' => 'Report ID or Document ID required']);
    exit;
}

if (!isset($input['to_department_id']) || !isset($input['from_department_id'])) {
    echo json_encode(['success' => false, 'message' => 'Department IDs required']);
    exit;
}

$reportId = isset($input['uploaded_report_id']) ? (int)$input['uploaded_report_id'] : (int)$input['document_id'];
$toDeptId = (int)$input['to_department_id'];
$fromDeptId = (int)$input['from_department_id'];
$sentBy = isset($input['sent_by']) ? $input['sent_by'] : 'System';
$reportData = isset($input['uploaded_report_data']) ? $input['uploaded_report_data'] : [];

// If report_data is empty, try to fetch from uploaded_reports table
if (empty($reportData)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM uploaded_reports WHERE id = ?");
        $stmt->execute([$reportId]);
        $fetched = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($fetched) {
            $reportData = $fetched;
            $reportData['sent_count'] = ($reportData['sent_count'] ?? 0) + 1;
            $reportData['is_sent'] = 1;
        }
    } catch(PDOException $e) {
        // Ignore
    }
}

// If still empty, try project_documents with doc_type = 'uploaded_report'
if (empty($reportData)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM project_documents WHERE id = ? AND doc_type = 'uploaded_report'");
        $stmt->execute([$reportId]);
        $fetched = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($fetched) {
            $reportData = $fetched;
            $reportData['sent_count'] = ($reportData['sent_count'] ?? 0) + 1;
            $reportData['is_sent'] = 1;
            $reportData['file_name'] = $fetched['file_name'];
            $reportData['file_path'] = $fetched['file_path'];
        }
    } catch(PDOException $e) {
        // Ignore
    }
}

// Validate report data
if (empty($reportData) || !isset($reportData['title'])) {
    echo json_encode(['success' => false, 'message' => 'Uploaded report data is required or incomplete']);
    exit;
}

// Get department names
try {
    $deptStmt = $pdo->prepare("SELECT name FROM departments WHERE id = ?");
    $deptStmt->execute([$fromDeptId]);
    $fromDeptName = $deptStmt->fetchColumn();
    $deptStmt->execute([$toDeptId]);
    $toDeptName = $deptStmt->fetchColumn();
} catch(PDOException $e) {
    $fromDeptName = isset($input['from_department_name']) ? $input['from_department_name'] : 'Department ' . $fromDeptId;
    $toDeptName = isset($input['to_department_name']) ? $input['to_department_name'] : 'Department ' . $toDeptId;
}

// ============================================================
// Check if sent_uploaded_reports table exists, create if not
// ============================================================
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
}

// ============================================================
// Copy file if it exists
// ============================================================
$filePath = $reportData['file_path'] ?? $reportData['file_name'] ?? '';
$fileName = $reportData['file_name'] ?? 'document';
$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

// Try to find the source file
$sourceFile = null;
$possiblePaths = [
    $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/' . $filePath,
    $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/frontend/assets/uploads/reports/' . basename($filePath),
    $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/assets/uploads/reports/' . basename($filePath),
    $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/backend/uploads/reports/' . basename($filePath),
    $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/frontend/assets/uploads/projects/project_documents/' . basename($filePath),
    $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/assets/uploads/projects/project_documents/' . basename($filePath),
];

foreach ($possiblePaths as $path) {
    if (file_exists($path)) {
        $sourceFile = $path;
        break;
    }
}

// Copy file to sent_documents folder
$sentFilePath = $filePath;
if ($sourceFile && file_exists($sourceFile)) {
    $sentDir = $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/frontend/assets/uploads/sent_documents/';
    if (!file_exists($sentDir)) {
        mkdir($sentDir, 0777, true);
    }
    
    $newFileName = time() . '_' . $reportId . '_' . basename($fileName);
    $sentFilePath = 'frontend/assets/uploads/sent_documents/' . $newFileName;
    $sentFile = $sentDir . $newFileName;
    
    if (!copy($sourceFile, $sentFile)) {
        $sentFilePath = $filePath;
    }
}

// ============================================================
// Prepare report data JSON
// ============================================================
$reportData['file_path'] = $sentFilePath;
$reportData['file_name'] = $fileName;
$reportDataJson = json_encode($reportData);

$reportTitle = $reportData['title'] ?? 'Untitled Uploaded Report';
$reportPeriod = $reportData['period'] ?? 'monthly';
$reportFile = $fileName;

// ============================================================
// Save to sent_uploaded_reports table
// ============================================================
try {
    // Check if already exists
    $checkStmt = $pdo->prepare("SELECT id FROM sent_uploaded_reports WHERE original_uploaded_report_id = ? AND to_department_id = ? AND is_deleted = 0");
    $checkStmt->execute([$reportId, $toDeptId]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        // Update existing
        $stmt = $pdo->prepare("UPDATE sent_uploaded_reports SET 
            uploaded_report_data = ?,
            from_department_id = ?,
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
            WHERE id = ?");
        
        $stmt->execute([
            $reportDataJson,
            $fromDeptId,
            $sentBy,
            $fromDeptName,
            $toDeptName,
            $reportTitle,
            $reportPeriod,
            $reportFile,
            $existing['id']
        ]);
        
        $sentId = $existing['id'];
    } else {
        // Insert new
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
    }
    
    // ============================================================
    // Update original uploaded report - mark as sent
    // ============================================================
    try {
        // Check if table has sent columns
        $checkCols = $pdo->query("SHOW COLUMNS FROM uploaded_reports LIKE 'sent_to_department'");
        if ($checkCols->rowCount() > 0) {
            $updateStmt = $pdo->prepare("UPDATE uploaded_reports SET 
                sent_to_department = ?,
                sent_from_department = ?,
                is_viewed_by_department = 0,
                sent_count = sent_count + 1,
                is_sent = 1,
                last_sent_at = NOW()
                WHERE id = ?");
            $updateStmt->execute([$toDeptId, $fromDeptId, $reportId]);
        }
    } catch(PDOException $e) {
        // Columns might not exist, ignore
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Uploaded report sent successfully',
        'sent_id' => $sentId,
        'to_department' => $toDeptId,
        'to_department_name' => $toDeptName,
        'from_department' => $fromDeptId,
        'from_department_name' => $fromDeptName,
        'report_title' => $reportTitle,
        'file_copied' => isset($sentFile) && file_exists($sentFile),
        'file_path' => $sentFilePath
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>