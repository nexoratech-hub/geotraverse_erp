<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Database connection
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

// Get POST data
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$department_id = isset($_POST['department_id']) ? intval($_POST['department_id']) : 0;
$uploaded_by = isset($_POST['uploaded_by']) ? trim($_POST['uploaded_by']) : 'System';

// Validate
if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Title is required']);
    exit;
}

if (!isset($_FILES['document_file']) || $_FILES['document_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'File upload failed. Error code: ' . ($_FILES['document_file']['error'] ?? 'No file')]);
    exit;
}

$file = $_FILES['document_file'];
$fileName = $file['name'];
$fileTmpPath = $file['tmp_name'];
$fileSize = $file['size'];
$fileType = $file['type'];

// Generate unique filename
$fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
$newFileName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $fileExt;

// Define upload path - CORRECT PATH
$uploadDir = '../../frontend/assets/uploads/projects/projects_documents/';
$uploadPath = $uploadDir . $newFileName;

// Create directory if not exists
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Move uploaded file
if (!move_uploaded_file($fileTmpPath, $uploadPath)) {
    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
    exit;
}

// Save to database
try {
    $stmt = $pdo->prepare("
        INSERT INTO project_documents (
            title, description, file_name, file_path, file_size, file_type,
            uploaded_by, department_id, created_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?,
            ?, ?, NOW()
        )
    ");
    
    $filePath = 'assets/uploads/projects/projects_documents/' . $newFileName;
    
    $stmt->execute([
        $title,
        $description,
        $fileName,
        $filePath,
        $fileSize,
        $fileType,
        $uploaded_by,
        $department_id
    ]);
    
    $documentId = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Document uploaded successfully',
        'data' => [
            'id' => $documentId,
            'title' => $title,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_size' => $fileSize
        ]
    ]);
    
} catch(PDOException $e) {
    // Delete uploaded file if database insert fails
    if (file_exists($uploadPath)) {
        unlink($uploadPath);
    }
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>