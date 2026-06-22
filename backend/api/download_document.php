<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "geotraverse_erp";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

$document_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$document_type = isset($_GET['type']) ? $_GET['type'] : 'project';

if (!$document_id) {
    echo json_encode(['success' => false, 'message' => 'Document ID is required']);
    exit;
}

// ===== GET DOCUMENT FROM DATABASE =====
$query = "SELECT * FROM project_documents WHERE id = ? AND is_deleted != 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $document_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Document not found']);
    exit;
}

$doc = $result->fetch_assoc();
$stmt->close();
$conn->close();

$file_name = $doc['file_name'];
$file_path = $doc['file_path'];

// Clean filename
$clean_file_name = $file_name;
if (empty($clean_file_name) && !empty($file_path)) {
    $clean_file_name = basename($file_path);
}
if (empty($clean_file_name)) {
    $parts = explode('/', $file_path);
    $clean_file_name = end($parts);
}

// ===== TRY MULTIPLE PATHS - FIXED =====
$possible_paths = [
    // MAIN PATH - frontend/assets/uploads/projects/projects_documents/
    __DIR__ . '/../../frontend/assets/uploads/projects/projects_documents/' . $clean_file_name,
    
    // Alternative - with file_path as is
    __DIR__ . '/../../frontend/' . $file_path,
    __DIR__ . '/../../frontend/assets/uploads/projects/' . $clean_file_name,
    
    // Try from document root
    $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/frontend/assets/uploads/projects/projects_documents/' . $clean_file_name,
    $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/frontend/assets/uploads/projects/' . $clean_file_name,
    $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/assets/uploads/projects/projects_documents/' . $clean_file_name,
    
    // Try with base path from file_path
    __DIR__ . '/../../' . $file_path,
    __DIR__ . '/../' . $file_path,
    
    // Try as is
    $file_path,
];

$found_file = null;
$found_path = null;

foreach ($possible_paths as $path) {
    if (!empty($path) && file_exists($path)) {
        $found_file = $path;
        $found_path = $path;
        break;
    }
}

// ===== IF FILE NOT FOUND, RETURN ERROR =====
if (!$found_file) {
    echo json_encode([
        'success' => false,
        'message' => 'File not found on server. Please check if the file exists.',
        'debug' => [
            'document_id' => $document_id,
            'document_type' => $document_type,
            'file_name' => $file_name,
            'file_path' => $file_path,
            'clean_file_name' => $clean_file_name,
            'checked_paths' => $possible_paths
        ]
    ]);
    exit;
}

// ===== SERVE FILE =====
$mime_type = mime_content_type($found_file) ?: 'application/octet-stream';

header('Content-Type: ' . $mime_type);
header('Content-Disposition: attachment; filename="' . $clean_file_name . '"');
header('Content-Length: ' . filesize($found_file));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: public');

readfile($found_file);
exit;
?>