<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['name'])) {
    sendResponse(false, 'Visitor name required');
}

try {
    $stmt = $pdo->prepare("INSERT INTO visitors (name, phone, department_to_visit, visit_date, visit_time, purpose, department_id, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $data['name'],
        $data['phone'] ?? null,
        $data['department'] ?? null,
        $data['date'] ?? date('Y-m-d'),
        $data['time'] ?? date('H:i:s'),
        $data['description'] ?? null,
        $data['department_id'] ?? 5,
        $data['created_by'] ?? 'System'
    ]);
    
    sendResponse(true, 'Visitor registered successfully', ['id' => $pdo->lastInsertId()]);
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>