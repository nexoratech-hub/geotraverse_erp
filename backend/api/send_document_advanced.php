<?php
// send_document_advanced.php - FIXED

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

error_reporting(0);
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
$required = ['document_id', 'to_department_id', 'from_department_id', 'doc_type'];
foreach ($required as $field) {
    if (!isset($input[$field])) {
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

$docId = (int)$input['document_id'];
$toDeptId = (int)$input['to_department_id'];
$fromDeptId = (int)$input['from_department_id'];
$docType = $input['doc_type'];
$sentBy = isset($input['sent_by']) ? $input['sent_by'] : 'System';

// ============================================================
// FIX: Check for document_data (primary) or uploaded_report_data (fallback)
// ============================================================
$docData = [];
if (isset($input['document_data']) && !empty($input['document_data'])) {
    $docData = $input['document_data'];
} elseif (isset($input['uploaded_report_data']) && !empty($input['uploaded_report_data'])) {
    $docData = $input['uploaded_report_data'];
} elseif (isset($input['doc_data']) && !empty($input['doc_data'])) {
    $docData = $input['doc_data'];
}

// If document_data is empty, try to fetch from database
if (empty($docData)) {
    try {
        if ($docType === 'uploaded_report') {
            $stmt = $pdo->prepare("SELECT * FROM uploaded_reports WHERE id = ?");
            $stmt->execute([$docId]);
            $docData = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM project_documents WHERE id = ?");
            $stmt->execute([$docId]);
            $docData = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        if ($docData) {
            $docData['sent_count'] = ($docData['sent_count'] ?? 0) + 1;
            $docData['is_sent'] = 1;
        }
    } catch(PDOException $e) {
        // Ignore
    }
}

// ============================================================
// FIX: Allow document_data to be minimal - just check for title or id
// ============================================================
if (empty($docData)) {
    // Try to use direct fields from input
    if (isset($input['document_title']) || isset($input['title'])) {
        $docData = [
            'id' => $docId,
            'title' => $input['document_title'] ?? $input['title'] ?? 'Document',
            'file_name' => $input['document_file'] ?? $input['file_name'] ?? '',
            'description' => $input['description'] ?? '',
            'uploaded_by' => $sentBy,
            'department_id' => $fromDeptId,
            'created_at' => date('Y-m-d H:i:s')
        ];
    } else {
        echo json_encode(['success' => false, 'message' => 'Document data is required or incomplete']);
        exit;
    }
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

$docTitle = $docData['title'] ?? 'Untitled Document';
$docFile = $docData['file_name'] ?? '';

// ============================================================
// Create sent_documents table if not exists
// ============================================================
$tableCheck = $pdo->query("SHOW TABLES LIKE 'sent_documents'");
if ($tableCheck->rowCount() == 0) {
    $createTable = "
    CREATE TABLE IF NOT EXISTS `sent_documents` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `original_document_id` int(11) NOT NULL,
        `document_data` longtext DEFAULT NULL,
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
        `document_title` varchar(255) DEFAULT NULL,
        `document_type` varchar(50) DEFAULT NULL,
        `document_file` varchar(255) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $pdo->exec($createTable);
}

// Prepare document data JSON
$docDataJson = json_encode($docData);

try {
    // Check if already exists
    $checkStmt = $pdo->prepare("SELECT id FROM sent_documents WHERE original_document_id = ? AND to_department_id = ? AND is_deleted = 0");
    $checkStmt->execute([$docId, $toDeptId]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        // Update existing
        $stmt = $pdo->prepare("UPDATE sent_documents SET 
            document_data = ?,
            from_department_id = ?,
            sent_by = ?,
            sent_at = NOW(),
            is_viewed = 0,
            sent_count = sent_count + 1,
            is_sent = 1,
            last_sent_at = NOW(),
            from_department_name = ?,
            to_department_name = ?,
            document_title = ?,
            document_type = ?,
            document_file = ?
            WHERE id = ?");
        
        $stmt->execute([
            $docDataJson,
            $fromDeptId,
            $sentBy,
            $fromDeptName,
            $toDeptName,
            $docTitle,
            $docType,
            $docFile,
            $existing['id']
        ]);
        $sentId = $existing['id'];
    } else {
        // Insert new
        $stmt = $pdo->prepare("INSERT INTO sent_documents (
            original_document_id,
            document_data,
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
            document_title,
            document_type,
            document_file
        ) VALUES (?, ?, ?, ?, ?, NOW(), 0, 0, 1, 1, NOW(), ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $docId,
            $docDataJson,
            $fromDeptId,
            $toDeptId,
            $sentBy,
            $fromDeptName,
            $toDeptName,
            $docTitle,
            $docType,
            $docFile
        ]);
        $sentId = $pdo->lastInsertId();
    }
    
    // Update original document
    try {
        if ($docType === 'uploaded_report') {
            $updateStmt = $pdo->prepare("UPDATE uploaded_reports SET 
                sent_to_department = ?,
                sent_from_department = ?,
                is_viewed_by_department = 0,
                sent_count = sent_count + 1,
                is_sent = 1,
                last_sent_at = NOW()
                WHERE id = ?");
            $updateStmt->execute([$toDeptId, $fromDeptId, $docId]);
        } else {
            $updateStmt = $pdo->prepare("UPDATE project_documents SET 
                sent_to_department = ?,
                sent_from_department = ?,
                is_viewed_by_department = 0,
                sent_count = sent_count + 1,
                is_sent = 1,
                last_sent_at = NOW()
                WHERE id = ?");
            $updateStmt->execute([$toDeptId, $fromDeptId, $docId]);
        }
    } catch(PDOException $e) {
        // Columns might not exist
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Document sent successfully',
        'sent_id' => $sentId,
        'to_department' => $toDeptId,
        'to_department_name' => $toDeptName,
        'from_department' => $fromDeptId,
        'from_department_name' => $fromDeptName,
        'document_title' => $docTitle,
        'document_type' => $docType
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>