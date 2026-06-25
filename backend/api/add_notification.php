<?php
// backend/api/add_notification.php
// ============================================================
// WITH ERROR LOGGING
// ============================================================

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ============================================================
// DIRECT DATABASE CONNECTION
// ============================================================
$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $errorMsg = 'Database connection failed: ' . $e->getMessage();
    error_log($errorMsg);
    echo json_encode(['success' => false, 'message' => $errorMsg]);
    exit;
}

// ============================================================
// GET INPUT DATA
// ============================================================
$input = file_get_contents('php://input');
error_log("add_notification.php - Input: " . $input);

$data = json_decode($input, true);

if (!$data) {
    $errorMsg = 'Invalid JSON input';
    error_log($errorMsg);
    echo json_encode(['success' => false, 'message' => $errorMsg]);
    exit;
}

$department_id = isset($data['department_id']) ? intval($data['department_id']) : 0;
$from_department_id = isset($data['from_department_id']) ? intval($data['from_department_id']) : 0;
$item_type = isset($data['item_type']) ? trim($data['item_type']) : '';
$item_id = isset($data['item_id']) ? intval($data['item_id']) : 0;
$item_title = isset($data['item_title']) ? trim($data['item_title']) : '';
$message = isset($data['message']) ? trim($data['message']) : '';

error_log("add_notification.php - Data: department_id=$department_id, item_type=$item_type, item_id=$item_id");

// Validate required fields
if (!$department_id || !$item_type || !$item_id) {
    $errorMsg = 'Missing required fields';
    error_log($errorMsg);
    echo json_encode([
        'success' => false, 
        'message' => 'Missing required fields: department_id, item_type, item_id'
    ]);
    exit;
}

// ============================================================
// CHECK AND CREATE NOTIFICATIONS TABLE
// ============================================================
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `notifications` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `department_id` int(11) NOT NULL,
            `from_department_id` int(11) DEFAULT NULL,
            `item_type` varchar(50) NOT NULL,
            `item_id` int(11) NOT NULL,
            `item_title` varchar(255) DEFAULT NULL,
            `message` text DEFAULT NULL,
            `is_viewed` tinyint(4) DEFAULT 0,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `viewed_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_department` (`department_id`),
            KEY `idx_item` (`item_type`, `item_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    error_log("add_notification.php - Table created/checked");
} catch(PDOException $e) {
    error_log("add_notification.php - Table creation error: " . $e->getMessage());
    // Continue - table might already exist
}

// ============================================================
// CHECK IF NOTIFICATION ALREADY EXISTS
// ============================================================
try {
    $checkStmt = $pdo->prepare("
        SELECT id FROM notifications 
        WHERE department_id = ? AND item_type = ? AND item_id = ? AND is_viewed = 0
    ");
    $checkStmt->execute([$department_id, $item_type, $item_id]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        error_log("add_notification.php - Notification already exists: " . $existing['id']);
        echo json_encode([
            'success' => true, 
            'message' => 'Notification already exists', 
            'id' => $existing['id']
        ]);
        exit;
    }
} catch(PDOException $e) {
    error_log("add_notification.php - Check error: " . $e->getMessage());
}

// ============================================================
// INSERT NOTIFICATION
// ============================================================
try {
    $stmt = $pdo->prepare("
        INSERT INTO notifications (
            department_id, 
            from_department_id, 
            item_type, 
            item_id, 
            item_title, 
            message, 
            is_viewed, 
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, 0, NOW())
    ");
    
    $result = $stmt->execute([
        $department_id,
        $from_department_id,
        $item_type,
        $item_id,
        $item_title,
        $message
    ]);
    
    if ($result) {
        $notificationId = $pdo->lastInsertId();
        error_log("add_notification.php - Notification added: " . $notificationId);
        
        echo json_encode([
            'success' => true,
            'message' => 'Notification added successfully',
            'id' => $notificationId
        ]);
    } else {
        $errorMsg = 'Failed to insert notification';
        error_log($errorMsg);
        echo json_encode(['success' => false, 'message' => $errorMsg]);
    }
    
} catch(PDOException $e) {
    $errorMsg = 'Database error: ' . $e->getMessage();
    error_log($errorMsg);
    echo json_encode(['success' => false, 'message' => $errorMsg]);
}
?>