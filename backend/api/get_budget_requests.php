<?php
require_once 'config.php';

$departmentId = $_GET['department_id'] ?? null;

$sql = "SELECT br.*, d.name as department_name FROM budget_requests br 
        LEFT JOIN departments d ON br.department_id = d.id 
        WHERE br.is_deleted = 0";

$params = [];

if ($departmentId) {
    $sql .= " AND (br.department_id = :dept_id OR br.is_viewed_by_admin = 1)";
    $params['dept_id'] = $departmentId;
}

$sql .= " ORDER BY br.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll();

sendResponse(true, 'Budget requests retrieved', $requests);
?>