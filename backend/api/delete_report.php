<?php
// backend/api/delete_report.php

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
// DELETE REPORTS
// ============================================================
try {
    $deleted_count = 0;
    $deleted_ids = [];
    $deleted_items = [];
    
    // ============================================================
    // 1. DELETE ORIGINAL REPORT
    // ============================================================
    $query = "SELECT id, title FROM reports WHERE id = ? AND is_deleted = 0";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$report_id]);
    $report = $stmt->fetch();
    
    if ($report) {
        // Soft delete original report
        $query = "UPDATE reports SET 
                  is_deleted = 1,
                  deleted_by_department = 1,
                  deleted_by_admin = 0,
                  deleted_at = NOW(),
                  deleted_by_department_id = ?
                  WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$department_id, $report_id]);
        $deleted_count++;
        $deleted_ids[] = $report_id;
        $deleted_items[] = [
            'table' => 'reports',
            'id' => $report_id,
            'name' => $report['title'],
            'type' => 'original'
        ];
        
        // Add to recycle bin
        addToRecycleBin($pdo, $report_id, 'report', $report['title'], $department_id, $deleted_by);
    }
    
    // ============================================================
    // 2. DELETE SENT REPORTS (Kwa original_report_id)
    // ============================================================
    $query = "SELECT id, report_title FROM sent_reports 
              WHERE original_report_id = ? AND is_deleted = 0";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$report_id]);
    $sentReports = $stmt->fetchAll();
    
    foreach ($sentReports as $sent) {
        $query = "UPDATE sent_reports SET 
                  is_deleted = 1,
                  deleted_by_department = ?,
                  deleted_by_admin = 0,
                  deleted_at = NOW()
                  WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$department_id, $sent['id']]);
        $deleted_count++;
        $deleted_ids[] = $sent['id'];
        $deleted_items[] = [
            'table' => 'sent_reports',
            'id' => $sent['id'],
            'name' => $sent['report_title'],
            'type' => 'sent'
        ];
        
        // Add to recycle bin
        addToRecycleBin($pdo, $sent['id'], 'sent_report', $sent['report_title'], $department_id, $deleted_by);
    }
    
    // ============================================================
    // 3. DELETE SENT REPORT KWA ID (Kama original haipo)
    // ============================================================
    if ($deleted_count == 0) {
        $query = "SELECT id, report_title FROM sent_reports 
                  WHERE id = ? AND is_deleted = 0";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$report_id]);
        $sent = $stmt->fetch();
        
        if ($sent) {
            $query = "UPDATE sent_reports SET 
                      is_deleted = 1,
                      deleted_by_department = ?,
                      deleted_by_admin = 0,
                      deleted_at = NOW()
                      WHERE id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$department_id, $report_id]);
            $deleted_count++;
            $deleted_ids[] = $report_id;
            $deleted_items[] = [
                'table' => 'sent_reports',
                'id' => $report_id,
                'name' => $sent['report_title'],
                'type' => 'sent'
            ];
            
            // Add to recycle bin
            addToRecycleBin($pdo, $report_id, 'sent_report', $sent['report_title'], $department_id, $deleted_by);
        }
    }
    
    // ============================================================
    // SEND RESPONSE
    // ============================================================
    if ($deleted_count > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Report(s) deleted successfully',
            'deleted_count' => $deleted_count,
            'deleted_ids' => $deleted_ids,
            'deleted_items' => $deleted_items
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Report not found',
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