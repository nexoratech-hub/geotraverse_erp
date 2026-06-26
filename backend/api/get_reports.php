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
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// ============================================================
// GET REPORTS
// ============================================================
try {
    $reports = [];
    $seenIds = [];
    
    // ============================================================
    // CASE 1: GET ALL REPORTS (for Super Admin)
    // ============================================================
    if ($all == 1) {
        // Get original reports
        $sql = "
            SELECT * FROM reports 
            WHERE (is_deleted = 0 OR is_deleted IS NULL)
            AND (deleted_by_admin = 0 OR deleted_by_admin IS NULL)
            AND (deleted_by_department = 0 OR deleted_by_department IS NULL)
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $originalReports = $stmt->fetchAll();
        
        foreach ($originalReports as $report) {
            $report['source_type'] = 'original';
            $reports[] = $report;
            $seenIds['orig_' . $report['id']] = true;
        }
        
        // Get sent reports
        $sql2 = "
            SELECT * FROM sent_reports 
            WHERE (is_deleted = 0 OR is_deleted IS NULL)
            ORDER BY sent_at DESC
            LIMIT :limit OFFSET :offset
        ";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt2->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt2->execute();
        $sentReports = $stmt2->fetchAll();
        
        foreach ($sentReports as $sent) {
            $data = json_decode($sent['report_data'], true);
            if ($data && is_array($data)) {
                $data['sent_id'] = $sent['id'];
                $data['source_type'] = 'sent';
                $data['_is_unviewed'] = ($sent['is_viewed'] == 0);
                $data['sent_from_department'] = $sent['from_department_id'];
                $data['sent_to_department'] = $sent['to_department_id'];
                $data['from_department_name'] = $sent['from_department_name'];
                $data['to_department_name'] = $sent['to_department_name'];
                $data['sent_at'] = $sent['sent_at'];
                $data['sent_by'] = $sent['sent_by'];
                $data['is_forward'] = $sent['is_forward'] ?? 0;
                $data['forward_count'] = $sent['forward_count'] ?? 0;
                $data['original_sender_department'] = $sent['original_sender_department'] ?? null;
                
                // Use a unique key to avoid duplicates
                $key = 'sent_' . $sent['id'];
                if (!isset($seenIds[$key])) {
                    $reports[] = $data;
                    $seenIds[$key] = true;
                }
            }
        }
        
        sendJson([
            'success' => true,
            'data' => $reports,
            'count' => count($reports),
            'message' => 'All reports retrieved successfully'
        ]);
        exit;
    }
    
    // ============================================================
    // CASE 2: GET REPORTS FOR SPECIFIC DEPARTMENT
    // ============================================================
    if ($department_id > 0) {
        // ============================================================
        // 1. GET ORIGINAL REPORTS (Created by this department)
        // ============================================================
        $sql = "
            SELECT * FROM reports 
            WHERE department_id = :department_id
            AND (is_deleted = 0 OR is_deleted IS NULL)
            AND (deleted_by_admin = 0 OR deleted_by_admin IS NULL)
            AND (deleted_by_department = 0 OR deleted_by_department IS NULL)
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':department_id', $department_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $originalReports = $stmt->fetchAll();
        
        foreach ($originalReports as $report) {
            $report['source_type'] = 'original';
            $report['_is_unviewed'] = false;
            $reports[] = $report;
            $seenIds['orig_' . $report['id']] = true;
        }
        
        // ============================================================
        // 2. GET SENT REPORTS (Received by this department)
        // ============================================================
        $sql2 = "
            SELECT * FROM sent_reports 
            WHERE to_department_id = :department_id
            AND (is_deleted = 0 OR is_deleted IS NULL)
            ORDER BY sent_at DESC
            LIMIT :limit OFFSET :offset
        ";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->bindValue(':department_id', $department_id, PDO::PARAM_INT);
        $stmt2->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt2->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt2->execute();
        $sentReports = $stmt2->fetchAll();
        
        foreach ($sentReports as $sent) {
            $data = json_decode($sent['report_data'], true);
            if ($data && is_array($data)) {
                // Add sent report metadata
                $data['sent_id'] = $sent['id'];
                $data['source_type'] = 'sent_received';
                $data['_is_unviewed'] = ($sent['is_viewed'] == 0);
                $data['sent_from_department'] = $sent['from_department_id'];
                $data['sent_to_department'] = $sent['to_department_id'];
                $data['from_department_name'] = $sent['from_department_name'];
                $data['to_department_name'] = $sent['to_department_name'];
                $data['sent_at'] = $sent['sent_at'];
                $data['sent_by'] = $sent['sent_by'];
                $data['is_forward'] = $sent['is_forward'] ?? 0;
                $data['forward_count'] = $sent['forward_count'] ?? 0;
                $data['original_sender_department'] = $sent['original_sender_department'] ?? null;
                $data['display_id'] = $sent['id'];
                
                // Use sent_id as the main ID for display
                $data['id'] = $sent['id'];
                
                $key = 'sent_rec_' . $sent['id'];
                if (!isset($seenIds[$key])) {
                    $reports[] = $data;
                    $seenIds[$key] = true;
                }
            }
        }
        
        // ============================================================
        // 3. GET SENT REPORTS (Sent by this department - their outgoing copies)
        // ============================================================
        $sql3 = "
            SELECT * FROM sent_reports 
            WHERE from_department_id = :department_id
            AND to_department_id != :department_id
            AND (is_deleted = 0 OR is_deleted IS NULL)
            ORDER BY sent_at DESC
            LIMIT :limit OFFSET :offset
        ";
        $stmt3 = $pdo->prepare($sql3);
        $stmt3->bindValue(':department_id', $department_id, PDO::PARAM_INT);
        $stmt3->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt3->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt3->execute();
        $sentFromReports = $stmt3->fetchAll();
        
        foreach ($sentFromReports as $sent) {
            $data = json_decode($sent['report_data'], true);
            if ($data && is_array($data)) {
                $data['sent_id'] = $sent['id'];
                $data['source_type'] = 'sent_from';
                $data['_is_unviewed'] = false;
                $data['sent_from_department'] = $sent['from_department_id'];
                $data['sent_to_department'] = $sent['to_department_id'];
                $data['from_department_name'] = $sent['from_department_name'];
                $data['to_department_name'] = $sent['to_department_name'];
                $data['sent_at'] = $sent['sent_at'];
                $data['sent_by'] = $sent['sent_by'];
                $data['is_forward'] = $sent['is_forward'] ?? 0;
                $data['forward_count'] = $sent['forward_count'] ?? 0;
                $data['original_sender_department'] = $sent['original_sender_department'] ?? null;
                $data['id'] = $sent['id'];
                
                $key = 'sent_from_' . $sent['id'];
                if (!isset($seenIds[$key])) {
                    $reports[] = $data;
                    $seenIds[$key] = true;
                }
            }
        }
        
        sendJson([
            'success' => true,
            'data' => $reports,
            'count' => count($reports),
            'department_id' => $department_id,
            'message' => 'Reports retrieved successfully'
        ]);
        exit;
    }
    
    // ============================================================
    // CASE 3: NO PARAMETERS
    // ============================================================
    sendJson([
        'success' => false,
        'message' => 'Missing department_id or all parameter',
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