<?php
require_once 'config.php';

$departmentId = $_GET['department_id'] ?? null;
$status = $_GET['status'] ?? null;
$limit = $_GET['limit'] ?? 'all';

$sql = "SELECT br.*, d.name as department_name, 
        (SELECT COUNT(*) FROM budget_requests WHERE is_deleted = 0 AND is_viewed_by_finance = 0 AND status = 'pending') as unviewed_count
        FROM budget_requests br 
        LEFT JOIN departments d ON br.department_id = d.id 
        WHERE br.is_deleted = 0";

$params = [];

// Super Admin (department_id = 1) na Finance (department_id = 2) wanaona request zote
if ($departmentId == 1 || $departmentId == 2) {
    // Super Admin na Finance wanaona request zote kutoka departments zote
    // Hakuna filter additional
} else {
    // Departments nyingine zinaona request zao tu
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

// Ikiwa limit si 'all', ongeza LIMIT clause
if ($limit !== 'all' && $limit != 'all') {
    $limitInt = intval($limit);
    if ($limitInt > 0) {
        $sql .= " LIMIT " . $limitInt;
    }
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll();

// Pata idadi ya requests ambazo hazijaangaliwa (unviewed)
$unviewedCount = 0;
if ($departmentId == 1 || $departmentId == 2) {
    // Kwa Finance na Super Admin, hesabu requests zote pending ambazo hazijaangaliwa
    $stmt2 = $pdo->prepare("SELECT COUNT(*) as count FROM budget_requests WHERE is_deleted = 0 AND is_viewed_by_finance = 0 AND status = 'pending'");
    $stmt2->execute();
    $unviewedCount = $stmt2->fetch()['count'];
} else {
    // Kwa departments nyingine, hesabu requests zao tu
    $stmt2 = $pdo->prepare("SELECT COUNT(*) as count FROM budget_requests WHERE department_id = :dept_id AND is_deleted = 0 AND is_viewed_by_finance = 0 AND status = 'pending'");
    $stmt2->execute(['dept_id' => $departmentId]);
    $unviewedCount = $stmt2->fetch()['count'];
}

sendResponse(true, 'Budget requests retrieved', [
    'requests' => $requests,
    'unviewed_count' => $unviewedCount,
    'total_count' => count($requests)
]);
?>