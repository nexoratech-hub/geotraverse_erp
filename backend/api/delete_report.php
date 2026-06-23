<?php
// delete_report.php - Delete Added Reports, Sent Added Reports, and Sent Reports
// Supports: reports table + sent_reports table

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
    // 1. CHECK IF REPORT EXISTS IN reports TABLE (Added / Sent Added)
    // ============================================================
    $checkStmt = $pdo->prepare("
        SELECT id, title, department_id, sent_from_department, sent_to_department 
        FROM reports 
        WHERE id = ?
        AND (is_deleted = 0 OR is_deleted IS NULL)
        AND (deleted_by_department = 0 OR deleted_by_department IS NULL)
        AND (deleted_by_admin = 0 OR deleted_by_admin IS NULL)
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
        
        // Soft delete in reports table
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
        $itemType = 'report';
        $itemName = $report['title'];
        
        addToRecycleBin($pdo, $reportId, $itemType, $itemName, $deletedByDepartment, $deletedByAdmin);
        
        echo json_encode([
            'success' => true,
            'message' => 'Report moved to recycle bin',
            'source' => 'reports_table',
            'item_type' => $itemType
        ]);
        exit;
    }
    
    // ============================================================
    // 2. CHECK IF REPORT EXISTS IN sent_reports TABLE
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
        AND (deleted_by_department = 0 OR deleted_by_department IS NULL)
        AND (deleted_by_admin = 0 OR deleted_by_admin IS NULL)
    ");
    $checkSentStmt->execute([$reportId]);
    $sentReport = $checkSentStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($sentReport) {
        // Check permission - recipient or sender can delete
        $isRecipient = ($sentReport['to_department_id'] == $departmentId);
        $isSender = ($sentReport['from_department_id'] == $departmentId);
        
        if (!$isRecipient && !$isSender && $deletedByAdmin == 0) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit;
        }
        
        // ============================================================
        // SOFT DELETE IN sent_reports TABLE
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
        
        // ============================================================
        // ADD TO RECYCLE BIN - USE CORRECT ITEM_TYPE!
        // ============================================================
        $itemType = 'sent_report';  // <-- HII NI MUHIMU!
        $itemName = $sentReport['report_title'] ?? 'Sent Report';
        
        addToRecycleBin($pdo, $reportId, $itemType, $itemName, $deletedByDepartment, $deletedByAdmin);
        
        echo json_encode([
            'success' => true,
            'message' => 'Sent report moved to recycle bin',
            'source' => 'sent_reports_table',
            'item_type' => $itemType,
            'item_id' => $reportId,
            'item_name' => $itemName
        ]);
        exit;
    }
    
    // ============================================================
    // 3. REPORT NOT FOUND ANYWHERE
    // ============================================================
    echo json_encode(['success' => false, 'message' => 'Report not found']);
    
} catch(PDOException $e) {
    error_log('Delete report error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

// ============================================================
// HELPER FUNCTION: Add to recycle bin
// ============================================================
function addToRecycleBin($pdo, $itemId, $itemType, $itemName, $deletedByDepartment, $deletedByAdmin) {
    try {
        // ============================================================
        // VALIDATE item_type - HAKIKISHA IKO KWENYE ENUM
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
        
        // If item_type is empty or invalid, default to 'report'
        if (empty($itemType) || !in_array($itemType, $validTypes)) {
            // Try to detect from name
            if (strpos(strtolower($itemName), 'sent') !== false) {
                $itemType = 'sent_report';
            } else {
                $itemType = 'report';
            }
        }
        
        // Check if already in recycle bin
        $checkBinStmt = $pdo->prepare("
            SELECT id FROM recycle_bin 
            WHERE item_id = ? AND item_type = ?
        ");
        $checkBinStmt->execute([$itemId, $itemType]);
        
        if ($checkBinStmt->fetch()) {
            // Already in recycle bin, skip
            return true;
        }
        
        // Insert into recycle bin
        $stmt2 = $pdo->prepare("
            INSERT INTO recycle_bin 
            (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt2->execute([
            $itemId,
            $itemType,
            $itemName,
            $deletedByDepartment > 0 ? $deletedByDepartment : null,
            $deletedByAdmin
        ]);
        
        return true;
        
    } catch(PDOException $e) {
        error_log('Recycle bin error: ' . $e->getMessage());
        return false;
    }
}
?>