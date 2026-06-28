<?php
// backend/api/delete_dailywork.php
// ============================================================
// FIXED: Delete Daily Work - Soft Delete Based on Ownership
// ============================================================

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

// ============================================================
// DATABASE CONNECTION
// ============================================================
try {
    $pdo = new PDO("mysql:host=localhost;dbname=geotraverse_erp;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}

// ============================================================
// GET INPUT
// ============================================================
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
    // 1. GET THE DAILY WORK RECORD
    // ============================================================
    $stmt = $pdo->prepare("SELECT 
                            id, 
                            work_description, 
                            project_id, 
                            project_name, 
                            department_id,
                            is_original,
                            is_sent_copy,
                            sent_from_dept,
                            sent_to_dept,
                            is_deleted
                           FROM dailywork 
                           WHERE id = ? AND is_deleted = 0");
    $stmt->execute([$dailyworkId]);
    $dailywork = $stmt->fetch();

    // ============================================================
    // 2. IF FOUND IN DAILYWORK TABLE
    // ============================================================
    if ($dailywork) {
        // ============================================================
        // CASE 1: ORIGINAL DAILY WORK - SENDER ANAFUTA
        // ============================================================
        if ($dailywork['is_original'] == 1 && $dailywork['department_id'] == $departmentId) {
            // Sender anafuta original daily work yake
            // Receiver atabaki na copy yake (kama ipo)
            
            $query = "UPDATE dailywork SET 
                      is_deleted = 1,
                      deleted_at = NOW()
                      WHERE id = ? AND is_original = 1";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$dailyworkId]);
            $deletedCount++;
            $deletedItems[] = [
                'table' => 'dailywork',
                'id' => $dailyworkId,
                'name' => $dailywork['work_description'] ?? 'Daily Work',
                'type' => 'original',
                'note' => 'Original deleted by sender. Receiver copies remain.'
            ];

            // Add to recycle bin
            $recycleQuery = "INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin, deleted_by_name, created_at) 
                            VALUES (?, 'dailywork_original', ?, ?, 0, ?, NOW())";
            $stmt = $pdo->prepare($recycleQuery);
            $stmt->execute([$dailyworkId, $dailywork['work_description'] ?? 'Daily Work', $departmentId, $deletedBy]);

            $message = "Original daily work deleted by sender. Receiver copies remain.";
        }
        
        // ============================================================
        // CASE 2: SENT COPY DAILY WORK - RECEIVER ANAFUTA COPY YAKE
        // ============================================================
        else if ($dailywork['is_sent_copy'] == 1 && $dailywork['sent_to_dept'] == $departmentId) {
            // Receiver anafuta copy yake ya daily work
            
            $query = "UPDATE dailywork SET 
                      is_deleted = 1,
                      deleted_at = NOW()
                      WHERE id = ? AND is_sent_copy = 1 AND sent_to_dept = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$dailyworkId, $departmentId]);
            $deletedCount++;
            $deletedItems[] = [
                'table' => 'dailywork',
                'id' => $dailyworkId,
                'name' => $dailywork['work_description'] ?? 'Daily Work',
                'type' => 'sent_copy',
                'note' => 'Copy deleted by receiver. Original remains with sender.'
            ];

            // Add to recycle bin
            $recycleQuery = "INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin, deleted_by_name, created_at) 
                            VALUES (?, 'dailywork_copy', ?, ?, 0, ?, NOW())";
            $stmt = $pdo->prepare($recycleQuery);
            $stmt->execute([$dailyworkId, $dailywork['work_description'] ?? 'Daily Work', $departmentId, $deletedBy]);

            $message = "Daily work copy deleted by receiver. Original remains with sender.";
        }
        
        // ============================================================
        // CASE 3: DAILY WORK OWNED BY THIS DEPARTMENT (Original)
        // ============================================================
        else if ($dailywork['department_id'] == $departmentId) {
            // Department owns this daily work (likely original)
            
            $query = "UPDATE dailywork SET 
                      is_deleted = 1,
                      deleted_at = NOW()
                      WHERE id = ? AND department_id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$dailyworkId, $departmentId]);
            $deletedCount++;
            $deletedItems[] = [
                'table' => 'dailywork',
                'id' => $dailyworkId,
                'name' => $dailywork['work_description'] ?? 'Daily Work',
                'type' => 'owned',
                'note' => 'Daily work deleted by owner department.'
            ];

            // Add to recycle bin
            $recycleQuery = "INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin, deleted_by_name, created_at) 
                            VALUES (?, 'dailywork', ?, ?, 0, ?, NOW())";
            $stmt = $pdo->prepare($recycleQuery);
            $stmt->execute([$dailyworkId, $dailywork['work_description'] ?? 'Daily Work', $departmentId, $deletedBy]);

            $message = "Daily work deleted by owner department.";
        }
        
        // ============================================================
        // CASE 4: NO PERMISSION TO DELETE
        // ============================================================
        else {
            echo json_encode([
                'success' => false,
                'message' => 'You do not have permission to delete this daily work record.',
                'debug' => [
                    'dailywork_department_id' => $dailywork['department_id'],
                    'your_department_id' => $departmentId,
                    'is_original' => $dailywork['is_original'],
                    'is_sent_copy' => $dailywork['is_sent_copy']
                ]
            ]);
            exit;
        }
    }
    
    // ============================================================
    // 3. IF NOT FOUND IN DAILYWORK, CHECK SENT_DAILYWORK TABLE
    // ============================================================
    else {
        // Check sent_dailywork table for copies
        $sentStmt = $pdo->prepare("SELECT 
                                    id, 
                                    original_dailywork_id,
                                    dailywork_project_name,
                                    from_department_id,
                                    to_department_id,
                                    is_deleted
                                   FROM sent_dailywork 
                                   WHERE id = ? AND is_deleted = 0");
        $sentStmt->execute([$dailyworkId]);
        $sentDailywork = $sentStmt->fetch();
        
        if ($sentDailywork) {
            // ============================================================
            // CASE 5: SENT DAILYWORK - RECEIVER ANAFUTA
            // ============================================================
            if ($sentDailywork['to_department_id'] == $departmentId) {
                $query = "UPDATE sent_dailywork SET 
                          is_deleted = 1,
                          deleted_at = NOW()
                          WHERE id = ? AND to_department_id = ?";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$dailyworkId, $departmentId]);
                $deletedCount++;
                $deletedItems[] = [
                    'table' => 'sent_dailywork',
                    'id' => $dailyworkId,
                    'name' => $sentDailywork['dailywork_project_name'] ?? 'Daily Work',
                    'type' => 'sent_copy',
                    'note' => 'Sent dailywork copy deleted by receiver.'
                ];
                
                // Add to recycle bin
                $recycleQuery = "INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin, deleted_by_name, created_at) 
                                VALUES (?, 'sent_dailywork_copy', ?, ?, 0, ?, NOW())";
                $stmt = $pdo->prepare($recycleQuery);
                $stmt->execute([$dailyworkId, $sentDailywork['dailywork_project_name'] ?? 'Daily Work', $departmentId, $deletedBy]);
                
                $message = "Sent daily work copy deleted by receiver.";
            }
            
            // ============================================================
            // CASE 6: SENT DAILYWORK - SENDER ANAFUTA RECORD YA KUTUMA
            // ============================================================
            else if ($sentDailywork['from_department_id'] == $departmentId) {
                $query = "UPDATE sent_dailywork SET 
                          is_deleted = 1,
                          deleted_at = NOW()
                          WHERE id = ? AND from_department_id = ?";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$dailyworkId, $departmentId]);
                $deletedCount++;
                $deletedItems[] = [
                    'table' => 'sent_dailywork',
                    'id' => $dailyworkId,
                    'name' => $sentDailywork['dailywork_project_name'] ?? 'Daily Work',
                    'type' => 'sent_from',
                    'note' => 'Sent dailywork record deleted by sender.'
                ];
                
                // Add to recycle bin
                $recycleQuery = "INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin, deleted_by_name, created_at) 
                                VALUES (?, 'sent_dailywork_from', ?, ?, 0, ?, NOW())";
                $stmt = $pdo->prepare($recycleQuery);
                $stmt->execute([$dailyworkId, $sentDailywork['dailywork_project_name'] ?? 'Daily Work', $departmentId, $deletedBy]);
                
                $message = "Sent daily work record deleted by sender.";
            }
            
            else {
                echo json_encode([
                    'success' => false,
                    'message' => 'You do not have permission to delete this sent daily work record.',
                    'debug' => [
                        'from_department_id' => $sentDailywork['from_department_id'],
                        'to_department_id' => $sentDailywork['to_department_id'],
                        'your_department_id' => $departmentId
                    ]
                ]);
                exit;
            }
        } else {
            // ============================================================
            // CASE 7: RECORD NOT FOUND ANYWHERE
            // ============================================================
            echo json_encode([
                'success' => false,
                'message' => 'Daily work record not found or already deleted.'
            ]);
            exit;
        }
    }

    // ============================================================
    // 4. SEND RESPONSE
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
            'message' => 'No records were deleted. Please check your permissions.'
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