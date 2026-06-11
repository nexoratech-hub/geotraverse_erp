<?php
require_once 'config.php';

$uploadDir = '../uploads/campaign_documents/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $departmentId = $_POST['department_id'] ?? 1;
    $uploadedBy = $_POST['uploaded_by'] ?? 'System';
    
    if (empty($title)) {
        sendResponse(false, 'Document title required');
    }
    
    if (!isset($_FILES['document_file']) || $_FILES['document_file']['error'] !== UPLOAD_ERR_OK) {
        sendResponse(false, 'File upload failed');
    }
    
    $file = $_FILES['document_file'];
    $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
    $filePath = $uploadDir . $fileName;
    $fileSize = $file['size'];
    $fileType = $file['type'];
    
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO project_documents (title, description, file_name, file_path, file_size, file_type, doc_type, department_id, uploaded_by, created_by) VALUES (?, ?, ?, ?, ?, ?, 'campaign', ?, ?, ?)");
            
            $stmt->execute([
                $title,
                $description,
                $fileName,
                $filePath,
                $fileSize,
                $fileType,
                $departmentId,
                $uploadedBy,
                $uploadedBy
            ]);
            
            sendResponse(true, 'Campaign document uploaded successfully', ['id' => $pdo->lastInsertId()]);
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