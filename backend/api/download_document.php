<?php
// download_document.php - FIXED: Handle sent documents

while (ob_get_level()) {
    ob_end_clean();
}

error_reporting(0);
ini_set('display_errors', 0);

$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die('Database error');
}

$docId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$type = isset($_GET['type']) ? $_GET['type'] : 'project';

if (!$docId) {
    die('Document ID required');
}

try {
    $doc = null;
    $fileName = '';
    $filePath = '';
    
    // ============================================================
    // Get document from appropriate table
    // ============================================================
    if ($type === 'project') {
        $stmt = $pdo->prepare("SELECT * FROM project_documents WHERE id = ?");
        $stmt->execute([$docId]);
        $doc = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        // For sent documents, get from sent_documents
        $stmt = $pdo->prepare("SELECT * FROM sent_documents WHERE id = ?");
        $stmt->execute([$docId]);
        $doc = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If found, try to get file info from document_data
        if ($doc && $doc['document_data']) {
            $data = json_decode($doc['document_data'], true);
            if ($data) {
                if (isset($data['file_path']) && empty($doc['document_file'])) {
                    $doc['document_file'] = $data['file_path'];
                }
                if (isset($data['file_name']) && empty($doc['document_file'])) {
                    $doc['document_file'] = $data['file_name'];
                }
            }
        }
    }

    if (!$doc) {
        die('Document not found');
    }

    // ============================================================
    // Get file name and path
    // ============================================================
    if ($type === 'project') {
        $fileName = $doc['file_name'] ?? 'document';
        $filePath = $doc['file_path'] ?? $fileName;
    } else {
        // For sent documents, use document_file or from data
        $fileName = $doc['document_file'] ?? $doc['file_name'] ?? 'document';
        $filePath = $doc['document_file'] ?? $doc['file_path'] ?? $fileName;
        
        // If file_path contains path, extract filename
        if (strpos($filePath, '/') !== false || strpos($filePath, '\\') !== false) {
            $fileName = basename($filePath);
        }
    }
    
    // Clean
    $fileName = basename($fileName);
    $filePath = basename($filePath);
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // ============================================================
    // Try to find the file
    // ============================================================
    $possiblePaths = [
        // For sent documents
        $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/frontend/assets/uploads/sent_documents/' . $filePath,
        $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/assets/uploads/sent_documents/' . $filePath,
        $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/backend/uploads/sent_documents/' . $filePath,
        // Original paths
        $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/frontend/assets/uploads/projects/project_documents/' . $filePath,
        $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/frontend/assets/uploads/projects/' . $filePath,
        $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/assets/uploads/projects/project_documents/' . $filePath,
        $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/assets/uploads/projects/' . $filePath,
        $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/uploads/' . $filePath,
        // With timestamp prefix
        $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/frontend/assets/uploads/sent_documents/*_' . $docId . '_' . $filePath,
    ];
    
    $fullPath = null;
    foreach ($possiblePaths as $path) {
        if (strpos($path, '*') !== false) {
            // Handle wildcard
            $dir = dirname($path);
            $pattern = basename($path);
            if (file_exists($dir)) {
                $files = glob($dir . '/' . $pattern);
                if (!empty($files) && file_exists($files[0])) {
                    $fullPath = $files[0];
                    break;
                }
            }
        } else if (file_exists($path)) {
            $fullPath = $path;
            break;
        }
    }
    
    // If still not found, search in uploads directory
    if (!$fullPath) {
        $searchDirs = [
            $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/frontend/assets/uploads/',
            $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/assets/uploads/',
            $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/backend/uploads/',
        ];
        foreach ($searchDirs as $dir) {
            if (file_exists($dir)) {
                $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
                foreach ($iterator as $file) {
                    if ($file->isFile() && $file->getFilename() === $filePath) {
                        $fullPath = $file->getRealPath();
                        break 2;
                    }
                }
            }
        }
    }
    
    if (!$fullPath || !file_exists($fullPath)) {
        die('File not found: ' . $filePath);
    }

    $fileSize = filesize($fullPath);
    
    // ============================================================
    // Set MIME type
    // ============================================================
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
        'mp3'  => 'audio/mpeg',
        'wav'  => 'audio/wav'
    ];
    
    $mimeType = $mimeTypes[$fileExtension] ?? 'application/octet-stream';
    
    // ============================================================
    // Send headers
    // ============================================================
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
    die('Database error');
} catch(Exception $e) {
    die('Error: ' . $e->getMessage());
}
?>