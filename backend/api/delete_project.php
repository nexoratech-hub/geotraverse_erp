<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['project_id'])) {
    sendResponse(false, 'Project ID required');
}

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Get project name first
    $stmtGet = $pdo->prepare("SELECT name FROM projects WHERE id = ?");
    $stmtGet->execute([$data['project_id']]);
    $project = $stmtGet->fetch();
    $projectName = $project ? $project['name'] : 'Unknown';
    
    // Soft delete - FIXED: Use correct column names
    $stmt = $pdo->prepare("UPDATE projects SET is_deleted = 1, deleted_by_department = :dept_deleted, deleted_by_admin = :admin_deleted, updated_at = NOW() WHERE id = :id");
    
    $stmt->execute([
        'dept_deleted' => isset($data['department_id']) ? 1 : 0,
        'admin_deleted' => isset($data['is_admin']) && $data['is_admin'] == 1 ? 1 : 0,
        'id' => $data['project_id']
    ]);
    
    // Add to recycle bin - FIXED: Use correct syntax
    $stmt2 = $pdo->prepare("INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin) VALUES (?, 'project', ?, ?, ?)");
    $stmt2->execute([
        $data['project_id'], 
        $projectName, 
        $data['department_id'] ?? null, 
        isset($data['is_admin']) && $data['is_admin'] == 1 ? 1 : 0
    ]);
    
    $pdo->commit();
    sendResponse(true, 'Project moved to recycle bin');
    
} catch(PDOException $e) {
    $pdo->rollBack();
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>