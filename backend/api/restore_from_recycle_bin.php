<?php
/**
 * restore_from_recycle_bin.php - Restore item from recycle bin
 * 
 * POST Parameters:
 * - recycle_bin_id: ID of the record in recycle_bin table
 * - user_id: ID of user performing restore
 * - is_admin: 1 for super admin
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$recycleBinId = isset($input['recycle_bin_id']) ? intval($input['recycle_bin_id']) : 0;
$userId = isset($input['user_id']) ? intval($input['user_id']) : 0;
$isAdmin = isset($input['is_admin']) ? intval($input['is_admin']) : 0;

if ($recycleBinId === 0) {
    echo json_encode(['success' => false, 'message' => 'recycle_bin_id is required']);
    exit;
}

try {
    $db->beginTransaction();
    
    // Get recycle bin record
    $getQuery = "SELECT * FROM recycle_bin WHERE id = :id AND permanently_deleted = 0";
    $getStmt = $db->prepare($getQuery);
    $getStmt->bindParam(':id', $recycleBinId);
    $getStmt->execute();
    $recycleItem = $getStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$recycleItem) {
        echo json_encode(['success' => false, 'message' => 'Item not found in recycle bin']);
        exit;
    }
    
    $originalTable = $recycleItem['original_table'];
    $originalId = $recycleItem['original_id'];
    $deletedData = json_decode($recycleItem['deleted_data'], true);
    
    // Restore based on table type
    if ($originalTable === 'messages') {
        // Restore message - clear the deleted flags
        $restoreQuery = "UPDATE messages SET sender_deleted = 0, receiver_deleted = 0, deleted_at = NULL WHERE id = :id";
    } else if ($originalTable === 'conversations') {
        // Restore conversation
        $restoreQuery = "UPDATE conversations SET status = 'active', deleted_by_user_id = NULL, 
                         deleted_by_department = 0, deleted_by_admin = 0, deleted_at = NULL WHERE id = :id";
    } else if ($originalTable === 'reports') {
        $restoreQuery = "UPDATE reports SET deleted_by_admin = 0, deleted_by_department = 0, deleted_at = NULL WHERE id = :id";
    } else if ($originalTable === 'projects') {
        $restoreQuery = "UPDATE projects SET deleted_by_admin = 0, deleted_by_department = 0, deleted_at = NULL WHERE id = :id";
    } else {
        echo json_encode(['success' => false, 'message' => 'Cannot restore this table type']);
        exit;
    }
    
    $restoreStmt = $db->prepare($restoreQuery);
    $restoreStmt->bindParam(':id', $originalId);
    $restoreStmt->execute();
    
    // Mark as restored in recycle bin
    $updateRecycle = "UPDATE recycle_bin SET restored_at = NOW(), restored_by = :user_id WHERE id = :id";
    $updateStmt = $db->prepare($updateRecycle);
    $updateStmt->bindParam(':user_id', $userId);
    $updateStmt->bindParam(':id', $recycleBinId);
    $updateStmt->execute();
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Item restored successfully from ' . $originalTable
    ]);
    
} catch (PDOException $e) {
    $db->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>