<?php
// File: backend/api/update_budget_request.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

session_start();

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$request_id = intval($data['id']);
$status = isset($data['status']) ? $data['status'] : 'pending';
$department_id = isset($data['department_id']) ? intval($data['department_id']) : 0;

try {
    // Update the request status
    $query = "UPDATE fund_requests 
              SET status = ?, 
                  updated_at = NOW(),
                  reviewed_at = NOW(),
                  reviewed_by = ?
              WHERE id = ? AND is_deleted = 0";
    
    $stmt = $conn->prepare($query);
    $reviewed_by = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'Finance Manager';
    $stmt->bind_param('ssi', $status, $reviewed_by, $request_id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        // Get the request details
        $query2 = "SELECT * FROM fund_requests WHERE id = ?";
        $stmt2 = $conn->prepare($query2);
        $stmt2->bind_param('i', $request_id);
        $stmt2->execute();
        $result = $stmt2->get_result();
        $request = $result->fetch_assoc();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Request updated successfully',
            'data' => $request
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or request not found']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>