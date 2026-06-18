<?php
// backend/api/download_report_document.php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Get parameters
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$id) {
    http_response_code(400);
    die('Report ID is required');
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
    http_response_code(500);
    die('Database connection failed');
}

try {
    // Get report details
    $sql = "SELECT file_name, file_path FROM uploaded_reports WHERE id = :id AND is_deleted = 0";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$doc) {
        http_response_code(404);
        die('Report not found');
    }
    
    $fileName = $doc['file_name'];
    $filePath = $doc['file_path'];
    
    // Try multiple paths
    $possiblePaths = [
        __DIR__ . '/../../' . $filePath,
        __DIR__ . '/../../frontend/assets/uploads/reports/' . basename($filePath),
        __DIR__ . '/../../assets/uploads/reports/' . basename($filePath),
        __DIR__ . '/../' . $filePath,
        __DIR__ . '/../../../' . $filePath
    ];
    
    // Search recursively
    $searchDir = __DIR__ . '/../../frontend/assets/uploads/';
    if (is_dir($searchDir)) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($searchDir));
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getFilename() === basename($filePath)) {
                $possiblePaths[] = $file->getPathname();
            }
        }
    }
    
    $fullPath = null;
    foreach ($possiblePaths as $path) {
        if (file_exists($path) && is_file($path)) {
            $fullPath = $path;
            break;
        }
    }
    
    if (!$fullPath) {
        http_response_code(404);
        die('File not found on server');
    }
    
    $fileSize = filesize($fullPath);
    
    // Send file
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Length: ' . $fileSize);
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    header('Accept-Ranges: bytes');
    
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    readfile($fullPath);
    exit;
    
} catch (PDOException $e) {
    http_response_code(500);
    die('Database error: ' . $e->getMessage());
}