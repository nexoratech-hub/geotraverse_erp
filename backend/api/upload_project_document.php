<?php
// upload_project_document.php - Fixed for all dashboards

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB Connection Error: ' . $e->getMessage()]);
    exit;
}

// ============================================================
// GET POST DATA (FormData)
// ============================================================
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$departmentId = isset($_POST['department_id']) ? (int)$_POST['department_id'] : 0;
$uploadedBy = isset($_POST['uploaded_by']) ? trim($_POST['uploaded_by']) : 'System';
$docType = isset($_POST['doc_type']) ? trim($_POST['doc_type']) : 'general';
$projectId = isset($_POST['project_id']) ? (int)$_POST['project_id'] : null;

// ============================================================
// VALIDATE doc_type
// ============================================================
$validTypes = ['general', 'aluminium', 'iron', 'project', 'report', 'contract', 'invoice', 'receipt', 'other'];
if (!in_array($docType, $validTypes)) {
    $docType = 'general';
}

// ============================================================
// VALIDATE FILES
// ============================================================
if (!$title) {
    echo json_encode(['success' => false, 'message' => 'Title is required']);
    exit;
}

if (!isset($_FILES['document_file']) || $_FILES['document_file']['error'] !== UPLOAD_ERR_OK) {
    $errorMsg = isset($_FILES['document_file']) ? 'Upload error: ' . $_FILES['document_file']['error'] : 'No file uploaded';
    echo json_encode(['success' => false, 'message' => $errorMsg]);
    exit;
}

// ============================================================
// FILE UPLOAD
// ============================================================
$file = $_FILES['document_file'];
$originalFileName = basename($file['name']);
$fileSize = $file['size'];
$fileTmp = $file['tmp_name'];
$fileMimeType = $file['type'];

$fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);
$uniqueFileName = uniqid() . '_' . time() . '.' . $fileExtension;
$filePath = 'projects/projects_documents/' . $uniqueFileName;

$uploadDir = '../../frontend/assets/uploads/projects/projects_documents/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$uploadFile = $uploadDir . $uniqueFileName;

if (!move_uploaded_file($fileTmp, $uploadFile)) {
    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
    exit;
}

// ============================================================
// CHECK AVAILABLE COLUMNS
// ============================================================
$checkDocType = $pdo->query("SHOW COLUMNS FROM project_documents LIKE 'doc_type'");
$hasDocType = $checkDocType->rowCount() > 0;

$checkProjectId = $pdo->query("SHOW COLUMNS FROM project_documents LIKE 'project_id'");
$hasProjectId = $checkProjectId->rowCount() > 0;

// ============================================================
// SAVE TO DATABASE - ONLY USE COLUMNS THAT EXIST
// ============================================================
try {
    if ($hasDocType && $hasProjectId) {
        $stmt = $pdo->prepare("
            INSERT INTO project_documents 
            (title, description, file_name, file_path, file_size, file_type, uploaded_by, department_id, project_id, doc_type, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $title,
            $description,
            $uniqueFileName,
            $filePath,
            $fileSize,
            $fileMimeType,
            $uploadedBy,
            $departmentId,
            $projectId,
            $docType
        ]);
        
    } elseif ($hasDocType) {
        $stmt = $pdo->prepare("
            INSERT INTO project_documents 
            (title, description, file_name, file_path, file_size, file_type, uploaded_by, department_id, doc_type, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $title,
            $description,
            $uniqueFileName,
            $filePath,
            $fileSize,
            $fileMimeType,
            $uploadedBy,
            $departmentId,
            $docType
        ]);
        
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO project_documents 
            (title, description, file_name, file_path, file_size, file_type, uploaded_by, department_id, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $title,
            $description,
            $uniqueFileName,
            $filePath,
            $fileSize,
            $fileMimeType,
            $uploadedBy,
            $departmentId
        ]);
    }
    
    $docId = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Document uploaded successfully',
        'doc_id' => $docId,
        'file_name' => $uniqueFileName,
        'file_path' => $filePath,
        'original_name' => $originalFileName,
        'doc_type' => $docType,
        'department_id' => $departmentId,
        'project_id' => $projectId
    ]);
    
} catch(PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>