<?php
// backend/api/get_uploaded_reports.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

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
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 500;

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
// GET UPLOADED REPORTS - EXCLUDE DELETED
// ============================================================
try {
    $reports = [];
    $seenIds = [];
    
    if ($all == 1 || $department_id == 0) {
        // ============================================================
        // GET ALL UPLOADED REPORTS - EXCLUDE DELETED
        // ============================================================
        $sql = "
            SELECT * FROM uploaded_reports 
            WHERE (is_deleted = 0 OR is_deleted IS NULL)
            AND (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00')
            ORDER BY created_at DESC
            LIMIT :limit
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $reports = $stmt->fetchAll();
        
        // ============================================================
        // GET SENT UPLOADED REPORTS - EXCLUDE DELETED
        // ============================================================
        $sql2 = "
            SELECT * FROM sent_uploaded_reports 
            WHERE (is_deleted = 0 OR is_deleted IS NULL)
            AND (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00')
            ORDER BY sent_at DESC
            LIMIT :limit
        ";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt2->execute();
        $sentReports = $stmt2->fetchAll();
        
        foreach ($sentReports as $sent) {
            $data = json_decode($sent['uploaded_report_data'], true);
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
                $data['id'] = $sent['id'];
                
                if (!isset($data['title']) || empty($data['title'])) {
                    $data['title'] = $sent['uploaded_report_title'] ?? 'Untitled Uploaded Report';
                }
                
                $reports[] = $data;
            }
        }
        
        sendJson([
            'success' => true,
            'data' => $reports,
            'count' => count($reports),
            'message' => 'All uploaded reports retrieved successfully (deleted excluded)'
        ]);
        exit;
    }
    
    // ============================================================
    // GET UPLOADED REPORTS FOR SPECIFIC DEPARTMENT - EXCLUDE DELETED
    // ============================================================
    if ($department_id > 0) {
        // Original uploaded reports
        $sql = "
            SELECT * FROM uploaded_reports 
            WHERE department_id = :department_id
            AND (is_deleted = 0 OR is_deleted IS NULL)
            AND (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00')
            ORDER BY created_at DESC
            LIMIT :limit
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':department_id', $department_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $reports = $stmt->fetchAll();
        
        // Sent uploaded reports received by this department
        $sql2 = "
            SELECT * FROM sent_uploaded_reports 
            WHERE to_department_id = :department_id
            AND (is_deleted = 0 OR is_deleted IS NULL)
            AND (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00')
            ORDER BY sent_at DESC
            LIMIT :limit
        ";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->bindValue(':department_id', $department_id, PDO::PARAM_INT);
        $stmt2->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt2->execute();
        $sentReports = $stmt2->fetchAll();
        
        foreach ($sentReports as $sent) {
            $data = json_decode($sent['uploaded_report_data'], true);
            if ($data && is_array($data)) {
                $data['sent_id'] = $sent['id'];
                $data['source_type'] = 'sent_received';
                $data['_is_unviewed'] = ($sent['is_viewed'] == 0);
                $data['sent_from_department'] = $sent['from_department_id'];
                $data['sent_to_department'] = $sent['to_department_id'];
                $data['from_department_name'] = $sent['from_department_name'];
                $data['to_department_name'] = $sent['to_department_name'];
                $data['sent_at'] = $sent['sent_at'];
                $data['sent_by'] = $sent['sent_by'];
                $data['id'] = $sent['id'];
                
                if (!isset($data['title']) || empty($data['title'])) {
                    $data['title'] = $sent['uploaded_report_title'] ?? 'Untitled Uploaded Report';
                }
                
                $reports[] = $data;
            }
        }
        
        // Sent uploaded reports sent by this department
        $sql3 = "
            SELECT * FROM sent_uploaded_reports 
            WHERE from_department_id = :department_id
            AND to_department_id != :department_id
            AND (is_deleted = 0 OR is_deleted IS NULL)
            AND (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00')
            ORDER BY sent_at DESC
            LIMIT :limit
        ";
        $stmt3 = $pdo->prepare($sql3);
        $stmt3->bindValue(':department_id', $department_id, PDO::PARAM_INT);
        $stmt3->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt3->execute();
        $sentFromReports = $stmt3->fetchAll();
        
        foreach ($sentFromReports as $sent) {
            $data = json_decode($sent['uploaded_report_data'], true);
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
                $data['id'] = $sent['id'];
                
                if (!isset($data['title']) || empty($data['title'])) {
                    $data['title'] = $sent['uploaded_report_title'] ?? 'Untitled Uploaded Report';
                }
                
                $reports[] = $data;
            }
        }
        
        sendJson([
            'success' => true,
            'data' => $reports,
            'count' => count($reports),
            'department_id' => $department_id,
            'message' => 'Uploaded reports retrieved successfully (deleted excluded)'
        ]);
        exit;
    }
    
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
}
?>