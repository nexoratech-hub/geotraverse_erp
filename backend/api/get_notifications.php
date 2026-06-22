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
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$item_type = isset($_GET['item_type']) ? trim($_GET['item_type']) : '';

if ($department_id == 0) {
    echo json_encode(['success' => false, 'message' => 'Department ID required']);
    exit;
}

try {
    // ============================================================
    // 1. BUILD QUERY
    // ============================================================
    $sql = "SELECT * FROM notifications WHERE department_id = ?";
    $params = [$department_id];
    
    if (!empty($item_type)) {
        $sql .= " AND item_type = ?";
        $params[] = $item_type;
    }
    
    $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;

    // ============================================================
    // 2. GET NOTIFICATIONS
    // ============================================================
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ============================================================
    // 3. GET DEPARTMENT NAMES FOR EACH NOTIFICATION
    // ============================================================
    $deptCache = [];
    foreach ($notifications as &$notif) {
        if ($notif['from_department_id'] > 0) {
            if (!isset($deptCache[$notif['from_department_id']])) {
                $deptStmt = $pdo->prepare("SELECT name FROM departments WHERE id = ?");
                $deptStmt->execute([$notif['from_department_id']]);
                $dept = $deptStmt->fetch(PDO::FETCH_ASSOC);
                $deptCache[$notif['from_department_id']] = $dept ? $dept['name'] : 'Unknown';
            }
            $notif['from_department_name'] = $deptCache[$notif['from_department_id']];
        } else {
            $notif['from_department_name'] = 'System';
        }
        
        // Format date
        $notif['created_at_formatted'] = date('Y-m-d H:i:s', strtotime($notif['created_at']));
        $notif['time_ago'] = timeAgo($notif['created_at']);
    }

    // ============================================================
    // 4. GET UNVIEWED COUNT
    // ============================================================
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) as total FROM notifications 
        WHERE department_id = ? AND is_viewed = 0
    ");
    $countStmt->execute([$department_id]);
    $unviewed = $countStmt->fetch(PDO::FETCH_ASSOC);

    // ============================================================
    // 5. RETURN RESPONSE
    // ============================================================
    echo json_encode([
        'success' => true,
        'data' => $notifications,
        'pagination' => [
            'limit' => $limit,
            'offset' => $offset,
            'total' => count($notifications)
        ],
        'unviewed_count' => intval($unviewed['total'] ?? 0)
    ]);

} catch(PDOException $e) {
    error_log("Get notifications error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}

// ============================================================
// HELPER FUNCTION: Time ago
// ============================================================
function timeAgo($timestamp) {
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;
    
    $minutes = round($seconds / 60);
    $hours = round($seconds / 3600);
    $days = round($seconds / 86400);
    $weeks = round($seconds / 604800);
    $months = round($seconds / 2629440);
    $years = round($seconds / 31553280);
    
    if ($seconds <= 60) {
        return "Just Now";
    } else if ($minutes <= 60) {
        return ($minutes == 1) ? "1 minute ago" : "$minutes minutes ago";
    } else if ($hours <= 24) {
        return ($hours == 1) ? "1 hour ago" : "$hours hours ago";
    } else if ($days <= 7) {
        return ($days == 1) ? "1 day ago" : "$days days ago";
    } else if ($weeks <= 4.3) {
        return ($weeks == 1) ? "1 week ago" : "$weeks weeks ago";
    } else if ($months <= 12) {
        return ($months == 1) ? "1 month ago" : "$months months ago";
    } else {
        return ($years == 1) ? "1 year ago" : "$years years ago";
    }
}
?>