<?php
require_once 'config.php';

$stmt = $pdo->prepare("SELECT * FROM departments ORDER BY id");
$stmt->execute();
$departments = $stmt->fetchAll();

sendResponse(true, 'Departments retrieved', $departments);
?>