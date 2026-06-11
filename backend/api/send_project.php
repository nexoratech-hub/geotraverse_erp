<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['project_id']) || empty($data['to_department_id'])) {
    sendResponse(false, 'Project ID and destination department required');
}

try {
    $stmt = $pdo->prepare("UPDATE projects SET sent_from_dept = :from_dept, sent_to_dept = :to_dept WHERE id = :id");
    $stmt->execute([
        'from_dept' => $data['from_department_id'],
        'to_dept' => $data['to_department_id'],
        'id' => $data['project_id']
    ]);
    
    // Add to activity log
    $stmt2 = $pdo->prepare("INSERT INTO activity_logs (user_name, department_id, action) VALUES ('System', :from_dept, :action)");
    $stmt2->execute([
        'from_dept' => $data['from_department_id'],
        'action' => "Sent project ID {$data['project_id']} to department {$data['to_department_id']}"
    ]);
    
    sendResponse(true, 'Project sent successfully');
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>