<?php
// delete_uploaded_report.php - Delete Uploaded Reports and Sent Uploaded Reports

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

if (!$data || empty($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'Uploaded report ID required']);
    exit;
}

$reportId = (int)$data['id'];
$departmentId = isset($data['department_id']) ? (int)$data['department_id'] : 0;
$isAdmin = isset($data['is_admin']) && $data['is_admin'] ? 1 : 0;
$deletedByDepartment = isset($data['deleted_by_department']) ? (int)$data['deleted_by_department'] : $departmentId;
$deletedByAdmin = isset($data['deleted_by_admin']) ? (int)$data['deleted_by_admin'] : $isAdmin;

try {
    // ============================================================
    // 1. CHECK IF REPORT EXISTS IN uploaded_reports TABLE
    // ============================================================
    $checkStmt = $pdo->prepare("
        SELECT id, title, department_id, sent_from_department, sent_to_department 
        FROM uploaded_reports 
        WHERE id = ?
        AND (is_deleted = 0 OR is_deleted IS NULL)
    ");
    $checkStmt->execute([$reportId]);
    $report = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($report) {
        // Check permission
        $isOwner = ($report['department_id'] == $departmentId);
        $isRecipient = ($report['sent_to_department'] == $departmentId);
        
        if (!$isOwner && !$isRecipient && $deletedByAdmin == 0) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit;
        }
        
        // Soft delete
        $stmt = $pdo->prepare("
            UPDATE uploaded_reports 
            SET 
                is_deleted = 1,
                deleted_by_department = :dept_deleted,
                deleted_by_admin = :admin_deleted,
                deleted_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            'dept_deleted' => $deletedByDepartment,
            'admin_deleted' => $deletedByAdmin,
            'id' => $reportId
        ]);
        
        // Add to recycle bin
        addToRecycleBin($pdo, $reportId, 'uploaded_report', $report['title'], $deletedByDepartment, $deletedByAdmin);
        
        echo json_encode([
            'success' => true,
            'message' => 'Uploaded report moved to recycle bin',
            'source' => 'uploaded_reports_table'
        ]);
        exit;
    }
    
    // ============================================================
    // 2. CHECK IF REPORT EXISTS IN sent_uploaded_reports TABLE
    // ============================================================
    $checkSentStmt = $pdo->prepare("
        SELECT 
            id, 
            report_title, 
            from_department_id, 
            to_department_id
        FROM sent_uploaded_reports 
        WHERE id = ?
        AND (is_deleted = 0 OR is_deleted IS NULL)
    ");
    $checkSentStmt->execute([$reportId]);
    $sentReport = $checkSentStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($sentReport) {
        // Check permission
        $isRecipient = ($sentReport['to_department_id'] == $departmentId);
        $isSender = ($sentReport['from_department_id'] == $departmentId);
        
        if (!$isRecipient && !$isSender && $deletedByAdmin == 0) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit;
        }
        
        // Soft delete
        $stmt = $pdo->prepare("
            UPDATE sent_uploaded_reports 
            SET 
                is_deleted = 1,
                deleted_by_department = :dept_deleted,
                deleted_by_admin = :admin_deleted,
                deleted_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            'dept_deleted' => $deletedByDepartment,
            'admin_deleted' => $deletedByAdmin,
            'id' => $reportId
        ]);
        
        // Add to recycle bin
        addToRecycleBin($pdo, $reportId, 'sent_uploaded_report', $sentReport['report_title'] ?? 'Sent Uploaded Report', $deletedByDepartment, $deletedByAdmin);
        
        echo json_encode([
            'success' => true,
            'message' => 'Sent uploaded report moved to recycle bin',
            'source' => 'sent_uploaded_reports_table'
        ]);
        exit;
    }
    
    echo json_encode(['success' => false, 'message' => 'Uploaded report not found']);
    
} catch(PDOException $e) {
    error_log('Delete uploaded report error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

// ============================================================
// HELPER FUNCTION: Add to recycle bin
// ============================================================
function addToRecycleBin($pdo, $itemId, $itemType, $itemName, $deletedByDepartment, $deletedByAdmin) {
    try {
        $checkBinStmt = $pdo->prepare("
            SELECT id FROM recycle_bin 
            WHERE item_id = ? AND item_type = ?
        ");
        $checkBinStmt->execute([$itemId, $itemType]);
        
        if (!$checkBinStmt->fetch()) {
            $stmt2 = $pdo->prepare("
                INSERT INTO recycle_bin 
                (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt2->execute([
                $itemId,
                $itemType,
                $itemName,
                $deletedByDepartment > 0 ? $deletedByDepartment : null,
                $deletedByAdmin
            ]);
        }
        return true;
    } catch(PDOException $e) {
        error_log('Recycle bin error: ' . $e->getMessage());
        return false;
    }
}
?>