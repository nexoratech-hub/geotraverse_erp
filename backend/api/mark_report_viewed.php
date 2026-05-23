<?php
/**
 * API: Mark Report as Viewed (Remove NEW Badge)
 * Method: POST
 * Parameters:
 *   - report_id (required)
 *   - department_id (required)
 *   - is_admin (0 or 1)
 * 
 * Response: { "success": true, "message": "Report marked as viewed" }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

$report_id = isset($data['report_id']) ? intval($data['report_id']) : 0;
$department_id = isset($data['department_id']) ? intval($data['department_id']) : 0;
$is_admin = isset($data['is_admin']) ? intval($data['is_admin']) : 0;

if ($report_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Report ID is required']);
    exit;
}

try {
    if ($is_admin == 1) {
        $query = "UPDATE reports SET is_viewed_by_admin = 1 WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $report_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Report marked as viewed by Admin']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update']);
        }
    } else {
        // Verify this report belongs to this department (sent to them)
        $checkQuery = "SELECT id FROM reports 
                       WHERE id = :id 
                       AND sent_to_department = :dept_id 
                       AND is_viewed_by_department = 0";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':id', $report_id);
        $checkStmt->bindParam(':dept_id', $department_id);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() == 0) {
            echo json_encode(['success' => false, 'message' => 'Report not found or already viewed']);
            exit;
        }
        
        $query = "UPDATE reports SET is_viewed_by_department = 1 WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $report_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Report marked as viewed']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update']);
        }
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>