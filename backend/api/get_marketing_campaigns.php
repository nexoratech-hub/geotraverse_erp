<?php
require_once 'config.php';

$departmentId = $_GET['department_id'] ?? null;
$limit = $_GET['limit'] ?? 100;
$page = $_GET['page'] ?? 1;
$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM marketing_campaigns WHERE is_deleted = 0";
$params = [];

if ($departmentId) {
    $sql .= " AND department_id = :dept_id";
    $params['dept_id'] = $departmentId;
}

$sql .= " ORDER BY id DESC LIMIT :limit OFFSET :offset";
$params['limit'] = intval($limit);
$params['offset'] = intval($offset);

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':limit', $params['limit'], PDO::PARAM_INT);
$stmt->bindParam(':offset', $params['offset'], PDO::PARAM_INT);
if (isset($params['dept_id'])) $stmt->bindParam(':dept_id', $params['dept_id']);
$stmt->execute();
$campaigns = $stmt->fetchAll();

sendResponse(true, 'Marketing campaigns retrieved', $campaigns);
?>