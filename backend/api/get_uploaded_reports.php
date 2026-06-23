<?php
// get_uploaded_reports.php - Fetch both original and sent uploaded reports

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
    // 1. Get original uploaded reports
    // ============================================================
    $stmt = $pdo->prepare("SELECT 
        *,
        'original' as source_type
        FROM uploaded_reports 
        WHERE (department_id = ? OR sent_to_department = ?)
        AND (is_deleted = 0 OR is_deleted IS NULL)
        ORDER BY created_at DESC");
    $stmt->execute([$departmentId, $departmentId]);
    $originalReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($originalReports as $r) {
        $r['is_sent_report'] = isset($r['sent_to_department']) && $r['sent_to_department'] > 0;
        $r['is_original'] = true;
        $r['is_unviewed'] = ($r['is_sent_report'] && ($r['is_viewed_by_department'] == 0 || $r['is_viewed_by_department'] === null));
        $r['_is_unviewed'] = $r['is_unviewed'];
        $reports[] = $r;
    }
    
    // ============================================================
    // 2. Get sent uploaded reports from sent_uploaded_reports table
    // ============================================================
    $sentStmt = $pdo->prepare("SELECT 
        sur.*,
        'sent' as source_type,
        sur.sent_at as created_at,
        sur.from_department_name as sender_name,
        sur.to_department_name as receiver_name
        FROM sent_uploaded_reports sur
        WHERE sur.to_department_id = ?
        AND (sur.is_deleted = 0 OR sur.is_deleted IS NULL)
        ORDER BY sur.sent_at DESC");
    $sentStmt->execute([$departmentId]);
    $sentReports = $sentStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($sentReports as $r) {
        // Decode report data
        $reportData = [];
        if (isset($r['uploaded_report_data']) && $r['uploaded_report_data']) {
            $decoded = json_decode($r['uploaded_report_data'], true);
            if ($decoded) {
                $reportData = $decoded;
            }
        }
        
        $isUnviewed = ($r['is_viewed'] == 0 || $r['is_viewed'] === null);
        
        $sentReport = [
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
            'sent_from_name' => $r['from_department_name'] ?? $r['sender_name'] ?? '',
            'sent_to_name' => $r['to_department_name'] ?? $r['receiver_name'] ?? '',
            'source_type' => 'sent',
            'sent_by' => $r['sent_by'] ?? 'System',
            'is_viewed_by_department' => $r['is_viewed'] ?? 0,
            'is_unviewed' => $isUnviewed,
            '_is_unviewed' => $isUnviewed,
            'is_new' => $isUnviewed,
            'viewed_at' => $r['viewed_at'] ?? null
        ];
        
        $reports[] = $sentReport;
    }
    
    // ============================================================
    // 3. Sort: Unviewed first
    // ============================================================
    usort($reports, function($a, $b) {
        $aUnviewed = isset($a['is_unviewed']) ? $a['is_unviewed'] : false;
        $bUnviewed = isset($b['is_unviewed']) ? $b['is_unviewed'] : false;
        
        if ($aUnviewed && !$bUnviewed) return -1;
        if (!$aUnviewed && $bUnviewed) return 1;
        
        $aDate = isset($a['created_at']) ? strtotime($a['created_at']) : 0;
        $bDate = isset($b['created_at']) ? strtotime($b['created_at']) : 0;
        return $bDate - $aDate;
    });
    
    echo json_encode([
        'success' => true,
        'data' => $reports,
        'total' => count($reports),
        'original_count' => count($originalReports),
        'sent_count' => count($sentReports),
        'unviewed_count' => count(array_filter($reports, function($r) { return $r['is_unviewed']; }))
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Query error: ' . $e->getMessage()]);
}
?>