<?php
// backend/api/delete_report.php

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

// Database connection
$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// Get input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['report_id']) || !isset($input['department_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields: report_id, department_id']);
    exit();
}

$report_id = intval($input['report_id']);
$department_id = intval($input['department_id']);
$deleted_by = isset($input['deleted_by']) ? $input['deleted_by'] : 'System';

try {
    $deleted_count = 0;
    $deleted_type = '';

    // ============================================================
    // STEP 1: CHECK IF THIS IS AN ORIGINAL REPORT
    // ============================================================
    $stmt = $pdo->prepare("
        SELECT id, title, department_id, sent_from_department, sent_to_department, 
               is_original, is_sent_copy, original_report_id
        FROM reports 
        WHERE id = ? AND (is_deleted = 0 OR is_deleted IS NULL)
    ");
    $stmt->execute([$report_id]);
    $report = $stmt->fetch();

    if ($report) {
        // ============================================================
        // CASE A: ORIGINAL REPORT - OWNED BY SENDER'S DEPARTMENT
        // ============================================================
        if ($report['department_id'] == $department_id) {
            // Only the owner department can delete the original
            // SOFT DELETE - only this department's copy
            $stmt = $pdo->prepare("
                UPDATE reports SET 
                    is_deleted = 1,
                    deleted_by_department = 1,
                    deleted_at = NOW(),
                    deleted_by = ?
                WHERE id = ? AND department_id = ?
            ");
            $stmt->execute([$deleted_by, $report_id, $department_id]);
            $deleted_count = $stmt->rowCount();
            $deleted_type = 'original_owner';
            
            if ($deleted_count > 0) {
                // Add to recycle bin for this department
                $recycleStmt = $pdo->prepare("
                    INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin, deleted_by_name, created_at) 
                    VALUES (?, 'report', ?, ?, 0, ?, NOW())
                ");
                $recycleStmt->execute([$report_id, $report['title'], $department_id, $deleted_by]);
            }
            
            // ============================================================
            // ALSO DELETE SENT COPIES FOR THIS DEPARTMENT ONLY
            // ============================================================
            // Delete sent_reports where this department is the receiver
            $stmt = $pdo->prepare("
                UPDATE sent_reports SET 
                    is_deleted = 1,
                    deleted_at = NOW()
                WHERE original_report_id = ? 
                AND to_department_id = ?
            ");
            $stmt->execute([$report_id, $department_id]);
            $deleted_count += $stmt->rowCount();
            
            // Also delete any copies this department has forwarded
            $stmt = $pdo->prepare("
                UPDATE sent_reports SET 
                    is_deleted = 1,
                    deleted_at = NOW()
                WHERE original_report_id = ? 
                AND from_department_id = ?
            ");
            $stmt->execute([$report_id, $department_id]);
            $deleted_count += $stmt->rowCount();
            
        } else {
            // ============================================================
            // CASE B: ANOTHER DEPARTMENT TRYING TO DELETE ORIGINAL
            // ============================================================
            // Check if this department has a sent copy
            $stmt = $pdo->prepare("
                SELECT id FROM sent_reports 
                WHERE original_report_id = ? 
                AND to_department_id = ?
                AND (is_deleted = 0 OR is_deleted IS NULL)
            ");
            $stmt->execute([$report_id, $department_id]);
            $sentCopy = $stmt->fetch();
            
            if ($sentCopy) {
                // Delete only this department's sent copy
                $stmt = $pdo->prepare("
                    UPDATE sent_reports SET 
                        is_deleted = 1,
                        deleted_at = NOW()
                    WHERE original_report_id = ? 
                    AND to_department_id = ?
                ");
                $stmt->execute([$report_id, $department_id]);
                $deleted_count = $stmt->rowCount();
                $deleted_type = 'sent_copy_receiver';
                
                if ($deleted_count > 0) {
                    // Add to recycle bin for this department
                    $recycleStmt = $pdo->prepare("
                        INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin, deleted_by_name, created_at) 
                        VALUES (?, 'sent_report', ?, ?, 0, ?, NOW())
                    ");
                    $recycleStmt->execute([$sentCopy['id'], $report['title'], $department_id, $deleted_by]);
                }
            } else {
                // Check if this department is the sender of a forwarded copy
                $stmt = $pdo->prepare("
                    SELECT id FROM sent_reports 
                    WHERE original_report_id = ? 
                    AND from_department_id = ?
                    AND (is_deleted = 0 OR is_deleted IS NULL)
                ");
                $stmt->execute([$report_id, $department_id]);
                $sentFrom = $stmt->fetch();
                
                if ($sentFrom) {
                    // Delete only the copy this department sent
                    $stmt = $pdo->prepare("
                        UPDATE sent_reports SET 
                            is_deleted = 1,
                            deleted_at = NOW()
                        WHERE original_report_id = ? 
                        AND from_department_id = ?
                    ");
                    $stmt->execute([$report_id, $department_id]);
                    $deleted_count = $stmt->rowCount();
                    $deleted_type = 'sent_copy_sender';
                    
                    if ($deleted_count > 0) {
                        $recycleStmt = $pdo->prepare("
                            INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin, deleted_by_name, created_at) 
                            VALUES (?, 'sent_report', ?, ?, 0, ?, NOW())
                        ");
                        $recycleStmt->execute([$sentFrom['id'], $report['title'], $department_id, $deleted_by]);
                    }
                }
            }
        }
    } else {
        // ============================================================
        // STEP 2: CHECK SENT_REPORTS TABLE (Copies)
        // ============================================================
        // Check if this is a sent report copy
        $stmt = $pdo->prepare("
            SELECT id, original_report_id, report_data, from_department_id, to_department_id
            FROM sent_reports 
            WHERE id = ? AND (is_deleted = 0 OR is_deleted IS NULL)
        ");
        $stmt->execute([$report_id]);
        $sentReport = $stmt->fetch();
        
        if ($sentReport) {
            // Only delete if this department is the receiver or sender
            if ($sentReport['to_department_id'] == $department_id || $sentReport['from_department_id'] == $department_id) {
                $stmt = $pdo->prepare("
                    UPDATE sent_reports SET 
                        is_deleted = 1,
                        deleted_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$report_id]);
                $deleted_count = $stmt->rowCount();
                $deleted_type = 'sent_report_direct';
                
                if ($deleted_count > 0) {
                    // Get report title from data
                    $title = 'Sent Report';
                    if ($sentReport['report_data']) {
                        $data = json_decode($sentReport['report_data'], true);
                        $title = $data['title'] ?? 'Sent Report';
                    }
                    $recycleStmt = $pdo->prepare("
                        INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin, deleted_by_name, created_at) 
                        VALUES (?, 'sent_report', ?, ?, 0, ?, NOW())
                    ");
                    $recycleStmt->execute([$report_id, $title, $department_id, $deleted_by]);
                }
            }
        }
    }

    // ============================================================
    // RESPONSE
    // ============================================================
    if ($deleted_count > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Report deleted successfully for department ' . $department_id,
            'data' => [
                'deleted_count' => $deleted_count,
                'deleted_type' => $deleted_type,
                'department_id' => $department_id,
                'note' => 'Only deleted for department ' . $department_id . '. Other departments still see their copies.'
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Report not found or already deleted for this department'
        ]);
    }

} catch(PDOException $e) {
    error_log("Delete report error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>