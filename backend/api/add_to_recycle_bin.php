<?php
error_reporting(0);
ini_set('display_errors', 0);

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
if (empty($input)) {
    echo json_encode(['success' => false, 'message' => 'No input data received']);
    exit;
}

$data = json_decode($input, true);
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

// Get parameters
$item_id = isset($data['item_id']) ? intval($data['item_id']) : 0;
$item_type = isset($data['item_type']) ? trim($data['item_type']) : '';
$item_name = isset($data['item_name']) ? trim($data['item_name']) : 'Item';
$deleted_by_department_id = isset($data['deleted_by_department_id']) ? intval($data['deleted_by_department_id']) : 0;
$deleted_by_admin = isset($data['deleted_by_admin']) ? intval($data['deleted_by_admin']) : 0;
$deleted_by = isset($data['deleted_by']) ? trim($data['deleted_by']) : 'System';

if ($item_id == 0 || empty($item_type)) {
    echo json_encode(['success' => false, 'message' => 'Item ID and type required']);
    exit;
}

try {
    // ============================================================
    // CHECK IF ITEM ALREADY EXISTS IN RECYCLE BIN
    // ============================================================
    $checkStmt = $pdo->prepare("
        SELECT id FROM recycle_bin 
        WHERE item_id = ? AND item_type = ? AND is_restored = 0
        LIMIT 1
    ");
    $checkStmt->execute([$item_id, $item_type]);
    
    if ($checkStmt->rowCount() > 0) {
        // Item already in recycle bin
        echo json_encode([
            'success' => true,
            'message' => 'Item already in recycle bin',
            'duplicate' => true
        ]);
        exit;
    }

    // ============================================================
    // INSERT INTO RECYCLE BIN
    // ============================================================
    $stmt = $pdo->prepare("
        INSERT INTO recycle_bin (
            item_id,
            item_type,
            item_name,
            deleted_by_department_id,
            deleted_by_admin,
            deleted_by,
            deleted_at,
            is_restored
        ) VALUES (
            ?, ?, ?, ?, ?, ?, NOW(), 0
        )
    ");
    
    $stmt->execute([
        $item_id,
        $item_type,
        $item_name,
        $deleted_by_department_id,
        $deleted_by_admin,
        $deleted_by
    ]);
    
    $recycle_id = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Item added to recycle bin',
        'data' => [
            'recycle_id' => $recycle_id,
            'item_id' => $item_id,
            'item_type' => $item_type,
            'item_name' => $item_name
        ]
    ]);

} catch(PDOException $e) {
    error_log("Add to recycle bin error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>