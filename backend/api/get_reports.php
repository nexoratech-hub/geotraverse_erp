<?php
// backend/api/get_reports.php

// ============================================================
// HEADERS
// ============================================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// ============================================================
// DATABASE CONNECTION - KWA PDO
// ============================================================
try {
    $host = 'localhost';
    $dbname = 'geotraverse_erp';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage(),
        'data' => []
    ]);
    exit;
}

// ============================================================
// GET PARAMETERS
// ============================================================
$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;
$all = isset($_GET['all']) ? intval($_GET['all']) : 1;

// ============================================================
// FUNCTION TO SEND RESPONSE
// ============================================================
function sendJsonResponse($success, $data, $message = '') {
    $response = [
        'success' => $success,
        'data' => $data,
        'count' => is_array($data) ? count($data) : 0,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// ============================================================
// GET REPORTS - ZISIZOFUTWA TU (is_deleted = 0)
// ============================================================
try {
    $reports = [];
    $seenIds = [];
    
    // ============================================================
    // 1. GET ORIGINAL REPORTS - ZISIZOFUTWA
    // ============================================================
    $query = "SELECT 
                id, title, period, content, status, 
                department_id, created_by, created_at, updated_at,
                sent_from_department, sent_to_department, is_viewed_by_department,
                is_deleted, deleted_at,
                'original' as source_type,
                0 as is_sent,
                0 as is_received,
                0 as _is_unviewed
              FROM reports 
              WHERE is_deleted = 0
                AND (deleted_by_department = 0 OR deleted_by_department IS NULL)
                AND (deleted_by_admin = 0 OR deleted_by_admin IS NULL)
              ORDER BY id DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll();
    
    foreach ($results as $row) {
        $reports[] = $row;
        $seenIds[$row['id']] = true;
    }
    
    // ============================================================
    // 2. GET SENT REPORTS - ZISIZOFUTWA
    // ============================================================
    $query = "SELECT 
                sr.original_report_id as id,
                sr.report_title as title,
                sr.report_period as period,
                sr.report_status as status,
                sr.from_department_id as department_id,
                sr.sent_by as created_by,
                sr.sent_at as created_at,
                sr.last_sent_at as updated_at,
                sr.from_department_id as sent_from_department,
                sr.to_department_id as sent_to_department,
                sr.is_viewed as is_viewed_by_department,
                sr.is_deleted,
                sr.deleted_at,
                sr.sent_count,
                sr.from_department_name as sent_from_name,
                sr.to_department_name as sent_to_name,
                sr.report_data,
                'sent' as source_type,
                1 as is_sent,
                1 as is_received,
                CASE WHEN (sr.is_viewed = 0 OR sr.is_viewed IS NULL) THEN 1 ELSE 0 END as _is_unviewed
              FROM sent_reports sr
              WHERE sr.is_deleted = 0
              ORDER BY sr.sent_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll();
    
    foreach ($results as $row) {
        // Decode report_data to get content
        if ($row['report_data']) {
            $reportData = json_decode($row['report_data'], true);
            if ($reportData) {
                $row['content'] = $reportData['content'] ?? '';
                if (empty($row['title']) && isset($reportData['title'])) {
                    $row['title'] = $reportData['title'];
                }
            } else {
                $row['content'] = '';
            }
        } else {
            $row['content'] = '';
        }
        unset($row['report_data']);
        
        // Check if already exists as original
        if (isset($seenIds[$row['id']])) {
            // Add sent metadata to original
            foreach ($reports as &$existing) {
                if ($existing['id'] == $row['id'] && $existing['source_type'] == 'original') {
                    $existing['sent_from_department'] = $row['sent_from_department'];
                    $existing['sent_to_department'] = $row['sent_to_department'];
                    $existing['sent_from_name'] = $row['sent_from_name'];
                    $existing['sent_to_name'] = $row['sent_to_name'];
                    $existing['is_viewed_by_department'] = $row['is_viewed_by_department'];
                    $existing['sent_count'] = $row['sent_count'];
                    $existing['is_sent'] = 1;
                    $existing['is_received'] = 1;
                    $existing['_is_unviewed'] = $row['_is_unviewed'];
                    $existing['source_type'] = 'original_with_sent';
                    break;
                }
            }
            continue;
        }
        
        $reports[] = $row;
    }
    
    // ============================================================
    // 3. FILTER BY DEPARTMENT (Ikiwa imepewa)
    // ============================================================
    if ($department_id > 0 && $all == 0) {
        $filtered = [];
        foreach ($reports as $report) {
            if ($report['department_id'] == $department_id || 
                $report['sent_from_department'] == $department_id || 
                $report['sent_to_department'] == $department_id) {
                $filtered[] = $report;
            }
        }
        $reports = $filtered;
    }
    
    // ============================================================
    // 4. SEND RESPONSE
    // ============================================================
    sendJsonResponse(true, $reports, 'Reports retrieved successfully');
    
} catch (PDOException $e) {
    sendJsonResponse(false, [], 'Database error: ' . $e->getMessage());
} catch (Exception $e) {
    sendJsonResponse(false, [], 'Error: ' . $e->getMessage());
}
?>