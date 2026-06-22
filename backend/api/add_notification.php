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

// Required parameters
$department_id = isset($data['department_id']) ? intval($data['department_id']) : 0;
$item_type = isset($data['item_type']) ? trim($data['item_type']) : '';
$item_id = isset($data['item_id']) ? intval($data['item_id']) : 0;
$item_title = isset($data['item_title']) ? trim($data['item_title']) : '';
$message = isset($data['message']) ? trim($data['message']) : '';
$from_department_id = isset($data['from_department_id']) ? intval($data['from_department_id']) : 0;

if ($department_id == 0 || empty($item_type) || $item_id == 0) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

// Validate item_type
$valid_types = ['project', 'report', 'uploaded_report', 'document', 'fund_request', 'dailywork', 'visitor'];
if (!in_array($item_type, $valid_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid item type']);
    exit;
}

try {
    // ============================================================
    // 1. CHECK IF DEPARTMENT EXISTS
    // ============================================================
    $stmt = $pdo->prepare("SELECT id FROM departments WHERE id = ?");
    $stmt->execute([$department_id]);
    if ($stmt->rowCount() == 0) {
        echo json_encode(['success' => false, 'message' => 'Department not found']);
        exit;
    }

    // ============================================================
    // 2. GET DEPARTMENT NAME
    // ============================================================
    $from_dept_name = '';
    if ($from_department_id > 0) {
        $stmt = $pdo->prepare("SELECT name FROM departments WHERE id = ?");
        $stmt->execute([$from_department_id]);
        $dept = $stmt->fetch(PDO::FETCH_ASSOC);
        $from_dept_name = $dept ? $dept['name'] : 'Unknown Department';
    }

    // ============================================================
    // 3. CHECK FOR DUPLICATE NOTIFICATION (PREVENT SPAM)
    // ============================================================
    $checkStmt = $pdo->prepare("
        SELECT id FROM notifications 
        WHERE department_id = ? 
        AND item_type = ? 
        AND item_id = ? 
        AND from_department_id = ? 
        AND is_viewed = 0
        AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        LIMIT 1
    ");
    $checkStmt->execute([$department_id, $item_type, $item_id, $from_department_id]);
    
    if ($checkStmt->rowCount() > 0) {
        // Duplicate notification - return success anyway
        echo json_encode([
            'success' => true, 
            'message' => 'Notification already exists',
            'duplicate' => true
        ]);
        exit;
    }

    // ============================================================
    // 4. INSERT NOTIFICATION
    // ============================================================
    $insertStmt = $pdo->prepare("
        INSERT INTO notifications (
            department_id,
            from_department_id,
            item_type,
            item_id,
            item_title,
            message,
            is_viewed,
            created_at,
            updated_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, 0, NOW(), NOW()
        )
    ");
    
    $insertStmt->execute([
        $department_id,
        $from_department_id,
        $item_type,
        $item_id,
        $item_title,
        $message
    ]);
    
    $notification_id = $pdo->lastInsertId();

    // ============================================================
    // 5. RETURN RESPONSE
    // ============================================================
    echo json_encode([
        'success' => true,
        'message' => 'Notification added successfully',
        'data' => [
            'notification_id' => $notification_id,
            'department_id' => $department_id,
            'item_type' => $item_type,
            'item_id' => $item_id,
            'item_title' => $item_title,
            'from_department' => $from_dept_name
        ]
    ]);

} catch(PDOException $e) {
    error_log("Add notification error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>