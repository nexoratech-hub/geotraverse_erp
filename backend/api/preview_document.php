<?php
error_reporting(0);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once 'db_connect.php';

$response = ['success' => false, 'message' => 'Document not found'];

if (isset($_GET['id']) && isset($_GET['type'])) {
    $id = intval($_GET['id']);
    $type = $_GET['type'];
    
    if ($type === 'project') {
        $stmt = $conn->prepare("SELECT * FROM project_documents WHERE id = ?");
    } else {
        $stmt = $conn->prepare("SELECT * FROM report_documents WHERE id = ?");
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $filePath = $row['file_path'];
        $fileName = $row['file_name'];
        
        $absolutePath = $_SERVER['DOCUMENT_ROOT'] . $filePath;
        
        $response = [
            'success' => true,
            'file_path' => $filePath,
            'file_exists' => file_exists($absolutePath),
            'file_name' => $fileName,
            'title' => $row['title'],
            'description' => $row['description'],
            'uploaded_by' => $row['uploaded_by'],
            'created_at' => $row['created_at'],
            'file_size' => $row['file_size'],
            'file_type' => $row['file_type']
        ];
        
        if ($type === 'report') {
            $response['period'] = $row['period'];
        }
    }
    $stmt->close();
}

echo json_encode($response);
$conn->close();
?>