<?php
// backend/api/delete_uploaded_report.php

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

$id = isset($input['id']) ? intval($input['id']) : 0;
$department_id = isset($input['department_id']) ? intval($input['department_id']) : 0;
$deleted_by = isset($input['deleted_by']) ? $input['deleted_by'] : 'System';

if ($id <= 0 || $department_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid id or department_id'
    ]);
    exit;
}

// ============================================================
// CHECK IF UPLOADED REPORT EXISTS AND DETERMINE OWNERSHIP
// ============================================================
try {
    // ============================================================
    // 1. CHECK ORIGINAL uploaded_reports TABLE
    // ============================================================
    $checkStmt = $pdo->prepare("
        SELECT id, title, department_id, sent_from_department, sent_to_department,
               is_original, is_sent_copy, original_uploaded_report_id
        FROM uploaded_reports 
        WHERE id = ?
    ");
    $checkStmt->execute([$id]);
    $report = $checkStmt->fetch();
    
    if ($report) {
        $isOwner = ($report['department_id'] == $department_id);
        $isReceiver = ($report['sent_to_department'] == $department_id);
        $isSender = ($report['sent_from_department'] == $department_id);
        
        // ============================================================
        // CASE A: THIS DEPARTMENT IS THE ORIGINAL OWNER (SENDER)
        // ============================================================
        if ($isOwner) {
            $stmt = $pdo->prepare("
                UPDATE uploaded_reports SET 
                    is_deleted = 1,
                    deleted_at = NOW(),
                    deleted_by = ?
                WHERE id = ?
            ");
            $stmt->execute([$deleted_by, $id]);
            
            // Delete sent copies FOR THIS DEPARTMENT ONLY
            $sentStmt = $pdo->prepare("
                UPDATE sent_uploaded_reports SET 
                    is_deleted = 1,
                    deleted_at = NOW()
                WHERE original_uploaded_report_id = ? 
                AND (to_department_id = ? OR from_department_id = ?)
            ");
            $sentStmt->execute([$id, $department_id, $department_id]);
            
            addToRecycleBin($pdo, $id, 'uploaded_report', $report['title'], $department_id, $deleted_by);
            
            echo json_encode([
                'success' => true,
                'message' => 'Uploaded report deleted for department ' . $department_id . ' (Original owner)',
                'data' => [
                    'id' => $id,
                    'title' => $report['title'],
                    'department_id' => $department_id,
                    'note' => 'Only deleted for department ' . $department_id . '. Others still have their copies.'
                ]
            ]);
            exit;
        }
        
        // ============================================================
        // CASE B: THIS DEPARTMENT IS A RECEIVER
        // ============================================================
        if ($isReceiver && !$isOwner) {
            $sentStmt = $pdo->prepare("
                UPDATE sent_uploaded_reports SET 
                    is_deleted = 1,
                    deleted_at = NOW()
                WHERE original_uploaded_report_id = ? 
                AND to_department_id = ?
            ");
            $sentStmt->execute([$id, $department_id]);
            
            if ($sentStmt->rowCount() > 0) {
                addToRecycleBin($pdo, $id, 'sent_uploaded_report', $report['title'], $department_id, $deleted_by);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Uploaded report deleted for department ' . $department_id . ' (Receiver)',
                    'data' => [
                        'id' => $id,
                        'title' => $report['title'],
                        'department_id' => $department_id,
                        'note' => 'Only your copy was deleted. Original sender still has the file.'
                    ]
                ]);
                exit;
            }
        }
        
        // ============================================================
        // CASE C: THIS DEPARTMENT HAS NO RELATION
        // ============================================================
        echo json_encode([
            'success' => false,
            'message' => 'You do not have permission to delete this uploaded report'
        ]);
        exit;
    }
    
    // ============================================================
    // 2. CHECK sent_uploaded_reports TABLE
    // ============================================================
    $sentCheck = $pdo->prepare("
        SELECT * FROM sent_uploaded_reports 
        WHERE id = ? OR (original_uploaded_report_id = ? AND to_department_id = ?)
    ");
    $sentCheck->execute([$id, $id, $department_id]);
    $sentReport = $sentCheck->fetch();
    
    if ($sentReport) {
        $stmt = $pdo->prepare("
            UPDATE sent_uploaded_reports SET 
                is_deleted = 1,
                deleted_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$sentReport['id']]);
        
        $title = $sentReport['uploaded_report_title'] ?? 'Sent Uploaded Report';
        addToRecycleBin($pdo, $sentReport['id'], 'sent_uploaded_report', $title, $department_id, $deleted_by);
        
        echo json_encode([
            'success' => true,
            'message' => 'Sent uploaded report deleted for department ' . $department_id,
            'data' => [
                'sent_id' => $sentReport['id'],
                'original_id' => $sentReport['original_uploaded_report_id'],
                'department_id' => $department_id,
                'note' => 'Only your copy was deleted.'
            ]
        ]);
        exit;
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Uploaded report not found with ID: ' . $id
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