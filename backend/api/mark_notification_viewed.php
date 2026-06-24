<?php
// backend/api/mark_notification_viewed.php

// ============================================================
// ENABLE ERROR REPORTING
// ============================================================
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// ============================================================
// HEADERS
// ============================================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ============================================================
// DATABASE CONNECTION
// ============================================================
try {
    $pdo = new PDO("mysql:host=localhost;dbname=geotraverse_erp;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    exit;
}

// ============================================================
// GET INPUT DATA - TRY MULTIPLE SOURCES
// ============================================================
$input = null;

// Try JSON first
$rawInput = file_get_contents('php://input');
if ($rawInput) {
    $input = json_decode($rawInput, true);
}

// If JSON failed, try $_POST
if (!$input) {
    $input = $_POST;
}

// If still empty, try $_GET
if (!$input || empty($input)) {
    $input = $_GET;
}

// ============================================================
// LOG FOR DEBUGGING
// ============================================================
error_log("mark_notification_viewed - Raw input: " . $rawInput);
error_log("mark_notification_viewed - Parsed input: " . print_r($input, true));

// ============================================================
// VALIDATE INPUT
// ============================================================
if (!$input || !isset($input['item_type']) || !isset($input['item_id']) || !isset($input['department_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields: item_type, item_id, department_id',
        'received' => $input,
        'raw' => $rawInput,
        'post' => $_POST,
        'get' => $_GET
    ]);
    exit;
}

$item_type = $input['item_type'];
$item_id = intval($input['item_id']);
$department_id = intval($input['department_id']);

// ============================================================
// LOG WHAT WE'RE DOING
// ============================================================
error_log("mark_notification_viewed - item_type: $item_type, item_id: $item_id, department_id: $department_id");

try {
    $updated = false;
    $table = '';
    $id_column = 'id';
    $viewed_column = 'is_viewed_by_department';

    // ============================================================
    // DETERMINE TABLE BASED ON ITEM TYPE
    // ============================================================
    switch ($item_type) {
        case 'project':
            $table = 'projects';
            break;
        case 'report':
            $table = 'reports';
            break;
        case 'uploaded_report':
            $table = 'uploaded_reports';
            break;
        case 'fund_request':
            $table = 'fund_requests';
            $viewed_column = 'is_viewed_by_department';
            break;
        case 'document':
        case 'project_document':
            $table = 'project_documents';
            break;
        case 'dailywork':
        case 'daily_work':
            $table = 'dailywork';
            break;
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Unknown item_type: ' . $item_type
            ]);
            exit;
    }

    // ============================================================
    // UPDATE VIEWED STATUS IN MAIN TABLE
    // ============================================================
    $query = "UPDATE $table SET $viewed_column = 1 WHERE $id_column = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$item_id]);
    $updated = $stmt->rowCount() > 0;

    // ============================================================
    // ALSO UPDATE NOTIFICATIONS TABLE
    // ============================================================
    $query = "UPDATE notifications SET 
              is_viewed = 1,
              viewed_at = NOW()
              WHERE department_id = ? 
                AND item_type = ? 
                AND item_id = ? 
                AND is_viewed = 0";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$department_id, $item_type, $item_id]);
    $notifications_updated = $stmt->rowCount();

    // ============================================================
    // SEND SUCCESS RESPONSE
    // ============================================================
    echo json_encode([
        'success' => true,
        'message' => 'Marked as viewed',
        'data' => [
            'item_type' => $item_type,
            'item_id' => $item_id,
            'department_id' => $department_id,
            'table' => $table,
            'updated' => $updated,
            'notifications_updated' => $notifications_updated
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>