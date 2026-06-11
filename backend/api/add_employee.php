<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['name']) || empty($data['email'])) {
    sendResponse(false, 'Name and email required');
}

// Hash password
$hashedPassword = password_hash($data['password'] ?? '1234', PASSWORD_BCRYPT);

try {
    $stmt = $pdo->prepare("INSERT INTO employees (name, email, phone, department_id, role, salary, password, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $data['name'],
        $data['email'],
        $data['phone'] ?? null,
        $data['department_id'] ?? null,
        $data['role'] ?? 'Staff',
        $data['salary'] ?? 0,
        $hashedPassword,
        $data['created_by'] ?? 'System'
    ]);
    
    sendResponse(true, 'Employee added successfully', ['id' => $pdo->lastInsertId()]);
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>