<?php
// backend/api/get_reports.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$database = new Database();
$conn = $database->getConnection();

$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$is_admin = isset($_GET['is_admin']) ? intval($_GET['is_admin']) : 0;

$query = "SELECT r.*, d.name as department_name 
          FROM reports r 
          LEFT JOIN departments d ON r.department_id = d.id 
          WHERE 1=1";

$params = [];

// Admin can see all non-deleted reports
if ($is_admin == 1) {
    $query .= " AND r.deleted_by_admin = 0";
} 
// Department user - only see reports not deleted by their department
else if ($department_id > 0) {
    $query .= " AND r.deleted_by_department = 0";
    $query .= " AND (r.department_id = ? OR r.department_id IS NULL)";
    $params[] = $department_id;
}
// Fallback
else {
    $query .= " AND r.deleted_by_department = 0 AND r.deleted_by_admin = 0";
}

$query .= " ORDER BY r.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count unviewed reports for this user/department
$unviewed_query = "SELECT COUNT(*) as unviewed_count FROM reports r 
                   WHERE (r.is_viewed_by_department = 0 OR r.is_viewed_by_department IS NULL)";
$unviewed_params = [];

if ($is_admin == 1) {
    $unviewed_query .= " AND r.deleted_by_admin = 0 AND r.department_id != 1";
} else if ($department_id > 0) {
    $unviewed_query .= " AND r.department_id = ? AND r.deleted_by_department = 0 AND r.deleted_by_admin = 0";
    $unviewed_params[] = $department_id;
} else {
    $unviewed_query .= " AND r.deleted_by_department = 0 AND r.deleted_by_admin = 0";
}

$unviewed_stmt = $conn->prepare($unviewed_query);
$unviewed_stmt->execute($unviewed_params);
$unviewed_count = $unviewed_stmt->fetchColumn();

echo json_encode([
    'success' => true,
    'data' => $reports,
    'unviewed_count' => intval($unviewed_count),
    'total' => count($reports)
]);
?>