<?php
// backend/api/upload_report_document.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');

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

// Check file upload
if (!isset($_FILES['report_file']) || $_FILES['report_file']['error'] !== UPLOAD_ERR_OK) {
    $errorMsg = isset($_FILES['report_file']) ? 'Upload error code: ' . $_FILES['report_file']['error'] : 'No file uploaded';
    echo json_encode(['success' => false, 'message' => $errorMsg]);
    exit;
}

// Get form data
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$period = isset($_POST['period']) ? trim($_POST['period']) : 'monthly';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$departmentId = isset($_POST['department_id']) ? intval($_POST['department_id']) : 1;
$uploadedBy = isset($_POST['uploaded_by']) ? trim($_POST['uploaded_by']) : 'System';

if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Report title is required']);
    exit;
}

$file = $_FILES['report_file'];
$originalName = basename($file['name']);
$fileSize = $file['size'];
$fileType = $file['type'];
$fileTmp = $file['tmp_name'];

// Validate file extension
$allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg', 'gif', 'txt'];
$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
if (!in_array($ext, $allowedExtensions)) {
    echo json_encode(['success' => false, 'message' => 'File type not allowed. Allowed: ' . implode(', ', $allowedExtensions)]);
    exit;
}

// Generate unique filename
$uniqueFileName = time() . '_' . uniqid() . '.' . $ext;
$uploadDir = __DIR__ . '/../../frontend/assets/uploads/reports/';

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

// Store in database
$relativePath = 'frontend/assets/uploads/reports/' . $uniqueFileName;

try {
    // Check if table exists
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'uploaded_reports'");
    if ($tableCheck->rowCount() == 0) {
        // Create table if not exists
        $createSql = "CREATE TABLE IF NOT EXISTS `uploaded_reports` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `period` enum('daily','weekly','monthly','quarterly','annual') DEFAULT 'monthly',
            `description` text DEFAULT NULL,
            `file_name` varchar(255) NOT NULL,
            `file_path` varchar(500) NOT NULL,
            `file_size` int(11) DEFAULT 0,
            `file_type` varchar(100) DEFAULT NULL,
            `uploaded_by` varchar(100) DEFAULT NULL,
            `department_id` int(11) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            `is_deleted` tinyint(4) DEFAULT 0,
            `deleted_at` timestamp NULL DEFAULT NULL,
            `deleted_by` varchar(100) DEFAULT NULL,
            `sent_from_department` int(11) DEFAULT NULL,
            `sent_to_department` int(11) DEFAULT NULL,
            `is_viewed_by_department` tinyint(4) DEFAULT 0,
            `sent_count` int(11) DEFAULT 0,
            `is_sent` tinyint(4) DEFAULT 0,
            `last_sent_at` timestamp NULL DEFAULT NULL,
            `is_original` tinyint(4) DEFAULT 1,
            `is_sent_copy` tinyint(4) DEFAULT 0,
            `original_uploaded_report_id` int(11) DEFAULT NULL,
            `file_name_only` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $pdo->exec($createSql);
    }
    
    // Check if file_name_only column exists
    $columns = $pdo->query("SHOW COLUMNS FROM uploaded_reports");
    $existingColumns = [];
    while ($col = $columns->fetch(PDO::FETCH_ASSOC)) {
        $existingColumns[] = $col['Field'];
    }
    
    $hasFileNameOnly = in_array('file_name_only', $existingColumns);
    $hasDescription = in_array('description', $existingColumns);
    
    // Build insert query
    if ($hasDescription && $hasFileNameOnly) {
        $sql = "INSERT INTO uploaded_reports (
                    title, period, description, file_name, file_name_only, file_path, 
                    file_size, file_type, uploaded_by, department_id, created_at
                ) VALUES (
                    :title, :period, :description, :file_name, :file_name_only, :file_path,
                    :file_size, :file_type, :uploaded_by, :department_id, NOW()
                )";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':title' => $title,
            ':period' => $period,
            ':description' => $description,
            ':file_name' => $originalName,
            ':file_name_only' => $uniqueFileName,
            ':file_path' => $relativePath,
            ':file_size' => $fileSize,
            ':file_type' => $fileType,
            ':uploaded_by' => $uploadedBy,
            ':department_id' => $departmentId
        ]);
    } else {
        $sql = "INSERT INTO uploaded_reports (
                    title, period, file_name, file_path, 
                    file_size, file_type, uploaded_by, department_id, created_at
                ) VALUES (
                    :title, :period, :file_name, :file_path,
                    :file_size, :file_type, :uploaded_by, :department_id, NOW()
                )";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':title' => $title,
            ':period' => $period,
            ':file_name' => $originalName,
            ':file_path' => $relativePath,
            ':file_size' => $fileSize,
            ':file_type' => $fileType,
            ':uploaded_by' => $uploadedBy,
            ':department_id' => $departmentId
        ]);
    }
    
    $reportId = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Report uploaded successfully',
        'report_id' => $reportId,
        'file_name' => $uniqueFileName,
        'original_name' => $originalName
    ]);
    
} catch (PDOException $e) {
    // Delete uploaded file if database insert fails
    if (file_exists($uploadPath)) {
        unlink($uploadPath);
    }
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}