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
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
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

$report_id = isset($data['id']) ? intval($data['id']) : 0;
$department_id = isset($data['department_id']) ? intval($data['department_id']) : 1;

if ($report_id == 0) {
    echo json_encode(['success' => false, 'message' => 'Report ID required']);
    exit;
}

try {
    // ============================================================
    // 1. CHECK IF REPORT EXISTS
    // ============================================================
    $stmt = $pdo->prepare("SELECT id, title FROM uploaded_reports WHERE id = ?");
    $stmt->execute([$report_id]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$report) {
        echo json_encode(['success' => false, 'message' => 'Uploaded report not found']);
        exit;
    }

    // ============================================================
    // 2. UPDATE is_deleted
    // ============================================================
    $updateStmt = $pdo->prepare("
        UPDATE uploaded_reports 
        SET is_deleted = 1, deleted_at = NOW() 
        WHERE id = ?
    ");
    $updateStmt->execute([$report_id]);

    // ============================================================
    // 3. ADD TO RECYCLE BIN
    // ============================================================
    try {
        $recycleStmt = $pdo->prepare("
            INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_at, is_restored)
            VALUES (?, 'uploaded_report', ?, ?, NOW(), 0)
        ");
        $recycleStmt->execute([$report_id, $report['title'], $department_id]);
    } catch(PDOException $e) {
        // Recycle bin might not exist or have different columns - ignore
        error_log("Recycle bin insert failed: " . $e->getMessage());
    }

    // ============================================================
    // 4. RETURN SUCCESS - CLEAN JSON
    // ============================================================
    echo json_encode([
        'success' => true,
        'message' => 'Uploaded report deleted successfully',
        'data' => ['report_id' => $report_id]
    ]);

} catch(PDOException $e) {
    error_log("Delete uploaded report error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}
?>