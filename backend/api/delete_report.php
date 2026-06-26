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

// ============================================================
// DATABASE CONNECTION
// ============================================================
$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit;
}

// ============================================================
// GET INPUT
// ============================================================
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid input'
    ]);
    exit;
}

$report_id = isset($input['report_id']) ? intval($input['report_id']) : 0;
$department_id = isset($input['department_id']) ? intval($input['department_id']) : 0;
$deleted_by = isset($input['deleted_by']) ? $input['deleted_by'] : 'System';

if ($report_id <= 0 || $department_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid report_id or department_id'
    ]);
    exit;
}

// ============================================================
// CHECK IF REPORT EXISTS AND DETERMINE OWNERSHIP
// ============================================================
try {
    // ============================================================
    // 1. CHECK ORIGINAL reports TABLE
    // ============================================================
    $checkStmt = $pdo->prepare("
        SELECT id, title, department_id, sent_from_department, sent_to_department, 
               is_original, is_sent_copy, original_report_id
        FROM reports 
        WHERE id = ?
    ");
    $checkStmt->execute([$report_id]);
    $report = $checkStmt->fetch();
    
    if ($report) {
        $isOwner = ($report['department_id'] == $department_id);
        $isReceiver = ($report['sent_to_department'] == $department_id);
        $isSender = ($report['sent_from_department'] == $department_id);
        
        // ============================================================
        // CASE A: THIS DEPARTMENT IS THE ORIGINAL OWNER (SENDER)
        // ============================================================
        if ($isOwner) {
            // Only delete the original for this department
            $stmt = $pdo->prepare("
                UPDATE reports SET 
                    is_deleted = 1,
                    deleted_at = NOW(),
                    deleted_by_department = 1,
                    deleted_by_department_id = ?
                WHERE id = ?
            ");
            $stmt->execute([$department_id, $report_id]);
            
            // Also delete any sent copies FOR THIS DEPARTMENT ONLY
            $sentStmt = $pdo->prepare("
                UPDATE sent_reports SET 
                    is_deleted = 1,
                    deleted_at = NOW()
                WHERE original_report_id = ? 
                AND (to_department_id = ? OR from_department_id = ?)
            ");
            $sentStmt->execute([$report_id, $department_id, $department_id]);
            
            addToRecycleBin($pdo, $report_id, 'report', $report['title'], $department_id, $deleted_by);
            
            echo json_encode([
                'success' => true,
                'message' => 'Report deleted for department ' . $department_id . ' (Original owner)',
                'data' => [
                    'report_id' => $report_id,
                    'title' => $report['title'],
                    'department_id' => $department_id,
                    'role' => 'owner',
                    'note' => 'Only deleted for department ' . $department_id . '. Other departments still see their copies.'
                ]
            ]);
            exit;
        }
        
        // ============================================================
        // CASE B: THIS DEPARTMENT IS A RECEIVER
        // ============================================================
        if ($isReceiver && !$isOwner) {
            // Delete only the sent copy for this department
            $sentStmt = $pdo->prepare("
                UPDATE sent_reports SET 
                    is_deleted = 1,
                    deleted_at = NOW()
                WHERE original_report_id = ? 
                AND to_department_id = ?
            ");
            $sentStmt->execute([$report_id, $department_id]);
            
            if ($sentStmt->rowCount() > 0) {
                addToRecycleBin($pdo, $report_id, 'sent_report', $report['title'], $department_id, $deleted_by);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Report deleted for department ' . $department_id . ' (Receiver)',
                    'data' => [
                        'report_id' => $report_id,
                        'title' => $report['title'],
                        'department_id' => $department_id,
                        'role' => 'receiver',
                        'note' => 'Only your copy was deleted. Original sender and other receivers still see their copies.'
                    ]
                ]);
                exit;
            } else {
                // Try to delete from sent_reports directly
                $directStmt = $pdo->prepare("
                    UPDATE sent_reports SET 
                        is_deleted = 1,
                        deleted_at = NOW()
                    WHERE original_report_id = ? 
                    AND to_department_id = ?
                ");
                $directStmt->execute([$report_id, $department_id]);
                
                if ($directStmt->rowCount() > 0) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Report deleted for department ' . $department_id,
                        'data' => [
                            'report_id' => $report_id,
                            'department_id' => $department_id
                        ]
                    ]);
                    exit;
                }
            }
            
            echo json_encode([
                'success' => false,
                'message' => 'No sent copy found for this department'
            ]);
            exit;
        }
        
        // ============================================================
        // CASE C: THIS DEPARTMENT HAS NO RELATION
        // ============================================================
        echo json_encode([
            'success' => false,
            'message' => 'You do not have permission to delete this report'
        ]);
        exit;
    }
    
    // ============================================================
    // 2. CHECK sent_reports TABLE (If not found in reports)
    // ============================================================
    $sentCheck = $pdo->prepare("
        SELECT * FROM sent_reports 
        WHERE id = ? OR (original_report_id = ? AND to_department_id = ?)
    ");
    $sentCheck->execute([$report_id, $report_id, $department_id]);
    $sentReport = $sentCheck->fetch();
    
    if ($sentReport) {
        // Delete only this department's sent copy
        $stmt = $pdo->prepare("
            UPDATE sent_reports SET 
                is_deleted = 1,
                deleted_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$sentReport['id']]);
        
        $title = $sentReport['report_title'] ?? 'Sent Report';
        addToRecycleBin($pdo, $sentReport['id'], 'sent_report', $title, $department_id, $deleted_by);
        
        echo json_encode([
            'success' => true,
            'message' => 'Sent report deleted for department ' . $department_id,
            'data' => [
                'sent_id' => $sentReport['id'],
                'original_report_id' => $sentReport['original_report_id'],
                'department_id' => $department_id,
                'note' => 'Only your copy was deleted. Others still have their copies.'
            ]
        ]);
        exit;
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Report not found with ID: ' . $report_id
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

// ============================================================
// HELPER FUNCTION
// ============================================================
function addToRecycleBin($pdo, $itemId, $itemType, $itemName, $departmentId, $deletedBy) {
    try {
        $recycleStmt = $pdo->prepare("
            INSERT INTO recycle_bin (
                item_id, item_type, item_name, 
                deleted_by_department_id, deleted_by_admin, 
                deleted_by_name, created_at
            ) VALUES (?, ?, ?, ?, 0, ?, NOW())
        ");
        $recycleStmt->execute([$itemId, $itemType, $itemName, $departmentId, $deletedBy]);
    } catch (PDOException $e) {
        error_log("Recycle bin error: " . $e->getMessage());
    }
}
?>