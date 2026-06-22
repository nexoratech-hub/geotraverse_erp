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

$notification_id = isset($data['notification_id']) ? intval($data['notification_id']) : 0;
$department_id = isset($data['department_id']) ? intval($data['department_id']) : 0;
$item_type = isset($data['item_type']) ? trim($data['item_type']) : '';
$item_id = isset($data['item_id']) ? intval($data['item_id']) : 0;

if ($notification_id == 0 && ($item_id == 0 || empty($item_type))) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

try {
    // ============================================================
    // 1. MARK NOTIFICATION AS VIEWED
    // ============================================================
    if ($notification_id > 0) {
        // Mark by notification_id
        $stmt = $pdo->prepare("
            UPDATE notifications 
            SET is_viewed = 1, viewed_at = NOW(), updated_at = NOW() 
            WHERE id = ? AND (department_id = ? OR ? = 0)
        ");
        $stmt->execute([$notification_id, $department_id, $department_id]);
        $affected = $stmt->rowCount();
        
    } else if ($item_id > 0 && !empty($item_type)) {
        // Mark by item_type and item_id (mark all notifications for this item)
        $stmt = $pdo->prepare("
            UPDATE notifications 
            SET is_viewed = 1, viewed_at = NOW(), updated_at = NOW() 
            WHERE item_type = ? AND item_id = ? AND department_id = ?
        ");
        $stmt->execute([$item_type, $item_id, $department_id]);
        $affected = $stmt->rowCount();
    }

    // ============================================================
    // 2. GET UPDATED UNVIEWED COUNT
    // ============================================================
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) as total FROM notifications 
        WHERE department_id = ? AND is_viewed = 0
    ");
    $countStmt->execute([$department_id]);
    $unviewed = $countStmt->fetch(PDO::FETCH_ASSOC);

    // ============================================================
    // 3. RETURN RESPONSE
    // ============================================================
    echo json_encode([
        'success' => true,
        'message' => 'Notification marked as viewed',
        'data' => [
            'affected' => $affected ?? 0,
            'unviewed_count' => intval($unviewed['total'] ?? 0)
        ]
    ]);

} catch(PDOException $e) {
    error_log("Mark notification viewed error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>