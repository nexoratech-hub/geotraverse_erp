<?php
// backend/api/mark_project_viewed.php
header('Content-Type: application/json');
require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['project_id']) || !isset($data['department_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

$project_id = intval($data['project_id']);
$dept_id = intval($data['department_id']);

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("UPDATE projects SET 
        is_viewed_by_department = 1,
        viewed_by_department_at = NOW()
        WHERE id = ? AND sent_to_department = ?");
    
    $stmt->execute([$project_id, $dept_id]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>