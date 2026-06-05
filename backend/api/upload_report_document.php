<?php
error_reporting(0);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

$response = ['success' => false, 'message' => 'Upload failed'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $description = isset($_POST['description']) ? $_POST['description'] : '';
    $period = isset($_POST['period']) ? $_POST['period'] : 'monthly';
    $department_id = isset($_POST['department_id']) ? intval($_POST['department_id']) : 1;
    $uploaded_by = isset($_POST['uploaded_by']) ? $_POST['uploaded_by'] : 'System';
    
    if (empty($title)) {
        echo json_encode(['success' => false, 'message' => 'Title is required']);
        exit;
    }
    
    if (!isset($_FILES['report_file']) || $_FILES['report_file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Please select a valid file']);
        exit;
    }
    
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/geotraverse/frontend/assets/uploads/reports/';
    
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $file = $_FILES['report_file'];
    $originalName = basename($file['name']);
    $fileExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowedExts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif', 'txt'];
    
    if (!in_array($fileExt, $allowedExts)) {
        echo json_encode(['success' => false, 'message' => 'File type not allowed']);
        exit;
    }
    
    $newFileName = time() . '_' . uniqid() . '.' . $fileExt;
    $uploadPath = $uploadDir . $newFileName;
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        $filePathForDB = '/geotraverse/frontend/assets/uploads/reports/' . $newFileName;
        $fileType = $file['type'];
        $fileSize = $file['size'];
        
        $stmt = $conn->prepare("INSERT INTO report_documents (title, description, period, department_id, file_name, file_path, file_type, file_size, uploaded_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssissssis", $title, $description, $period, $department_id, $originalName, $filePathForDB, $fileType, $fileSize, $uploaded_by);
        
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Report uploaded successfully', 'document_id' => $stmt->insert_id];
        } else {
            unlink($uploadPath);
            $response['message'] = 'Database error: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        $response['message'] = 'Failed to move uploaded file. Check folder permissions.';
    }
}

echo json_encode($response);
$conn->close();
?>