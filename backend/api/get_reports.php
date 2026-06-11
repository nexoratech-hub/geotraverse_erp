<?php
require_once 'config.php';

$departmentId = $_GET['department_id'] ?? null;

// Fetch text reports from reports table
$sql = "SELECT r.*, d.name as department_name, NULL as file_name, NULL as file_path, NULL as file_size, NULL as file_type 
        FROM reports r 
        LEFT JOIN departments d ON r.department_id = d.id 
        WHERE r.is_deleted = 0";

$params = [];

if ($departmentId) {
    $sql .= " AND (r.department_id = :dept_id OR r.sent_to_department = :dept_id2)";
    $params['dept_id'] = $departmentId;
    $params['dept_id2'] = $departmentId;
}

$sql .= " ORDER BY r.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reports = $stmt->fetchAll();

// Fetch uploaded reports from uploaded_reports table
$uploadedSql = "SELECT ur.*, d.name as department_name, ur.title, ur.period, ur.file_name, ur.file_path, ur.file_size, ur.file_type, ur.department_id, ur.uploaded_by as created_by, ur.created_at 
                FROM uploaded_reports ur 
                LEFT JOIN departments d ON ur.department_id = d.id 
                WHERE ur.is_deleted = 0";

$uploadedParams = [];

if ($departmentId) {
    $uploadedSql .= " AND ur.department_id = :uploaded_dept_id";
    $uploadedParams['uploaded_dept_id'] = $departmentId;
}

$uploadedSql .= " ORDER BY ur.id DESC";

$uploadedStmt = $pdo->prepare($uploadedSql);
$uploadedStmt->execute($uploadedParams);
$uploadedReports = $uploadedStmt->fetchAll();

// Merge both arrays
$allReports = array_merge($reports, $uploadedReports);

// Sort by created_at desc
usort($allReports, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

sendResponse(true, 'Reports retrieved', $allReports);
?>