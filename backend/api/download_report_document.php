<?php
// /geotraverse/backend/api/download_report_document.php

// Allow cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, HEAD, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Range');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get parameters
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$fileParam = isset($_GET['file']) ? $_GET['file'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : 'report';

// Log for debugging
error_log("Download request - ID: $id, File: $fileParam, Type: $type");

// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'geotraverse_erp';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    error_log("DB Connection failed: " . $conn->connect_error);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$fileName = '';

// Try to get file by ID
if ($id > 0) {
    // Check in uploaded_reports table
    $stmt = $conn->prepare("SELECT file_name, file_path FROM uploaded_reports WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $fileName = $row['file_name'] ?: $row['file_path'];
        error_log("Found in uploaded_reports: $fileName");
    }
    $stmt->close();
    
    // If not found, check in sent_uploaded_reports
    if (empty($fileName)) {
        $stmt = $conn->prepare("SELECT uploaded_report_data FROM sent_uploaded_reports WHERE original_uploaded_report_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $data = json_decode($row['uploaded_report_data'], true);
            if ($data && isset($data['file_name'])) {
                $fileName = $data['file_name'];
                error_log("Found in sent_uploaded_reports: $fileName");
            }
        }
        $stmt->close();
    }
}

// If still no file, use file parameter
if (empty($fileName) && !empty($fileParam)) {
    $fileName = $fileParam;
    error_log("Using file parameter: $fileName");
}

// Clean filename - remove any path separators
$fileName = basename($fileName);

if (empty($fileName)) {
    error_log("No file found for ID: $id");
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'File not found']);
    exit;
}

// Define possible base paths
$basePaths = [
    __DIR__ . '/../uploads/reports/',
    __DIR__ . '/../../frontend/assets/uploads/reports/',
    __DIR__ . '/../../assets/uploads/reports/',
    __DIR__ . '/uploads/reports/',
    __DIR__ . '/../../frontend/uploads/reports/',
    __DIR__ . '/../../../frontend/assets/uploads/reports/'
];

// Also try the upload path from project root
$projectRoot = $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/';
$additionalPaths = [
    $projectRoot . 'frontend/assets/uploads/reports/',
    $projectRoot . 'assets/uploads/reports/',
    $projectRoot . 'backend/uploads/reports/'
];
$basePaths = array_merge($basePaths, $additionalPaths);

// Search for file
$foundPath = null;
foreach ($basePaths as $path) {
    $fullPath = $path . $fileName;
    error_log("Checking path: $fullPath");
    if (file_exists($fullPath)) {
        $foundPath = $fullPath;
        error_log("Found file at: $fullPath");
        break;
    }
}

if (!$foundPath) {
    error_log("File not found: $fileName");
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'File not found on server']);
    exit;
}

// Get file info
$mimeType = mime_content_type($foundPath);
if (!$mimeType) {
    // Fallback mime types
    $ext = strtolower(pathinfo($foundPath, PATHINFO_EXTENSION));
    $mimeTypes = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'txt' => 'text/plain'
    ];
    $mimeType = isset($mimeTypes[$ext]) ? $mimeTypes[$ext] : 'application/octet-stream';
}

$fileSize = filesize($foundPath);

// Set headers for download
header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . $fileSize);
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

// Disable error reporting to prevent corruption
error_reporting(0);

// Output file
if (readfile($foundPath) === false) {
    error_log("Failed to read file: $foundPath");
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to read file']);
    exit;
}

$conn->close();
exit;
?>