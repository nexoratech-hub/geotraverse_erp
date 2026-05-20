<?php
/**
 * empty_recycle_bin.php - Permanently delete items from recycle bin
 * 
 * POST Parameters:
 * - user_id: ID of user performing action
 * - is_admin: 1 for super admin (required)
 * - item_ids: (optional) Array of specific IDs to delete, empty for all
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

$userId = isset($input['user_id']) ? intval($input['user_id']) : 0;
$isAdmin = isset($input['is_admin']) ? intval($input['is_admin']) : 0;
$itemIds = isset($input['item_ids']) && is_array($input['item_ids']) ? $input['item_ids'] : [];

if ($isAdmin !== 1) {
    echo json_encode(['success' => false, 'message' => 'Only Super Admin can empty recycle bin']);
    exit;
}

try {
    $db->beginTransaction();
    
    if (empty($itemIds)) {
        // Delete all items
        $deleteQuery = "DELETE FROM recycle_bin WHERE permanently_deleted = 0";
        $stmt = $db->prepare($deleteQuery);
        $stmt->execute();
        $affectedCount = $stmt->rowCount();
    } else {
        // Delete specific items
        $placeholders = implode(',', array_fill(0, count($itemIds), '?'));
        $deleteQuery = "DELETE FROM recycle_bin WHERE id IN ($placeholders) AND permanently_deleted = 0";
        $stmt = $db->prepare($deleteQuery);
        $stmt->execute($itemIds);
        $affectedCount = $stmt->rowCount();
    }
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "$affectedCount items permanently deleted from recycle bin",
        'deleted_count' => $affectedCount
    ]);
    
} catch (PDOException $e) {
    $db->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>