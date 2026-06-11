<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['request_id'])) {
    sendResponse(false, 'Request ID required');
}

try {
    $stmt = $pdo->prepare("UPDATE budget_requests SET 
        status = 'cancelled', 
        reviewed_by = :reviewed_by, 
        reviewed_at = NOW(),
        admin_notes = :notes
        WHERE id = :id");
    
    $stmt->execute([
        'reviewed_by' => $data['reviewed_by'] ?? 2,
        'notes' => $data['notes'] ?? 'Request cancelled',
        'id' => $data['request_id']
    ]);
    
    // Log activity
    $stmt2 = $pdo->prepare("INSERT INTO activity_logs (user_name, department_id, action, details) VALUES (?, 2, 'Cancelled budget request', ?)");
    $stmt2->execute([$data['reviewed_by_name'] ?? 'Finance Manager', "Request #{$data['request_id']}"]);
    
    sendResponse(true, 'Budget request cancelled successfully');
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>