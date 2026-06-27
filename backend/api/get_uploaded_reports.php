<?php
// backend/api/get_uploaded_reports.php
// Returns ORIGINAL + RECEIVED COPIES + SENT UPLOADED REPORTS

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
    // 1. ORIGINAL REPORTS (is_original = 1) - SENDER'S OWN
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
        $reports[] = $row;
        $seenIds[$row['id']] = true;
    }

    // ============================================================
    // 2. COPY REPORTS (is_sent_copy = 1) - RECEIVED FROM OTHERS
    // ============================================================
    $query = "SELECT 
                r.*,
                'copy' as source_type,
                1 as is_received,
                r.sent_from_department as sent_from_name
              FROM uploaded_reports r
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
            $deptStmt = $pdo->prepare("SELECT name FROM departments WHERE id = ?");
            $deptStmt->execute([$row['sent_from_department']]);
            $row['sent_from_name'] = $deptStmt->fetchColumn() ?: 'Unknown';
        }
        
        $row['_is_unviewed'] = ($row['is_viewed_by_department'] == 0 || $row['is_viewed_by_department'] === null);
        $row['_is_sent_by_me'] = false;
        $row['_is_received_by_me'] = true;
        $row['is_sent'] = 1;
        $row['_display_type'] = 'Received Copy';
        $row['_display_icon'] = '📨';
        $reports[] = $row;
        $seenIds[$row['id']] = true;
    }

    // ============================================================
    // 3. SENT UPLOADED REPORTS - DIRECT FROM sent_uploaded_reports
    // ============================================================
    // HII NDIO SEHEMU MUHIMU - INACHUKUA SENT UPLOADED REPORTS
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
                'sent' as source_type
              FROM sent_uploaded_reports sr
              LEFT JOIN departments d1 ON d1.id = sr.from_department_id
              LEFT JOIN departments d2 ON d2.id = sr.to_department_id
              WHERE (sr.from_department_id = ? OR sr.to_department_id = ?)
                AND sr.is_deleted = 0
              ORDER BY sr.sent_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$department_id, $department_id]);
    
    while ($row = $stmt->fetch()) {
        // ============================================================
        // CHECK IF ALREADY SEEN (by original or copy ID)
        // ============================================================
        if (isset($seenIds[$row['original_uploaded_report_id']])) {
            continue;
        }
        
        // ============================================================
        // EXTRACT DATA FROM uploaded_report_data
        // ============================================================
        $reportData = [];
        if (!empty($row['uploaded_report_data'])) {
            $reportData = json_decode($row['uploaded_report_data'], true);
            if (!is_array($reportData)) {
                $reportData = [];
            }
        }
        
        // ============================================================
        // BUILD REPORT ENTRY FROM sent_uploaded_reports
        // ============================================================
        $report = [
            'id' => $row['sent_id'], // Use sent_id as primary ID
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
            'is_sent_copy' => 1,
            'source_type' => $row['source_type'],
            '_is_sent_by_me' => ($row['from_department_id'] == $department_id),
            '_is_received_by_me' => ($row['to_department_id'] == $department_id),
            '_is_unviewed' => false, // Already sent, no need to view
            '_display_type' => ($row['from_department_id'] == $department_id) ? 'Sent History' : 'Received History',
            '_display_icon' => ($row['from_department_id'] == $department_id) ? '📤' : '📨',
            '_is_sent_history' => true,
            '_is_copy' => true
        ];
        
        // Set unviewed only if received by this department and not viewed
        if ($row['to_department_id'] == $department_id && ($row['is_viewed'] == 0 || $row['is_viewed'] === null)) {
            $report['_is_unviewed'] = true;
        }
        
        // ============================================================
        // ONLY ADD IF NOT ALREADY SEEN
        // ============================================================
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
    
    $sentCount = count(array_filter($reports, function($r) { 
        return $r['source_type'] === 'sent'; 
    }));

    // ============================================================
    // 6. SORT BY CREATED AT (newest first)
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
            'sent_count' => $sentCount,
            'total' => count($reports),
            'note' => 'Returns: ORIGINAL + RECEIVED COPIES + SENT UPLOADED REPORTS'
        ]
    ]);

} catch(PDOException $e) {
    sendJson(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>