<?php
// get_reports.php - Fetch both original and sent reports (SORTED: Unviewed first)

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

error_reporting(0);
ini_set('display_errors', 0);

$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB Error']);
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
    // 1. Get original reports
    // ============================================================
    $stmt = $pdo->prepare("SELECT 
        *,
        'original' as source_type
        FROM reports 
        WHERE (department_id = ? OR sent_to_department = ?)
        AND (is_deleted = 0 OR is_deleted IS NULL)
        ORDER BY created_at DESC");
    $stmt->execute([$departmentId, $departmentId]);
    $originalReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($originalReports as $r) {
        // Check if this is a sent report (from another department)
        $isSentReport = isset($r['sent_to_department']) && $r['sent_to_department'] > 0 && 
                        isset($r['sent_from_department']) && $r['sent_from_department'] != $departmentId;
        
        // Check if unviewed
        $isUnviewed = false;
        if ($isSentReport) {
            if ($r['is_viewed_by_department'] == 0 || $r['is_viewed_by_department'] === null) {
                $isUnviewed = true;
            }
        }
        
        $r['is_sent_report'] = $isSentReport;
        $r['is_original'] = true;
        $r['is_unviewed'] = $isUnviewed;
        $r['_is_unviewed'] = $isUnviewed;
        $r['is_new'] = $isUnviewed;
        $r['sent_from_name'] = isset($r['sent_from_department']) ? departmentNames[$r['sent_from_department']] ?? '' : '';
        $reports[] = $r;
    }
    
    // ============================================================
    // 2. Get sent reports from sent_reports table
    // ============================================================
    $sentStmt = $pdo->prepare("SELECT 
        sr.*,
        'sent' as source_type,
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
        // Decode report data
        $reportData = [];
        if (isset($r['report_data']) && $r['report_data']) {
            $decoded = json_decode($r['report_data'], true);
            if ($decoded) {
                $reportData = $decoded;
            }
        }
        
        // Check if unviewed
        $isUnviewed = ($r['is_viewed'] == 0 || $r['is_viewed'] === null);
        
        $sentReport = [
            'id' => $r['id'],
            'original_report_id' => $r['original_report_id'],
            'title' => $r['report_title'] ?? $reportData['title'] ?? 'Sent Report',
            'period' => $r['report_period'] ?? $reportData['period'] ?? 'monthly',
            'content' => $reportData['content'] ?? 'No content available',
            'status' => 'sent',
            'department_id' => $r['to_department_id'],
            'created_at' => $r['sent_at'],
            'is_deleted' => $r['is_deleted'] ?? 0,
            'sent_from_department' => $r['from_department_id'],
            'sent_to_department' => $r['to_department_id'],
            'sent_count' => $r['sent_count'] ?? 0,
            'is_sent' => 1,
            'last_sent_at' => $r['last_sent_at'],
            'is_original' => false,
            'is_sent_report' => true,
            'sent_from_name' => $r['from_department_name'] ?? $r['sender_name'] ?? '',
            'sent_to_name' => $r['to_department_name'] ?? $r['receiver_name'] ?? '',
            'source_type' => 'sent',
            'sent_by' => $r['sent_by'] ?? 'System',
            'is_viewed_by_department' => $r['is_viewed'] ?? 0,
            'is_unviewed' => $isUnviewed,
            '_is_unviewed' => $isUnviewed,
            'is_new' => $isUnviewed,
            'viewed_at' => $r['viewed_at'] ?? null,
            'created_by' => $r['sent_by'] ?? $reportData['created_by'] ?? 'System'
        ];
        
        $reports[] = $sentReport;
    }
    
    // ============================================================
    // 3. GET SENT UPLOADED REPORTS (from sent_uploaded_reports)
    // ============================================================
    $sentUploadedStmt = $pdo->prepare("SELECT 
        sur.*,
        'sent_uploaded' as source_type,
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
        // Decode report data
        $reportData = [];
        if (isset($r['uploaded_report_data']) && $r['uploaded_report_data']) {
            $decoded = json_decode($r['uploaded_report_data'], true);
            if ($decoded) {
                $reportData = $decoded;
            }
        }
        
        $isUnviewed = ($r['is_viewed'] == 0 || $r['is_viewed'] === null);
        
        $sentUploadedReport = [
            'id' => $r['id'],
            'original_uploaded_report_id' => $r['original_uploaded_report_id'],
            'title' => $r['uploaded_report_title'] ?? $reportData['title'] ?? 'Sent Uploaded Report',
            'period' => $r['uploaded_report_period'] ?? $reportData['period'] ?? 'monthly',
            'description' => $reportData['description'] ?? '',
            'file_name' => $r['uploaded_report_file'] ?? $reportData['file_name'] ?? '',
            'file_path' => $reportData['file_path'] ?? $r['uploaded_report_file'] ?? '',
            'file_size' => $reportData['file_size'] ?? 0,
            'file_type' => $reportData['file_type'] ?? '',
            'uploaded_by' => $reportData['uploaded_by'] ?? $r['sent_by'] ?? 'System',
            'department_id' => $r['to_department_id'],
            'created_at' => $r['sent_at'],
            'is_deleted' => $r['is_deleted'] ?? 0,
            'sent_from_department' => $r['from_department_id'],
            'sent_to_department' => $r['to_department_id'],
            'sent_count' => $r['sent_count'] ?? 0,
            'is_sent' => 1,
            'last_sent_at' => $r['last_sent_at'],
            'is_original' => false,
            'is_sent_report' => true,
            'is_uploaded_report' => true,
            'sent_from_name' => $r['from_department_name'] ?? $r['sender_name'] ?? '',
            'sent_to_name' => $r['to_department_name'] ?? $r['receiver_name'] ?? '',
            'source_type' => 'sent_uploaded',
            'sent_by' => $r['sent_by'] ?? 'System',
            'is_viewed_by_department' => $r['is_viewed'] ?? 0,
            'is_unviewed' => $isUnviewed,
            '_is_unviewed' => $isUnviewed,
            'is_new' => $isUnviewed,
            'viewed_at' => $r['viewed_at'] ?? null
        ];
        
        $reports[] = $sentUploadedReport;
    }
    
    // ============================================================
    // 4. SORT: Unviewed (NEW) first, then by date
    // ============================================================
    usort($reports, function($a, $b) {
        // First: Unviewed reports come first
        $aUnviewed = isset($a['is_unviewed']) ? $a['is_unviewed'] : false;
        $bUnviewed = isset($b['is_unviewed']) ? $b['is_unviewed'] : false;
        
        if ($aUnviewed && !$bUnviewed) return -1;
        if (!$aUnviewed && $bUnviewed) return 1;
        
        // Second: Sort by date (newest first)
        $aDate = isset($a['created_at']) ? strtotime($a['created_at']) : 0;
        $bDate = isset($b['created_at']) ? strtotime($b['created_at']) : 0;
        return $bDate - $aDate;
    });
    
    // ============================================================
    // 5. Count unviewed reports
    // ============================================================
    $unviewedCount = 0;
    foreach ($reports as $r) {
        if (isset($r['is_unviewed']) && $r['is_unviewed']) {
            $unviewedCount++;
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $reports,
        'total' => count($reports),
        'original_count' => count($originalReports),
        'sent_count' => count($sentReports),
        'sent_uploaded_count' => count($sentUploadedReports),
        'unviewed_count' => $unviewedCount
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Query error: ' . $e->getMessage()]);
}
?>