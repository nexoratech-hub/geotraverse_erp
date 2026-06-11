<?php
require_once 'config.php';
session_start();

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['email']) || empty($data['password'])) {
    sendResponse(false, 'Email and password required');
}

try {
    $email = $data['email'];
    $password = $data['password'];
    $departmentId = $data['department_id'] ?? null;
    
    // Find user by email
    $stmt = $pdo->prepare("SELECT e.*, d.name as department_name FROM employees e 
        LEFT JOIN departments d ON e.department_id = d.id 
        WHERE e.email = :email AND e.is_deleted = 0");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        sendResponse(false, 'Invalid email or password');
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        sendResponse(false, 'Invalid email or password');
    }
    
    // For department login, verify department matches
    if ($departmentId && $user['department_id'] != $departmentId && $user['role'] !== 'Super Administrator') {
        sendResponse(false, 'You are not authorized for this department');
    }
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['department_id'] = $user['department_id'];
    $_SESSION['role'] = $user['role'];
    
    // Determine redirect based on role/department
    $redirect = 'super_admin.html';
    if ($user['role'] !== 'Super Administrator') {
        $deptPages = [
            2 => 'finance.html',
            3 => 'sales_marketing.html',
            4 => 'manager.html',
            5 => 'secretary.html',
            6 => 'bricks_timber.html',
            7 => 'aluminium.html',
            8 => 'town_planning.html',
            9 => 'architectural.html',
            10 => 'survey.html',
            11 => 'construction.html',
            12 => 'hatimiliki.html'
        ];
        $redirect = $deptPages[$user['department_id']] ?? 'dashboard.html';
    }
    
    sendResponse(true, 'Login successful', [
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'department_id' => $user['department_id'],
            'department_name' => $user['department_name'],
            'role' => $user['role']
        ],
        'redirect' => $redirect
    ]);
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>