<?php
// ============================================================
// mark_all_notifications_viewed.php - Kuweka zote kama zimeonekana
// ============================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$department_id = isset($data['department_id']) ? intval($data['department_id']) : 0;

if (!$department_id) {
    echo json_encode(['success' => false, 'message' => 'Department ID required']);
    exit;
}

try {
    $db = getDB();
    
    $stmt = $db->prepare("
        UPDATE notifications 
        SET is_viewed = 1, updated_at = NOW() 
        WHERE department_id = ? AND is_viewed = 0
    ");
    $stmt->execute([$department_id]);
    
    $affected = $stmt->rowCount();
    
    echo json_encode([
        'success' => true,
        'message' => 'All notifications marked as viewed',
        'affected' => $affected
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>