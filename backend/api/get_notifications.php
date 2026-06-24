<?php
// ============================================================
// GET NOTIFICATIONS API - KWA DEPARTMENT HUSIKA
// ============================================================

if (ob_get_length()) ob_clean();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../config/database.php';

if (!function_exists('sendResponse')) {
    function sendResponse($success, $data = null, $message = "", $unviewed_count = null) {
        if (ob_get_length()) ob_clean();
        $response = array(
            "success" => $success,
            "timestamp" => date('Y-m-d H:i:s')
        );
        if ($data !== null) $response["data"] = $data;
        if ($data !== null && is_array($data)) $response["count"] = count($data);
        if ($message !== "") $response["message"] = $message;
        if ($unviewed_count !== null) $response["unviewed_count"] = $unviewed_count;
        if (!headers_sent()) {
            header('Content-Type: application/json');
            header('Access-Control-Allow-Origin: *');
        }
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit();
    }
}

// ============================================================
// PARAMETERS
// ============================================================
$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;

if ($department_id == 0) {
    sendResponse(false, null, 'Department ID required');
    exit;
}

try {
    $conn = $db;
    
    // ============================================================
    // GET NOTIFICATIONS - HAKIKISHA TABLE IPO
    // ============================================================
    // Angalia kama table ipo
    $table_check = "SHOW TABLES LIKE 'notifications'";
    $table_result = $conn->query($table_check);
    
    if ($table_result->rowCount() == 0) {
        // Table haipo, rudisha array tupu
        sendResponse(true, [], 'Notifications table not found');
        exit;
    }
    
    // ============================================================
    // GET NOTIFICATIONS
    // ============================================================
    $sql = "SELECT 
                n.id,
                n.department_id,
                n.item_type,
                n.item_id,
                n.from_department_id,
                n.item_title,
                n.message,
                n.is_viewed,
                n.viewed_at,
                n.created_at,
                n.updated_at,
                d.name as from_department_name
            FROM notifications n
            LEFT JOIN departments d ON n.from_department_id = d.id
            WHERE n.department_id = :department_id
            ORDER BY n.created_at DESC
            LIMIT 50";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':department_id', $department_id, PDO::PARAM_INT);
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // ============================================================
    // FORMAT RESPONSE
    // ============================================================
    foreach ($notifications as &$n) {
        // Ensure from_department_name is set
        if (empty($n['from_department_name'])) {
            $n['from_department_name'] = 'Unknown';
        }
        
        // Ensure is_viewed is integer
        $n['is_viewed'] = intval($n['is_viewed'] ?? 0);
        
        // Format dates
        if ($n['created_at']) {
            $n['created_at_formatted'] = date('Y-m-d H:i:s', strtotime($n['created_at']));
        }
    }
    
    sendResponse(true, $notifications, 'Notifications retrieved successfully');
    
} catch (PDOException $e) {
    error_log("get_notifications.php PDO Error: " . $e->getMessage());
    sendResponse(true, [], 'Database error: ' . $e->getMessage());
} catch (Exception $e) {
    error_log("get_notifications.php Error: " . $e->getMessage());
    sendResponse(true, [], 'Error: ' . $e->getMessage());
}
?>