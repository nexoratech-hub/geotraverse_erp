<?php
// backend/api/delete_uploaded_report.php

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
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit;
}

// ============================================================
// GET INPUT DATA
// ============================================================
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['id']) || !isset($input['department_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields: id, department_id'
    ]);
    exit;
}

$report_id = intval($input['id']);
$department_id = intval($input['department_id']);
$deleted_by = isset($input['deleted_by']) ? $input['deleted_by'] : 'System';

// ============================================================
// FUNCTION TO ADD TO RECYCLE BIN
// ============================================================
function addToRecycleBin($pdo, $item_id, $item_type, $item_name, $department_id, $deleted_by) {
    try {
        $query = "INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin, deleted_by_name, created_at) 
                  VALUES (?, ?, ?, ?, 0, ?, NOW())";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$item_id, $item_type, $item_name, $department_id, $deleted_by]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// ============================================================
// DELETE UPLOADED REPORTS
// ============================================================
try {
    $deleted_count = 0;
    $deleted_ids = [];
    $deleted_items = [];
    
    // ============================================================
    // 1. DELETE ORIGINAL UPLOADED REPORT
    // ============================================================
    $query = "SELECT id, title FROM uploaded_reports WHERE id = ? AND is_deleted = 0";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$report_id]);
    $report = $stmt->fetch();
    
    if ($report) {
        $query = "UPDATE uploaded_reports SET 
                  is_deleted = 1,
                  deleted_at = NOW(),
                  deleted_by = ?
                  WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$deleted_by, $report_id]);
        $deleted_count++;
        $deleted_ids[] = $report_id;
        $deleted_items[] = [
            'table' => 'uploaded_reports',
            'id' => $report_id,
            'name' => $report['title'],
            'type' => 'original'
        ];
        
        addToRecycleBin($pdo, $report_id, 'uploaded_report', $report['title'], $department_id, $deleted_by);
    }
    
    // ============================================================
    // 2. DELETE SENT UPLOADED REPORTS (Kwa original_uploaded_report_id)
    // ============================================================
    $query = "SELECT id, uploaded_report_title FROM sent_uploaded_reports 
              WHERE original_uploaded_report_id = ? AND is_deleted = 0";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$report_id]);
    $sentReports = $stmt->fetchAll();
    
    foreach ($sentReports as $sent) {
        $query = "UPDATE sent_uploaded_reports SET 
                  is_deleted = 1,
                  deleted_at = NOW()
                  WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$sent['id']]);
        $deleted_count++;
        $deleted_ids[] = $sent['id'];
        $deleted_items[] = [
            'table' => 'sent_uploaded_reports',
            'id' => $sent['id'],
            'name' => $sent['uploaded_report_title'],
            'type' => 'sent'
        ];
        
        addToRecycleBin($pdo, $sent['id'], 'sent_uploaded_report', $sent['uploaded_report_title'], $department_id, $deleted_by);
    }
    
    // ============================================================
    // 3. DELETE SENT UPLOADED REPORT KWA ID (Kama original haipo)
    // ============================================================
    if ($deleted_count == 0) {
        $query = "SELECT id, uploaded_report_title FROM sent_uploaded_reports 
                  WHERE id = ? AND is_deleted = 0";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$report_id]);
        $sent = $stmt->fetch();
        
        if ($sent) {
            $query = "UPDATE sent_uploaded_reports SET 
                      is_deleted = 1,
                      deleted_at = NOW()
                      WHERE id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$report_id]);
            $deleted_count++;
            $deleted_ids[] = $report_id;
            $deleted_items[] = [
                'table' => 'sent_uploaded_reports',
                'id' => $report_id,
                'name' => $sent['uploaded_report_title'],
                'type' => 'sent'
            ];
            
            addToRecycleBin($pdo, $report_id, 'sent_uploaded_report', $sent['uploaded_report_title'], $department_id, $deleted_by);
        }
    }
    
    // ============================================================
    // SEND RESPONSE
    // ============================================================
    if ($deleted_count > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Uploaded report(s) deleted successfully',
            'deleted_count' => $deleted_count,
            'deleted_ids' => $deleted_ids,
            'deleted_items' => $deleted_items
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Uploaded report not found',
            'deleted_count' => 0
        ]);
    }
    
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