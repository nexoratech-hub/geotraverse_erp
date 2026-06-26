<?php
// backend/api/delete_fund_request.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ============================================================
// ERROR REPORTING
// ============================================================
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// ============================================================
// DATABASE CONNECTION
// ============================================================
try {
    $pdo = new PDO("mysql:host=localhost;dbname=geotraverse_erp;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit();
}

// ============================================================
// GET INPUT
// ============================================================
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

// ============================================================
// PARAMETERS
// ============================================================
$request_id = isset($input['request_id']) ? intval($input['request_id']) : 
              (isset($input['id']) ? intval($input['id']) : 0);
$department_id = isset($input['department_id']) ? intval($input['department_id']) : 0;
$deleted_by = isset($input['deleted_by']) ? $input['deleted_by'] : 'System';
$is_admin = isset($input['is_admin']) ? intval($input['is_admin']) : 0;
$permanent_delete = isset($input['permanent_delete']) ? intval($input['permanent_delete']) : 0;

if (!$request_id || !$department_id) {
    echo json_encode([
        'success' => false, 
        'message' => 'Missing required fields: request_id and department_id'
    ]);
    exit();
}

// ============================================================
// CHECK IF REQUEST EXISTS
// ============================================================
try {
    $stmt = $pdo->prepare("SELECT * FROM fund_requests WHERE id = ?");
    $stmt->execute([$request_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$request) {
        echo json_encode(['success' => false, 'message' => 'Request not found']);
        exit();
    }
    
    // ============================================================
    // CHECK PERMISSIONS
    // ============================================================
    // Only Super Admin (department_id = 1) can permanently delete
    if ($permanent_delete == 1 && $department_id != 1) {
        echo json_encode([
            'success' => false, 
            'message' => 'Only Super Admin can permanently delete requests'
        ]);
        exit();
    }
    
    // ============================================================
    // PERFORM DELETE
    // ============================================================
    if ($permanent_delete == 1) {
        // ============================================================
        // SUPER ADMIN - PERMANENT DELETE FROM DATABASE
        // ============================================================
        
        $pdo->beginTransaction();
        
        $deletedItems = [];
        
        // 1. Delete linked transactions
        $transCheck = $pdo->prepare("SELECT id, source FROM transactions WHERE source LIKE ? OR description LIKE ?");
        $searchTerm = '%' . $request['title'] . '%';
        $transCheck->execute([$searchTerm, $searchTerm]);
        $linkedTransactions = $transCheck->fetchAll();
        
        if (count($linkedTransactions) > 0) {
            $transDelete = $pdo->prepare("DELETE FROM transactions WHERE source LIKE ? OR description LIKE ?");
            $transDelete->execute([$searchTerm, $searchTerm]);
            $deletedItems['transactions'] = count($linkedTransactions);
            error_log("🗑️ Deleted " . count($linkedTransactions) . " linked transactions for request #" . $request_id);
        }
        
        // 2. Delete notifications linked to this request
        $notifDelete = $pdo->prepare("DELETE FROM notifications WHERE item_type = 'fund_request' AND item_id = ?");
        $notifDelete->execute([$request_id]);
        $deletedItems['notifications'] = $notifDelete->rowCount();
        
        // 3. Delete from recycle bin if exists
        $recycleDelete = $pdo->prepare("DELETE FROM recycle_bin WHERE item_id = ? AND item_type = 'budget_request'");
        $recycleDelete->execute([$request_id]);
        $deletedItems['recycle_bin'] = $recycleDelete->rowCount();
        
        // 4. Delete from sent_requests if exists (for sent copies)
        try {
            $sentDelete = $pdo->prepare("DELETE FROM sent_requests WHERE original_request_id = ?");
            $sentDelete->execute([$request_id]);
            $deletedItems['sent_requests'] = $sentDelete->rowCount();
        } catch(PDOException $e) {
            // Table might not exist
        }
        
        // 5. PERMANENT DELETE the request
        $stmt = $pdo->prepare("DELETE FROM fund_requests WHERE id = ?");
        $stmt->execute([$request_id]);
        $deletedItems['request'] = $stmt->rowCount();
        
        // Commit transaction
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Request permanently deleted from ALL dashboards',
            'data' => [
                'request_id' => $request_id,
                'permanent' => true,
                'deleted_by' => $deleted_by,
                'deleted_items' => $deletedItems
            ]
        ]);
        
    } else {
        // ============================================================
        // DEPARTMENT DELETE - SOFT DELETE (for Finance or other depts)
        // ============================================================
        if ($is_admin == 1) {
            // Super Admin soft delete - marks deleted_by_admin
            $stmt = $pdo->prepare("UPDATE fund_requests SET 
                deleted_by_admin = 1,
                deleted_by_department = 0,
                deleted_at = NOW(),
                deleted_by_name = ?
                WHERE id = ?");
            $stmt->execute([$deleted_by, $request_id]);
        } else {
            // Department soft delete - marks deleted_by_department
            $stmt = $pdo->prepare("UPDATE fund_requests SET 
                deleted_by_department = ?,
                deleted_by_admin = 0,
                deleted_at = NOW(),
                deleted_by_name = ?
                WHERE id = ?");
            $stmt->execute([$department_id, $deleted_by, $request_id]);
        }
        
        $affected = $stmt->rowCount();
        
        if ($affected > 0) {
            // Add to recycle bin
            try {
                $itemName = $request['title'] ?? 'Budget Request #' . $request_id;
                $recycleStmt = $pdo->prepare("INSERT INTO recycle_bin 
                    (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin, deleted_by_name, created_at) 
                    VALUES (?, 'budget_request', ?, ?, ?, ?, NOW())");
                $recycleStmt->execute([
                    $request_id,
                    $itemName,
                    $department_id,
                    $is_admin,
                    $deleted_by
                ]);
            } catch(PDOException $e) {
                // Recycle bin might not exist, ignore
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Request soft deleted for department ' . $department_id,
                'data' => [
                    'request_id' => $request_id,
                    'department_id' => $department_id,
                    'is_admin' => $is_admin,
                    'soft_delete' => true
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No changes made. Request may already be deleted.'
            ]);
        }
    }
    
} catch(PDOException $e) {
    // Rollback transaction if active
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Delete fund request error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch(Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>