<?php
// File: backend/api/get_all_budget_requests.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) && !isset($_SESSION['department_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated', 'data' => []]);
    exit;
}

$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;

try {
    // Get all budget requests from all departments
    $query = "SELECT fr.*, d.name as department_name, e.name as requested_by_name 
              FROM fund_requests fr
              LEFT JOIN departments d ON fr.department_id = d.id
              LEFT JOIN employees e ON fr.requested_by = e.email
              WHERE fr.is_deleted = 0
              ORDER BY fr.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $requests = [];
    while ($row = $result->fetch_assoc()) {
        // Add is_viewed_by_finance field if not exists (default 0)
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