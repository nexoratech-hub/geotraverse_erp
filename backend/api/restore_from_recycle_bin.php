<?php
// restore_from_recycle_bin.php - Restore both Added Reports and Sent Added Reports

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB Connection Error: ' . $e->getMessage()]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['recycle_id'])) {
    echo json_encode(['success' => false, 'message' => 'Recycle ID required']);
    exit;
}

$recycleId = (int)$data['recycle_id'];

try {
    // Get recycle bin item
    $stmt = $pdo->prepare("
        SELECT * FROM recycle_bin WHERE id = ?
    ");
    $stmt->execute([$recycleId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$item) {
        echo json_encode(['success' => false, 'message' => 'Item not found in recycle bin']);
        exit;
    }
    
    $itemId = (int)$item['item_id'];
    $itemType = $item['item_type'];
    $itemName = $item['item_name'];
    
    // ============================================================
    // RESTORE BASED ON ITEM TYPE
    // ============================================================
    if ($itemType === 'report') {
        // Restore to reports table
        $restoreStmt = $pdo->prepare("
            UPDATE reports 
            SET 
                is_deleted = 0,
                deleted_by_department = NULL,
                deleted_by_admin = NULL,
                deleted_at = NULL
            WHERE id = ?
        ");
        $restoreStmt->execute([$itemId]);
        
        if ($restoreStmt->rowCount() > 0) {
            // Delete from recycle bin
            $delStmt = $pdo->prepare("DELETE FROM recycle_bin WHERE id = ?");
            $delStmt->execute([$recycleId]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Report restored successfully',
                'item_type' => 'report',
                'item_id' => $itemId
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Report not found in reports table'
            ]);
        }
        
    } elseif ($itemType === 'sent_report') {
        // Restore to sent_reports table
        $restoreStmt = $pdo->prepare("
            UPDATE sent_reports 
            SET 
                is_deleted = 0,
                deleted_by_department = NULL,
                deleted_by_admin = NULL,
                deleted_at = NULL
            WHERE id = ?
        ");
        $restoreStmt->execute([$itemId]);
        
        if ($restoreStmt->rowCount() > 0) {
            // Delete from recycle bin
            $delStmt = $pdo->prepare("DELETE FROM recycle_bin WHERE id = ?");
            $delStmt->execute([$recycleId]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Sent report restored successfully',
                'item_type' => 'sent_report',
                'item_id' => $itemId
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Sent report not found in sent_reports table'
            ]);
        }
        
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Unsupported item type: ' . $itemType
        ]);
    }
    
} catch(PDOException $e) {
    error_log('Restore error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>