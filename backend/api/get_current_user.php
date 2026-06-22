<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    echo json_encode([
        'success' => true,
        'data' => [
            'id' => $_SESSION['user_id'] ?? null,
            'name' => $_SESSION['user_name'] ?? 'User',
            'email' => $_SESSION['user_email'] ?? null,
            'department_id' => $_SESSION['department_id'] ?? null,
            'role' => $_SESSION['role'] ?? 'User',
            'is_admin' => $_SESSION['is_admin'] ?? false
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Not logged in',
        'data' => null
    ]);
}
?>