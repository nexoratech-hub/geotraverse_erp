<?php
require_once 'config.php';

$departmentId = $_GET['department_id'] ?? null;

$sql = "SELECT * FROM uploaded_reports WHERE is_deleted = 0";
$params = [];

if ($departmentId) {
    $sql .= " AND department_id = :dept_id";
    $params['dept_id'] = $departmentId;
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reports = $stmt->fetchAll();

sendResponse(true, 'Uploaded reports retrieved', $reports);
?>