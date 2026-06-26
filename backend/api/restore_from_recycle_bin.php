<?php
// backend/api/restore_from_recycle_bin.php

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
// RESTORE FROM RECYCLE BIN
// ============================================================
try {
    // Get the item first
    $stmt = $db->prepare("SELECT * FROM recycle_bin WHERE id = ? AND restored = 0");
    $stmt->execute([$recycleId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$item) {
        echo json_encode([
            'success' => false,
            'message' => 'Item not found or already restored'
        ]);
        exit;
    }
    
    // ============================================================
    // RESTORE BASED ON ITEM TYPE
    // ============================================================
    $restored = false;
    $table = '';
    $idColumn = 'id';
    
    switch ($item['item_type']) {
        case 'project':
            $table = 'projects';
            break;
        case 'project_document':
            $table = 'project_documents';
            break;
        case 'budget_request':
            $table = 'fund_requests';
            break;
        case 'report':
            $table = 'reports';
            break;
        case 'uploaded_report':
            $table = 'uploaded_reports';
            break;
        case 'daily_work':
            $table = 'dailywork';
            break;
        case 'employee':
            $table = 'employees';
            break;
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Unknown item type: ' . $item['item_type']
            ]);
            exit;
    }
    
    // Restore the item
    $restoreStmt = $db->prepare("UPDATE $table SET is_deleted = 0, deleted_at = NULL WHERE id = ?");
    $restored = $restoreStmt->execute([$item['item_id']]);
    
    if ($restored) {
        // Mark as restored in recycle bin
        $updateStmt = $db->prepare("UPDATE recycle_bin SET restored = 1, restored_at = NOW() WHERE id = ?");
        $updateStmt->execute([$recycleId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Item restored successfully',
            'item_id' => $item['item_id'],
            'item_type' => $item['item_type'],
            'item_name' => $item['item_name']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to restore item'
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