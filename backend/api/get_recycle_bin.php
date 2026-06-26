<?php
// backend/api/get_recycle_bin.php

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
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ============================================================
// INCLUDE CONFIG - HII NDIO MUHIMU
// ============================================================
require_once '../config/database.php';

// ============================================================
// GET PARAMETERS
// ============================================================
$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;

// ============================================================
// GET RECYCLE BIN ITEMS
// ============================================================
try {
    // ============================================================
    // TUMIA $db KUTOKA database.php (Config file)
    // ============================================================
    $query = "SELECT * FROM recycle_bin 
              WHERE restored = 0 
              ORDER BY created_at DESC";
    
    if ($department_id > 0) {
        $query .= " AND (deleted_by_department_id = ? OR deleted_by_admin = 1)";
    }
    
    if ($limit > 0) {
        $query .= " LIMIT " . intval($limit);
    }
    
    $stmt = $db->prepare($query);
    
    if ($department_id > 0) {
        $stmt->execute([$department_id]);
    } else {
        $stmt->execute();
    }
    
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $items,
        'count' => count($items),
        'department_id' => $department_id,
        'message' => 'Recycle bin items retrieved successfully'
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => []
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>