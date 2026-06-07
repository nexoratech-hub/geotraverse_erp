<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';

$response = ['success' => false, 'message' => ''];

try {
    // Check if file was uploaded
    if (!isset($_FILES['report_file']) || $_FILES['report_file']['error'] !== UPLOAD_ERR_OK) {
        $uploadError = isset($_FILES['report_file']) ? $_FILES['report_file']['error'] : 'No file';
        throw new Exception('File upload failed. Error code: ' . $uploadError);
    }

    $title = $_POST['title'] ?? '';
    $period = $_POST['period'] ?? 'monthly';
    $department_id = intval($_POST['department_id'] ?? 1);
    $uploaded_by = $_POST['uploaded_by'] ?? 'System';

    if (empty($title)) {
        throw new Exception('Title is required');
    }

    // Create upload directory if not exists
    $uploadDir = dirname(__DIR__, 2) . '/frontend/assets/uploads/reports/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Generate unique filename
    $file = $_FILES['report_file'];
    $originalName = basename($file['name']);
    $fileExt = pathinfo($originalName, PATHINFO_EXTENSION);
    $fileName = 'report_' . time() . '_' . uniqid() . '.' . $fileExt;
    $filePath = $uploadDir . $fileName;
    $relativePath = '/geotraverse/frontend/assets/uploads/reports/' . $fileName;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        throw new Exception('Failed to save file to server. Check directory permissions.');
    }

    // Save to database
    $database = new Database();
    $db = $database->getConnection();

    $query = "INSERT INTO reports (title, period, content, file_name, file_path, file_type, file_size, department_id, created_by, status, created_at) 
              VALUES (:title, :period, :content, :file_name, :file_path, :file_type, :file_size, :department_id, :created_by, 'draft', NOW())";
    
    $stmt = $db->prepare($query);
    
    $content = "📎 UPLOADED REPORT\nTitle: " . $title . "\nFile: " . $originalName . "\nUploaded: " . date('Y-m-d H:i:s');
    $fileSize = $file['size'];
    $fileType = $file['type'];

    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':period', $period);
    $stmt->bindParam(':content', $content);
    $stmt->bindParam(':file_name', $originalName);
    $stmt->bindParam(':file_path', $relativePath);
    $stmt->bindParam(':file_type', $fileType);
    $stmt->bindParam(':file_size', $fileSize);
    $stmt->bindParam(':department_id', $department_id);
    $stmt->bindParam(':created_by', $uploaded_by);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Report uploaded successfully';
        $response['file_path'] = $relativePath;
        $response['id'] = $db->lastInsertId();
    } else {
        // Delete file if database insert fails
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        throw new Exception('Failed to save to database');
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>