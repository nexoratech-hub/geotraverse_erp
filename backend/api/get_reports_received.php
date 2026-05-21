<?php
/**
 * get_reports_received.php - Get reports received by a department
 * 
 * GET Parameters:
 * - department_id: ID of the receiving department (required)
 * - limit: number of records per page
 * - offset: pagination offset
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$departmentId = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

if ($departmentId === 0) {
    echo json_encode(['success' => false, 'message' => 'department_id is required', 'data' => []]);
    exit;
}

try {
    $query = "SELECT 
                r.id,
                r.title,
                r.period,
                r.content,
                r.status,
                r.sent_from_dept,
                r.sent_to_department,
                r.is_viewed_by_department,
                r.created_at,
                d_sender.name as sender_department
            FROM reports r
            LEFT JOIN departments d_sender ON r.sent_from_dept = d_sender.id
            WHERE r.sent_to_department = :dept_id
            AND r.deleted_by_department = 0 
            AND r.deleted_by_admin = 0
            ORDER BY r.created_at DESC
            LIMIT :limit OFFSET :offset";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':dept_id', $departmentId);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM reports 
                   WHERE sent_to_department = :dept_id
                   AND deleted_by_department = 0 AND deleted_by_admin = 0";
    $countStmt = $db->prepare($countQuery);
    $countStmt->bindParam(':dept_id', $departmentId);
    $countStmt->execute();
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get unviewed count
    $unviewedQuery = "SELECT COUNT(*) as unviewed FROM reports 
                      WHERE sent_to_department = :dept_id 
                      AND is_viewed_by_department = 0 
                      AND deleted_by_department = 0 
                      AND deleted_by_admin = 0";
    $unviewedStmt = $db->prepare($unviewedQuery);
    $unviewedStmt->bindParam(':dept_id', $departmentId);
    $unviewedStmt->execute();
    $unviewed = $unviewedStmt->fetch(PDO::FETCH_ASSOC)['unviewed'];
    
    echo json_encode([
        'success' => true,
        'data' => $reports,
        'total' => $total,
        'unviewed_count' => intval($unviewed)
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>