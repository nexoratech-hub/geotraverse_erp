<?php
// backend/api/get_uploaded_reports.php

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
// INCLUDE CONFIG
// ============================================================
require_once '../config/database.php';

// ============================================================
// GET PARAMETERS
// ============================================================
$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;
$all = isset($_GET['all']) ? intval($_GET['all']) : 0;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 500;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

// ============================================================
// GET UPLOADED REPORTS - EXCLUDE DELETED
// ============================================================
try {
    $reports = [];
    $seenIds = [];

    // ============================================================
    // 1. ORIGINAL UPLOADED REPORTS - ONLY is_deleted = 0
    // ============================================================
    $query = "SELECT 
                id, 
                title, 
                description, 
                file_name, 
                file_path,
                file_size, 
                file_type, 
                period, 
                department_id,
                uploaded_by, 
                created_at, 
                updated_at,
                is_deleted, 
                deleted_at,
                sent_from_department,
                sent_to_department,
                is_viewed_by_department,
                sent_count,
                is_sent,
                last_sent_at,
                is_original,
                is_sent_copy,
                original_uploaded_report_id,
                'original' as source_type,
                0 as is_sent_copy_flag
              FROM uploaded_reports 
              WHERE is_deleted = 0  -- ✅ ONLY NOT DELETED
              AND (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00')";  // ✅ ALSO CHECK deleted_at
    
    // Kama department_id imetolewa na all=0, chuja kwa department
    if ($department_id > 0 && $all == 0) {
        $query .= " AND (department_id = " . intval($department_id) . " OR sent_to_department = " . intval($department_id) . ")";
    }
    $query .= " ORDER BY id DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $row['is_sent'] = 0;
        $row['_is_unviewed'] = 0;
        
        // Angalia kama imetumwa
        if ($row['sent_from_department'] && $row['sent_from_department'] != $department_id && 
            $row['sent_to_department'] == $department_id && 
            ($row['is_viewed_by_department'] == 0 || $row['is_viewed_by_department'] === null)) {
            $row['_is_unviewed'] = 1;
        }
        
        // Get department names
        if ($row['sent_from_department']) {
            $deptStmt = $db->prepare("SELECT name FROM departments WHERE id = ?");
            $deptStmt->execute([$row['sent_from_department']]);
            $row['sent_from_name'] = $deptStmt->fetchColumn() ?: 'Unknown';
        }
        if ($row['sent_to_department']) {
            $deptStmt = $db->prepare("SELECT name FROM departments WHERE id = ?");
            $deptStmt->execute([$row['sent_to_department']]);
            $row['sent_to_name'] = $deptStmt->fetchColumn() ?: 'Unknown';
        }
        
        $reports[] = $row;
        $seenIds[] = $row['id'];
    }

    // ============================================================
    // 2. SENT UPLOADED REPORTS - ONLY is_deleted = 0
    // ============================================================
    if ($all == 1 || $department_id > 0) {
        $sentQuery = "SELECT 
                        s.id as sent_id,
                        s.original_uploaded_report_id as id,
                        s.uploaded_report_title as title,
                        NULL as description,
                        s.uploaded_report_file as file_name,
                        NULL as file_path,
                        0 as file_size,
                        NULL as file_type,
                        s.uploaded_report_period as period,
                        s.from_department_id as department_id,
                        s.sent_by as uploaded_by,
                        s.sent_at as created_at,
                        s.last_sent_at as updated_at,
                        s.is_deleted,
                        s.deleted_at,
                        s.from_department_id as sent_from_department,
                        s.to_department_id as sent_to_department,
                        s.is_viewed as is_viewed_by_department,
                        s.sent_count,
                        s.is_sent,
                        s.last_sent_at,
                        s.from_department_name as sent_from_name,
                        s.to_department_name as sent_to_name,
                        s.uploaded_report_data,
                        'sent' as source_type,
                        1 as is_sent_copy_flag,
                        1 as is_sent
                      FROM sent_uploaded_reports s 
                      WHERE s.is_deleted = 0  -- ✅ ONLY NOT DELETED
                      AND (s.deleted_at IS NULL OR s.deleted_at = '0000-00-00 00:00:00')";  // ✅ ALSO CHECK deleted_at
        
        if ($department_id > 0 && $all == 0) {
            $sentQuery .= " AND (s.to_department_id = " . intval($department_id) . " OR s.from_department_id = " . intval($department_id) . ")";
        }
        $sentQuery .= " ORDER BY s.sent_at DESC";
        
        $sentStmt = $db->prepare($sentQuery);
        $sentStmt->execute();
        while ($row = $sentStmt->fetch(PDO::FETCH_ASSOC)) {
            // Extract data from uploaded_report_data JSON
            if ($row['uploaded_report_data']) {
                $data = json_decode($row['uploaded_report_data'], true);
                if ($data) {
                    if (isset($data['description']) && !$row['description']) {
                        $row['description'] = $data['description'];
                    }
                    if (isset($data['file_path']) && !$row['file_path']) {
                        $row['file_path'] = $data['file_path'];
                    }
                    if (isset($data['file_size']) && !$row['file_size']) {
                        $row['file_size'] = $data['file_size'];
                    }
                    if (isset($data['file_type']) && !$row['file_type']) {
                        $row['file_type'] = $data['file_type'];
                    }
                    if (isset($data['uploaded_by']) && !$row['uploaded_by']) {
                        $row['uploaded_by'] = $data['uploaded_by'];
                    }
                    if (isset($data['period']) && !$row['period']) {
                        $row['period'] = $data['period'];
                    }
                    if (isset($data['created_at']) && !$row['created_at']) {
                        $row['created_at'] = $data['created_at'];
                    }
                }
            }
            unset($row['uploaded_report_data']);
            
            $id = $row['id'];
            if (in_array($id, $seenIds)) {
                // Update the original with sent metadata
                foreach ($reports as &$existing) {
                    if ($existing['id'] == $id && $existing['source_type'] == 'original') {
                        $existing['sent_from_department'] = $row['sent_from_department'];
                        $existing['sent_to_department'] = $row['sent_to_department'];
                        $existing['is_viewed_by_department'] = $row['is_viewed_by_department'];
                        $existing['sent_count'] = $row['sent_count'];
                        $existing['is_sent'] = 1;
                        $existing['_is_unviewed'] = ($row['is_viewed_by_department'] == 0 || $row['is_viewed_by_department'] === null) ? 1 : 0;
                        $existing['source_type'] = 'original_with_sent';
                        $existing['sent_from_name'] = $row['sent_from_name'];
                        $existing['sent_to_name'] = $row['sent_to_name'];
                        if (empty($existing['file_path']) && !empty($row['file_path'])) {
                            $existing['file_path'] = $row['file_path'];
                        }
                        break;
                    }
                }
                continue;
            }
            
            $row['_is_unviewed'] = ($row['is_viewed_by_department'] == 0 || $row['is_viewed_by_department'] === null) ? 1 : 0;
            $reports[] = $row;
        }
    }

    // ============================================================
    // 3. REMOVE DUPLICATES AND SORT
    // ============================================================
    $uniqueReports = [];
    $uniqueKeys = [];
    foreach ($reports as $report) {
        $key = $report['id'] . '_' . $report['source_type'];
        if (!in_array($key, $uniqueKeys)) {
            $uniqueKeys[] = $key;
            $uniqueReports[] = $report;
        }
    }
    $reports = $uniqueReports;

    // Sort by created_at (newest first)
    usort($reports, function($a, $b) {
        $dateA = strtotime($a['created_at'] ?? $a['sent_at'] ?? '1970-01-01');
        $dateB = strtotime($b['created_at'] ?? $b['sent_at'] ?? '1970-01-01');
        return $dateB - $dateA;
    });

    if ($limit > 0) {
        $reports = array_slice($reports, $offset, $limit);
    }

    // ============================================================
    // 4. STATISTICS
    // ============================================================
    $totalOriginal = 0;
    $totalSent = 0;
    $totalUnviewed = 0;
    
    foreach ($reports as $r) {
        if ($r['source_type'] == 'original' || $r['source_type'] == 'original_with_sent') {
            $totalOriginal++;
        }
        if ($r['source_type'] == 'sent') {
            $totalSent++;
        }
        if ($r['_is_unviewed'] == 1) {
            $totalUnviewed++;
        }
    }

    // ============================================================
    // 5. LOG KWA DEBUG
    // ============================================================
    error_log("📊 get_uploaded_reports.php - Department: $department_id, Total: " . count($reports) . ", Original: $totalOriginal, Sent: $totalSent");

    echo json_encode([
        'success' => true,
        'data' => $reports,
        'count' => count($reports),
        'total_original' => $totalOriginal,
        'total_sent' => $totalSent,
        'total_unviewed' => $totalUnviewed,
        'department_id' => $department_id,
        'all' => $all,
        'limit' => $limit,
        'offset' => $offset,
        'message' => 'Uploaded reports retrieved successfully (excluding deleted)'
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (PDOException $e) {
    error_log("❌ get_uploaded_reports.php error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => []
    ]);
} catch (Exception $e) {
    error_log("❌ get_uploaded_reports.php error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>