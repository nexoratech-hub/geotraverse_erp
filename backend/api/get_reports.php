<?php
// get_reports.php - Fetch ADDED REPORTS + SENT ADDED REPORTS + SENT REPORTS

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');

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
    $addedReportIds = [];
    
    // ============================================================
    // 1. GET ADDED REPORTS (department_id = ?)
    // ============================================================
    $stmt = $pdo->prepare("
        SELECT 
            r.*,
            'added' as source_type,
            'added_report' as report_type,
            r.created_at
        FROM reports r
        WHERE r.department_id = ?
        AND (r.is_deleted = 0 OR r.is_deleted IS NULL)
        AND (r.deleted_by_department = 0 OR r.deleted_by_department IS NULL)
        AND (r.deleted_by_admin = 0 OR r.deleted_by_admin IS NULL)
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$departmentId]);
    $addedReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($addedReports as $r) {
        $addedReportIds[] = $r['id'];
        
        $sentFromDept = $r['sent_from_department'] ?? null;
        $isSentReport = ($sentFromDept && $sentFromDept > 0 && $sentFromDept != $departmentId);
        
        $isUnviewed = false;
        if ($isSentReport) {
            $isViewed = $r['is_viewed_by_department'] ?? 1;
            if ($isViewed == 0 || $isViewed === null || $isViewed === '0') {
                $isUnviewed = true;
            }
        }
        
        $senderName = '';
        if ($sentFromDept && isset($departmentNames[$sentFromDept])) {
            $senderName = $departmentNames[$sentFromDept];
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
        $r['is_sent_record'] = false;
        
        $reports[] = $r;
    }
    
    // ============================================================
    // 2. GET SENT ADDED REPORTS (sent_to_department = ?)
    // ============================================================
    $stmt2 = $pdo->prepare("
        SELECT 
            r.*,
            'added' as source_type,
            'added_report' as report_type,
            r.created_at
        FROM reports r
        WHERE r.sent_to_department = ?
        AND (r.is_deleted = 0 OR r.is_deleted IS NULL)
        AND (r.deleted_by_department = 0 OR r.deleted_by_department IS NULL)
        AND (r.deleted_by_admin = 0 OR r.deleted_by_admin IS NULL)
        ORDER BY r.created_at DESC
    ");
    $stmt2->execute([$departmentId]);
    $sentToReports = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($sentToReports as $r) {
        if (in_array($r['id'], $addedReportIds)) {
            continue;
        }
        $addedReportIds[] = $r['id'];
        
        $sentFromDept = $r['sent_from_department'] ?? null;
        $isSentReport = ($sentFromDept && $sentFromDept > 0 && $sentFromDept != $departmentId);
        
        $isUnviewed = false;
        if ($isSentReport) {
            $isViewed = $r['is_viewed_by_department'] ?? 1;
            if ($isViewed == 0 || $isViewed === null || $isViewed === '0') {
                $isUnviewed = true;
            }
        }
        
        $senderName = '';
        if ($sentFromDept && isset($departmentNames[$sentFromDept])) {
            $senderName = $departmentNames[$sentFromDept];
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
        $r['is_sent_record'] = false;
        
        $reports[] = $r;
    }
    
    // ============================================================
    // 3. GET SENT REPORTS (FROM sent_reports TABLE)
    //    ✅ SASA INAANGAZIA deleted_by_department na deleted_by_admin
    // ============================================================
    $sentStmt = $pdo->prepare("
        SELECT 
            sr.*,
            'sent' as source_type,
            'sent_report' as report_type,
            sr.sent_at as created_at
        FROM sent_reports sr
        WHERE sr.to_department_id = ?
        AND (sr.is_deleted = 0 OR sr.is_deleted IS NULL)
        AND (sr.deleted_by_department = 0 OR sr.deleted_by_department IS NULL)
        AND (sr.deleted_by_admin = 0 OR sr.deleted_by_admin IS NULL)
        ORDER BY sr.sent_at DESC
    ");
    $sentStmt->execute([$departmentId]);
    $sentReports = $sentStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($sentReports as $r) {
        $reportData = json_decode($r['report_data'] ?? '{}', true);
        if (!is_array($reportData)) $reportData = [];
        
        // Check if this sent report already exists
        $exists = false;
        foreach ($reports as $existing) {
            if (isset($existing['id']) && $existing['id'] == $r['id']) {
                $exists = true;
                break;
            }
            if (isset($existing['original_report_id']) && $existing['original_report_id'] == $r['original_report_id']) {
                $exists = true;
                break;
            }
        }
        
        if (!$exists) {
            $isUnviewed = ($r['is_viewed'] == 0 || $r['is_viewed'] === null || $r['is_viewed'] === '0');
            
            $senderName = $r['from_department_name'] ?? '';
            if (!$senderName && isset($r['from_department_id']) && isset($departmentNames[$r['from_department_id']])) {
                $senderName = $departmentNames[$r['from_department_id']];
            }
            
            $sentReport = [
                'id' => $r['id'],
                'original_report_id' => $r['original_report_id'] ?? $r['report_id'] ?? null,
                'title' => $r['report_title'] ?? $reportData['title'] ?? 'Sent Report',
                'period' => $r['report_period'] ?? $reportData['period'] ?? 'monthly',
                'content' => $reportData['content'] ?? $r['report_content'] ?? 'No content available',
                'status' => $r['report_status'] ?? $reportData['status'] ?? 'sent',
                'department_id' => $r['to_department_id'],
                'created_at' => $r['sent_at'],
                'updated_at' => $r['last_sent_at'] ?? $r['sent_at'],
                'is_deleted' => $r['is_deleted'] ?? 0,
                'deleted_by_department' => $r['deleted_by_department'] ?? 0,
                'deleted_by_admin' => $r['deleted_by_admin'] ?? 0,
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
                'sent_to_name' => $r['to_department_name'] ?? '',
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
                'original_from_department_name' => $r['original_from_department_name'] ?? null,
                'is_sent_record' => true,
                'report_data' => $reportData
            ];
            
            $reports[] = $sentReport;
        }
    }
    
    // ============================================================
    // 4. SORT: Unviewed FIRST, then by created_at DESC
    // ============================================================
    usort($reports, function($a, $b) {
        $aUnviewed = isset($a['is_unviewed']) ? (bool)$a['is_unviewed'] : false;
        $bUnviewed = isset($b['is_unviewed']) ? (bool)$b['is_unviewed'] : false;
        
        if ($aUnviewed && !$bUnviewed) return -1;
        if (!$aUnviewed && $bUnviewed) return 1;
        
        $aDate = isset($a['created_at']) ? strtotime($a['created_at']) : 0;
        $bDate = isset($b['created_at']) ? strtotime($b['created_at']) : 0;
        return $bDate - $aDate;
    });
    
    // ============================================================
    // 5. COUNTS
    // ============================================================
    $unviewedCount = 0;
    $totalCount = count($reports);
    $addedCount = 0;
    $sentCount = 0;
    
    foreach ($reports as $r) {
        if (isset($r['is_unviewed']) && $r['is_unviewed']) {
            $unviewedCount++;
        }
        
        $type = $r['source_type'] ?? '';
        if ($type === 'added') {
            $addedCount++;
        } elseif ($type === 'sent') {
            $sentCount++;
        }
    }
    
    // ============================================================
    // 6. RETURN RESPONSE
    // ============================================================
    echo json_encode([
        'success' => true,
        'data' => $reports,
        'total' => $totalCount,
        'added_count' => $addedCount,
        'sent_count' => $sentCount,
        'unviewed_count' => $unviewedCount,
        'department_id' => $departmentId,
        'debug' => [
            'added_reports_count' => count($addedReports),
            'sent_to_reports_count' => count($sentToReports),
            'sent_reports_count' => count($sentReports)
        ]
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Query error: ' . $e->getMessage()
    ]);
}
?>