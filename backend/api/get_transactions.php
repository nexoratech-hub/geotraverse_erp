<?php
require_once 'config.php';

$departmentId = $_GET['department_id'] ?? null;

$sql = "SELECT * FROM transactions WHERE is_deleted = 0";
$params = [];

if ($departmentId) {
    $sql .= " AND department_id = :dept_id";
    $params['dept_id'] = $departmentId;
}

$sql .= " ORDER BY transaction_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

sendResponse(true, 'Transactions retrieved', $transactions);
?>