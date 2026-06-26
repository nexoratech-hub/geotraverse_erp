<?php
// backend/api/empty_recycle_bin.php

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
$department_id = isset($input['department_id']) ? intval($input['department_id']) : 0;

// ============================================================
// EMPTY RECYCLE BIN
// ============================================================
try {
    $query = "DELETE FROM recycle_bin WHERE restored = 0";
    $params = [];
    
    if ($department_id > 0) {
        $query .= " AND (deleted_by_department_id = ? OR deleted_by_admin = 1)";
        $params = [$department_id];
    }
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $deleted = $stmt->rowCount();
    
    echo json_encode([
        'success' => true,
        'message' => 'Recycle bin emptied successfully',
        'deleted' => $deleted,
        'department_id' => $department_id
    ]);

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