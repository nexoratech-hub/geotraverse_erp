<?php
require_once 'config.php';

$departmentId = $_GET['department_id'] ?? null;

$sql = "SELECT r.*, d.name as department_name FROM reports r 
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

sendResponse(true, 'Reports retrieved', $reports);
?>