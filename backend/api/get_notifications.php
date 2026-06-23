<?php
// ============================================================
// get_notifications.php - Kupata notifications za department
// ============================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

if (!$department_id) {
    echo json_encode(['success' => false, 'message' => 'Department ID required']);
    exit;
}

try {
    $db = getDB();
    
    // Get notifications
    $stmt = $db->prepare("
        SELECT 
            n.id,
            n.department_id,
            n.from_department_id,
            d.name as from_department_name,
            n.item_type,
            n.item_id,
            n.item_title,
            n.message,
            n.is_viewed,
            n.created_at,
            n.updated_at
        FROM notifications n
        LEFT JOIN departments d ON n.from_department_id = d.id
        WHERE n.department_id = ?
        ORDER BY n.created_at DESC
        LIMIT ? OFFSET ?
    ");
    
    $stmt->execute([$department_id, $limit, $offset]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count
    $countStmt = $db->prepare("SELECT COUNT(*) as total FROM notifications WHERE department_id = ?");
    $countStmt->execute([$department_id]);
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo json_encode([
        'success' => true,
        'data' => $notifications,
        'total' => intval($total),
        'limit' => $limit,
        'offset' => $offset
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>