<?php
// backend/api/delete_report.php

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
header('Access-Control-Allow-Methods: POST, OPTIONS');
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
        'message' => 'Database connection error: ' . $e->getMessage()
    ]);
    exit;
}

// ============================================================
// GET INPUT DATA
// ============================================================
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['report_id']) || !isset($input['department_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields: report_id, department_id'
    ]);
    exit;
}

$report_id = intval($input['report_id']);
$department_id = intval($input['department_id']);
$deleted_by = isset($input['deleted_by']) ? $input['deleted_by'] : 'System';
$report_type = isset($input['report_type']) ? $input['report_type'] : '';
$is_deleted = isset($input['is_deleted']) ? intval($input['is_deleted']) : 1;

try {
    // ============================================================
    // STEP 1: CHECK IF THIS IS FROM sent_reports TABLE
    // ============================================================
    $checkSent = $pdo->prepare("SELECT id, from_department_id, to_department_id, report_title FROM sent_reports WHERE id = ? AND is_deleted = 0");
    $checkSent->execute([$report_id]);
    $sentReport = $checkSent->fetch();
    
    if ($sentReport) {
        // Check if department has permission
        if ($sentReport['from_department_id'] == $department_id || $sentReport['to_department_id'] == $department_id) {
            $deleteSent = $pdo->prepare("UPDATE sent_reports SET is_deleted = 1, deleted_at = NOW() WHERE id = ?");
            $deleteSent->execute([$report_id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Sent report deleted successfully',
                'data' => [
                    'type' => 'sent_report',
                    'id' => $report_id,
                    'title' => $sentReport['report_title'] ?? 'Sent Report'
                ]
            ]);
            exit;
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'You do not have permission to delete this sent report'
            ]);
            exit;
        }
    }
    
    // ============================================================
    // STEP 2: CHECK REPORT FROM reports TABLE
    // ============================================================
    $query = "SELECT 
                id, title, department_id, is_original, is_sent_copy, 
                original_report_id, sent_from_department, sent_to_department,
                is_deleted, deleted_by_department, deleted_by_admin
              FROM reports 
              WHERE id = ? AND is_deleted = 0";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$report_id]);
    $report = $stmt->fetch();
    
    if (!$report) {
        echo json_encode([
            'success' => false,
            'message' => 'Report not found'
        ]);
        exit;
    }
    
    $title = $report['title'] ?? 'Untitled Report';
    $isOriginal = $report['is_original'] == 1;
    $isSentCopy = $report['is_sent_copy'] == 1;
    $reportDeptId = $report['department_id'];
    $sentFromDept = $report['sent_from_department'];
    $sentToDept = $report['sent_to_department'];
    $origReportId = $report['original_report_id'] ?? $report_id;
    
    // ============================================================
    // STEP 3: ORIGINAL REPORT - SENDER DELETING
    // ============================================================
    if ($isOriginal) {
        if ($reportDeptId != $department_id) {
            echo json_encode([
                'success' => false,
                'message' => 'You can only delete your own original reports'
            ]);
            exit;
        }
        
        $deleteStmt = $pdo->prepare("UPDATE reports SET 
            is_deleted = 1,
            deleted_by_department = 1,
            deleted_at = NOW(),
            deleted_by_department_id = ?
            WHERE id = ? AND is_original = 1");
        $deleteStmt->execute([$department_id, $report_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Original report deleted successfully',
            'data' => [
                'type' => 'original',
                'id' => $report_id,
                'title' => $title,
                'note' => 'Original deleted. Copies remain with receivers.'
            ]
        ]);
        exit;
    }
    
    // ============================================================
    // STEP 4: COPY REPORT - RECEIVER DELETING
    // ============================================================
    if ($isSentCopy) {
        if ($reportDeptId != $department_id) {
            echo json_encode([
                'success' => false,
                'message' => 'You can only delete your own copy reports'
            ]);
            exit;
        }
        
        $deleteStmt = $pdo->prepare("UPDATE reports SET 
            is_deleted = 1,
            deleted_by_department = 1,
            deleted_at = NOW(),
            deleted_by_department_id = ?
            WHERE id = ? AND is_sent_copy = 1");
        $deleteStmt->execute([$department_id, $report_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Copy report deleted successfully',
            'data' => [
                'type' => 'copy',
                'id' => $report_id,
                'title' => $title,
                'original_id' => $origReportId,
                'note' => 'Copy deleted. Original remains with sender.'
            ]
        ]);
        exit;
    }
    
    // ============================================================
    // STEP 5: FALLBACK - Delete based on department ownership
    // ============================================================
    if ($reportDeptId == $department_id) {
        $deleteStmt = $pdo->prepare("UPDATE reports SET 
            is_deleted = 1,
            deleted_by_department = 1,
            deleted_at = NOW(),
            deleted_by_department_id = ?
            WHERE id = ?");
        $deleteStmt->execute([$department_id, $report_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Report deleted successfully',
            'data' => [
                'type' => 'owned',
                'id' => $report_id,
                'title' => $title
            ]
        ]);
        exit;
    }
    
    // ============================================================
    // STEP 6: NO PERMISSION
    // ============================================================
    echo json_encode([
        'success' => false,
        'message' => 'You do not have permission to delete this report'
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>