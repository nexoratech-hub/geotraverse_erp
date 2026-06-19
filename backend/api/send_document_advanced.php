<?php
// send_document_advanced.php - FIXED: Copy file when sending

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

if (!$input || !isset($input['document_id']) || !isset($input['to_department_id']) || !isset($input['from_department_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$docId = (int)$input['document_id'];
$toDeptId = (int)$input['to_department_id'];
$fromDeptId = (int)$input['from_department_id'];
$sentBy = isset($input['sent_by']) ? $input['sent_by'] : 'System';
$docType = isset($input['doc_type']) ? $input['doc_type'] : 'project';

// ============================================================
// Get original document
// ============================================================
try {
    $stmt = $pdo->prepare("SELECT * FROM project_documents WHERE id = ?");
    $stmt->execute([$docId]);
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$doc) {
        echo json_encode(['success' => false, 'message' => 'Document not found']);
        exit;
    }
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching document: ' . $e->getMessage()]);
    exit;
}

// ============================================================
// Get department names
// ============================================================
try {
    $deptStmt = $pdo->prepare("SELECT name FROM departments WHERE id = ?");
    $deptStmt->execute([$fromDeptId]);
    $fromDeptName = $deptStmt->fetchColumn();
    $deptStmt->execute([$toDeptId]);
    $toDeptName = $deptStmt->fetchColumn();
} catch(PDOException $e) {
    $fromDeptName = 'Department ' . $fromDeptId;
    $toDeptName = 'Department ' . $toDeptId;
}

// ============================================================
// Find and copy the actual file
// ============================================================
$filePath = $doc['file_path'] ?? $doc['file_name'] ?? '';
$fileName = $doc['file_name'] ?? 'document';
$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

// Try to find the file in multiple locations
$possiblePaths = [
    $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/' . $filePath,
    $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/frontend/assets/uploads/projects/project_documents/' . basename($filePath),
    $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/frontend/assets/uploads/projects/' . basename($filePath),
    $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/assets/uploads/projects/project_documents/' . basename($filePath),
    $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/assets/uploads/projects/' . basename($filePath),
    $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/backend/uploads/' . basename($filePath),
    $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/uploads/' . basename($filePath),
];

$sourceFile = null;
foreach ($possiblePaths as $path) {
    if (file_exists($path)) {
        $sourceFile = $path;
        break;
    }
}

// If still not found, search in entire project
if (!$sourceFile) {
    $searchDir = $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/';
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($searchDir));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getFilename() === basename($fileName)) {
            $sourceFile = $file->getRealPath();
            break;
        }
    }
}

// ============================================================
// Copy file to sent documents folder
// ============================================================
$sentFile = null;
$sentFilePath = null;

if ($sourceFile && file_exists($sourceFile)) {
    // Create sent documents directory if not exists
    $sentDir = $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/frontend/assets/uploads/sent_documents/';
    if (!file_exists($sentDir)) {
        mkdir($sentDir, 0777, true);
    }
    
    // Generate unique filename
    $newFileName = time() . '_' . $docId . '_' . basename($fileName);
    $sentFilePath = 'frontend/assets/uploads/sent_documents/' . $newFileName;
    $sentFile = $sentDir . $newFileName;
    
    // Copy the file
    if (!copy($sourceFile, $sentFile)) {
        // If copy fails, try to use the original file path
        $sentFilePath = $filePath;
        $sentFile = $sourceFile;
    }
} else {
    // If file not found, use original path from database
    $sentFilePath = $filePath;
    $sentFile = null;
}

// ============================================================
// Prepare document data for sent_documents table
// ============================================================
$documentData = [
    'id' => $doc['id'],
    'title' => $doc['title'],
    'description' => $doc['description'],
    'file_name' => $fileName,
    'file_path' => $sentFilePath, // Use copied file path
    'file_size' => $doc['file_size'] ?? 0,
    'file_type' => $doc['file_type'] ?? '',
    'uploaded_by' => $doc['uploaded_by'],
    'department_id' => $doc['department_id'],
    'created_at' => $doc['created_at'],
    'doc_type' => $doc['doc_type'] ?? 'general',
    'sent_from_department' => $fromDeptId,
    'sent_to_department' => $toDeptId,
    'sent_by' => $sentBy,
    'sent_at' => date('Y-m-d H:i:s'),
    'is_sent' => 1,
    'sent_count' => ($doc['sent_count'] ?? 0) + 1
];

$documentDataJson = json_encode($documentData);

// ============================================================
// Save to sent_documents table
// ============================================================
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
            $documentDataJson,
            $fromDeptId,
            $sentBy,
            $fromDeptName,
            $toDeptName,
            $doc['title'],
            $docType,
            $fileName,
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
            $documentDataJson,
            $fromDeptId,
            $toDeptId,
            $sentBy,
            $fromDeptName,
            $toDeptName,
            $doc['title'],
            $docType,
            $fileName
        ]);
        
        $sentId = $pdo->lastInsertId();
    }
    
    // ============================================================
    // Update original document - mark as sent
    // ============================================================
    $updateStmt = $pdo->prepare("UPDATE project_documents SET 
        sent_to_department = ?,
        sent_from_department = ?,
        is_viewed_by_department = 0,
        sent_count = sent_count + 1,
        is_sent = 1,
        last_sent_at = NOW()
        WHERE id = ?");
    $updateStmt->execute([$toDeptId, $fromDeptId, $docId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Document sent successfully with file copy',
        'sent_id' => $sentId,
        'to_department' => $toDeptId,
        'to_department_name' => $toDeptName,
        'file_copied' => ($sentFile && file_exists($sentFile)),
        'source_file' => $sourceFile,
        'sent_file' => $sentFilePath,
        'document_title' => $doc['title'],
        'file_name' => $fileName
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>