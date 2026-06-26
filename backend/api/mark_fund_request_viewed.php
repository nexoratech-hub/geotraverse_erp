<?php
// backend/api/mark_notification_viewed.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'geotraverse_erp';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

// ============================================================
// GET PARAMETERS - SUPPORT MULTIPLE FIELD NAMES
// ============================================================
$request_id = 0;
$department_id = 0;
$item_type = '';
$item_id = 0;

// Try different field names
if (isset($data['request_id'])) {
    $request_id = intval($data['request_id']);
} elseif (isset($data['item_id'])) {
    $request_id = intval($data['item_id']);
} elseif (isset($data['id'])) {
    $request_id = intval($data['id']);
}

if (isset($data['department_id'])) {
    $department_id = intval($data['department_id']);
}

if (isset($data['item_type'])) {
    $item_type = $data['item_type'];
}

$item_id = $request_id;

if (!$request_id || !$department_id) {
    echo json_encode([
        'success' => false, 
        'message' => 'Request ID and Department ID required',
        'data' => $data
    ]);
    exit();
}

// ============================================================
// DETERMINE ITEM TYPE
// ============================================================
if (empty($item_type)) {
    // Auto-detect from request
    if (isset($data['item_type']) && $data['item_type'] !== '') {
        $item_type = $data['item_type'];
    } else {
        // Check if this is a fund request by looking at the table
        $checkStmt = $conn->prepare("SELECT id FROM fund_requests WHERE id = ?");
        $checkStmt->bind_param("i", $request_id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        if ($result->num_rows > 0) {
            $item_type = 'fund_request';
        } else {
            $item_type = 'fund_request'; // Default
        }
        $checkStmt->close();
    }
}

// ============================================================
// MARK AS VIEWED - FUND REQUESTS
// ============================================================
$success = false;
$message = '';

if ($item_type === 'fund_request' || $item_type === 'fund_requests') {
    // ============================================================
    // CHECK IF COLUMN EXISTS FIRST
    // ============================================================
    $columns = [];
    $colQuery = "SHOW COLUMNS FROM fund_requests";
    $colResult = $conn->query($colQuery);
    while ($row = $colResult->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    
    // Determine which column to update
    $updateColumn = '';
    if (in_array('is_viewed_by_department', $columns)) {
        $updateColumn = 'is_viewed_by_department';
    } elseif (in_array('is_viewed_by_finance', $columns)) {
        $updateColumn = 'is_viewed_by_finance';
    } elseif (in_array('is_viewed_by_admin', $columns)) {
        $updateColumn = 'is_viewed_by_admin';
    } elseif (in_array('is_viewed', $columns)) {
        $updateColumn = 'is_viewed';
    } else {
        // No column found - add it
        $alterStmt = $conn->prepare("ALTER TABLE fund_requests ADD COLUMN is_viewed_by_department TINYINT(1) DEFAULT 0");
        $alterStmt->execute();
        $alterStmt->close();
        $updateColumn = 'is_viewed_by_department';
    }
    
    // ============================================================
    // UPDATE FUND REQUEST
    // ============================================================
    $viewedAtColumn = in_array('viewed_at', $columns) ? 'viewed_at' : 
                     (in_array('viewed_by_department_at', $columns) ? 'viewed_by_department_at' : 'updated_at');
    
    $sql = "UPDATE fund_requests SET 
            `$updateColumn` = 1, 
            `$viewedAtColumn` = NOW() 
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $request_id);
    
    if ($stmt->execute()) {
        $success = true;
        $message = 'Fund request marked as viewed';
        
        // Also update notifications
        $notifStmt = $conn->prepare("UPDATE notifications SET is_viewed = 1, viewed_at = NOW() 
                                     WHERE item_type = 'fund_request' AND item_id = ? AND department_id = ?");
        $notifStmt->bind_param("ii", $request_id, $department_id);
        $notifStmt->execute();
        $notifStmt->close();
        
        // Also update sent_fund_requests if exists
        if (in_array('sent_fund_requests', $columns)) {
            $sentStmt = $conn->prepare("UPDATE sent_fund_requests SET is_viewed = 1, viewed_at = NOW() 
                                        WHERE original_request_id = ? AND to_department_id = ?");
            $sentStmt->bind_param("ii", $request_id, $department_id);
            $sentStmt->execute();
            $sentStmt->close();
        }
    } else {
        $message = 'Failed to mark as viewed: ' . $stmt->error;
    }
    $stmt->close();
}

// ============================================================
// MARK AS VIEWED - OTHER ITEMS (PROJECTS, REPORTS, ETC)
// ============================================================
else if ($item_type === 'project' || $item_type === 'projects') {
    // Check if column exists
    $colQuery = "SHOW COLUMNS FROM projects LIKE 'is_viewed_by_department'";
    $colResult = $conn->query($colQuery);
    
    if ($colResult->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE projects SET is_viewed_by_department = 1, viewed_by_department_at = NOW() WHERE id = ?");
        $stmt->bind_param("i", $request_id);
    } else {
        // Try other column names
        $stmt = $conn->prepare("UPDATE projects SET is_viewed = 1, viewed_at = NOW() WHERE id = ?");
        $stmt->bind_param("i", $request_id);
    }
    
    if ($stmt->execute()) {
        $success = true;
        $message = 'Project marked as viewed';
        
        // Update notifications
        $notifStmt = $conn->prepare("UPDATE notifications SET is_viewed = 1, viewed_at = NOW() 
                                     WHERE item_type = 'project' AND item_id = ? AND department_id = ?");
        $notifStmt->bind_param("ii", $request_id, $department_id);
        $notifStmt->execute();
        $notifStmt->close();
    } else {
        $message = 'Failed to mark project as viewed: ' . $stmt->error;
    }
    $stmt->close();
}

else if ($item_type === 'report' || $item_type === 'reports') {
    $stmt = $conn->prepare("UPDATE reports SET is_viewed_by_department = 1, viewed_by_department_at = NOW() WHERE id = ?");
    $stmt->bind_param("i", $request_id);
    
    if ($stmt->execute()) {
        $success = true;
        $message = 'Report marked as viewed';
        
        $notifStmt = $conn->prepare("UPDATE notifications SET is_viewed = 1, viewed_at = NOW() 
                                     WHERE item_type = 'report' AND item_id = ? AND department_id = ?");
        $notifStmt->bind_param("ii", $request_id, $department_id);
        $notifStmt->execute();
        $notifStmt->close();
    } else {
        $message = 'Failed to mark report as viewed: ' . $stmt->error;
    }
    $stmt->close();
}

else if ($item_type === 'uploaded_report' || $item_type === 'uploaded_reports') {
    $stmt = $conn->prepare("UPDATE uploaded_reports SET is_viewed_by_department = 1, viewed_by_department_at = NOW() WHERE id = ?");
    $stmt->bind_param("i", $request_id);
    
    if ($stmt->execute()) {
        $success = true;
        $message = 'Uploaded report marked as viewed';
        
        $notifStmt = $conn->prepare("UPDATE notifications SET is_viewed = 1, viewed_at = NOW() 
                                     WHERE item_type = 'uploaded_report' AND item_id = ? AND department_id = ?");
        $notifStmt->bind_param("ii", $request_id, $department_id);
        $notifStmt->execute();
        $notifStmt->close();
    } else {
        $message = 'Failed to mark uploaded report as viewed: ' . $stmt->error;
    }
    $stmt->close();
}

// ============================================================
// MARK AS VIEWED - DOCUMENTS
// ============================================================
else if ($item_type === 'document' || $item_type === 'project_document') {
    $stmt = $conn->prepare("UPDATE project_documents SET is_viewed_by_department = 1, viewed_by_department_at = NOW() WHERE id = ?");
    $stmt->bind_param("i", $request_id);
    
    if ($stmt->execute()) {
        $success = true;
        $message = 'Document marked as viewed';
        
        $notifStmt = $conn->prepare("UPDATE notifications SET is_viewed = 1, viewed_at = NOW() 
                                     WHERE item_type = 'document' AND item_id = ? AND department_id = ?");
        $notifStmt->bind_param("ii", $request_id, $department_id);
        $notifStmt->execute();
        $notifStmt->close();
    } else {
        $message = 'Failed to mark document as viewed: ' . $stmt->error;
    }
    $stmt->close();
}

// ============================================================
// RESPONSE
// ============================================================
echo json_encode([
    'success' => $success,
    'message' => $message,
    'data' => [
        'request_id' => $request_id,
        'department_id' => $department_id,
        'item_type' => $item_type,
        'updated_at' => date('Y-m-d H:i:s')
    ]
]);

$conn->close();
?>