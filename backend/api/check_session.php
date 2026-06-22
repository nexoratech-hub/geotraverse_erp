<?php
require_once 'config.php';
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ========== CHECK SESSION ==========
if (isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $response = [
        'success' => true,
        'logged_in' => true,
        'user_id' => $_SESSION['user_id'],
        'user_name' => $_SESSION['user_name'],
        'user_email' => $_SESSION['user_email'],
        'department_id' => $_SESSION['department_id'],
        'role' => $_SESSION['role'],
        'is_admin' => isset($_SESSION['is_admin']) ? $_SESSION['is_admin'] : 0
    ];
    
    // Determine redirect if needed
    if ($_SESSION['role'] === 'Super Administrator') {
        $response['redirect'] = 'super_admin.html';
    } else {
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
        $response['redirect'] = $deptPages[$_SESSION['department_id']] ?? 'dashboard.html';
    }
    
    echo json_encode($response);
} else {
    echo json_encode([
        'success' => true,
        'logged_in' => false,
        'message' => 'No active session'
    ]);
}
?>