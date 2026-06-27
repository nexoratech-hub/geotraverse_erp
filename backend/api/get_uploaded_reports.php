<?php
// backend/api/get_uploaded_reports.php
// Retrieve uploaded reports - ORIGINAL + SENT COPIES (NO DUPLICATES)

// ============================================================
// ERROR REPORTING
// ============================================================
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// ============================================================
// HEADERS
// ============================================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ============================================================
// DATABASE CONNECTION
// ============================================================
try {
    $pdo = new PDO("mysql:host=localhost;dbname=geotraverse_erp;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => []
    ]);
    exit;
}

// ============================================================
// GET PARAMETERS
// ============================================================
$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;
$all = isset($_GET['all']) ? intval($_GET['all']) : 0;

if ($department_id <= 0 && $all == 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Department ID required',
        'data' => []
    ]);
    exit;
}

// ============================================================
// FUNCTION TO SEND JSON
// ============================================================
function sendJson($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// ============================================================
// GET UPLOADED REPORTS - ORIGINAL + SENT COPIES
// ============================================================
try {
    $uploadedReports = [];
    $seenOriginalIds = [];

    // ============================================================
    // 1. ORIGINAL UPLOADED REPORTS - KWA DEPARTMENT HII
    //    (zilizoundwa na department hii)
    // ============================================================
    $query = "SELECT 
                ur.*,
                'original' as source_type,
                0 as is_sent_copy_display,
                NULL as sent_from_name,
                NULL as sent_to_name,
                ur.department_id as owner_department_id
              FROM uploaded_reports ur
              WHERE ur.department_id = ?
                AND (ur.is_deleted = 0 OR ur.is_deleted IS NULL)
                AND (ur.is_original = 1 OR ur.is_original IS NULL)
              ORDER BY ur.id DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$department_id]);
    
    while ($row = $stmt->fetch()) {
        $row['is_sent'] = ($row['sent_count'] > 0) ? 1 : 0;
        $row['is_original'] = 1;
        $row['is_sent_copy'] = 0;
        $row['original_uploaded_report_id'] = $row['id'];
        
        $uploadedReports[] = $row;
        $seenOriginalIds[$row['id']] = true;
    }

    // ============================================================
    // 2. SENT UPLOADED REPORTS - ZILIZOTUMWA KWA DEPARTMENT HII
    //    (zilizotumwa na department nyingine)
    // ============================================================
    $query = "SELECT 
                sur.*,
                'sent_copy' as source_type,
                1 as is_sent_copy_display,
                d1.name as sent_from_name,
                d2.name as sent_to_name,
                sur.to_department_id as owner_department_id
              FROM sent_uploaded_reports sur
              LEFT JOIN departments d1 ON d1.id = sur.from_department_id
              LEFT JOIN departments d2 ON d2.id = sur.to_department_id
              WHERE sur.to_department_id = ?
                AND (sur.is_deleted = 0 OR sur.is_deleted IS NULL)
                AND sur.is_sent_copy = 1
              ORDER BY sur.sent_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$department_id]);
    
    while ($row = $stmt->fetch()) {
        // ============================================================
        // DECODE UPLOADED_REPORT_DATA
        // ============================================================
        $reportData = [];
        if ($row['uploaded_report_data']) {
            $reportData = json_decode($row['uploaded_report_data'], true);
        }
        
        // ============================================================
        // BUILD REPORT DATA
        // ============================================================
        $report = [
            // ID ni ya sent_uploaded_reports
            'id' => $row['id'],
            'sent_id' => $row['id'],
            
            // Original report ID
            'original_uploaded_report_id' => $row['original_uploaded_report_id'],
            
            // Report data from JSON or fields
            'title' => $reportData['title'] ?? $row['uploaded_report_title'] ?? 'Untitled Report',
            'file_name' => $reportData['file_name'] ?? $row['uploaded_report_file'] ?? 'document.pdf',
            'file_path' => $reportData['file_path'] ?? '',
            'description' => $reportData['description'] ?? '',
            'period' => $reportData['period'] ?? $row['uploaded_report_period'] ?? 'monthly',
            'uploaded_by' => $reportData['uploaded_by'] ?? $row['sent_by'] ?? 'System',
            'created_at' => $reportData['created_at'] ?? $row['sent_at'] ?? date('Y-m-d H:i:s'),
            
            // Department info
            'department_id' => $row['to_department_id'],
            'sent_from_department' => $row['from_department_id'],
            'sent_from_name' => $row['sent_from_name'] ?? $row['from_department_name'],
            'sent_to_department' => $row['to_department_id'],
            'sent_to_name' => $row['sent_to_name'] ?? $row['to_department_name'],
            
            // Status flags
            'is_original' => 0,
            'is_sent_copy' => 1,
            'is_sent' => 1,
            'is_viewed_by_department' => $row['is_viewed'] ?? 0,
            'sent_count' => $row['sent_count'] ?? 1,
            'sent_at' => $row['sent_at'] ?? null,
            'last_sent_at' => $row['last_sent_at'] ?? null,
            
            // Source type
            'source_type' => 'sent_copy',
            
            // Additional data
            '_is_unviewed' => ($row['is_viewed'] == 0 || $row['is_viewed'] === null) ? 1 : 0
        ];
        
        // Check if we already have this original report
        if (isset($seenOriginalIds[$row['original_uploaded_report_id']])) {
            // Skip - original already exists
            continue;
        }
        
        $uploadedReports[] = $report;
        $seenOriginalIds[$row['original_uploaded_report_id']] = true;
    }

    // ============================================================
    // 3. SENT UPLOADED REPORTS - ZILIZOTUMWA KUTOKA DEPARTMENT HII
    //    (zilizotumwa na department hii kwenda nyingine)
    // ============================================================
    $query = "SELECT 
                sur.*,
                'sent_from' as source_type,
                1 as is_sent_copy_display,
                d1.name as sent_from_name,
                d2.name as sent_to_name,
                sur.from_department_id as owner_department_id
              FROM sent_uploaded_reports sur
              LEFT JOIN departments d1 ON d1.id = sur.from_department_id
              LEFT JOIN departments d2 ON d2.id = sur.to_department_id
              WHERE sur.from_department_id = ?
                AND (sur.is_deleted = 0 OR sur.is_deleted IS NULL)
                AND sur.is_sent_copy = 1
              ORDER BY sur.sent_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$department_id]);
    
    while ($row = $stmt->fetch()) {
        // Check if we already have this original report
        if (isset($seenOriginalIds[$row['original_uploaded_report_id']])) {
            // Skip - original already exists or already processed
            continue;
        }
        
        // Decode uploaded_report_data
        $reportData = [];
        if ($row['uploaded_report_data']) {
            $reportData = json_decode($row['uploaded_report_data'], true);
        }
        
        // Build report data
        $report = [
            'id' => $row['id'],
            'sent_id' => $row['id'],
            'original_uploaded_report_id' => $row['original_uploaded_report_id'],
            'title' => $reportData['title'] ?? $row['uploaded_report_title'] ?? 'Untitled Report',
            'file_name' => $reportData['file_name'] ?? $row['uploaded_report_file'] ?? 'document.pdf',
            'file_path' => $reportData['file_path'] ?? '',
            'description' => $reportData['description'] ?? '',
            'period' => $reportData['period'] ?? $row['uploaded_report_period'] ?? 'monthly',
            'uploaded_by' => $reportData['uploaded_by'] ?? $row['sent_by'] ?? 'System',
            'created_at' => $reportData['created_at'] ?? $row['sent_at'] ?? date('Y-m-d H:i:s'),
            'department_id' => $row['from_department_id'],
            'sent_from_department' => $row['from_department_id'],
            'sent_from_name' => $row['sent_from_name'] ?? $row['from_department_name'],
            'sent_to_department' => $row['to_department_id'],
            'sent_to_name' => $row['sent_to_name'] ?? $row['to_department_name'],
            'is_original' => 0,
            'is_sent_copy' => 1,
            'is_sent' => 1,
            'is_viewed_by_department' => 1, // Sender doesn't need to view their own sent items
            'sent_count' => $row['sent_count'] ?? 1,
            'sent_at' => $row['sent_at'] ?? null,
            'last_sent_at' => $row['last_sent_at'] ?? null,
            'source_type' => 'sent_from',
            '_is_unviewed' => 0
        ];
        
        $uploadedReports[] = $report;
        $seenOriginalIds[$row['original_uploaded_report_id']] = true;
    }

    // ============================================================
    // 4. SORT BY DATE (newest first)
    // ============================================================
    usort($uploadedReports, function($a, $b) {
        $dateA = strtotime($a['created_at'] ?? $a['sent_at'] ?? '1970-01-01');
        $dateB = strtotime($b['created_at'] ?? $b['sent_at'] ?? '1970-01-01');
        return $dateB - $dateA;
    });

    // ============================================================
    // 5. COUNT BY TYPE
    // ============================================================
    $originalCount = 0;
    $sentCopyCount = 0;
    $sentFromCount = 0;
    
    foreach ($uploadedReports as $r) {
        if ($r['source_type'] === 'original') $originalCount++;
        else if ($r['source_type'] === 'sent_copy') $sentCopyCount++;
        else if ($r['source_type'] === 'sent_from') $sentFromCount++;
    }

    sendJson([
        'success' => true,
        'data' => $uploadedReports,
        'count' => count($uploadedReports),
        'department_id' => $department_id,
        'all' => $all,
        'debug' => [
            'original_count' => $originalCount,
            'sent_copy_count' => $sentCopyCount,
            'sent_from_count' => $sentFromCount,
            'note' => 'Original: from uploaded_reports, Sent copies: from sent_uploaded_reports'
        ],
        'message' => 'Uploaded reports retrieved successfully'
    ]);

} catch (PDOException $e) {
    sendJson([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => []
    ]);
} catch (Exception $e) {
    sendJson([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>