<?php
/**
 * mark_report_viewed.php - Mark a report as viewed
 * 
 * POST Parameters:
 * - report_id: ID of the report (required)
 * - is_admin: 1 for admin, 0 for department (default 0)
 * - user_id: ID of user marking as viewed (optional)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$reportId = isset($input['report_id']) ? intval($input['report_id']) : 0;
$isAdmin = isset($input['is_admin']) ? intval($input['is_admin']) : 0;
$userId = isset($input['user_id']) ? intval($input['user_id']) : 0;

if ($reportId === 0) {
    echo json_encode(['success' => false, 'message' => 'report_id is required']);
    exit;
}

try {
    if ($isAdmin == 1) {
        $query = "UPDATE reports SET is_viewed_by_admin = 1, updated_at = NOW() WHERE id = :id";
    } else {
        $query = "UPDATE reports SET is_viewed_by_department = 1, updated_at = NOW() WHERE id = :id";
    }
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $reportId);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Report marked as viewed'
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>