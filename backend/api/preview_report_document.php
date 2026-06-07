<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';

$response = ['success' => false, 'message' => '', 'file_path' => '', 'file_name' => '', 'title' => '', 'description' => '', 'period' => '', 'uploaded_by' => '', 'created_at' => '', 'file_exists' => false, 'file_size' => 0, 'file_type' => ''];

try {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($id <= 0) {
        throw new Exception('Invalid document ID');
    }

    $database = new Database();
    $db = $database->getConnection();

    $query = "SELECT id, title, description, period, file_name, file_path, file_type, file_size, uploaded_by, created_at, department_id 
              FROM reports 
              WHERE id = :id AND file_name IS NOT NULL AND file_name != ''";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        throw new Exception('Document not found');
    }

    $doc = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $response['id'] = $doc['id'];
    $response['title'] = $doc['title'];
    $response['description'] = $doc['description'] ?? '';
    $response['period'] = $doc['period'] ?? 'monthly';
    $response['file_name'] = $doc['file_name'];
    $response['uploaded_by'] = $doc['uploaded_by'] ?? 'System';
    $response['created_at'] = $doc['created_at'];
    $response['file_type'] = $doc['file_type'] ?? '';
    $response['file_size'] = $doc['file_size'] ?? 0;
    
    // Build full file path
    $basePath = dirname(__DIR__, 2) . '/frontend';
    $fullFilePath = $basePath . $doc['file_path'];
    
    if (file_exists($fullFilePath)) {
        $response['file_exists'] = true;
        $response['file_path'] = $doc['file_path'];
    } else {
        // Try alternative path
        $altPath = dirname(__DIR__, 2) . '/frontend/assets/uploads/reports/' . $doc['file_name'];
        if (file_exists($altPath)) {
            $response['file_exists'] = true;
            $response['file_path'] = '/geotraverse/frontend/assets/uploads/reports/' . $doc['file_name'];
        } else {
            $response['file_exists'] = false;
            $response['message'] = 'File not found on server';
        }
    }
    
    $response['success'] = true;

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>