<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['current_password']) || empty($data['new_password'])) {
    sendResponse(false, 'Current and new password required');
}

try {
    $departmentId = $data['department_id'] ?? null;
    
    if ($departmentId) {
        // Department user - find by department_id
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE department_id = :dept_id LIMIT 1");
        $stmt->execute(['dept_id' => $departmentId]);
    } else {
        // Admin user
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE role = 'Super Administrator' LIMIT 1");
        $stmt->execute();
    }
    
    $user = $stmt->fetch();
    
    if (!$user) {
        sendResponse(false, 'User not found');
    }
    
    if (!password_verify($data['current_password'], $user['password'])) {
        sendResponse(false, 'Current password is incorrect');
    }
    
    $newHashedPassword = password_hash($data['new_password'], PASSWORD_BCRYPT);
    
    $stmt2 = $pdo->prepare("UPDATE employees SET password = :password WHERE id = :id");
    $stmt2->execute([
        'password' => $newHashedPassword,
        'id' => $user['id']
    ]);
    
    sendResponse(true, 'Password changed successfully');
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>