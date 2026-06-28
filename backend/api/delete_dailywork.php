<?php
// backend/api/delete_dailywork.php
// ============================================================
// SUPER ADMIN ONLY - Delete Daily Work Record
// ============================================================

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ============================================================
// DATABASE CONNECTION
// ============================================================
$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// ============================================================
// GET INPUT
// ============================================================
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['id']) || !isset($input['department_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields: id, department_id']);
    exit;
}

$dailyworkId = intval($input['id']);
$departmentId = intval($input['department_id']);
$deletedBy = isset($input['deleted_by']) ? $input['deleted_by'] : 'System';

// ============================================================
// CHECK IF USER IS SUPER ADMIN (department_id = 1)
// ============================================================
if ($departmentId !== 1) {
    echo json_encode([
        'success' => false, 
        'message' => '❌ You do not have permission to delete this daily work record. Only Super Admin can delete.',
        'error_code' => 'PERMISSION_DENIED'
    ]);
    exit;
}

try {
    // ============================================================
    // CHECK IF RECORD EXISTS
    // ============================================================
    $checkStmt = $pdo->prepare("SELECT id, work_description, project_name, department_id, is_deleted FROM dailywork WHERE id = ?");
    $checkStmt->execute([$dailyworkId]);
    $record = $checkStmt->fetch();

    if (!$record) {
        echo json_encode(['success' => false, 'message' => 'Daily work record not found or already deleted']);
        exit;
    }

    if ($record['is_deleted'] == 1) {
        echo json_encode(['success' => false, 'message' => 'Daily work record already deleted']);
        exit;
    }

    // ============================================================
    // SOFT DELETE THE DAILY WORK RECORD
    // ============================================================
    $stmt = $pdo->prepare("UPDATE dailywork SET 
                           is_deleted = 1,
                           deleted_at = NOW()
                           WHERE id = ?");
    $stmt->execute([$dailyworkId]);
    $deleted = $stmt->rowCount();

    if ($deleted > 0) {
        // ============================================================
        // ADD TO RECYCLE BIN
        // ============================================================
        try {
            $recycleQuery = "INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin, deleted_by_name, created_at) 
                            VALUES (?, 'dailywork', ?, ?, 1, ?, NOW())";
            $recycleStmt = $pdo->prepare($recycleQuery);
            $recycleStmt->execute([
                $dailyworkId,
                $record['work_description'] ?? $record['project_name'] ?? 'Daily Work',
                $departmentId,
                $deletedBy
            ]);
        } catch (Exception $e) {
            // Ignore recycle bin errors
        }

        echo json_encode([
            'success' => true,
            'message' => 'Daily work record deleted successfully',
            'data' => [
                'id' => $dailyworkId,
                'deleted_by' => $deletedBy,
                'department_id' => $departmentId
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete daily work record']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>