<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['id'])) {
    sendResponse(false, 'Campaign ID required');
}

try {
    $stmt = $pdo->prepare("UPDATE marketing_campaigns SET 
        campaign_name = ?,
        campaign_type = ?,
        budget = ?,
        spent = ?,
        start_date = ?,
        end_date = ?,
        target_audience = ?,
        description = ?,
        status = ?,
        updated_by = ?
        WHERE id = ?");
    
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
        $data['updated_by'] ?? 'System',
        $data['id']
    ]);
    
    sendResponse(true, 'Marketing campaign updated successfully');
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>