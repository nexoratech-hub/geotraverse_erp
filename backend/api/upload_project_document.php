<?php
// backend/api/upload_project_document.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Database connection
$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['document_file']) || $_FILES['document_file']['error'] !== UPLOAD_ERR_OK) {
    $errorMsg = isset($_FILES['document_file']) ? 'Upload error code: ' . $_FILES['document_file']['error'] : 'No file uploaded';
    echo json_encode(['success' => false, 'message' => $errorMsg]);
    exit;
}

// Get form data
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$departmentId = isset($_POST['department_id']) ? intval($_POST['department_id']) : 1;
$uploadedBy = isset($_POST['uploaded_by']) ? trim($_POST['uploaded_by']) : 'System';
$docType = isset($_POST['doc_type']) ? trim($_POST['doc_type']) : 'general';
$projectId = isset($_POST['project_id']) ? intval($_POST['project_id']) : null;

if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Document title is required']);
    exit;
}

$file = $_FILES['document_file'];
$fileName = basename($file['name']);
$fileSize = $file['size'];
$fileType = $file['type'];
$fileTmp = $file['tmp_name'];

// Validate file size (10MB max)
if ($fileSize > 10 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File too large. Max size is 10MB']);
    exit;
}

// Validate file extension
$allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg', 'gif', 'txt'];
$ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
if (!in_array($ext, $allowedExtensions)) {
    echo json_encode(['success' => false, 'message' => 'File type not allowed. Allowed: ' . implode(', ', $allowedExtensions)]);
    exit;
}

// Generate unique filename
$uniqueFileName = time() . '_' . uniqid() . '.' . $ext;
$uploadDir = __DIR__ . '/../../frontend/assets/uploads/projects/';

// Create directory if it doesn't exist
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
        exit;
    }
}

$uploadPath = $uploadDir . $uniqueFileName;

// Move uploaded file
if (!move_uploaded_file($fileTmp, $uploadPath)) {
    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
    exit;
}

// Store relative path in database
$relativePath = 'frontend/assets/uploads/projects/' . $uniqueFileName;

try {
    // Check which columns exist
    $columns = $pdo->query("SHOW COLUMNS FROM project_documents");
    $existingColumns = [];
    while ($col = $columns->fetch(PDO::FETCH_ASSOC)) {
        $existingColumns[] = $col['Field'];
    }
    
    $hasDocType = in_array('doc_type', $existingColumns);
    $hasProjectId = in_array('project_id', $existingColumns);
    
    // Build insert query dynamically
    $fields = ['title', 'description', 'file_name', 'file_path', 'file_size', 'file_type', 'uploaded_by', 'department_id', 'created_at'];
    $placeholders = [':title', ':description', ':file_name', ':file_path', ':file_size', ':file_type', ':uploaded_by', ':department_id', 'NOW()'];
    $params = [
        ':title' => $title,
        ':description' => $description,
        ':file_name' => $fileName,
        ':file_path' => $relativePath,
        ':file_size' => $fileSize,
        ':file_type' => $fileType,
        ':uploaded_by' => $uploadedBy,
        ':department_id' => $departmentId
    ];
    
    if ($hasDocType) {
        $fields[] = 'doc_type';
        $placeholders[] = ':doc_type';
        $params[':doc_type'] = $docType;
    }
    
    if ($hasProjectId && $projectId) {
        $fields[] = 'project_id';
        $placeholders[] = ':project_id';
        $params[':project_id'] = $projectId;
    }
    
    $sql = "INSERT INTO project_documents (" . implode(', ', $fields) . ") 
            VALUES (" . implode(', ', $placeholders) . ")";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    $documentId = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Document uploaded successfully',
        'document_id' => $documentId,
        'file_path' => $relativePath,
        'file_name' => $fileName
    ]);
    
} catch (PDOException $e) {
    // Delete uploaded file if database insert fails
    if (file_exists($uploadPath)) {
        unlink($uploadPath);
    }
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}