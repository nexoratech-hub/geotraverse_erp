<?php
// backend/api/send_uploaded_report.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['report_id']) || !isset($data['to_department_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$report_id = intval($data['report_id']);
$to_dept = intval($data['to_department_id']);
$from_dept = intval($data['from_department_id'] ?? 0);

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("UPDATE uploaded_reports SET 
        sent_to_department = ?, 
        sent_from_department = ?,
        is_viewed_by_department = 0,
        updated_at = NOW()
        WHERE id = ?");
    
    $stmt->execute([$to_dept, $from_dept, $report_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Report sent successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Report not found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>