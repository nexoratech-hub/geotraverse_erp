<?php
// delete_report.php - Delete both Added Reports and Sent Added Reports
// Works for: 
//   1. Added reports (reports table)
//   2. Sent added reports (reports table where sent_to_department = ?)

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

if (!$data || empty($data['report_id'])) {
    echo json_encode(['success' => false, 'message' => 'Report ID required']);
    exit;
}

$reportId = (int)$data['report_id'];
$departmentId = isset($data['department_id']) ? (int)$data['department_id'] : 0;
$isAdmin = isset($data['is_admin']) && $data['is_admin'] ? 1 : 0;
$deletedByDepartment = isset($data['deleted_by_department']) ? (int)$data['deleted_by_department'] : $departmentId;
$deletedByAdmin = isset($data['deleted_by_admin']) ? (int)$data['deleted_by_admin'] : $isAdmin;

try {
    // ============================================================
    // CHECK IF REPORT EXISTS IN reports TABLE
    // ============================================================
    $checkStmt = $pdo->prepare("
        SELECT id, title, department_id, sent_from_department, sent_to_department 
        FROM reports 
        WHERE id = ?
    ");
    $checkStmt->execute([$reportId]);
    $report = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    // ============================================================
    // CASE 1: Report exists in reports table (Added Report or Sent Added Report)
    // ============================================================
    if ($report) {
        // Check if this report belongs to this department OR was sent to this department
        $isOwner = ($report['department_id'] == $departmentId);
        $isRecipient = ($report['sent_to_department'] == $departmentId);
        
        if (!$isOwner && !$isRecipient && $deletedByAdmin == 0) {
            echo json_encode([
                'success' => false, 
                'message' => 'You do not have permission to delete this report'
            ]);
            exit;
        }
        
        // Update report as deleted
        $stmt = $pdo->prepare("
            UPDATE reports 
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
        $checkBinStmt = $pdo->prepare("
            SELECT id FROM recycle_bin 
            WHERE item_id = ? AND item_type = 'report'
        ");
        $checkBinStmt->execute([$reportId]);
        
        if (!$checkBinStmt->fetch()) {
            $stmt2 = $pdo->prepare("
                INSERT INTO recycle_bin 
                (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin) 
                VALUES (?, 'report', ?, ?, ?)
            ");
            $stmt2->execute([
                $reportId,
                $report['title'],
                $deletedByDepartment > 0 ? $deletedByDepartment : null,
                $deletedByAdmin
            ]);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Report moved to recycle bin',
            'report_id' => $reportId,
            'source' => 'reports_table',
            'is_owner' => $isOwner,
            'is_recipient' => $isRecipient
        ]);
        exit;
    }
    
    // ============================================================
    // CASE 2: Report not in reports table - Check sent_reports table
    // ============================================================
    $checkSentStmt = $pdo->prepare("
        SELECT 
            id, 
            report_title, 
            from_department_id, 
            to_department_id,
            original_report_id
        FROM sent_reports 
        WHERE id = ?
        AND (is_deleted = 0 OR is_deleted IS NULL)
    ");
    $checkSentStmt->execute([$reportId]);
    $sentReport = $checkSentStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($sentReport) {
        // Check if this sent report was sent to this department
        $isRecipient = ($sentReport['to_department_id'] == $departmentId);
        $isSender = ($sentReport['from_department_id'] == $departmentId);
        
        if (!$isRecipient && !$isSender && $deletedByAdmin == 0) {
            echo json_encode([
                'success' => false, 
                'message' => 'You do not have permission to delete this sent report'
            ]);
            exit;
        }
        
        // ============================================================
        // UPDATE sent_reports - Soft delete
        // ============================================================
        $stmt = $pdo->prepare("
            UPDATE sent_reports 
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
        $checkBinStmt = $pdo->prepare("
            SELECT id FROM recycle_bin 
            WHERE item_id = ? AND item_type = 'sent_report'
        ");
        $checkBinStmt->execute([$reportId]);
        
        if (!$checkBinStmt->fetch()) {
            $stmt2 = $pdo->prepare("
                INSERT INTO recycle_bin 
                (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin) 
                VALUES (?, 'sent_report', ?, ?, ?)
            ");
            $stmt2->execute([
                $reportId,
                $sentReport['report_title'] ?? 'Sent Report',
                $deletedByDepartment > 0 ? $deletedByDepartment : null,
                $deletedByAdmin
            ]);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Sent report moved to recycle bin',
            'report_id' => $reportId,
            'source' => 'sent_reports_table',
            'is_recipient' => $isRecipient,
            'is_sender' => $isSender
        ]);
        exit;
    }
    
    // ============================================================
    // CASE 3: Report not found anywhere
    // ============================================================
    echo json_encode([
        'success' => false,
        'message' => 'Report not found'
    ]);
    
} catch(PDOException $e) {
    error_log('Delete report error: ' . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>