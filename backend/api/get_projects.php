<?php
require_once 'config.php';

$departmentId = $_GET['department_id'] ?? null;
$projectType = $_GET['project_type'] ?? null;

$sql = "SELECT p.*, d.name as department_name FROM projects p 
        LEFT JOIN departments d ON p.department_id = d.id 
        WHERE p.is_deleted = 0";

$params = [];

if ($departmentId) {
    $sql .= " AND (p.department_id = :dept_id OR p.sent_to_dept = :dept_id2)";
    $params['dept_id'] = $departmentId;
    $params['dept_id2'] = $departmentId;
}

if ($projectType) {
    $sql .= " AND p.project_type = :project_type";
    $params['project_type'] = $projectType;
}

$sql .= " ORDER BY p.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll();

sendResponse(true, 'Projects retrieved', $projects);
?>