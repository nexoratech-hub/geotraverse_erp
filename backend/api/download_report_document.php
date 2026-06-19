<?php
// download_report_document.php - Fixed version for uploaded reports

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die(json_encode(['success' => false, 'message' => 'Database error']));
}

$docId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$docId) {
    die(json_encode(['success' => false, 'message' => 'Document ID required']));
}

try {
    $stmt = $pdo->prepare("SELECT * FROM uploaded_reports WHERE id = ?");
    $stmt->execute([$docId]);
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doc) {
        die(json_encode(['success' => false, 'message' => 'Document not found']));
    }

    $filePath = $doc['file_path'] ?? $doc['file_name'] ?? '';
    if (empty($filePath)) {
        die(json_encode(['success' => false, 'message' => 'File path not found']));
    }

    $filePath = str_replace(['../', '..\\'], '', $filePath);
    $filePath = ltrim($filePath, '/');
    
    $basePath = $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/';
    $fullPath = $basePath . $filePath;
    
    if (!file_exists($fullPath)) {
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/frontend/assets/uploads/reports/' . basename($filePath);
    }
    if (!file_exists($fullPath)) {
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/backend/uploads/' . basename($filePath);
    }

    if (!file_exists($fullPath)) {
        die(json_encode(['success' => false, 'message' => 'File not found']));
    }

    $fileName = $doc['file_name'] ?? basename($filePath);
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $fileSize = filesize($fullPath);
    
    $mimeTypes = [
        'pdf'  => 'application/pdf',
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls'  => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt'  => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'txt'  => 'text/plain',
        'csv'  => 'text/csv',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
        'svg'  => 'image/svg+xml',
        'zip'  => 'application/zip',
        'rar'  => 'application/x-rar-compressed',
        '7z'   => 'application/x-7z-compressed',
        'mp4'  => 'video/mp4',
        'mp3'  => 'audio/mpeg'
    ];
    
    $mimeType = $mimeTypes[$fileExtension] ?? 'application/octet-stream';
    
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Length: ' . $fileSize);
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Expires: 0');
    header('Content-Encoding: none');
    
    readfile($fullPath);
    exit;
    
} catch(PDOException $e) {
    die(json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]));
}
?>