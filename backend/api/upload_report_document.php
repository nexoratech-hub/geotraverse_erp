<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// ============ DATABASE CONNECTION ============
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

// ============ CHECK FILE ============
if (!isset($_FILES['report_file']) || $_FILES['report_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit;
}

$file = $_FILES['report_file'];
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$period = isset($_POST['period']) ? trim($_POST['period']) : 'monthly';
$department_id = isset($_POST['department_id']) ? intval($_POST['department_id']) : 0;
$uploaded_by = isset($_POST['uploaded_by']) ? trim($_POST['uploaded_by']) : 'System';

// ============ VALIDATION ============
if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Title is required']);
    exit;
}

if ($department_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Department ID is required']);
    exit;
}

// ============ FILE VALIDATION ============
$maxFileSize = 10 * 1024 * 1024; // 10MB
if ($file['size'] > $maxFileSize) {
    echo json_encode(['success' => false, 'message' => 'File too large. Max size is 10MB']);
    exit;
}

$allowedTypes = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'image/jpeg',
    'image/png',
    'image/gif'
];

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'File type not allowed. Allowed: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, GIF']);
    exit;
}

// ============ GENERATE UNIQUE FILENAME ============
$originalName = $file['name'];
$extension = pathinfo($originalName, PATHINFO_EXTENSION);
// Generate unique name with timestamp and random string
$uniqueName = 'report_' . date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.' . $extension;

// ============ SAVE FILE ============
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/frontend/assets/uploads/reports/';

// Create directory if not exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$destination = $uploadDir . $uniqueName;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save file']);
    exit;
}

// ============ SAVE TO DATABASE ============
try {
    $stmt = $pdo->prepare("
        INSERT INTO uploaded_reports (
            title,
            period,
            description,
            file_name,
            file_path,
            file_size,
            file_type,
            uploaded_by,
            department_id,
            created_at,
            is_deleted,
            is_original,
            is_sent_copy,
            sent_count
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 0, 1, 0, 0
        )
    ");
    
    $stmt->execute([
        $title,
        $period,
        '', // description
        $uniqueName, // store unique name as file_name
        $uniqueName, // store unique name as file_path
        $file['size'],
        $mimeType,
        $uploaded_by,
        $department_id
    ]);
    
    $id = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Report uploaded successfully',
        'id' => $id,
        'file_name' => $uniqueName,
        'file_path' => $uniqueName
    ]);
    
} catch(PDOException $e) {
    // Delete file if database fails
    if (file_exists($destination)) {
        unlink($destination);
    }
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>