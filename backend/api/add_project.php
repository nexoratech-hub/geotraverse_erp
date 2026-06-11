<?php
require_once 'config.php';
session_start();

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['name'])) {
    sendResponse(false, 'Project name required');
}

try {
    $stmt = $pdo->prepare("INSERT INTO projects (name, client_name, amount, location, description, status, progress, start_date, end_date, image, project_type, department_id, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $data['name'],
        $data['client_name'] ?? null,
        $data['amount'] ?? 0,
        $data['location'] ?? null,
        $data['description'] ?? null,
        $data['status'] ?? 'pending',
        $data['progress'] ?? 0,
        $data['start_date'] ?? null,
        $data['end_date'] ?? null,
        $data['image'] ?? null,
        $data['project_type'] ?? 'general',
        $data['department_id'] ?? 1,
        $data['created_by'] ?? 'System'
    ]);
    
    sendResponse(true, 'Project added successfully', ['id' => $pdo->lastInsertId()]);
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>