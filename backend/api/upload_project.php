<?php
// upload_project.php
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
$client_name = isset($_POST['client_name']) ? trim($_POST['client_name']) : '';
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
$location = isset($_POST['location']) ? trim($_POST['location']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$status = isset($_POST['status']) ? trim($_POST['status']) : 'pending';
$progress = isset($_POST['progress']) ? intval($_POST['progress']) : 0;
$department_id = isset($_POST['department_id']) ? intval($_POST['department_id']) : 0;
$created_by = isset($_POST['created_by']) ? trim($_POST['created_by']) : 'System';

if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Project title is required']);
    exit();
}

$image_path = '';
$file_type = '';
$upload_error = null;

// Handle file upload
if (isset($_FILES['project_file']) && $_FILES['project_file']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../uploads/projects/';
    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file = $_FILES['project_file'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
    
    if (!in_array($file_extension, $allowed_extensions)) {
        echo json_encode(['success' => false, 'message' => 'File type not allowed. Allowed: jpg, png, pdf, doc, xls']);
        exit();
    }
    
    $file_type = $file['type'];
    $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
    $upload_path = $upload_dir . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        $image_path = $new_filename;
    } else {
        $upload_error = 'Failed to move uploaded file';
    }
}

// Insert into database
$conn = getConnection();
$query = "INSERT INTO projects (name, client_name, amount, location, description, status, progress, image, file_type, department_id, created_by, created_at) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($query);
$stmt->bind_param("ssdssssssis", $title, $client_name, $amount, $location, $description, $status, $progress, $image_path, $file_type, $department_id, $created_by);

if ($stmt->execute()) {
    $project_id = $conn->insert_id;
    echo json_encode([
        'success' => true,
        'message' => 'Project uploaded successfully',
        'project_id' => $project_id,
        'image_path' => $image_path,
        'file_type' => $file_type
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>