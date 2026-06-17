<?php
// backend/api/send_project.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['project_id']) || !isset($data['to_department_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$project_id = intval($data['project_id']);
$to_dept = intval($data['to_department_id']);
$from_dept = intval($data['from_department_id'] ?? 0);

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Update project - use CORRECT column names
    $stmt = $conn->prepare("UPDATE projects SET 
        sent_to_dept = ?, 
        sent_from_dept = ?,
        is_viewed_by_department = 0,
        is_viewed_by_admin = 0,
        updated_at = NOW()
        WHERE id = ?");
    
    $stmt->execute([$to_dept, $from_dept, $project_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Project sent successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Project not found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>