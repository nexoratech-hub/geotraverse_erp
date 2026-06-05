<?php
error_reporting(0);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once 'db_connect.php';

$response = ['success' => false, 'message' => 'Document not found'];

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $stmt = $conn->prepare("SELECT * FROM report_documents WHERE id = ?");
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
            'period' => $row['period'],
            'uploaded_by' => $row['uploaded_by'],
            'created_at' => $row['created_at'],
            'file_size' => $row['file_size'],
            'file_type' => $row['file_type']
        ];
    }
    $stmt->close();
}

echo json_encode($response);
$conn->close();
?>