<?php
// get_reports.php - Fetch BOTH added reports AND uploaded reports for department

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

error_reporting(0);
ini_set('display_errors', 0);

$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

// Department names cache
$departmentNames = [
    1 => 'Super Admin',
    2 => 'Finance',
    3 => 'Sales & Marketing',
    4 => 'Manager',
    5 => 'Secretary',
    6 => 'Bricks & Timber',
    7 => 'Aluminium',
    8 => 'Town Planning',
    9 => 'Architectural',
    10 => 'Survey',
    11 => 'Construction',
    12 => 'Hatimiliki'
];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB Connection Error: ' . $e->getMessage()]);
    exit;
}

$departmentId = isset($_GET['department_id']) ? (int)$_GET['department_id'] : 0;

if (!$departmentId) {
    echo json_encode(['success' => false, 'message' => 'Department ID required']);
    exit;
}

try {
    $reports = [];
    
    // ============================================================
    // 1. GET ADDED REPORTS (from reports table)
    //    - department_id = ? (reports zao wenyewe)
    //    - OR sent_to_department = ? (reports zilizotumwa kwazo)
    // ============================================================
    $stmt = $pdo->prepare("SELECT 
        *,
        'added' as source_type,
        'added_report' as report_type
        FROM reports 
        WHERE (
            department_id = ? 
            OR sent_to_department = ? 
            OR sent_to_dept = ?
        )
        AND (is_deleted = 0 OR is_deleted IS NULL)
        AND (deleted_by_admin = 0 OR deleted_by_admin IS NULL)
        ORDER BY created_at DESC");
    $stmt->execute([$departmentId, $departmentId, $departmentId]);
    $addedReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($addedReports as $r) {
        $sentFromDept = $r['sent_from_department'] ?? $r['sent_from_dept'] ?? null;
        $sentToDept = $r['sent_to_department'] ?? $r['sent_to_dept'] ?? null;
        
        // Check if this report was sent from another department
        $isSentReport = ($sentFromDept && $sentFromDept > 0 && $sentFromDept != $departmentId);
        
        // Check if unviewed (only for sent reports)
        $isUnviewed = false;
        if ($isSentReport) {
            $isViewed = $r['is_viewed_by_department'] ?? $r['is_viewed_by_dept'] ?? 1;
            if ($isViewed == 0 || $isViewed === null) {
                $isUnviewed = true;
            }
        }
        
        // Get sender name
        $senderName = '';
        if ($sentFromDept && isset($departmentNames[$sentFromDept])) {
            $senderName = $departmentNames[$sentFromDept];
        } elseif ($sentFromDept) {
            try {
                $deptStmt = $pdo->prepare("SELECT name FROM departments WHERE id = ?");
                $deptStmt->execute([$sentFromDept]);
                $senderName = $deptStmt->fetchColumn() ?: '';
            } catch(PDOException $e) { /* ignore */ }
        }
        
        $r['is_sent_report'] = $isSentReport;
        $r['is_original'] = true;
        $r['is_added_report'] = true;
        $r['is_uploaded_report'] = false;
        $r['is_unviewed'] = $isUnviewed;
        $r['_is_unviewed'] = $isUnviewed;
        $r['is_new'] = $isUnviewed;
        $r['sent_from_name'] = $senderName;
        $r['source_type'] = 'added';
        $r['report_type'] = 'added';
        
        $reports[] = $r;
    }
    
    // ============================================================
    // 2. GET UPLOADED REPORTS (from uploaded_reports table)
    //    - department_id = ? (uploaded reports zao wenyewe)
    //    - OR sent_to_department = ? (uploaded reports zilizotumwa kwazo)
    // ============================================================
    $uploadedStmt = $pdo->prepare("SELECT 
        ur.*,
        'uploaded' as source_type,
        'uploaded_report' as report_type,
        ur.created_at,
        ur.file_name,
        ur.file_path,
        ur.file_size,
        ur.file_type,
        ur.uploaded_by,
        ur.description
        FROM uploaded_reports ur
        WHERE (
            ur.department_id = ? 
            OR ur.sent_to_department = ? 
            OR ur.sent_to_dept = ?
        )
        AND (ur.is_deleted = 0 OR ur.is_deleted IS NULL)
        AND (ur.deleted_by_admin = 0 OR ur.deleted_by_admin IS NULL)
        ORDER BY ur.created_at DESC");
    $uploadedStmt->execute([$departmentId, $departmentId, $departmentId]);
    $uploadedReports = $uploadedStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($uploadedReports as $r) {
        $sentFromDept = $r['sent_from_department'] ?? $r['sent_from_dept'] ?? null;
        $sentToDept = $r['sent_to_department'] ?? $r['sent_to_dept'] ?? null;
        
        // Check if this uploaded report was sent from another department
        $isSentReport = ($sentFromDept && $sentFromDept > 0 && $sentFromDept != $departmentId);
        
        // Check if unviewed (only for sent reports)
        $isUnviewed = false;
        if ($isSentReport) {
            $isViewed = $r['is_viewed_by_department'] ?? $r['is_viewed_by_dept'] ?? 1;
            if ($isViewed == 0 || $isViewed === null) {
                $isUnviewed = true;
            }
        }
        
        // Get sender name
        $senderName = '';
        if ($sentFromDept && isset($departmentNames[$sentFromDept])) {
            $senderName = $departmentNames[$sentFromDept];
        } elseif ($sentFromDept) {
            try {
                $deptStmt = $pdo->prepare("SELECT name FROM departments WHERE id = ?");
                $deptStmt->execute([$sentFromDept]);
                $senderName = $deptStmt->fetchColumn() ?: '';
            } catch(PDOException $e) { /* ignore */ }
        }
        
        $r['is_sent_report'] = $isSentReport;
        $r['is_original'] = false;
        $r['is_added_report'] = false;
        $r['is_uploaded_report'] = true;
        $r['is_unviewed'] = $isUnviewed;
        $r['_is_unviewed'] = $isUnviewed;
        $r['is_new'] = $isUnviewed;
        $r['sent_from_name'] = $senderName;
        $r['source_type'] = 'uploaded';
        $r['report_type'] = 'uploaded';
        
        $reports[] = $r;
    }
    
    // ============================================================
    // 3. GET SENT REPORTS (from sent_reports table) - For forwarded reports
    // ============================================================
    $sentStmt = $pdo->prepare("SELECT 
        sr.*,
        'sent' as source_type,
        'sent_report' as report_type,
        sr.sent_at as created_at,
        sr.from_department_name as sender_name,
        sr.to_department_name as receiver_name
        FROM sent_reports sr
        WHERE sr.to_department_id = ?
        AND (sr.is_deleted = 0 OR sr.is_deleted IS NULL)
        ORDER BY sr.sent_at DESC");
    $sentStmt->execute([$departmentId]);
    $sentReports = $sentStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($sentReports as $r) {
        $reportData = json_decode($r['report_data'] ?? '{}', true);
        if (!is_array($reportData)) $reportData = [];
        
        $isUnviewed = ($r['is_viewed'] == 0 || $r['is_viewed'] === null);
        
        $senderName = $r['from_department_name'] ?? $r['sender_name'] ?? '';
        if (!$senderName && isset($r['from_department_id']) && isset($departmentNames[$r['from_department_id']])) {
            $senderName = $departmentNames[$r['from_department_id']];
        }
        
        $sentReport = [
            'id' => $r['id'],
            'original_report_id' => $r['original_report_id'],
            'title' => $r['report_title'] ?? $reportData['title'] ?? 'Sent Report',
            'period' => $r['report_period'] ?? $reportData['period'] ?? 'monthly',
            'content' => $reportData['content'] ?? 'No content available',
            'status' => $r['report_status'] ?? $reportData['status'] ?? 'sent',
            'department_id' => $r['to_department_id'],
            'created_at' => $r['sent_at'],
            'updated_at' => $r['last_sent_at'] ?? $r['sent_at'],
            'is_deleted' => $r['is_deleted'] ?? 0,
            'sent_from_department' => $r['from_department_id'],
            'sent_to_department' => $r['to_department_id'],
            'sent_count' => $r['sent_count'] ?? 0,
            'is_sent' => 1,
            'last_sent_at' => $r['last_sent_at'],
            'is_original' => false,
            'is_sent_report' => true,
            'is_added_report' => false,
            'is_uploaded_report' => false,
            'sent_from_name' => $senderName,
            'sent_to_name' => $r['to_department_name'] ?? $r['receiver_name'] ?? '',
            'source_type' => 'sent',
            'report_type' => 'sent',
            'sent_by' => $r['sent_by'] ?? 'System',
            'is_viewed_by_department' => $r['is_viewed'] ?? 0,
            'is_unviewed' => $isUnviewed,
            '_is_unviewed' => $isUnviewed,
            'is_new' => $isUnviewed,
            'viewed_at' => $r['viewed_at'] ?? null,
            'created_by' => $r['sent_by'] ?? $reportData['created_by'] ?? 'System',
            'original_from_department_id' => $r['original_from_department_id'] ?? null,
            'original_from_department_name' => $r['original_from_department_name'] ?? null
        ];
        
        $reports[] = $sentReport;
    }
    
    // ============================================================
    // 4. GET SENT UPLOADED REPORTS (from sent_uploaded_reports)
    // ============================================================
    $sentUploadedStmt = $pdo->prepare("SELECT 
        sur.*,
        'sent_uploaded' as source_type,
        'sent_uploaded_report' as report_type,
        sur.sent_at as created_at,
        sur.from_department_name as sender_name,
        sur.to_department_name as receiver_name
        FROM sent_uploaded_reports sur
        WHERE sur.to_department_id = ?
        AND (sur.is_deleted = 0 OR sur.is_deleted IS NULL)
        ORDER BY sur.sent_at DESC");
    $sentUploadedStmt->execute([$departmentId]);
    $sentUploadedReports = $sentUploadedStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($sentUploadedReports as $r) {
        $reportData = json_decode($r['uploaded_report_data'] ?? '{}', true);
        if (!is_array($reportData)) $reportData = [];
        
        $isUnviewed = ($r['is_viewed'] == 0 || $r['is_viewed'] === null);
        
        $senderName = $r['from_department_name'] ?? $r['sender_name'] ?? '';
        if (!$senderName && isset($r['from_department_id']) && isset($departmentNames[$r['from_department_id']])) {
            $senderName = $departmentNames[$r['from_department_id']];
        }
        
        $filePath = $r['uploaded_report_file'] ?? $reportData['file_path'] ?? $reportData['file_name'] ?? '';
        $fileName = $r['uploaded_report_file'] ?? $reportData['file_name'] ?? '';
        
        $sentUploadedReport = [
            'id' => $r['id'],
            'original_uploaded_report_id' => $r['original_uploaded_report_id'],
            'title' => $r['uploaded_report_title'] ?? $reportData['title'] ?? 'Sent Uploaded Report',
            'period' => $r['uploaded_report_period'] ?? $reportData['period'] ?? 'monthly',
            'description' => $reportData['description'] ?? '',
            'content' => $reportData['description'] ?? '',
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_size' => $reportData['file_size'] ?? 0,
            'file_type' => $reportData['file_type'] ?? '',
            'uploaded_by' => $reportData['uploaded_by'] ?? $r['sent_by'] ?? 'System',
            'department_id' => $r['to_department_id'],
            'created_at' => $r['sent_at'],
            'updated_at' => $r['last_sent_at'] ?? $r['sent_at'],
            'is_deleted' => $r['is_deleted'] ?? 0,
            'sent_from_department' => $r['from_department_id'],
            'sent_to_department' => $r['to_department_id'],
            'sent_count' => $r['sent_count'] ?? 0,
            'is_sent' => 1,
            'last_sent_at' => $r['last_sent_at'],
            'is_original' => false,
            'is_sent_report' => true,
            'is_added_report' => false,
            'is_uploaded_report' => true,
            'sent_from_name' => $senderName,
            'sent_to_name' => $r['to_department_name'] ?? $r['receiver_name'] ?? '',
            'source_type' => 'sent_uploaded',
            'report_type' => 'sent_uploaded',
            'sent_by' => $r['sent_by'] ?? 'System',
            'is_viewed_by_department' => $r['is_viewed'] ?? 0,
            'is_unviewed' => $isUnviewed,
            '_is_unviewed' => $isUnviewed,
            'is_new' => $isUnviewed,
            'viewed_at' => $r['viewed_at'] ?? null,
            'created_by' => $r['sent_by'] ?? $reportData['created_by'] ?? 'System',
            'original_from_department_id' => $r['original_from_department_id'] ?? null,
            'original_from_department_name' => $r['original_from_department_name'] ?? null
        ];
        
        $reports[] = $sentUploadedReport;
    }
    
    // ============================================================
    // 5. SORT: Unviewed (NEW) first, then by date (newest first)
    // ============================================================
    usort($reports, function($a, $b) {
        // First: Unviewed reports come first
        $aUnviewed = isset($a['is_unviewed']) ? (bool)$a['is_unviewed'] : false;
        $bUnviewed = isset($b['is_unviewed']) ? (bool)$b['is_unviewed'] : false;
        
        if ($aUnviewed && !$bUnviewed) return -1;
        if (!$aUnviewed && $bUnviewed) return 1;
        
        // Second: Sort by date (newest first)
        $aDate = isset($a['created_at']) ? strtotime($a['created_at']) : 0;
        $bDate = isset($b['created_at']) ? strtotime($b['created_at']) : 0;
        return $bDate - $aDate;
    });
    
    // ============================================================
    // 6. Count unviewed reports
    // ============================================================
    $unviewedCount = 0;
    foreach ($reports as $r) {
        if (isset($r['is_unviewed']) && $r['is_unviewed']) {
            $unviewedCount++;
        }
    }
    
    // Count by type
    $addedCount = 0;
    $uploadedCount = 0;
    $sentCount = 0;
    $sentUploadedCount = 0;
    
    foreach ($reports as $r) {
        $type = $r['source_type'] ?? '';
        if ($type === 'added') $addedCount++;
        elseif ($type === 'uploaded') $uploadedCount++;
        elseif ($type === 'sent') $sentCount++;
        elseif ($type === 'sent_uploaded') $sentUploadedCount++;
    }
    
    // ============================================================
    // 7. Return response
    // ============================================================
    echo json_encode([
        'success' => true,
        'data' => $reports,
        'total' => count($reports),
        'added_count' => $addedCount,
        'uploaded_count' => $uploadedCount,
        'sent_count' => $sentCount,
        'sent_uploaded_count' => $sentUploadedCount,
        'unviewed_count' => $unviewedCount,
        'department_id' => $departmentId
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Query error: ' . $e->getMessage()
    ]);
}
?>