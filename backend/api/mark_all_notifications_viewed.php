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

$department_id = isset($data['department_id']) ? intval($data['department_id']) : 0;
$item_type = isset($data['item_type']) ? trim($data['item_type']) : '';

if ($department_id == 0) {
    echo json_encode(['success' => false, 'message' => 'Department ID required']);
    exit;
}

try {
    // ============================================================
    // 1. MARK ALL NOTIFICATIONS AS VIEWED
    // ============================================================
    $sql = "UPDATE notifications SET is_viewed = 1, viewed_at = NOW(), updated_at = NOW() WHERE department_id = ? AND is_viewed = 0";
    $params = [$department_id];
    
    if (!empty($item_type)) {
        $sql .= " AND item_type = ?";
        $params[] = $item_type;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $affected = $stmt->rowCount();

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
        'message' => 'All notifications marked as viewed',
        'data' => [
            'affected' => $affected,
            'remaining_unviewed' => intval($unviewed['total'] ?? 0)
        ]
    ]);

} catch(PDOException $e) {
    error_log("Mark all notifications viewed error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>