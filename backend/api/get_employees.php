<?php
require_once 'config.php';

$sql = "SELECT e.*, d.name as department_name FROM employees e 
        LEFT JOIN departments d ON e.department_id = d.id 
        WHERE e.is_deleted = 0 
        ORDER BY e.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$employees = $stmt->fetchAll();

sendResponse(true, 'Employees retrieved', $employees);
?>