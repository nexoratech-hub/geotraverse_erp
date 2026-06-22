<?php
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
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$dailywork_id = isset($data['id']) ? intval($data['id']) : 0;
$department_id = isset($data['department_id']) ? intval($data['department_id']) : 0;
$is_deleted = isset($data['is_deleted']) ? intval($data['is_deleted']) : 1;

if ($dailywork_id == 0 || $department_id == 0) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

try {
    // ============================================================
    // 1. GET DAILY WORK DATA
    // ============================================================
    $stmt = $pdo->prepare("SELECT * FROM dailywork WHERE id = ?");
    $stmt->execute([$dailywork_id]);
    $dailywork = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$dailywork) {
        echo json_encode(['success' => false, 'message' => 'Daily work record not found']);
        exit;
    }

    // ============================================================
    // 2. CHECK IF DEPARTMENT OWNS THE DAILY WORK
    // ============================================================
    if ($dailywork['department_id'] != $department_id) {
        echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this record']);
        exit;
    }

    // ============================================================
    // 3. SOFT DELETE THE DAILY WORK
    // ============================================================
    $updateStmt = $pdo->prepare("
        UPDATE dailywork 
        SET 
            is_deleted = ?,
            deleted_at = NOW()
        WHERE id = ?
    ");
    $updateStmt->execute([$is_deleted, $dailywork_id]);

    // ============================================================
    // 4. ADD TO RECYCLE BIN
    // ============================================================
    $recycleStmt = $pdo->prepare("
        INSERT INTO recycle_bin (
            item_id,
            item_type,
            item_name,
            deleted_by_department_id,
            deleted_at
        ) VALUES (
            ?, 'dailywork', ?, ?, NOW()
        )
    ");
    
    $recycleStmt->execute([
        $dailywork_id,
        $dailywork['project_name'] ?? 'Daily Work',
        $department_id
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Daily work record deleted successfully',
        'data' => [
            'dailywork_id' => $dailywork_id
        ]
    ]);

} catch(PDOException $e) {
    error_log("Delete daily work error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>