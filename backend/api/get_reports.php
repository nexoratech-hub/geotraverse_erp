<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : null;
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
$is_admin = isset($_GET['is_admin']) ? intval($_GET['is_admin']) : 0;

try {
    if ($department_id == 1 || $is_admin == 1) {
        // Admin view - show all reports sent to admin OR created by admin
        $query = "SELECT r.*, 
                  d.name as department_name,
                  CASE 
                      WHEN r.sent_from_department IS NOT NULL AND r.sent_from_department != 1 THEN 1
                      ELSE 0
                  END as is_from_department
                  FROM reports r
                  LEFT JOIN departments d ON r.department_id = d.id
                  WHERE (r.sent_to_department = 1 OR r.department_id = 1)
                    AND r.deleted_by_admin = 0
                  ORDER BY r.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Count unviewed reports for admin
        $unviewedCount = 0;
        foreach ($reports as $r) {
            if ($r['is_viewed_by_admin'] == 0 && $r['department_id'] != 1) {
                $unviewedCount++;
            }
        }
        
        echo json_encode([
            'success' => true, 
            'data' => $reports, 
            'unviewed_count' => $unviewedCount,
            'is_admin' => true
        ]);
        
    } else if ($department_id) {
        // Department view - show reports for this department
        $query = "SELECT r.*, 
                  d.name as department_name
                  FROM reports r
                  LEFT JOIN departments d ON r.department_id = d.id
                  WHERE (r.department_id = ? OR r.sent_to_department = ?)
                    AND r.deleted_by_department = 0
                  ORDER BY r.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute([$department_id, $department_id]);
        $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Count unviewed reports for this department
        $unviewedCount = 0;
        foreach ($reports as $r) {
            if ($r['is_viewed_by_department'] == 0 && $r['sent_from_department'] != $department_id && $r['department_id'] != $department_id) {
                $unviewedCount++;
            }
        }
        
        echo json_encode([
            'success' => true, 
            'data' => $reports, 
            'unviewed_count' => $unviewedCount,
            'department_id' => $department_id
        ]);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Department ID required', 'data' => []]);
    }
    
} catch (PDOException $e) {
    error_log("Get reports error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'data' => []]);
}
?>