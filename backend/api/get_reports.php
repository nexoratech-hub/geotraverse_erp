<?php
// get_reports.php - Fetch ALL reports for department

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
    
    // ============================================================
    // 1. GET ADDED REPORTS - TUMIA COLUMN SAHIHI
    // ============================================================
    // Reports za department yenyewe
    $stmt = $pdo->prepare("
        SELECT 
            r.*,
            'added' as source_type,
            'added_report' as report_type
        FROM reports r
        WHERE r.department_id = ?
        AND (r.is_deleted = 0 OR r.is_deleted IS NULL)
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$departmentId]);
    $addedReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Reports zilizotumwa kwa department hii
    $stmt2 = $pdo->prepare("
        SELECT 
            r.*,
            'added' as source_type,
            'added_report' as report_type
        FROM reports r
        WHERE r.sent_to_department = ?
        AND (r.is_deleted = 0 OR r.is_deleted IS NULL)
        ORDER BY r.created_at DESC
    ");
    $stmt2->execute([$departmentId]);
    $sentToReports = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    // Merge reports
    $allAddedReports = array_merge($addedReports, $sentToReports);
    
    // Remove duplicates (if any)
    $uniqueAdded = [];
    $seenIds = [];
    foreach ($allAddedReports as $r) {
        if (!in_array($r['id'], $seenIds)) {
            $seenIds[] = $r['id'];
            $uniqueAdded[] = $r;
        }
    }
    
    foreach ($uniqueAdded as $r) {
        $sentFromDept = $r['sent_from_department'] ?? null;
        $sentToDept = $r['sent_to_department'] ?? null;
        
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
        
        $reports[] = $r;
    }
    
    // ============================================================
    // 2. GET UPLOADED REPORTS - TUMIA COLUMN SAHIHI
    // ============================================================
    // Uploaded reports za department yenyewe
    $uploadedStmt = $pdo->prepare("
        SELECT 
            ur.*,
            'uploaded' as source_type,
            'uploaded_report' as report_type
        FROM uploaded_reports ur
        WHERE ur.department_id = ?
        AND (ur.is_deleted = 0 OR ur.is_deleted IS NULL)
        ORDER BY ur.created_at DESC
    ");
    $uploadedStmt->execute([$departmentId]);
    $uploadedReports = $uploadedStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Uploaded reports zilizotumwa kwa department hii
    $uploadedStmt2 = $pdo->prepare("
        SELECT 
            ur.*,
            'uploaded' as source_type,
            'uploaded_report' as report_type
        FROM uploaded_reports ur
        WHERE ur.sent_to_department = ?
        AND (ur.is_deleted = 0 OR ur.is_deleted IS NULL)
        ORDER BY ur.created_at DESC
    ");
    $uploadedStmt2->execute([$departmentId]);
    $sentUploadedReports = $uploadedStmt2->fetchAll(PDO::FETCH_ASSOC);
    
    // Merge uploaded reports
    $allUploadedReports = array_merge($uploadedReports, $sentUploadedReports);
    
    // Remove duplicates
    $uniqueUploaded = [];
    $seenIds = [];
    foreach ($allUploadedReports as $r) {
        if (!in_array($r['id'], $seenIds)) {
            $seenIds[] = $r['id'];
            $uniqueUploaded[] = $r;
        }
    }
    
    foreach ($uniqueUploaded as $r) {
        $sentFromDept = $r['sent_from_department'] ?? null;
        $sentToDept = $r['sent_to_department'] ?? null;
        
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
    // 3. GET SENT REPORTS (from sent_reports table)
    // ============================================================
    $sentStmt = $pdo->prepare("
        SELECT 
            sr.*,
            'sent' as source_type,
            'sent_report' as report_type
        FROM sent_reports sr
        WHERE sr.to_department_id = ?
        AND (sr.is_deleted = 0 OR sr.is_deleted IS NULL)
        ORDER BY sr.sent_at DESC
    ");
    $sentStmt->execute([$departmentId]);
    $sentReports = $sentStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($sentReports as $r) {
        $reportData = json_decode($r['report_data'] ?? '{}', true);
        if (!is_array($reportData)) $reportData = [];
        
        $isUnviewed = ($r['is_viewed'] == 0 || $r['is_viewed'] === null || $r['is_viewed'] === '0');
        
        $senderName = $r['from_department_name'] ?? '';
        if (!$senderName && isset($r['from_department_id']) && isset($departmentNames[$r['from_department_id']])) {
            $senderName = $departmentNames[$r['from_department_id']];
        }
        
        $sentReport = [
            'id' => $r['id'],
            'original_report_id' => $r['original_report_id'] ?? $r['report_id'],
            'title' => $r['report_title'] ?? $reportData['title'] ?? 'Sent Report',
            'period' => $r['report_period'] ?? $reportData['period'] ?? 'monthly',
            'content' => $reportData['content'] ?? $r['report_content'] ?? 'No content available',
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
            'original_from_department_name' => $r['original_from_department_name'] ?? null
        ];
        
        $reports[] = $sentReport;
    }
    
    // ============================================================
    // 4. GET SENT UPLOADED REPORTS
    // ============================================================
    $sentUploadedStmt = $pdo->prepare("
        SELECT 
            sur.*,
            'sent_uploaded' as source_type,
            'sent_uploaded_report' as report_type
        FROM sent_uploaded_reports sur
        WHERE sur.to_department_id = ?
        AND (sur.is_deleted = 0 OR sur.is_deleted IS NULL)
        ORDER BY sur.sent_at DESC
    ");
    $sentUploadedStmt->execute([$departmentId]);
    $sentUploadedReports = $sentUploadedStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($sentUploadedReports as $r) {
        $reportData = json_decode($r['uploaded_report_data'] ?? '{}', true);
        if (!is_array($reportData)) $reportData = [];
        
        $isUnviewed = ($r['is_viewed'] == 0 || $r['is_viewed'] === null || $r['is_viewed'] === '0');
        
        $senderName = $r['from_department_name'] ?? '';
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
            'sent_to_name' => $r['to_department_name'] ?? '',
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
    // 5. SORT: Unviewed first, then by date
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
    // 6. Counts
    // ============================================================
    $unviewedCount = 0;
    $addedCount = 0;
    $uploadedCount = 0;
    $sentCount = 0;
    $sentUploadedCount = 0;
    
    foreach ($reports as $r) {
        if (isset($r['is_unviewed']) && $r['is_unviewed']) {
            $unviewedCount++;
        }
        
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