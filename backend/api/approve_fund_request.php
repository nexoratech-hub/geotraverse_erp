<?php
// Force JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'geotraverse_erp';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// If no JSON, try POST parameters
if (!$data) {
    $data = $_POST;
}

// Validate required fields
if (!$data || !isset($data['request_id'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Request ID required',
        'received' => $data
    ]);
    exit();
}

$request_id = intval($data['request_id']);
$reviewed_by = isset($data['reviewed_by']) ? trim($data['reviewed_by']) : 'Finance Manager';

// Get the request details
$stmt = $conn->prepare("SELECT * FROM fund_requests WHERE id = ? AND is_deleted = 0");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database prepare failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Request not found']);
    $stmt->close();
    $conn->close();
    exit();
}

$request = $result->fetch_assoc();
$stmt->close();

// Check if request is already processed
if ($request['status'] !== 'pending') {
    echo json_encode([
        'success' => false, 
        'message' => 'Request is already ' . $request['status'],
        'current_status' => $request['status']
    ]);
    $conn->close();
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    // 1. Update fund request status to 'approved'
    $updateStmt = $conn->prepare("UPDATE fund_requests SET status = 'approved', reviewed_by = ?, reviewed_at = NOW(), is_viewed_by_finance = 1, viewed_at = NOW() WHERE id = ?");
    if (!$updateStmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    $updateStmt->bind_param("si", $reviewed_by, $request_id);
    
    if (!$updateStmt->execute()) {
        throw new Exception('Failed to approve request: ' . $updateStmt->error);
    }
    $updateStmt->close();
    
    // 2. Add transaction (expense) - automatically PAID
    $amount = floatval($request['amount']);
    $transaction_date = date('Y-m-d');
    $source = 'Budget Request: ' . $request['title'];
    $description = 'Approved fund request from department ' . ($request['department_id'] ?? 'Unknown') . ': ' . ($request['description'] ?? '');
    
    $transStmt = $conn->prepare("INSERT INTO transactions (type, source, amount, paid_amount, transaction_date, status, description, department_id, created_by, created_at) VALUES ('expense', ?, ?, ?, ?, 'paid', ?, 2, ?, NOW())");
    if (!$transStmt) {
        throw new Exception('Prepare transaction failed: ' . $conn->error);
    }
    $transStmt->bind_param("sddsss", $source, $amount, $amount, $transaction_date, $description, $reviewed_by);
    
    if (!$transStmt->execute()) {
        throw new Exception('Failed to add transaction: ' . $transStmt->error);
    }
    $transaction_id = $conn->insert_id;
    $transStmt->close();
    
    // 3. Add notification for the department that made the request
    try {
        $notifStmt = $conn->prepare("INSERT INTO notifications (department_id, item_type, item_id, from_department_id, item_title, message, created_at) VALUES (?, 'fund_request', ?, 2, ?, CONCAT('Your fund request \"', ?, '\" has been APPROVED and paid'), NOW())");
        if ($notifStmt) {
            $notifStmt->bind_param("iiss", $request['department_id'], $request_id, $request['title'], $request['title']);
            $notifStmt->execute();
            $notifStmt->close();
        }
    } catch (Exception $e) {
        // Notification failed, but transaction succeeded
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Request approved and added as expense transaction',
        'request_id' => $request_id,
        'transaction_id' => $transaction_id,
        'amount' => $amount
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>