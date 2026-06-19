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

// ============ GET PARAMETERS ============
$doc_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($doc_id <= 0) {
    die(json_encode(['success' => false, 'message' => 'Invalid document ID']));
}

// ============ GET DOCUMENT DATA ============
try {
    $stmt = $pdo->prepare("SELECT * FROM project_documents WHERE id = ? AND is_deleted = 0");
    $stmt->execute([$doc_id]);
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$doc) {
        die(json_encode(['success' => false, 'message' => 'Document not found']));
    }
    
    // Check if it's a campaign document (doc_type = 'campaign')
    // If not, check if it's associated with campaign via project_id
    $isCampaignDoc = false;
    if (isset($doc['doc_type']) && $doc['doc_type'] === 'campaign') {
        $isCampaignDoc = true;
    } else if (isset($doc['project_id'])) {
        // Check if the project is actually a campaign
        $checkStmt = $pdo->prepare("SELECT * FROM marketing_campaigns WHERE id = ? AND is_deleted = 0");
        $checkStmt->execute([$doc['project_id']]);
        if ($checkStmt->fetch()) {
            $isCampaignDoc = true;
        }
    }
    
    // ============ BUILD FILE PATH ============
    // Files are uploaded to: /geotraverse/frontend/assets/uploads/projects/projects_documents/
    // or directly to projects folder
    
    $filePath = $doc['file_path'] ?? $doc['file_name'] ?? '';
    $fileName = $doc['file_name'] ?? 'document';
    
    // Clean the path
    $filePath = str_replace(['../', '..\\', '\\'], '/', $filePath);
    $filePath = ltrim($filePath, '/');
    
    // Remove any path prefix if it exists
    $filePath = basename($filePath);
    
    // Build full file path - campaign documents are stored in projects_documents folder
    $basePath = __DIR__ . '/../../frontend/assets/uploads/projects/projects_documents/';
    $fullPath = $basePath . $filePath;
    
    // If file doesn't exist in projects_documents, try projects folder
    if (!file_exists($fullPath)) {
        $basePath = __DIR__ . '/../../frontend/assets/uploads/projects/';
        $fullPath = $basePath . $filePath;
    }
    
    // If still not found, try reports folder
    if (!file_exists($fullPath)) {
        $basePath = __DIR__ . '/../../frontend/assets/uploads/reports/';
        $fullPath = $basePath . $filePath;
    }
    
    // ============ CHECK IF FILE EXISTS ============
    if (!file_exists($fullPath)) {
        // Try to find the file by searching in all upload directories
        $searchPaths = [
            __DIR__ . '/../../frontend/assets/uploads/projects/projects_documents/',
            __DIR__ . '/../../frontend/assets/uploads/projects/',
            __DIR__ . '/../../frontend/assets/uploads/reports/'
        ];
        
        $found = false;
        foreach ($searchPaths as $path) {
            $testPath = $path . $filePath;
            if (file_exists($testPath)) {
                $fullPath = $testPath;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            die(json_encode(['success' => false, 'message' => 'File not found on server. Path: ' . $fullPath]));
        }
    }
    
    // ============ DOWNLOAD FILE ============
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
    header('Content-Length: ' . filesize($fullPath));
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    
    readfile($fullPath);
    exit;
    
} catch(PDOException $e) {
    die(json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]));
}