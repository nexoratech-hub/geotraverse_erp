<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;

try {
    $query = "SELECT fr.*, d.name as department_name 
              FROM fund_requests fr
              LEFT JOIN departments d ON fr.department_id = d.id
              WHERE fr.is_deleted = 0 OR fr.is_deleted IS NULL
              ORDER BY fr.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $requests = [];
    while ($row = $result->fetch_assoc()) {
        if (!isset($row['is_viewed_by_finance'])) {
            $row['is_viewed_by_finance'] = 0;
        }
        $requests[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $requests]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'data' => []]);
}

$conn->close();
?>