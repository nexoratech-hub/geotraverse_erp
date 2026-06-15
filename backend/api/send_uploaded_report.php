<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$report_id = $data['report_id'] ?? null;
$to_department_id = $data['to_department_id'] ?? null;
$from_department_id = $data['from_department_id'] ?? 1;

if (!$report_id || !$to_department_id) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    // Update the uploaded report to mark it as sent
    $stmt = $conn->prepare("
        UPDATE uploaded_reports 
        SET sent_to_department = ?, 
            sent_from_department = ?, 
            status = 'sent',
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->bind_param("iii", $to_department_id, $from_department_id, $report_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Report sent successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }
    $stmt->close();
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>