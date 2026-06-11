<?php
require_once 'config.php';

$departmentId = $_GET['department_id'] ?? null;

$sql = "SELECT * FROM project_documents WHERE doc_type = 'campaign' AND is_deleted = 0";
$params = [];

if ($departmentId) {
    $sql .= " AND department_id = :dept_id";
    $params['dept_id'] = $departmentId;
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$documents = $stmt->fetchAll();

sendResponse(true, 'Campaign documents retrieved', $documents);
?>