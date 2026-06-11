<?php
require_once 'config.php';
session_start();

if (isset($_SESSION['user_id'])) {
    sendResponse(true, 'Session active', [
        'logged_in' => true,
        'user_id' => $_SESSION['user_id'],
        'user_name' => $_SESSION['user_name'],
        'department_id' => $_SESSION['department_id'],
        'role' => $_SESSION['role'],
        'is_admin' => ($_SESSION['role'] === 'Super Administrator')
    ]);
} else {
    sendResponse(true, 'No active session', ['logged_in' => false]);
}
?>