<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['id'])) {
    sendResponse(false, 'Project ID required');
}

try {
    $stmt = $pdo->prepare("UPDATE projects SET name = ?, client_name = ?, amount = ?, location = ?, description = ?, status = ?, progress = ?, start_date = ?, end_date = ?, image = ?, updated_by = ? WHERE id = ?");
    
    $stmt->execute([
        $data['name'] ?? null,
        $data['client_name'] ?? null,
        $data['amount'] ?? 0,
        $data['location'] ?? null,
        $data['description'] ?? null,
        $data['status'] ?? 'pending',
        $data['progress'] ?? 0,
        $data['start_date'] ?? null,
        $data['end_date'] ?? null,
        $data['image'] ?? null,
        $data['updated_by'] ?? 'System',
        $data['id']
    ]);
    
    sendResponse(true, 'Project updated successfully');
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>