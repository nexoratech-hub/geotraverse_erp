<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

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
    exit;
}

// Get input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$request_id = isset($data['request_id']) ? intval($data['request_id']) : 0;
$department_id = isset($data['department_id']) ? intval($data['department_id']) : 0;
$is_deleted = isset($data['is_deleted']) ? intval($data['is_deleted']) : 1;

if ($request_id == 0 || $department_id == 0) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

try {
    // ============================================================
    // 1. GET REQUEST DATA
    // ============================================================
    $stmt = $pdo->prepare("SELECT * FROM fund_requests WHERE id = ?");
    $stmt->execute([$request_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        echo json_encode(['success' => false, 'message' => 'Request not found']);
        exit;
    }

    // ============================================================
    // 2. CHECK IF DEPARTMENT OWNS THE REQUEST
    // ============================================================
    if ($request['department_id'] != $department_id) {
        echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this request']);
        exit;
    }

    // ============================================================
    // 3. SOFT DELETE THE REQUEST
    // ============================================================
    $updateStmt = $pdo->prepare("
        UPDATE fund_requests 
        SET 
            is_deleted = ?,
            deleted_at = NOW()
        WHERE id = ?
    ");
    $updateStmt->execute([$is_deleted, $request_id]);

    // ============================================================
    // 4. ADD TO RECYCLE BIN
    // ============================================================
    $recycleStmt = $pdo->prepare("
        INSERT INTO recycle_bin (
            item_id,
            item_type,
            item_name,
            deleted_by_department_id,
            deleted_at
        ) VALUES (
            ?, 'fund_request', ?, ?, NOW()
        )
    ");
    
    $recycleStmt->execute([
        $request_id,
        $request['title'] ?? $request['source'] ?? 'Request',
        $department_id
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Fund request deleted successfully',
        'data' => [
            'request_id' => $request_id
        ]
    ]);

} catch(PDOException $e) {
    error_log("Delete fund request error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>