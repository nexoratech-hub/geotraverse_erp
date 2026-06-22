<?php
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

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
    error_log("DB Connection failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Get parameters
$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;
$item_type = isset($_GET['item_type']) ? $_GET['item_type'] : 'all';

if ($department_id == 0) {
    echo json_encode(['success' => false, 'message' => 'Department ID required']);
    exit;
}

try {
    $response = [
        'success' => true,
        'data' => [
            'projects' => 0,
            'reports' => 0,
            'uploaded_reports' => 0,
            'fund_requests' => 0,
            'documents' => 0,
            'dailywork' => 0,
            'notifications' => 0,
            'total' => 0
        ]
    ];

    // Get counts from notifications table
    $sql = "SELECT COUNT(*) as count FROM notifications 
            WHERE department_id = ? AND is_viewed = 0";
    $params = [$department_id];

    // Filter by item_type if specified
    if ($item_type !== 'all') {
        $sql .= " AND item_type = ?";
        $params[] = $item_type;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $count = $stmt->fetch(PDO::FETCH_ASSOC);

    $totalCount = $count['count'] ?? 0;

    // If item_type is 'all', get breakdown by type
    if ($item_type === 'all') {
        $stmt = $pdo->prepare("SELECT item_type, COUNT(*) as count 
                              FROM notifications 
                              WHERE department_id = ? AND is_viewed = 0 
                              GROUP BY item_type");
        $stmt->execute([$department_id]);
        $breakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($breakdown as $row) {
            $type = $row['item_type'];
            $count = intval($row['count']);
            $response['data'][$type] = $count;
        }
    } else {
        // For specific item_type
        $response['data'][$item_type] = $totalCount;
    }

    $response['data']['total'] = $totalCount;

    echo json_encode($response);

} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>