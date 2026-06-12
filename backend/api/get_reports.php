<?php
require_once 'config.php';

$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;
$include_deleted = isset($_GET['include_deleted']) ? intval($_GET['include_deleted']) : 0;

try {
    if ($include_deleted) {
        $stmt = $pdo->prepare("SELECT * FROM reports WHERE department_id = ? OR sent_to_department = ? ORDER BY id DESC");
        $stmt->execute([$department_id, $department_id]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM reports WHERE (department_id = ? OR sent_to_department = ?) AND (is_deleted != 1 OR is_deleted IS NULL) ORDER BY id DESC");
        $stmt->execute([$department_id, $department_id]);
    }
    
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
    sendResponse(true, 'Reports fetched successfully', $reports);
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>