<?php
// ============================================================
// send_notification_websocket.php
// PHP Bridge to send notifications via WebSocket
// ============================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

// Configuration
define('WEBSOCKET_API_URL', 'http://localhost:3001/send-notification');

function sendWebSocketNotification($departmentId, $data) {
    $payload = array_merge($data, [
        'department_id' => (int)$departmentId,
        'from_department_id' => isset($data['from_department_id']) ? (int)$data['from_department_id'] : 1,
        'from_department_name' => isset($data['from_department_name']) ? $data['from_department_name'] : 'Super Admin'
    ]);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, WEBSOCKET_API_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['success' => false, 'error' => $error];
    }
    
    return json_decode($response, true) ?? ['success' => false, 'error' => 'Invalid response'];
}

// ============================================================
// HANDLE POST REQUEST
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['department_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'department_id required']);
        exit;
    }
    
    $result = sendWebSocketNotification($input['department_id'], $input);
    echo json_encode($result);
    exit;
}

// ============================================================
// HANDLE GET REQUEST - Test
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $departmentId = isset($_GET['department_id']) ? (int)$_GET['department_id'] : 1;
    
    $result = sendWebSocketNotification($departmentId, [
        'item_type' => 'test',
        'item_id' => 0,
        'item_title' => '🔔 Test Notification',
        'message' => 'This is a real-time test notification from Super Admin',
        'action_url' => '#'
    ]);
    
    echo json_encode($result);
    exit;
}

// ============================================================
// HELPER FUNCTION FOR OTHER PHP FILES
// ============================================================
function notifyDepartment($departmentId, $itemType, $itemId, $itemTitle, $message, $actionUrl = '') {
    return sendWebSocketNotification($departmentId, [
        'item_type' => $itemType,
        'item_id' => (int)$itemId,
        'item_title' => $itemTitle,
        'message' => $message,
        'action_url' => $actionUrl
    ]);
}
?>