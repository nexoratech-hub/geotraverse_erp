<?php
require_once 'db_connect.php';

if (isset($_GET['id']) && isset($_GET['type'])) {
    $id = intval($_GET['id']);
    $type = $_GET['type'];
    
    if ($type === 'project') {
        $stmt = $conn->prepare("SELECT file_path, file_name FROM project_documents WHERE id = ?");
    } else {
        $stmt = $conn->prepare("SELECT file_path, file_name FROM report_documents WHERE id = ?");
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $filePath = $_SERVER['DOCUMENT_ROOT'] . $row['file_path'];
        $fileName = $row['file_name'];
        
        if (file_exists($filePath)) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        }
    }
    $stmt->close();
}

header('HTTP/1.0 404 Not Found');
echo 'File not found';
$conn->close();
?>