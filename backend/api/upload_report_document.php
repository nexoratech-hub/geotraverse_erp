<?php
require_once 'config.php';

$uploadDir = '../uploads/reports/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $period = $_POST['period'] ?? 'monthly';
    $departmentId = $_POST['department_id'] ?? 1;
    $uploadedBy = $_POST['uploaded_by'] ?? 'System';
    
    if (empty($title)) {
        sendResponse(false, 'Report title required');
    }
    
    if (!isset($_FILES['report_file']) || $_FILES['report_file']['error'] !== UPLOAD_ERR_OK) {
        sendResponse(false, 'File upload failed');
    }
    
    $file = $_FILES['report_file'];
    $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
    $filePath = $uploadDir . $fileName;
    $fileSize = $file['size'];
    $fileType = $file['type'];
    
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO uploaded_reports (title, period, file_name, file_path, file_size, file_type, department_id, uploaded_by, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $title,
                $period,
                $fileName,
                $filePath,
                $fileSize,
                $fileType,
                $departmentId,
                $uploadedBy,
                $uploadedBy
            ]);
            
            sendResponse(true, 'Report uploaded successfully', ['id' => $pdo->lastInsertId()]);
        } catch(PDOException $e) {
            unlink($filePath);
            sendResponse(false, 'Database error: ' . $e->getMessage());
        }
    } else {
        sendResponse(false, 'Failed to save file');
    }
} else {
    sendResponse(false, 'Invalid request method');
}
?>