<?php
require_once 'config.php';
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

function sendResponse($success, $message, $data = null) {
    $response = ['success' => $success, 'message' => $message];
    if ($data !== null) {
        $response = array_merge($response, $data);
    }
    echo json_encode($response);
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Method not allowed');
}

// Get input data
$inputData = file_get_contents('php://input');
$data = json_decode($inputData, true);

// Validate input
if (!$data || empty($data['email']) || empty($data['password'])) {
    sendResponse(false, 'Email and password required');
}

try {
    $email = trim($data['email']);
    $password = $data['password'];
    $departmentId = isset($data['department_id']) ? intval($data['department_id']) : null;
    
    // ========== FIND USER BY EMAIL ==========
    $stmt = $pdo->prepare("SELECT e.*, d.name as department_name FROM employees e 
        LEFT JOIN departments d ON e.department_id = d.id 
        WHERE e.email = :email AND e.is_deleted = 0 AND e.is_active = 1");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // ========== DEBUG LOGGING ==========
    error_log("Login attempt for email: " . $email);
    error_log("User found: " . ($user ? 'Yes' : 'No'));
    if ($user) {
        error_log("User ID: " . $user['id']);
        error_log("User Role: " . $user['role']);
        error_log("User Department: " . $user['department_id']);
        error_log("Stored Password Hash: " . $user['password']);
    }
    
    // ========== CHECK IF USER EXISTS ==========
    if (!$user) {
        error_log("User not found: " . $email);
        sendResponse(false, 'Invalid email or password');
    }
    
    // ========== VERIFY PASSWORD ==========
    $passwordVerified = password_verify($password, $user['password']);
    error_log("Password verified: " . ($passwordVerified ? 'Yes' : 'No'));
    
    if (!$passwordVerified) {
        error_log("Password verification failed for: " . $email);
        sendResponse(false, 'Invalid email or password');
    }
    
    // ========== DEPARTMENT VERIFICATION ==========
    if ($departmentId && $user['role'] !== 'Super Administrator') {
        if ($user['department_id'] != $departmentId) {
            error_log("Department mismatch: User Dept={$user['department_id']}, Requested Dept={$departmentId}");
            sendResponse(false, 'You are not authorized for this department');
        }
    }
    
    // ========== SET SESSION ==========
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['department_id'] = $user['department_id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['is_admin'] = ($user['role'] === 'Super Administrator') ? 1 : 0;
    $_SESSION['logged_in'] = true;
    
    error_log("Session created for user: " . $user['name'] . " (ID: " . $user['id'] . ")");
    
    // ========== DETERMINE REDIRECT ==========
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
    
    // ========== RETURN SUCCESS ==========
    sendResponse(true, 'Login successful', [
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'department_id' => $user['department_id'],
            'department_name' => $user['department_name'],
            'role' => $user['role'],
            'is_admin' => ($user['role'] === 'Super Administrator') ? 1 : 0
        ],
        'redirect' => $redirect
    ]);
    
} catch(PDOException $e) {
    error_log("Database error in login.php: " . $e->getMessage());
    sendResponse(false, 'Database error occurred');
} catch(Exception $e) {
    error_log("General error in login.php: " . $e->getMessage());
    sendResponse(false, 'An error occurred');
}
?>