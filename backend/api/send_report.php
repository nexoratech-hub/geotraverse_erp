<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['report_id']) || empty($data['to_department_id'])) {
    sendResponse(false, 'Report ID and destination department required');
}

try {
    $stmt = $pdo->prepare("UPDATE reports SET sent_from_department = :from_dept, sent_to_department = :to_dept WHERE id = :id");
    $stmt->execute([
        'from_dept' => $data['from_department_id'],
        'to_dept' => $data['to_department_id'],
        'id' => $data['report_id']
    ]);
    
    sendResponse(true, 'Report sent successfully');
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>