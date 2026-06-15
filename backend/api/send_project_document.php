<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$document_id = $data['document_id'] ?? null;
$to_department_id = $data['to_department_id'] ?? null;
$from_department_id = $data['from_department_id'] ?? 1;
$message = $data['message'] ?? '';

if (!$document_id || !$to_department_id) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields: document_id and to_department_id']);
    exit;
}

try {
    // First, get the document details
    $stmt = $conn->prepare("SELECT * FROM project_documents WHERE id = ?");
    $stmt->bind_param("i", $document_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $document = $result->fetch_assoc();
    $stmt->close();
    
    if (!$document) {
        echo json_encode(['success' => false, 'message' => 'Document not found']);
        exit;
    }
    
    // Update the document to mark it as sent
    $stmt = $conn->prepare("
        UPDATE project_documents 
        SET sent_to_department = ?, 
            sent_from_department = ?, 
            status = 'sent',
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->bind_param("iii", $to_department_id, $from_department_id, $document_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Document sent successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }
    $stmt->close();
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>