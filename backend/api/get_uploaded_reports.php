<?php
// backend/api/get_uploaded_reports.php
// Returns: ORIGINAL (owned) + RECEIVED COPIES only
// Each department sees only ONE copy per report

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

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
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;
$all = isset($_GET['all']) ? intval($_GET['all']) : 0;

if ($department_id <= 0 && $all == 0) {
    echo json_encode(['success' => false, 'message' => 'Department ID required']);
    exit;
}

function sendJson($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    $reports = [];
    $seenIds = [];

    // ============================================================
    // 1. ORIGINAL REPORTS (from uploaded_reports)
    // ============================================================
    $query = "SELECT 
                r.*,
                'original' as source_type,
                0 as is_received
              FROM uploaded_reports r
              WHERE r.is_original = 1
                AND r.is_deleted = 0
                AND r.department_id = ?
              ORDER BY r.id DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$department_id]);
    
    while ($row = $stmt->fetch()) {
        $row['_is_sent_by_me'] = false;
        $row['_is_received_by_me'] = false;
        $row['_is_unviewed'] = false;
        $row['is_sent'] = ($row['sent_count'] > 0) ? 1 : 0;
        $row['_display_type'] = 'Original';
        $row['_display_icon'] = '📁';
        $row['_is_original'] = true;
        $row['_is_copy'] = false;
        $reports[] = $row;
        $seenIds[$row['id']] = true;
    }

    // ============================================================
    // 2. RECEIVED COPIES (from uploaded_reports)
    // ============================================================
    // SHOW: Only copies received by this department (sent_to_department = department_id)
    // DO NOT SHOW: Copies sent by this department
    // ============================================================
    $query = "SELECT 
                r.*,
                'copy' as source_type,
                1 as is_received,
                r.sent_from_department as sent_from_name,
                d1.name as from_dept_name,
                d2.name as to_dept_name
              FROM uploaded_reports r
              LEFT JOIN departments d1 ON d1.id = r.sent_from_department
              LEFT JOIN departments d2 ON d2.id = r.sent_to_department
              WHERE r.is_sent_copy = 1
                AND r.is_deleted = 0
                AND r.sent_to_department = ?
                AND r.department_id = ?
              ORDER BY r.id DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$department_id, $department_id]);
    
    while ($row = $stmt->fetch()) {
        if (isset($seenIds[$row['id']])) {
            continue;
        }
        
        if ($row['sent_from_department']) {
            $row['sent_from_name'] = $row['from_dept_name'] ?? 'Unknown';
        }
        $row['to_dept_name'] = $row['to_dept_name'] ?? 'Unknown';
        
        $row['_is_unviewed'] = ($row['is_viewed_by_department'] == 0 || $row['is_viewed_by_department'] === null);
        $row['_is_sent_by_me'] = false;
        $row['_is_received_by_me'] = true;
        $row['is_sent'] = 1;
        $row['_display_type'] = '📨 Received Copy';
        $row['_display_icon'] = '📨';
        $row['_is_original'] = false;
        $row['_is_copy'] = true;
        $reports[] = $row;
        $seenIds[$row['id']] = true;
    }

    // ============================================================
    // 3. SENT HISTORY (from sent_uploaded_reports)
    // ============================================================
    // SHOW: History of reports sent by this department
    // DO NOT SHOW AS COPIES - just history
    // ============================================================
    $query = "SELECT 
                sr.id as sent_id,
                sr.original_uploaded_report_id,
                sr.uploaded_report_data,
                sr.from_department_id,
                sr.to_department_id,
                sr.sent_by,
                sr.sent_at,
                sr.is_viewed,
                sr.viewed_at,
                sr.is_deleted,
                sr.sent_count,
                sr.is_sent,
                sr.last_sent_at,
                sr.from_department_name,
                sr.to_department_name,
                sr.uploaded_report_title,
                sr.uploaded_report_period,
                sr.uploaded_report_file,
                d1.name as from_dept_name,
                d2.name as to_dept_name,
                'sent_history' as source_type
              FROM sent_uploaded_reports sr
              LEFT JOIN departments d1 ON d1.id = sr.from_department_id
              LEFT JOIN departments d2 ON d2.id = sr.to_department_id
              WHERE sr.from_department_id = ?
                AND sr.is_deleted = 0
              ORDER BY sr.sent_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$department_id]);
    
    while ($row = $stmt->fetch()) {
        if (isset($seenIds[$row['original_uploaded_report_id']])) {
            continue;
        }
        
        $reportData = [];
        if (!empty($row['uploaded_report_data'])) {
            $reportData = json_decode($row['uploaded_report_data'], true);
            if (!is_array($reportData)) {
                $reportData = [];
            }
        }
        
        $report = [
            'id' => $row['sent_id'],
            'sent_id' => $row['sent_id'],
            'original_uploaded_report_id' => $row['original_uploaded_report_id'],
            'title' => $row['uploaded_report_title'] ?? $reportData['title'] ?? 'Untitled Sent Report',
            'description' => $reportData['description'] ?? '',
            'file_name' => $row['uploaded_report_file'] ?? $reportData['file_name'] ?? 'document.pdf',
            'file_path' => $reportData['file_path'] ?? '',
            'file_size' => $reportData['file_size'] ?? 0,
            'file_type' => $reportData['file_type'] ?? '',
            'period' => $row['uploaded_report_period'] ?? $reportData['period'] ?? 'monthly',
            'uploaded_by' => $reportData['uploaded_by'] ?? $row['sent_by'] ?? 'System',
            'created_at' => $reportData['created_at'] ?? $row['sent_at'],
            'department_id' => $row['from_department_id'],
            'sent_from_department' => $row['from_department_id'],
            'sent_to_department' => $row['to_department_id'],
            'sent_from_name' => $row['from_dept_name'] ?? $row['from_department_name'] ?? 'Unknown',
            'sent_to_name' => $row['to_dept_name'] ?? $row['to_department_name'] ?? 'Unknown',
            'sent_at' => $row['sent_at'],
            'sent_count' => $row['sent_count'] ?? 1,
            'is_viewed_by_department' => $row['is_viewed'] ?? 0,
            'is_sent' => 1,
            'is_original' => 0,
            'is_sent_copy' => 0,
            'source_type' => $row['source_type'],
            '_is_sent_by_me' => true,
            '_is_received_by_me' => false,
            '_is_unviewed' => false,
            '_is_sent_history' => true,
            '_is_copy' => false,
            '_is_original' => false,
            '_display_type' => '📤 Sent History',
            '_display_icon' => '📤',
            'is_sent_copy' => 0
        ];
        
        if (!isset($seenIds[$report['id']]) && !isset($seenIds[$report['original_uploaded_report_id']])) {
            $reports[] = $report;
            $seenIds[$report['id']] = true;
            $seenIds[$report['original_uploaded_report_id']] = true;
        }
    }

    // ============================================================
    // 4. REMOVE DUPLICATES
    // ============================================================
    $uniqueReports = [];
    $uniqueIds = [];
    foreach ($reports as $report) {
        $id = $report['id'] ?? $report['sent_id'] ?? 0;
        if (!in_array($id, $uniqueIds)) {
            $uniqueIds[] = $id;
            $uniqueReports[] = $report;
        }
    }
    $reports = $uniqueReports;

    // ============================================================
    // 5. COUNT BY TYPE
    // ============================================================
    $originalCount = count(array_filter($reports, function($r) { 
        return $r['source_type'] === 'original'; 
    }));
    
    $copyCount = count(array_filter($reports, function($r) { 
        return $r['source_type'] === 'copy'; 
    }));
    
    $sentHistoryCount = count(array_filter($reports, function($r) { 
        return $r['source_type'] === 'sent_history'; 
    }));

    // ============================================================
    // 6. SORT
    // ============================================================
    usort($reports, function($a, $b) {
        $dateA = strtotime($a['created_at'] ?? $a['sent_at'] ?? '1970-01-01');
        $dateB = strtotime($b['created_at'] ?? $b['sent_at'] ?? '1970-01-01');
        return $dateB - $dateA;
    });

    sendJson([
        'success' => true,
        'data' => $reports,
        'count' => count($reports),
        'department_id' => $department_id,
        'debug' => [
            'original_count' => $originalCount,
            'copy_count' => $copyCount,
            'sent_history_count' => $sentHistoryCount,
            'total' => count($reports),
            'note' => 'Each department sees: ORIGINAL (owned) + RECEIVED COPIES + SENT HISTORY'
        ]
    ]);

} catch(PDOException $e) {
    sendJson(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>