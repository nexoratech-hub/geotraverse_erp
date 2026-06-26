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

$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['id']) || !isset($input['department_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields: id, department_id']);
    exit();
}

$id = intval($input['id']);
$department_id = intval($input['department_id']);
$deleted_by = isset($input['deleted_by']) ? $input['deleted_by'] : 'System';

try {
    // ============================================================
    // CHECK IF THIS IS AN ORIGINAL UPLOADED REPORT
    // ============================================================
    $stmt = $pdo->prepare("
        SELECT id, title, department_id, sent_from_department, sent_to_department
        FROM uploaded_reports 
        WHERE id = ? AND (is_deleted = 0 OR is_deleted IS NULL)
    ");
    $stmt->execute([$id]);
    $report = $stmt->fetch();

    if ($report) {
        // ============================================================
        // ONLY OWNER DEPARTMENT CAN DELETE ORIGINAL
        // ============================================================
        if ($report['department_id'] == $department_id) {
            $stmt = $pdo->prepare("
                UPDATE uploaded_reports SET 
                    is_deleted = 1,
                    deleted_by_department = 1,
                    deleted_at = NOW(),
                    deleted_by = ?
                WHERE id = ? AND department_id = ?
            ");
            $stmt->execute([$deleted_by, $id, $department_id]);
            $deleted_count = $stmt->rowCount();
            
            if ($deleted_count > 0) {
                $recycleStmt = $pdo->prepare("
                    INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin, deleted_by_name, created_at) 
                    VALUES (?, 'uploaded_report', ?, ?, 0, ?, NOW())
                ");
                $recycleStmt->execute([$id, $report['title'], $department_id, $deleted_by]);
            }
        } else {
            // ============================================================
            // DELETE SENT COPY FOR THIS DEPARTMENT ONLY
            // ============================================================
            $stmt = $pdo->prepare("
                UPDATE sent_uploaded_reports SET 
                    is_deleted = 1,
                    deleted_at = NOW()
                WHERE original_uploaded_report_id = ? 
                AND to_department_id = ?
            ");
            $stmt->execute([$id, $department_id]);
            $deleted_count = $stmt->rowCount();
            
            if ($deleted_count > 0) {
                $recycleStmt = $pdo->prepare("
                    INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin, deleted_by_name, created_at) 
                    VALUES (?, 'sent_uploaded_report', ?, ?, 0, ?, NOW())
                ");
                $recycleStmt->execute([$id, $report['title'], $department_id, $deleted_by]);
            }
        }
    } else {
        // ============================================================
        // CHECK SENT_UPLOADED_REPORTS TABLE
        // ============================================================
        $stmt = $pdo->prepare("
            SELECT id, original_uploaded_report_id, from_department_id, to_department_id
            FROM sent_uploaded_reports 
            WHERE id = ? AND (is_deleted = 0 OR is_deleted IS NULL)
        ");
        $stmt->execute([$id]);
        $sentReport = $stmt->fetch();
        
        if ($sentReport) {
            if ($sentReport['to_department_id'] == $department_id || $sentReport['from_department_id'] == $department_id) {
                $stmt = $pdo->prepare("
                    UPDATE sent_uploaded_reports SET 
                        is_deleted = 1,
                        deleted_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$id]);
                $deleted_count = $stmt->rowCount();
                
                if ($deleted_count > 0) {
                    $recycleStmt = $pdo->prepare("
                        INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin, deleted_by_name, created_at) 
                        VALUES (?, 'sent_uploaded_report', 'Sent Uploaded Report', ?, 0, ?, NOW())
                    ");
                    $recycleStmt->execute([$id, $department_id, $deleted_by]);
                }
            }
        }
    }

    if ($deleted_count > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Uploaded report deleted for department ' . $department_id,
            'data' => [
                'deleted_count' => $deleted_count,
                'department_id' => $department_id,
                'note' => 'Only deleted for department ' . $department_id
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Uploaded report not found or already deleted'
        ]);
    }

} catch(PDOException $e) {
    error_log("Delete uploaded report error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>