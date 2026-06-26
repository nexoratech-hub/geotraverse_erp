<?php
// backend/api/add_to_recycle_bin.php

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
if (!isset($input['item_id']) || !isset($input['item_type']) || !isset($input['item_name'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields: item_id, item_type, item_name'
    ]);
    exit;
}

$itemId = intval($input['item_id']);
$itemType = $input['item_type'];
$itemName = $input['item_name'];
$deletedByDepartmentId = isset($input['deleted_by_department_id']) ? intval($input['deleted_by_department_id']) : null;
$deletedByAdmin = isset($input['deleted_by_admin']) ? intval($input['deleted_by_admin']) : 0;
$deletedByName = isset($input['deleted_by_name']) ? $input['deleted_by_name'] : 'System';

// ============================================================
// ADD TO RECYCLE BIN
// ============================================================
try {
    // Check if already exists
    $checkStmt = $db->prepare("SELECT id FROM recycle_bin WHERE item_id = ? AND item_type = ? AND restored = 0");
    $checkStmt->execute([$itemId, $itemType]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        echo json_encode([
            'success' => true,
            'message' => 'Item already in recycle bin',
            'id' => $existing['id']
        ]);
        exit;
    }
    
    // Insert into recycle bin
    $stmt = $db->prepare("INSERT INTO recycle_bin (
        item_id, 
        item_type, 
        item_name, 
        deleted_by_department_id, 
        deleted_by_admin, 
        deleted_by_name, 
        restored,
        created_at
    ) VALUES (?, ?, ?, ?, ?, ?, 0, NOW())");
    
    $stmt->execute([
        $itemId,
        $itemType,
        $itemName,
        $deletedByDepartmentId,
        $deletedByAdmin,
        $deletedByName
    ]);
    
    $id = $db->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Item added to recycle bin',
        'id' => $id,
        'item_id' => $itemId,
        'item_type' => $itemType
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