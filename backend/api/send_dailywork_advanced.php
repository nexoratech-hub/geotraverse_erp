<?php
// backend/api/send_dailywork_advanced.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// ============ DATABASE CONNECTION ============
$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// ============ GET POST DATA ============
$input = json_decode(file_get_contents('php://input'), true);

$dailywork_id = isset($input['dailywork_id']) ? intval($input['dailywork_id']) : 0;
$from_department_id = isset($input['from_department_id']) ? intval($input['from_department_id']) : 0;
$to_department_id = isset($input['to_department_id']) ? intval($input['to_department_id']) : 0;
$sent_by = isset($input['sent_by']) ? trim($input['sent_by']) : 'System';

// ============ VALIDATION ============
if ($dailywork_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid daily work ID']);
    exit;
}

if ($from_department_id <= 0 || $to_department_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid department IDs']);
    exit;
}

if ($from_department_id == $to_department_id) {
    echo json_encode(['success' => false, 'message' => 'Cannot send to your own department']);
    exit;
}

// ============ GET DAILY WORK DATA ============
try {
    $stmt = $pdo->prepare("
        SELECT * FROM dailywork 
        WHERE id = ? AND is_deleted = 0
    ");
    $stmt->execute([$dailywork_id]);
    $dailywork = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$dailywork) {
        echo json_encode(['success' => false, 'message' => 'Daily work record not found']);
        exit;
    }
    
    // ============ CREATE COPY FOR RECIPIENT ============
    $insert_stmt = $pdo->prepare("
        INSERT INTO dailywork (
            date, work_description, work_type, 
            project_id, project_name,
            department_id, budget, amount, expenses, income, status,
            created_by, created_at,
            is_original, is_sent_copy, original_dailywork_id,
            sent_from_dept, sent_to_dept, is_viewed_by_department
        ) VALUES (
            ?, ?, ?,
            ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, NOW(),
            0, 1, ?,
            ?, ?, 0
        )
    ");
    
    $insert_stmt->execute([
        $dailywork['date'],
        $dailywork['work_description'],
        $dailywork['work_type'],
        $dailywork['project_id'],
        $dailywork['project_name'],
        $to_department_id,
        $dailywork['budget'],
        $dailywork['amount'],
        $dailywork['expenses'],
        $dailywork['income'],
        $dailywork['status'],
        $sent_by,
        $dailywork_id,
        $from_department_id,
        $to_department_id
    ]);
    
    $new_dailywork_id = $pdo->lastInsertId();
    
    // ============ SAVE TO SENT_DAILYWORK TABLE ============
    $sent_stmt = $pdo->prepare("
        INSERT INTO sent_dailywork (
            original_dailywork_id, dailywork_data, from_department_id,
            to_department_id, sent_by, sent_at
        ) VALUES (
            ?, ?, ?, ?, ?, NOW()
        )
    ");
    
    $sent_stmt->execute([
        $dailywork_id,
        json_encode($dailywork),
        $from_department_id,
        $to_department_id,
        $sent_by
    ]);
    
    // ============ UPDATE ORIGINAL DAILY WORK ============
    $update_stmt = $pdo->prepare("
        UPDATE dailywork 
        SET is_sent = 1,
            sent_from_dept = ?,
            sent_to_dept = ?,
            sent_count = sent_count + 1,
            last_sent_at = NOW()
        WHERE id = ?
    ");
    $update_stmt->execute([$from_department_id, $to_department_id, $dailywork_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Daily work sent successfully',
        'data' => [
            'original_dailywork_id' => $dailywork_id,
            'new_dailywork_id' => $new_dailywork_id,
            'from_department_id' => $from_department_id,
            'to_department_id' => $to_department_id
        ]
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>