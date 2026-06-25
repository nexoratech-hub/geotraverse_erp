<?php
// ============================================================
// test_simple.php - ULTRA SIMPLE TEST
// ============================================================

// Enable all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Clear output
if (ob_get_length()) ob_clean();

// Set header
header('Content-Type: application/json');

// Return simple response
echo json_encode([
    'success' => true,
    'message' => 'API is working',
    'time' => date('Y-m-d H:i:s')
]);
exit;
?>