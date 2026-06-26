<?php
// backend/api/approve_fund_request.php - COMPLETE FIX

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Database connection
$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// Get input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit();
}

$request_id = isset($data['request_id']) ? intval($data['request_id']) : 0;
$department_id = isset($data['department_id']) ? intval($data['department_id']) : 0;
$reviewed_by = isset($data['reviewed_by']) ? $data['reviewed_by'] : 'Finance';
$admin_notes = isset($data['admin_notes']) ? $data['admin_notes'] : '';

if ($request_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid request ID']);
    exit();
}

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // ============================================================
    // 1. GET THE REQUEST
    // ============================================================
    $stmt = $pdo->prepare("SELECT id, title, amount, department_id, status, is_deleted, source, description FROM fund_requests WHERE id = ?");
    $stmt->execute([$request_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$request) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Request not found']);
        exit();
    }
    
    // ============================================================
    // 2. CHECK IF ALREADY APPROVED
    // ============================================================
    if ($request['status'] === 'approved' || $request['status'] === 'Approved') {
        $pdo->rollBack();
        echo json_encode([
            'success' => true, 
            'message' => 'Request is already approved',
            'data' => ['already_approved' => true]
        ]);
        exit();
    }
    
    // ============================================================
    // 3. CHECK IF DELETED
    // ============================================================
    if ($request['is_deleted'] == 1) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Request has been deleted']);
        exit();
    }
    
    // ============================================================
    // 4. CHECK IF TRANSACTION ALREADY EXISTS
    // ============================================================
    $source = 'Budget Request: ' . $request['title'];
    $transCheck = $pdo->prepare("SELECT id FROM transactions WHERE source = ? AND amount = ? AND department_id = ? AND is_deleted = 0");
    $transCheck->execute([$source, $request['amount'], $request['department_id']]);
    $existingTrans = $transCheck->fetch(PDO::FETCH_ASSOC);
    
    if ($existingTrans) {
        // Transaction exists, just update the request status
        $updateStmt = $pdo->prepare("UPDATE fund_requests SET 
            status = 'approved', 
            reviewed_by = :reviewed_by,
            reviewed_at = NOW(),
            admin_notes = :admin_notes,
            updated_at = NOW()
            WHERE id = :id");
        
        $updateStmt->execute([
            ':id' => $request_id,
            ':reviewed_by' => $reviewed_by,
            ':admin_notes' => $admin_notes ?: 'Approved by Finance Department (Transaction already existed)'
        ]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Request approved (transaction already existed)',
            'data' => [
                'id' => $request_id,
                'status' => 'approved',
                'transaction_exists' => true,
                'transaction_id' => $existingTrans['id']
            ]
        ]);
        exit();
    }
    
    // ============================================================
    // 5. UPDATE REQUEST STATUS
    // ============================================================
    $updateStmt = $pdo->prepare("UPDATE fund_requests SET 
        status = 'approved', 
        reviewed_by = :reviewed_by,
        reviewed_at = NOW(),
        admin_notes = :admin_notes,
        updated_at = NOW()
        WHERE id = :id AND status != 'approved'");
    
    $updateResult = $updateStmt->execute([
        ':id' => $request_id,
        ':reviewed_by' => $reviewed_by,
        ':admin_notes' => $admin_notes ?: 'Approved by Finance Department'
    ]);
    
    if (!$updateResult || $updateStmt->rowCount() == 0) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Failed to update request status']);
        exit();
    }
    
    // ============================================================
    // 6. CREATE TRANSACTION - AUTOMATIC
    // ============================================================
    $amount = floatval($request['amount']);
    $transDescription = 'Approved budget request: ' . $request['title'] . ' from department ' . $request['department_id'];
    
    // Check if source field exists (if not, use title)
    $sourceField = $request['source'] ?: $request['title'];
    
    $transStmt = $pdo->prepare("INSERT INTO transactions 
        (department_id, amount, type, source, status, paid_amount, transaction_date, description, created_by, created_at) 
        VALUES (?, ?, 'expense', ?, 'paid', ?, NOW(), ?, ?, NOW())");
    
    $transResult = $transStmt->execute([
        $request['department_id'],
        $amount,
        'Budget Request: ' . $request['title'],
        $amount,
        $transDescription,
        $reviewed_by
    ]);
    
    if (!$transResult) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Failed to create transaction']);
        exit();
    }
    
    $transaction_id = $pdo->lastInsertId();
    
    // ============================================================
    // 7. ADD NOTIFICATION
    // ============================================================
    try {
        $notifStmt = $pdo->prepare("INSERT INTO notifications 
            (department_id, from_department_id, item_type, item_id, item_title, message, created_at, is_viewed) 
            VALUES (?, ?, 'fund_request', ?, ?, ?, NOW(), 0)");
        
        $notifStmt->execute([
            $request['department_id'],
            $department_id,
            $request_id,
            $request['title'],
            'Your fund request "' . $request['title'] . '" has been approved by Finance Department and added to transactions.'
        ]);
    } catch(PDOException $e) {
        // Log but don't fail
        error_log("Notification insert failed: " . $e->getMessage());
    }
    
    // ============================================================
    // 8. COMMIT TRANSACTION
    // ============================================================
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Budget request approved and added to transactions successfully',
        'data' => [
            'id' => $request_id,
            'title' => $request['title'],
            'amount' => $amount,
            'approved_by' => $reviewed_by,
            'status' => 'approved',
            'transaction_id' => $transaction_id,
            'transaction_created' => true
        ]
    ]);
    
} catch(PDOException $e) {
    $pdo->rollBack();
    error_log("Approve fund request error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch(Exception $e) {
    $pdo->rollBack();
    error_log("Approve fund request error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>