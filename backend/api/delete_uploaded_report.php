<?php
error_reporting(0);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

$response = ['success' => false, 'message' => 'Delete failed'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = isset($input['id']) ? intval($input['id']) : 0;
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        exit;
    }
    
    $stmt = $conn->prepare("DELETE FROM report_documents WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $response = ['success' => true, 'message' => 'Report deleted successfully'];
    } else {
        $response['message'] = 'Database error: ' . $stmt->error;
    }
    $stmt->close();
}

echo json_encode($response);
$conn->close();
?>