<?php
// add_to_recycle_bin.php - Add item to recycle bin

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB Connection Error: ' . $e->getMessage()]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['item_id'])) {
    echo json_encode(['success' => false, 'message' => 'Item ID required']);
    exit;
}

$itemId = (int)$data['item_id'];
$itemType = isset($data['item_type']) ? trim($data['item_type']) : '';
$itemName = isset($data['item_name']) ? trim($data['item_name']) : 'Unknown';
$deletedByDepartment = isset($data['deleted_by_department_id']) ? (int)$data['deleted_by_department_id'] : null;
$deletedByAdmin = isset($data['deleted_by_admin']) ? (int)$data['deleted_by_admin'] : 0;

// ============================================================
// VALIDATE ITEM TYPE - IWE NA DEFAULT VALUE
// ============================================================
$validTypes = [
    'project', 'sent_project',
    'project_document', 'sent_project_document',
    'budget_request',
    'report', 'sent_report',
    'uploaded_report', 'sent_uploaded_report',
    'daily_work', 'employee',
    'visitor', 'campaign', 'campaign_document',
    'transaction'
];

// ============================================================
// IF item_type IS EMPTY, TRY TO DETECT FROM CONTEXT
// ============================================================
if (empty($itemType)) {
    // Try to detect from item_name or other clues
    if (strpos($itemName, 'sent_report') !== false || strpos($itemName, 'Sent Report') !== false) {
        $itemType = 'sent_report';
    } elseif (strpos($itemName, 'sent_project') !== false || strpos($itemName, 'Sent Project') !== false) {
        $itemType = 'sent_project';
    } elseif (strpos($itemName, 'sent_uploaded_report') !== false) {
        $itemType = 'sent_uploaded_report';
    } else {
        // Default to 'report'
        $itemType = 'report';
    }
}

// Make sure item_type is valid
if (!in_array($itemType, $validTypes)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid item type: ' . $itemType,
        'valid_types' => $validTypes,
        'received_data' => $data
    ]);
    exit;
}

try {
    // Check if already in recycle bin
    $checkStmt = $pdo->prepare("
        SELECT id FROM recycle_bin 
        WHERE item_id = ? AND item_type = ?
    ");
    $checkStmt->execute([$itemId, $itemType]);
    
    if ($checkStmt->fetch()) {
        echo json_encode([
            'success' => true,
            'message' => 'Item already in recycle bin',
            'already_exists' => true,
            'item_type' => $itemType
        ]);
        exit;
    }
    
    // Insert into recycle bin
    $stmt = $pdo->prepare("
        INSERT INTO recycle_bin 
        (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$itemId, $itemType, $itemName, $deletedByDepartment, $deletedByAdmin]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Item added to recycle bin',
        'recycle_id' => $pdo->lastInsertId(),
        'item_type' => $itemType,
        'item_id' => $itemId,
        'item_name' => $itemName
    ]);
    
} catch(PDOException $e) {
    error_log('Add to recycle bin error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>