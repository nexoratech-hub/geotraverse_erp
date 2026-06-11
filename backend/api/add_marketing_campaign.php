<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['campaign_name'])) {
    sendResponse(false, 'Campaign name required');
}

try {
    $stmt = $pdo->prepare("INSERT INTO marketing_campaigns 
        (campaign_name, campaign_type, budget, spent, start_date, end_date, target_audience, description, status, department_id, created_by) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $data['campaign_name'],
        $data['campaign_type'] ?? 'digital',
        $data['budget'] ?? 0,
        $data['spent'] ?? 0,
        $data['start_date'] ?? null,
        $data['end_date'] ?? null,
        $data['target_audience'] ?? null,
        $data['description'] ?? null,
        $data['status'] ?? 'planned',
        $data['department_id'] ?? 3,
        $data['created_by'] ?? 'System'
    ]);
    
    sendResponse(true, 'Marketing campaign added successfully', ['id' => $pdo->lastInsertId()]);
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>