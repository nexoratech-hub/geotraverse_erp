<?php
// backend/api/delete_dailywork.php

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=geotraverse_erp;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['id']) || !isset($input['department_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields: id, department_id']);
    exit;
}

$dailyworkId = intval($input['id']);
$departmentId = intval($input['department_id']);
$deletedBy = isset($input['deleted_by']) ? $input['deleted_by'] : 'System';

try {
    $deletedCount = 0;
    $deletedItems = [];

    // ============================================================
    // 1. CHECK IF THIS IS AN ORIGINAL DAILY WORK
    // ============================================================
    $stmt = $pdo->prepare("SELECT id, work_description, project_id, project_name, department_id, is_deleted 
                           FROM dailywork WHERE id = ?");
    $stmt->execute([$dailyworkId]);
    $dailywork = $stmt->fetch();

    // ============================================================
    // 2. CHECK IF THIS IS A SENT DAILY WORK (RECEIVER VIEW)
    // ============================================================
    $sentStmt = $pdo->prepare("SELECT id, original_dailywork_id, dailywork_project_name, from_department_id, to_department_id, is_deleted 
                               FROM sent_dailywork WHERE id = ?");
    $sentStmt->execute([$dailyworkId]);
    $sentDailywork = $sentStmt->fetch();

    // ============================================================
    // 3. CASE 1: ORIGINAL DAILY WORK - SENDER ANAFUTA
    // ============================================================
    if ($dailywork && $dailywork['department_id'] == $departmentId) {
        // Sender anafuta original daily work yake
        // Receiver atabaki na copy yake
        
        $query = "UPDATE dailywork SET 
                  is_deleted = 1,
                  deleted_at = NOW()
                  WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$dailyworkId]);
        $deletedCount++;
        $deletedItems[] = [
            'table' => 'dailywork',
            'id' => $dailyworkId,
            'name' => $dailywork['work_description'] ?? 'Daily Work',
            'type' => 'original'
        ];

        // Add to recycle bin
        $recycleQuery = "INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin, deleted_by_name, created_at) 
                        VALUES (?, 'dailywork', ?, ?, 0, ?, NOW())";
        $stmt = $pdo->prepare($recycleQuery);
        $stmt->execute([$dailyworkId, $dailywork['work_description'] ?? 'Daily Work', $departmentId, $deletedBy]);

        $message = "Original daily work deleted by sender. Receiver still has their copy.";
    }

    // ============================================================
    // 4. CASE 2: SENT DAILY WORK - RECEIVER ANAFUTA COPY YAKE
    // ============================================================
    else if ($sentDailywork && $sentDailywork['to_department_id'] == $departmentId) {
        // Receiver anafuta sent daily work copy yake
        
        $query = "UPDATE sent_dailywork SET 
                  is_deleted = 1,
                  deleted_at = NOW()
                  WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$dailyworkId]);
        $deletedCount++;
        $deletedItems[] = [
            'table' => 'sent_dailywork',
            'id' => $dailyworkId,
            'name' => $sentDailywork['dailywork_project_name'] ?? 'Daily Work',
            'type' => 'sent_copy'
        ];

        // Add to recycle bin
        $recycleQuery = "INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin, deleted_by_name, created_at) 
                        VALUES (?, 'sent_dailywork', ?, ?, 0, ?, NOW())";
        $stmt = $pdo->prepare($recycleQuery);
        $stmt->execute([$dailyworkId, $sentDailywork['dailywork_project_name'] ?? 'Daily Work', $departmentId, $deletedBy]);

        $message = "Sent daily work copy deleted by receiver. Sender still has original.";
    }

    // ============================================================
    // 5. CASE 3: SENT DAILY WORK - SENDER ANAFUTA RECORD YA KUTUMA
    // ============================================================
    else if ($sentDailywork && $sentDailywork['from_department_id'] == $departmentId) {
        // Sender anafuta record ya sent daily work (zile alizotuma)
        // Hii haifuti copy ya receiver
        
        $query = "UPDATE sent_dailywork SET 
                  is_deleted = 1,
                  deleted_at = NOW()
                  WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$dailyworkId]);
        $deletedCount++;
        $deletedItems[] = [
            'table' => 'sent_dailywork',
            'id' => $dailyworkId,
            'name' => $sentDailywork['dailywork_project_name'] ?? 'Daily Work',
            'type' => 'sent_from'
        ];

        // Add to recycle bin
        $recycleQuery = "INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin, deleted_by_name, created_at) 
                        VALUES (?, 'sent_dailywork', ?, ?, 0, ?, NOW())";
        $stmt = $pdo->prepare($recycleQuery);
        $stmt->execute([$dailyworkId, $sentDailywork['dailywork_project_name'] ?? 'Daily Work', $departmentId, $deletedBy]);

        $message = "Sent daily work record deleted by sender. Receiver still has their copy.";
    }

    // ============================================================
    // 6. CASE 4: NO RECORD FOUND
    // ============================================================
    else {
        $message = "Daily work not found or already deleted for this department";
    }

    // ============================================================
    // 7. SEND RESPONSE
    // ============================================================
    if ($deletedCount > 0) {
        echo json_encode([
            'success' => true,
            'message' => $message ?? 'Daily work deleted successfully',
            'data' => [
                'deleted_count' => $deletedCount,
                'deleted_items' => $deletedItems,
                'deleted_by_department' => $departmentId,
                'note' => 'Soft delete only - data can be restored from recycle bin'
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $message ?? 'Daily work not found or already deleted for this department'
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