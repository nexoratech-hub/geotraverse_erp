<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['request_id'])) {
    sendResponse(false, 'Request ID required');
}

try {
    // First, get the request details
    $stmt = $pdo->prepare("SELECT * FROM budget_requests WHERE id = :id AND is_deleted = 0");
    $stmt->execute(['id' => $data['request_id']]);
    $request = $stmt->fetch();
    
    if (!$request) {
        sendResponse(false, 'Request not found');
    }
    
    // Update budget request status
    $stmt2 = $pdo->prepare("UPDATE budget_requests SET 
        status = 'approved', 
        reviewed_by = :reviewed_by, 
        reviewed_at = NOW(),
        admin_notes = :notes
        WHERE id = :id");
    
    $stmt2->execute([
        'reviewed_by' => $data['reviewed_by'] ?? 2,
        'notes' => $data['notes'] ?? null,
        'id' => $data['request_id']
    ]);
    
    // Add to transactions table as an expense/income record
    $stmt3 = $pdo->prepare("INSERT INTO transactions 
        (type, source, amount, paid_amount, transaction_date, status, description, department_id, created_by) 
        VALUES (?, ?, ?, ?, ?, 'approved', ?, ?, ?)");
    
    $stmt3->execute([
        $request['type'],
        $request['source'],
        $request['amount'],
        $request['amount'],
        $request['transaction_date'] ?? date('Y-m-d'),
        "Approved budget request: " . ($request['title'] ?? $request['source']),
        $request['department_id'],
        'Finance Department'
    ]);
    
    // Log activity
    $stmt4 = $pdo->prepare("INSERT INTO activity_logs (user_name, department_id, action, details) VALUES (?, 2, 'Approved budget request', ?)");
    $stmt4->execute([$data['reviewed_by_name'] ?? 'Finance Manager', "Request #{$data['request_id']} - {$request['source']} - Amount: {$request['amount']}"]);
    
    sendResponse(true, 'Budget request approved successfully', [
        'transaction_id' => $pdo->lastInsertId()
    ]);
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>