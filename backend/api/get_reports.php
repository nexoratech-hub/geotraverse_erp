<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : null;
$is_admin = isset($_GET['is_admin']) ? intval($_GET['is_admin']) : 0;

$departmentNames = [
    1 => 'Super Admin', 2 => 'Finance', 3 => 'Sales & Marketing', 4 => 'Manager',
    5 => 'Secretary', 6 => 'Bricks & Timber', 7 => 'Aluminium', 8 => 'Town Planning',
    9 => 'Architectural', 10 => 'Survey', 11 => 'Construction', 12 => 'Hatimiliki'
];

try {
    if ($department_id == 1 || $is_admin == 1) {
        // Admin view - show reports for admin
        $query = "SELECT r.*, 
                  d.name as department_name,
                  sfd.name as sent_from_department_name,
                  std.name as sent_to_department_name
                  FROM reports r
                  LEFT JOIN departments d ON r.department_id = d.id
                  LEFT JOIN departments sfd ON r.sent_from_department = sfd.id
                  LEFT JOIN departments std ON r.sent_to_department = std.id
                  WHERE (r.sent_to_department = 1 OR r.department_id = 1 OR r.sent_from_department = 1)
                    AND (r.deleted_by_admin = 0 OR r.deleted_by_admin IS NULL)
                  ORDER BY r.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Count unviewed reports
        $unviewedCount = 0;
        foreach ($reports as $r) {
            if (($r['is_viewed_by_admin'] == 0 || $r['is_viewed_by_admin'] === null) && 
                $r['department_id'] != 1 && $r['sent_from_department'] != 1) {
                $unviewedCount++;
            }
        }
        
        echo json_encode([
            'success' => true, 
            'data' => $reports, 
            'unviewed_count' => $unviewedCount
        ]);
        
    } else if ($department_id) {
        // Department view - show ONLY reports (not messages)
        $query = "SELECT r.*, 
                  d.name as department_name,
                  sfd.name as sent_from_department_name,
                  std.name as sent_to_department_name
                  FROM reports r
                  LEFT JOIN departments d ON r.department_id = d.id
                  LEFT JOIN departments sfd ON r.sent_from_department = sfd.id
                  LEFT JOIN departments std ON r.sent_to_department = std.id
                  WHERE (r.department_id = ? OR r.sent_to_department = ? OR r.sent_from_department = ?)
                    AND (r.deleted_by_department = 0 OR r.deleted_by_department IS NULL)
                    AND r.title NOT LIKE '%REPORT%' 
                    AND r.title NOT LIKE '%Message%'
                  ORDER BY r.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute([$department_id, $department_id, $department_id]);
        $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Count unviewed reports
        $unviewedCount = 0;
        foreach ($reports as $r) {
            if (($r['is_viewed_by_department'] == 0 || $r['is_viewed_by_department'] === null) && 
                $r['sent_from_department'] != $department_id && 
                $r['department_id'] != $department_id) {
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