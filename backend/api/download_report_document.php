<?php
// ============ DATABASE CONNECTION ============
$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// ============ GET PARAMETERS ============
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die('Invalid document ID');
}

try {
    $stmt = $pdo->prepare("SELECT * FROM uploaded_reports WHERE id = ? AND is_deleted = 0");
    $stmt->execute([$id]);
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$doc) {
        die('Document not found');
    }
    
    // ============ GET FILE NAME ============
    // file_path stores the unique filename
    $fileName = $doc['file_path'] ?? $doc['file_name'] ?? '';
    
    if (empty($fileName)) {
        die('File name not found in database');
    }
    
    // ============ BUILD FULL PATH ============
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/frontend/assets/uploads/reports/';
    $fullPath = $uploadDir . $fileName;
    
    // ============ CHECK IF FILE EXISTS ============
    if (!file_exists($fullPath)) {
        die('File not found on server: ' . $fileName);
    }
    
    // ============ GET ORIGINAL NAME ============
    // Try to get original name from database, fallback to file name
    $originalName = $doc['original_file_name'] ?? $doc['file_name'] ?? $fileName;
    
    // ============ DOWNLOAD FILE ============
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $originalName . '"');
    header('Content-Length: ' . filesize($fullPath));
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: 0');
    
    readfile($fullPath);
    exit;
    
} catch(PDOException $e) {
    die('Database error: ' . $e->getMessage());
}
?>