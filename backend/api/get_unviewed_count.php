<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
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

// Get parameters
$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;
$item_type = isset($_GET['item_type']) ? trim($_GET['item_type']) : '';

if ($department_id == 0) {
    echo json_encode(['success' => false, 'message' => 'Department ID required']);
    exit;
}

try {
    // ============================================================
    // 1. GET UNVIEWED NOTIFICATIONS COUNT BY TYPE
    // ============================================================
    $response = [
        'success' => true,
        'data' => [
            'projects' => 0,
            'reports' => 0,
            'uploaded_reports' => 0,
            'fund_requests' => 0,
            'documents' => 0,
            'dailywork' => 0,
            'visitor' => 0,
            'total' => 0
        ]
    ];

    // Get total unviewed count
    $totalStmt = $pdo->prepare("
        SELECT COUNT(*) as total FROM notifications 
        WHERE department_id = ? AND is_viewed = 0
    ");
    $totalStmt->execute([$department_id]);
    $total = $totalStmt->fetch(PDO::FETCH_ASSOC);
    $response['data']['total'] = intval($total['total'] ?? 0);

    // If item_type is specified, return count for that type only
    if (!empty($item_type)) {
        $typeStmt = $pdo->prepare("
            SELECT COUNT(*) as count FROM notifications 
            WHERE department_id = ? AND is_viewed = 0 AND item_type = ?
        ");
        $typeStmt->execute([$department_id, $item_type]);
        $count = $typeStmt->fetch(PDO::FETCH_ASSOC);
        $response['data'][$item_type] = intval($count['count'] ?? 0);
        
        // Return only the requested type
        echo json_encode([
            'success' => true,
            'data' => [
                'count' => intval($count['count'] ?? 0),
                'total' => intval($total['total'] ?? 0)
            ]
        ]);
        exit;
    }

    // ============================================================
    // 2. GET BREAKDOWN BY ITEM TYPE
    // ============================================================
    $breakdownStmt = $pdo->prepare("
        SELECT item_type, COUNT(*) as count 
        FROM notifications 
        WHERE department_id = ? AND is_viewed = 0 
        GROUP BY item_type
    ");
    $breakdownStmt->execute([$department_id]);
    $breakdown = $breakdownStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($breakdown as $row) {
        $type = $row['item_type'];
        if (isset($response['data'][$type])) {
            $response['data'][$type] = intval($row['count']);
        }
    }

    // ============================================================
    // 3. RETURN RESPONSE
    // ============================================================
    echo json_encode($response);

} catch(PDOException $e) {
    error_log("Get unviewed count error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>