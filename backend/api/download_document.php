<?php
// backend/api/download_document.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Database connection
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

// Get parameters
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$type = isset($_GET['type']) ? $_GET['type'] : 'project';

if ($id <= 0) {
    die(json_encode(['success' => false, 'message' => 'Invalid document ID']));
}

// ============================================
// FUNCTION: Serve file to browser
// ============================================
function serveFile($filePath, $fileName) {
    if ($filePath && file_exists($filePath)) {
        // Get mime type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);
        
        if (!$mimeType) {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mimeTypes = [
                'pdf' => 'application/pdf',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'xls' => 'application/vnd.ms-excel',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif'
            ];
            $mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';
        }
        
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
    return false;
}

// ============================================
// FUNCTION: Find file in multiple paths
// ============================================
function findFile($fileName, $filePath = '') {
    // Base paths to search
    $basePaths = [
        'C:/xampp/htdocs/geotraverse/frontend/assets/uploads/projects/project_documents/',
        'C:/xampp/htdocs/geotraverse/frontend/assets/uploads/projects/',
        'C:/xampp/htdocs/geotraverse/frontend/assets/uploads/reports/',
        'C:/xampp/htdocs/geotraverse/frontend/assets/uploads/',
        '../frontend/assets/uploads/projects/project_documents/',
        '../frontend/assets/uploads/projects/',
        '../frontend/assets/uploads/reports/',
        '../../frontend/assets/uploads/projects/project_documents/',
        '../../frontend/assets/uploads/projects/',
        '../../frontend/assets/uploads/reports/',
        __DIR__ . '/../../frontend/assets/uploads/projects/project_documents/',
        __DIR__ . '/../../frontend/assets/uploads/projects/',
        __DIR__ . '/../../frontend/assets/uploads/reports/',
    ];
    
    // Clean the file name
    $cleanFileName = basename($fileName);
    $cleanFilePath = basename($filePath);
    
    // If filePath is provided and contains a path, extract the filename
    if ($filePath && strpos($filePath, '/') !== false) {
        $cleanFilePath = basename($filePath);
    }
    
    // Build list of filenames to try
    $fileNamesToTry = [];
    if ($cleanFilePath) {
        $fileNamesToTry[] = $cleanFilePath;
    }
    if ($cleanFileName && $cleanFileName !== $cleanFilePath) {
        $fileNamesToTry[] = $cleanFileName;
    }
    // Also try the original full path if provided
    if ($filePath && strpos($filePath, '/') !== false) {
        $fileNamesToTry[] = $filePath;
    }
    
    // Search through all base paths
    foreach ($basePaths as $basePath) {
        foreach ($fileNamesToTry as $name) {
            $fullPath = $basePath . $name;
            if (file_exists($fullPath)) {
                return $fullPath;
            }
        }
    }
    
    // Also try looking in subdirectories
    foreach ($basePaths as $basePath) {
        if (is_dir($basePath)) {
            $files = scandir($basePath);
            if ($files) {
                foreach ($files as $file) {
                    if ($file === '.' || $file === '..') continue;
                    // Check if filename matches (case insensitive)
                    if (strtolower($file) === strtolower($cleanFileName) || 
                        strtolower($file) === strtolower($cleanFilePath)) {
                        return $basePath . $file;
                    }
                    // Check if file contains the name
                    if (stripos($file, $cleanFileName) !== false || 
                        stripos($file, $cleanFilePath) !== false) {
                        return $basePath . $file;
                    }
                }
            }
        }
    }
    
    return null;
}

// ============================================
// MAIN LOGIC: Get document and find file
// ============================================
try {
    $fileName = '';
    $filePath = '';
    $documentTitle = '';
    
    // ========== STEP 1: Check sent_documents table ==========
    $stmt = $pdo->prepare("SELECT * FROM sent_documents WHERE id = ? AND is_deleted = 0");
    $stmt->execute([$id]);
    $sentDoc = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($sentDoc) {
        // Try to get file info from document_data JSON
        $docData = json_decode($sentDoc['document_data'], true);
        if ($docData) {
            $fileName = isset($docData['file_name']) ? $docData['file_name'] : '';
            $filePath = isset($docData['file_path']) ? $docData['file_path'] : '';
            $documentTitle = isset($docData['title']) ? $docData['title'] : $sentDoc['document_title'];
        }
        
        // If not found in JSON, use sent_document fields
        if (!$fileName) {
            $fileName = $sentDoc['document_file'];
            $filePath = $sentDoc['document_file'];
        }
        if (!$documentTitle) {
            $documentTitle = $sentDoc['document_title'];
        }
        
        // Try to find the file
        $foundPath = findFile($fileName, $filePath);
        if ($foundPath && serveFile($foundPath, $fileName)) {
            exit;
        }
        
        // If not found, try to find original document
        if ($sentDoc['original_document_id']) {
            $origId = $sentDoc['original_document_id'];
            $stmt2 = $pdo->prepare("SELECT * FROM project_documents WHERE id = ?");
            $stmt2->execute([$origId]);
            $origDoc = $stmt2->fetch(PDO::FETCH_ASSOC);
            if ($origDoc) {
                $origFileName = $origDoc['file_name'];
                $origFilePath = $origDoc['file_path'];
                $foundPath = findFile($origFileName, $origFilePath);
                if ($foundPath && serveFile($foundPath, $origFileName)) {
                    exit;
                }
            }
        }
    }
    
    // ========== STEP 2: Check project_documents table ==========
    $stmt = $pdo->prepare("SELECT * FROM project_documents WHERE id = ? AND is_deleted != 1");
    $stmt->execute([$id]);
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($doc) {
        $fileName = isset($doc['file_name']) ? $doc['file_name'] : 'document';
        $filePath = isset($doc['file_path']) ? $doc['file_path'] : $fileName;
        $foundPath = findFile($fileName, $filePath);
        if ($foundPath && serveFile($foundPath, $fileName)) {
            exit;
        }
    }
    
    // ========== STEP 3: Check uploaded_reports table ==========
    $stmt = $pdo->prepare("SELECT * FROM uploaded_reports WHERE id = ? AND is_deleted != 1");
    $stmt->execute([$id]);
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($doc) {
        $fileName = isset($doc['file_name']) ? $doc['file_name'] : 'report';
        $filePath = isset($doc['file_path']) ? $doc['file_path'] : $fileName;
        $foundPath = findFile($fileName, $filePath);
        if ($foundPath && serveFile($foundPath, $fileName)) {
            exit;
        }
    }
    
    // ========== STEP 4: Check sent_uploaded_reports table ==========
    $stmt = $pdo->prepare("SELECT * FROM sent_uploaded_reports WHERE id = ? AND is_deleted = 0");
    $stmt->execute([$id]);
    $sentDoc = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($sentDoc) {
        $docData = json_decode($sentDoc['uploaded_report_data'], true);
        if ($docData) {
            $fileName = isset($docData['file_name']) ? $docData['file_name'] : '';
            $filePath = isset($docData['file_path']) ? $docData['file_path'] : '';
        }
        if (!$fileName) {
            $fileName = $sentDoc['uploaded_report_file'];
            $filePath = $sentDoc['uploaded_report_file'];
        }
        $foundPath = findFile($fileName, $filePath);
        if ($foundPath && serveFile($foundPath, $fileName)) {
            exit;
        }
        
        // Try original uploaded report
        if ($sentDoc['original_uploaded_report_id']) {
            $origId = $sentDoc['original_uploaded_report_id'];
            $stmt2 = $pdo->prepare("SELECT * FROM uploaded_reports WHERE id = ?");
            $stmt2->execute([$origId]);
            $origDoc = $stmt2->fetch(PDO::FETCH_ASSOC);
            if ($origDoc) {
                $origFileName = $origDoc['file_name'];
                $origFilePath = $origDoc['file_path'];
                $foundPath = findFile($origFileName, $origFilePath);
                if ($foundPath && serveFile($foundPath, $origFileName)) {
                    exit;
                }
            }
        }
    }
    
    // ========== STEP 5: Try by title search ==========
    if ($documentTitle) {
        // Search project_documents by title
        $stmt = $pdo->prepare("SELECT * FROM project_documents WHERE title = ? AND is_deleted != 1 ORDER BY id DESC LIMIT 1");
        $stmt->execute([$documentTitle]);
        $doc = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($doc) {
            $fileName = isset($doc['file_name']) ? $doc['file_name'] : 'document';
            $filePath = isset($doc['file_path']) ? $doc['file_path'] : $fileName;
            $foundPath = findFile($fileName, $filePath);
            if ($foundPath && serveFile($foundPath, $fileName)) {
                exit;
            }
        }
        
        // Search uploaded_reports by title
        $stmt = $pdo->prepare("SELECT * FROM uploaded_reports WHERE title = ? AND is_deleted != 1 ORDER BY id DESC LIMIT 1");
        $stmt->execute([$documentTitle]);
        $doc = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($doc) {
            $fileName = isset($doc['file_name']) ? $doc['file_name'] : 'report';
            $filePath = isset($doc['file_path']) ? $doc['file_path'] : $fileName;
            $foundPath = findFile($fileName, $filePath);
            if ($foundPath && serveFile($foundPath, $fileName)) {
                exit;
            }
        }
    }
    
    // ========== If file not found ==========
    http_response_code(404);
    echo json_encode([
        'success' => false, 
        'message' => 'File not found on server. Please check if the file exists.',
        'debug' => [
            'document_id' => $id,
            'document_type' => $type,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'title' => $documentTitle
        ]
    ]);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>