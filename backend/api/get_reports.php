<?php
// backend/api/get_reports.php

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
        'message' => 'Database connection error: ' . $e->getMessage(),
        'data' => []
    ]);
    exit;
}

// ============================================================
// GET PARAMETERS
// ============================================================
$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;
$all = isset($_GET['all']) ? intval($_GET['all']) : 0;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 500;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

// ============================================================
// FUNCTION TO SEND JSON
// ============================================================
function sendJson($data) {
    while (ob_get_level()) {
        ob_end_clean();
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// ============================================================
// GET REPORTS FOR SUPER ADMIN (department_id = 1)
// ============================================================
try {
    $reports = [];
    $seenIds = [];
    
    // ============================================================
    // CASE: SUPER ADMIN - ANAONA ORIGINAL TU (HAONI COPIES ZAKE)
    // ============================================================
    if ($department_id == 1) {
        
        // ============================================================
        // 1. ORIGINAL REPORTS ONLY (is_original = 1, department_id = 1)
        // Super Admin anaona original zake mwenyewe TU
        // ============================================================
        $sql = "
            SELECT * FROM reports 
            WHERE department_id = 1
            AND is_original = 1
            AND (is_deleted = 0 OR is_deleted IS NULL)
            AND (deleted_by_admin = 0 OR deleted_by_admin IS NULL)
            AND (deleted_by_department = 0 OR deleted_by_department IS NULL)
            AND (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00')
            ORDER BY created_at DESC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $originalReports = $stmt->fetchAll();
        
        foreach ($originalReports as $report) {
            $report['source_type'] = 'original';
            $report['_is_unviewed'] = false;
            $report['_is_copy'] = false;
            $report['_is_sent_by_me'] = false;
            $report['_is_received_by_me'] = false;
            $reports[] = $report;
            $seenIds['orig_' . $report['id']] = true;
        }
        
        // ============================================================
        // 2. RECEIVED COPIES (zilizotumwa kwake kutoka dept nyingine)
        // Super Admin anaona copies zilizotumwa kwake
        // ============================================================
        $sql2 = "
            SELECT * FROM reports 
            WHERE sent_to_department = 1
            AND is_sent_copy = 1
            AND is_deleted = 0
            AND department_id != 1
            AND sent_from_department != 1
            AND (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00')
            ORDER BY created_at DESC
        ";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute();
        $receivedReports = $stmt2->fetchAll();
        
        foreach ($receivedReports as $report) {
            $report['source_type'] = 'received';
            $report['_is_unviewed'] = ($report['is_viewed_by_department'] == 0);
            $report['_is_copy'] = true;
            $report['_is_sent_by_me'] = false;
            $report['_is_received_by_me'] = true;
            $reports[] = $report;
            $seenIds['recv_' . $report['id']] = true;
        }
        
        // ============================================================
        // 3. SENT REPORTS FROM sent_reports TABLE (Received by Super Admin)
        // ============================================================
        $sql3 = "
            SELECT * FROM sent_reports 
            WHERE to_department_id = 1
            AND from_department_id != 1
            AND (is_deleted = 0 OR is_deleted IS NULL)
            AND (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00')
            ORDER BY sent_at DESC
        ";
        $stmt3 = $pdo->prepare($sql3);
        $stmt3->execute();
        $sentReportsData = $stmt3->fetchAll();
        
        foreach ($sentReportsData as $sent) {
            $data = json_decode($sent['report_data'], true);
            if ($data && is_array($data)) {
                // Skip if original already shown or already added
                $origId = $sent['original_report_id'] ?? $data['id'] ?? 0;
                if (isset($seenIds['orig_' . $origId])) {
                    continue;
                }
                
                $data['sent_id'] = $sent['id'];
                $data['source_type'] = 'sent_received';
                $data['_is_unviewed'] = ($sent['is_viewed'] == 0);
                $data['_is_copy'] = true;
                $data['_is_sent_by_me'] = false;
                $data['_is_received_by_me'] = true;
                $data['sent_from_department'] = $sent['from_department_id'];
                $data['sent_to_department'] = $sent['to_department_id'];
                $data['from_department_name'] = $sent['from_department_name'];
                $data['to_department_name'] = $sent['to_department_name'];
                $data['sent_at'] = $sent['sent_at'];
                $data['sent_by'] = $sent['sent_by'];
                $data['is_forward'] = $sent['is_forward'] ?? 0;
                $data['forward_count'] = $sent['forward_count'] ?? 0;
                $data['original_sender_department'] = $sent['original_sender_department'] ?? null;
                $data['is_sent_copy'] = 1;
                $data['is_original'] = 0;
                $data['display_id'] = $sent['id'];
                $data['id'] = $sent['id'];
                
                if (!isset($data['title']) || empty($data['title'])) {
                    $data['title'] = $sent['report_title'] ?? 'Untitled Report';
                }
                if (!isset($data['period']) || empty($data['period'])) {
                    $data['period'] = $sent['report_period'] ?? 'daily';
                }
                if (!isset($data['status']) || empty($data['status'])) {
                    $data['status'] = $sent['report_status'] ?? 'draft';
                }
                if (!isset($data['content']) || empty($data['content'])) {
                    $data['content'] = 'No content available';
                }
                if (!isset($data['created_at']) || empty($data['created_at'])) {
                    $data['created_at'] = $sent['sent_at'];
                }
                
                $key = 'sent_rec_' . $sent['id'];
                if (!isset($seenIds[$key])) {
                    $reports[] = $data;
                    $seenIds[$key] = true;
                }
            }
        }
        
        // ============================================================
        // 4. SKIP SENT FROM (copies alizotuma) - HAZIONYESHWI KWA ADMIN
        // Super Admin haoni copies alizotuma, anaona original tu
        // ============================================================
        // TUMEACHA HII SEHEMU - HAIONYESHI COPIES ALIZOTUMA
        
        // ============================================================
        // 5. REMOVE DUPLICATES
        // ============================================================
        $uniqueReports = [];
        $uniqueIds = [];
        
        foreach ($reports as $report) {
            $id = $report['id'] ?? 0;
            $sourceType = $report['source_type'] ?? 'unknown';
            $key = $sourceType . '_' . $id;
            if (!in_array($key, $uniqueIds)) {
                $uniqueIds[] = $key;
                $uniqueReports[] = $report;
            }
        }
        $reports = $uniqueReports;
        
        // ============================================================
        // 6. SORT BY DATE (newest first)
        // ============================================================
        usort($reports, function($a, $b) {
            $dateA = strtotime($a['created_at'] ?? $a['sent_at'] ?? '1970-01-01');
            $dateB = strtotime($b['created_at'] ?? $b['sent_at'] ?? '1970-01-01');
            return $dateB - $dateA;
        });
        
        // ============================================================
        // 7. COUNT UNVIEWED
        // ============================================================
        $unviewedCount = 0;
        foreach ($reports as $r) {
            if (isset($r['_is_unviewed']) && $r['_is_unviewed'] === true) {
                $unviewedCount++;
            }
        }
        
        sendJson([
            'success' => true,
            'data' => $reports,
            'count' => count($reports),
            'department_id' => $department_id,
            'unviewed_count' => $unviewedCount,
            'debug' => [
                'original_count' => count(array_filter($reports, function($r) { 
                    return $r['source_type'] === 'original'; 
                })),
                'received_count' => count(array_filter($reports, function($r) { 
                    return $r['source_type'] === 'received' || $r['source_type'] === 'sent_received'; 
                })),
                'note' => 'Super Admin: Sees ORIGINAL (his own) + RECEIVED (copies from others). Does NOT see SENT FROM copies.'
            ],
            'message' => 'Reports retrieved successfully'
        ]);
        exit;
    }
    
    // ============================================================
    // CASE: OTHER DEPARTMENTS
    // ============================================================
    if ($department_id > 0 && $department_id != 1) {
        // ============================================================
        // 1. ORIGINAL REPORTS (department's own)
        // ============================================================
        $sql = "
            SELECT * FROM reports 
            WHERE department_id = :department_id
            AND is_original = 1
            AND is_deleted = 0
            AND (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00')
            ORDER BY created_at DESC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':department_id', $department_id, PDO::PARAM_INT);
        $stmt->execute();
        $originalReports = $stmt->fetchAll();
        
        foreach ($originalReports as $report) {
            $report['source_type'] = 'original';
            $report['_is_unviewed'] = false;
            $report['_is_copy'] = false;
            $reports[] = $report;
            $seenIds['orig_' . $report['id']] = true;
        }
        
        // ============================================================
        // 2. COPIES RECEIVED (sent to this department)
        // ============================================================
        $sql2 = "
            SELECT * FROM reports 
            WHERE sent_to_department = :department_id
            AND is_sent_copy = 1
            AND is_deleted = 0
            AND department_id != :department_id
            AND sent_from_department != :department_id
            AND (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00')
            ORDER BY created_at DESC
        ";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->bindValue(':department_id', $department_id, PDO::PARAM_INT);
        $stmt2->execute();
        $receivedReports = $stmt2->fetchAll();
        
        foreach ($receivedReports as $report) {
            $report['source_type'] = 'received';
            $report['_is_unviewed'] = ($report['is_viewed_by_department'] == 0);
            $report['_is_copy'] = true;
            $reports[] = $report;
            $seenIds['recv_' . $report['id']] = true;
        }
        
        // ============================================================
        // 3. SENT REPORTS FROM sent_reports TABLE
        // ============================================================
        $sql3 = "
            SELECT * FROM sent_reports 
            WHERE to_department_id = :department_id
            AND from_department_id != :department_id
            AND (is_deleted = 0 OR is_deleted IS NULL)
            AND (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00')
            ORDER BY sent_at DESC
        ";
        $stmt3 = $pdo->prepare($sql3);
        $stmt3->bindValue(':department_id', $department_id, PDO::PARAM_INT);
        $stmt3->execute();
        $sentReportsData = $stmt3->fetchAll();
        
        foreach ($sentReportsData as $sent) {
            $data = json_decode($sent['report_data'], true);
            if ($data && is_array($data)) {
                $origId = $sent['original_report_id'] ?? $data['id'] ?? 0;
                if (isset($seenIds['orig_' . $origId])) {
                    continue;
                }
                
                $data['sent_id'] = $sent['id'];
                $data['source_type'] = 'sent_received';
                $data['_is_unviewed'] = ($sent['is_viewed'] == 0);
                $data['_is_copy'] = true;
                $data['sent_from_department'] = $sent['from_department_id'];
                $data['sent_to_department'] = $sent['to_department_id'];
                $data['from_department_name'] = $sent['from_department_name'];
                $data['to_department_name'] = $sent['to_department_name'];
                $data['sent_at'] = $sent['sent_at'];
                $data['sent_by'] = $sent['sent_by'];
                $data['is_forward'] = $sent['is_forward'] ?? 0;
                $data['forward_count'] = $sent['forward_count'] ?? 0;
                $data['is_sent_copy'] = 1;
                $data['is_original'] = 0;
                $data['display_id'] = $sent['id'];
                $data['id'] = $sent['id'];
                
                if (!isset($data['title']) || empty($data['title'])) {
                    $data['title'] = $sent['report_title'] ?? 'Untitled Report';
                }
                if (!isset($data['period']) || empty($data['period'])) {
                    $data['period'] = $sent['report_period'] ?? 'daily';
                }
                if (!isset($data['status']) || empty($data['status'])) {
                    $data['status'] = $sent['report_status'] ?? 'draft';
                }
                if (!isset($data['content']) || empty($data['content'])) {
                    $data['content'] = 'No content available';
                }
                if (!isset($data['created_at']) || empty($data['created_at'])) {
                    $data['created_at'] = $sent['sent_at'];
                }
                
                $key = 'sent_rec_' . $sent['id'];
                if (!isset($seenIds[$key])) {
                    $reports[] = $data;
                    $seenIds[$key] = true;
                }
            }
        }
        
        // ============================================================
        // 4. REMOVE DUPLICATES
        // ============================================================
        $uniqueReports = [];
        $uniqueIds = [];
        
        foreach ($reports as $report) {
            $id = $report['id'] ?? 0;
            $sourceType = $report['source_type'] ?? 'unknown';
            $key = $sourceType . '_' . $id;
            if (!in_array($key, $uniqueIds)) {
                $uniqueIds[] = $key;
                $uniqueReports[] = $report;
            }
        }
        $reports = $uniqueReports;
        
        // ============================================================
        // 5. SORT BY DATE
        // ============================================================
        usort($reports, function($a, $b) {
            $dateA = strtotime($a['created_at'] ?? $a['sent_at'] ?? '1970-01-01');
            $dateB = strtotime($b['created_at'] ?? $b['sent_at'] ?? '1970-01-01');
            return $dateB - $dateA;
        });
        
        $unviewedCount = 0;
        foreach ($reports as $r) {
            if (isset($r['_is_unviewed']) && $r['_is_unviewed'] === true) {
                $unviewedCount++;
            }
        }
        
        sendJson([
            'success' => true,
            'data' => $reports,
            'count' => count($reports),
            'department_id' => $department_id,
            'unviewed_count' => $unviewedCount,
            'message' => 'Reports retrieved successfully'
        ]);
        exit;
    }
    
    // ============================================================
    // INVALID REQUEST
    // ============================================================
    sendJson([
        'success' => false,
        'message' => 'Invalid department_id',
        'data' => []
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