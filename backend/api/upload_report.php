<?php
// upload_report.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$period = isset($_POST['period']) ? trim($_POST['period']) : 'monthly';
$department_id = isset($_POST['department_id']) ? intval($_POST['department_id']) : 0;
$created_by = isset($_POST['created_by']) ? trim($_POST['created_by']) : 'System';
$status = isset($_POST['status']) ? trim($_POST['status']) : 'draft';

if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Report title is required']);
    exit();
}

$file_content = '';
$file_path = '';
$file_type = '';
$file_name = '';

// Handle file upload - extract content for preview
if (isset($_FILES['report_file']) && $_FILES['report_file']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../uploads/reports/';
    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file = $_FILES['report_file'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $file_name = $file['name'];
    $file_type = $file['type'];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'csv'];
    
    if (!in_array($file_extension, $allowed_extensions)) {
        echo json_encode(['success' => false, 'message' => 'File type not allowed']);
        exit();
    }
    
    $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
    $upload_path = $upload_dir . $new_filename;
    $file_path = $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        // Extract content from file for preview
        $file_content = extractFileContent($upload_path, $file_extension, $file_name, $created_by);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
        exit();
    }
}

// If no file uploaded, use text content from textarea
if (!$file_content && isset($_POST['content']) && !empty($_POST['content'])) {
    $file_content = trim($_POST['content']);
    $file_content .= "\n\n---\nCreated by: " . $created_by . " on " . date('Y-m-d H:i:s');
}

// Insert into database
$conn = getConnection();
$query = "INSERT INTO reports (title, period, content, department_id, status, created_by, created_at, file_path, file_type, file_name) 
          VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)";

$stmt = $conn->prepare($query);
$stmt->bind_param("sssississ", $title, $period, $file_content, $department_id, $status, $created_by, $file_path, $file_type, $file_name);

if ($stmt->execute()) {
    $report_id = $conn->insert_id;
    echo json_encode([
        'success' => true,
        'message' => 'Report uploaded successfully',
        'report_id' => $report_id,
        'content_preview' => substr($file_content, 0, 500) . (strlen($file_content) > 500 ? '...' : '')
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}

$stmt->close();
$conn->close();

// Function to extract content from different file types
function extractFileContent($file_path, $extension, $original_name, $created_by) {
    $content = "📎 UPLOADED DOCUMENT: " . $original_name . "\n";
    $content .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $content .= "Uploaded by: " . $created_by . "\n";
    $content .= "Uploaded on: " . date('Y-m-d H:i:s') . "\n";
    $content .= "File type: " . strtoupper($extension) . "\n";
    $content .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    // Extract text from different file types
    if ($extension == 'txt') {
        $file_content = file_get_contents($file_path);
        $content .= $file_content;
    } 
    elseif ($extension == 'pdf') {
        $content .= "[PDF DOCUMENT UPLOADED]\n";
        $content .= "File: " . $original_name . "\n";
        $content .= "To view the full document, please download the file.\n";
        $content .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $content .= "PDF documents can be viewed by clicking the 'View Document' button.\n";
    }
    elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
        $content .= "[IMAGE DOCUMENT UPLOADED]\n";
        $content .= "File: " . $original_name . "\n";
        $content .= "Image size: " . round(filesize($file_path) / 1024, 2) . " KB\n";
        $content .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $content .= "To view the image, click the 'View Image' button.\n";
    }
    elseif (in_array($extension, ['doc', 'docx', 'xls', 'xlsx'])) {
        $content .= "[OFFICE DOCUMENT UPLOADED]\n";
        $content .= "File: " . $original_name . "\n";
        $content .= "File size: " . round(filesize($file_path) / 1024, 2) . " KB\n";
        $content .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $content .= "To view this document, please download the file.\n";
    }
    else {
        $content .= "[DOCUMENT UPLOADED]\n";
        $content .= "File: " . $original_name . "\n";
        $content .= "File size: " . round(filesize($file_path) / 1024, 2) . " KB\n";
    }
    
    return $content;
}
?>