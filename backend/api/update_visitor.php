<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['id'])) {
    sendResponse(false, 'Visitor ID required');
}

try {
    $stmt = $pdo->prepare("UPDATE visitors SET name = ?, phone = ?, department_to_visit = ?, visit_date = ?, visit_time = ?, purpose = ? WHERE id = ?");
    
    $stmt->execute([
        $data['name'],
        $data['phone'] ?? null,
        $data['department'] ?? null,
        $data['date'] ?? date('Y-m-d'),
        $data['time'] ?? date('H:i:s'),
        $data['description'] ?? null,
        $data['id']
    ]);
    
    sendResponse(true, 'Visitor updated successfully');
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>