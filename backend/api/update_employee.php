<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['id'])) {
    sendResponse(false, 'Employee ID required');
}

try {
    $sql = "UPDATE employees SET name = ?, email = ?, phone = ?, department_id = ?, role = ?, salary = ?, updated_by = ?";
    $params = [
        $data['name'],
        $data['email'],
        $data['phone'] ?? null,
        $data['department_id'] ?? null,
        $data['role'] ?? 'Staff',
        $data['salary'] ?? 0,
        $data['updated_by'] ?? 'System'
    ];
    
    if (!empty($data['password'])) {
        $sql .= ", password = ?";
        $params[] = password_hash($data['password'], PASSWORD_BCRYPT);
    }
    
    $sql .= " WHERE id = ?";
    $params[] = $data['id'];
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    sendResponse(true, 'Employee updated successfully');
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>