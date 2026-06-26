<?php
// backend/api/permanently_delete_item.php

// ============================================================
// ERROR REPORTING
// ============================================================
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// ============================================================
// HEADERS
// ============================================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ============================================================
// INCLUDE CONFIG
// ============================================================
require_once '../config/database.php';

// ============================================================
// GET INPUT
// ============================================================
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid input'
    ]);
    exit;
}

// ============================================================
// VALIDATE REQUIRED FIELDS
// ============================================================
if (!isset($input['recycle_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Recycle ID required'
    ]);
    exit;
}

$recycleId = intval($input['recycle_id']);

// ============================================================
// PERMANENTLY DELETE FROM RECYCLE BIN
// ============================================================
try {
    // Delete from recycle bin
    $stmt = $db->prepare("DELETE FROM recycle_bin WHERE id = ?");
    $stmt->execute([$recycleId]);
    
    $deleted = $stmt->rowCount();
    
    if ($deleted > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Item permanently deleted from recycle bin',
            'deleted' => $deleted
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Item not found or already deleted'
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>