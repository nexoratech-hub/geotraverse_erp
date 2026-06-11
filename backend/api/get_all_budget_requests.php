<?php
require_once 'config.php';

$departmentId = $_GET['department_id'] ?? null;
$status = $_GET['status'] ?? null;
$limit = $_GET['limit'] ?? 100;

$sql = "SELECT br.*, d.name as department_name, 
        (SELECT COUNT(*) FROM budget_requests WHERE is_deleted = 0 AND is_viewed_by_finance = 0 AND status = 'pending') as unviewed_count
        FROM budget_requests br 
        LEFT JOIN departments d ON br.department_id = d.id 
        WHERE br.is_deleted = 0";

$params = [];

if ($departmentId == 2) {
    // Finance department - can see all pending and approved requests
    $sql .= " AND (br.status IN ('pending', 'approved', 'cancelled'))";
} elseif ($departmentId == 1) {
    // Super Admin - can see all
    // No additional filter
} else {
    // Other departments - only see their own
    $sql .= " AND br.department_id = :dept_id";
    $params['dept_id'] = $departmentId;
}

if ($status) {
    $sql .= " AND br.status = :status";
    $params['status'] = $status;
}

$sql .= " ORDER BY 
    CASE WHEN br.status = 'pending' THEN 1 ELSE 2 END,
    br.id DESC";

if ($limit != 'all') {
    $sql .= " LIMIT " . intval($limit);
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll();

// Get unviewed count for badge
$unviewedCount = 0;
if ($departmentId == 2 || $departmentId == 1) {
    $stmt2 = $pdo->prepare("SELECT COUNT(*) as count FROM budget_requests WHERE is_deleted = 0 AND is_viewed_by_finance = 0 AND status = 'pending'");
    $stmt2->execute();
    $unviewedCount = $stmt2->fetch()['count'];
}

sendResponse(true, 'Budget requests retrieved', [
    'requests' => $requests,
    'unviewed_count' => $unviewedCount
]);
?>