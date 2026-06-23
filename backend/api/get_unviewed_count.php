<?php
// ============================================================
// get_unviewed_count.php - Kupata idadi ya notifications zisizoonekana
// ============================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;

if (!$department_id) {
    echo json_encode(['success' => false, 'message' => 'Department ID required']);
    exit;
}

try {
    $db = getDB();
    
    // Get counts by item_type
    $stmt = $db->prepare("
        SELECT 
            item_type,
            COUNT(*) as count
        FROM notifications
        WHERE department_id = ? AND is_viewed = 0
        GROUP BY item_type
    ");
    $stmt->execute([$department_id]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Default counts
    $counts = [
        'projects' => 0,
        'reports' => 0,
        'uploaded_reports' => 0,
        'documents' => 0,
        'fund_requests' => 0,
        'dailywork' => 0,
        'total' => 0
    ];
    
    // Map item_type to display keys
    $typeMap = [
        'project' => 'projects',
        'report' => 'reports',
        'uploaded_report' => 'uploaded_reports',
        'document' => 'documents',
        'fund_request' => 'fund_requests',
        'dailywork' => 'dailywork'
    ];
    
    foreach ($results as $row) {
        $key = isset($typeMap[$row['item_type']]) ? $typeMap[$row['item_type']] : $row['item_type'];
        $counts[$key] = intval($row['count']);
        $counts['total'] += intval($row['count']);
    }
    
    echo json_encode([
        'success' => true,
        'data' => $counts,
        'department_id' => $department_id
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>