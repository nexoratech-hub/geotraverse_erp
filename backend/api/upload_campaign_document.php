<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// ============ DATABASE CONNECTION ============
$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]));
}

// ============ CHECK REQUEST METHOD ============
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => 'Method not allowed']));
}

// ============ GET POST DATA ============
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$department_id = isset($_POST['department_id']) ? intval($_POST['department_id']) : 0;
$uploaded_by = isset($_POST['uploaded_by']) ? trim($_POST['uploaded_by']) : 'System';
$campaign_id = isset($_POST['campaign_id']) ? intval($_POST['campaign_id']) : 0;

// ============ VALIDATION ============
if (empty($title)) {
    die(json_encode(['success' => false, 'message' => 'Document title required']));
}

if (!isset($_FILES['document_file']) || $_FILES['document_file']['error'] !== UPLOAD_ERR_OK) {
    die(json_encode(['success' => false, 'message' => 'File upload failed or no file selected']));
}

// ============ FILE UPLOAD CONFIGURATION ============
$uploadDir = __DIR__ . '/../../frontend/assets/uploads/projects/projects_documents/';

// Create directory if not exists
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$file = $_FILES['document_file'];
$fileName = basename($file['name']);
$fileSize = $file['size'];
$fileType = $file['type'];
$fileTmp = $file['tmp_name'];

// Generate unique filename
$fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
$uniqueName = uniqid() . '_' . time() . '.' . $fileExt;
$targetFile = $uploadDir . $uniqueName;

// ============ MOVE UPLOADED FILE ============
if (!move_uploaded_file($fileTmp, $targetFile)) {
    die(json_encode(['success' => false, 'message' => 'Failed to save file']));
}

// ============ SAVE TO DATABASE ============
try {
    $relativePath = 'projects/projects_documents/' . $uniqueName;
    
    $stmt = $pdo->prepare("
        INSERT INTO project_documents (
            title, description, file_name, file_path, file_size, file_type,
            uploaded_by, department_id, created_at, doc_type, project_id
        ) VALUES (
            ?, ?, ?, ?, ?, ?,
            ?, ?, NOW(), 'campaign', ?
        )
    ");
    
    $stmt->execute([
        $title,
        $description,
        $fileName,
        $relativePath,
        $fileSize,
        $fileType,
        $uploaded_by,
        $department_id,
        $campaign_id
    ]);
    
    $docId = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Campaign document uploaded successfully',
        'data' => [
            'id' => $docId,
            'title' => $title,
            'file_name' => $fileName,
            'file_path' => $relativePath
        ]
    ]);
    
} catch(PDOException $e) {
    die(json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]));
}