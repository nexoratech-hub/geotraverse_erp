<?php
require_once 'config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['report_id'])) {
    echo json_encode(['success' => false, 'message' => 'Report ID required']);
    exit;
}

try {
    // Check if report exists
    $checkStmt = $pdo->prepare("SELECT id, title FROM reports WHERE id = ?");
    $checkStmt->execute([$data['report_id']]);
    $report = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$report) {
        echo json_encode(['success' => true, 'message' => 'Report already deleted']);
        exit;
    }
    
    // Update report as deleted
    $stmt = $pdo->prepare("UPDATE reports SET is_deleted = 1, deleted_by_department = :dept_deleted, deleted_by_admin = :admin_deleted WHERE id = :id");
    
    $stmt->execute([
        'dept_deleted' => isset($data['department_id']) && $data['department_id'] ? 1 : 0,
        'admin_deleted' => isset($data['is_admin']) && $data['is_admin'] ? 1 : 0,
        'id' => $data['report_id']
    ]);
    
    // Add to recycle bin (check if already exists)
    $checkBinStmt = $pdo->prepare("SELECT id FROM recycle_bin WHERE item_id = ? AND item_type = 'report'");
    $checkBinStmt->execute([$data['report_id']]);
    
    if (!$checkBinStmt->fetch()) {
        $stmt2 = $pdo->prepare("INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin) VALUES (?, 'report', ?, ?, ?)");
        $stmt2->execute([
            $data['report_id'], 
            $report['title'], 
            isset($data['department_id']) && $data['department_id'] ? $data['department_id'] : null, 
            isset($data['is_admin']) && $data['is_admin'] ? 1 : 0
        ]);
    }
    
    echo json_encode(['success' => true, 'message' => 'Report moved to recycle bin']);
    
} catch(PDOException $e) {
    error_log('Delete report error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>