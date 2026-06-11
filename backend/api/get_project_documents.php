<?php
require_once 'config.php';

$departmentId = $_GET['department_id'] ?? null;
$docType = $_GET['doc_type'] ?? null;

$sql = "SELECT * FROM project_documents WHERE is_deleted = 0";
$params = [];

if ($departmentId) {
    $sql .= " AND department_id = :dept_id";
    $params['dept_id'] = $departmentId;
}

if ($docType) {
    $sql .= " AND doc_type = :doc_type";
    $params['doc_type'] = $docType;
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$documents = $stmt->fetchAll();

sendResponse(true, 'Documents retrieved', $documents);
?>