<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once '../config/database.php';

$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 2;

$conn = getConnection();

// Get unviewed fund requests count
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM fund_requests WHERE department_id = ? AND is_deleted = 0 AND is_viewed_by_finance = 0");
$stmt->bind_param("i", $department_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$fund_requests_count = $row['count'];

// Get unviewed notifications count
$stmt2 = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE department_id = ? AND is_viewed = 0");
$stmt2->bind_param("i", $department_id);
$stmt2->execute();
$result2 = $stmt2->get_result();
$row2 = $result2->fetch_assoc();
$notifications_count = $row2['count'];

echo json_encode([
    'success' => true,
    'data' => [
        'fund_requests' => $fund_requests_count,
        'notifications' => $notifications_count,
        'total' => $fund_requests_count + $notifications_count
    ]
]);

$stmt->close();
$stmt2->close();
$conn->close();
?>