<?php
require_once 'config.php';

$departmentId = $_GET['department_id'] ?? null;

$sql = "SELECT * FROM visitors";
$params = [];

if ($departmentId) {
    $sql .= " WHERE department_id = :dept_id";
    $params['dept_id'] = $departmentId;
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$visitors = $stmt->fetchAll();

sendResponse(true, 'Visitors retrieved', $visitors);
?>