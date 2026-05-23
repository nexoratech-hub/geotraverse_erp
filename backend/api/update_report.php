<?php
/**
 * API: Update an existing Report
 * Method: POST
 * Parameters:
 *   - id (required)
 *   - title
 *   - period
 *   - content
 *   - status
 *   - department_id (for authorization check)
 * 
 * Response: { "success": true, "message": "Report updated successfully" }
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

$report_id = isset($data['id']) ? intval($data['id']) : 0;
$department_id = isset($data['department_id']) ? intval($data['department_id']) : 0;
$is_admin = isset($data['is_admin']) ? intval($data['is_admin']) : 0;

if ($report_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Report ID is required']);
    exit;
}

// Build update fields dynamically
$updateFields = [];
$params = [':id' => $report_id];

if (isset($data['title'])) {
    $updateFields[] = "title = :title";
    $params[':title'] = trim($data['title']);
}
if (isset($data['period'])) {
    $updateFields[] = "period = :period";
    $params[':period'] = $data['period'];
}
if (isset($data['content'])) {
    $updateFields[] = "content = :content";
    $params[':content'] = trim($data['content']);
}
if (isset($data['status'])) {
    $updateFields[] = "status = :status";
    $params[':status'] = $data['status'];
}

if (empty($updateFields)) {
    echo json_encode(['success' => false, 'message' => 'No fields to update']);
    exit;
}

try {
    // First verify ownership (unless admin)
    if ($is_admin != 1) {
        $checkQuery = "SELECT id FROM reports WHERE id = :id AND department_id = :dept_id AND deleted_by_department = 0 AND deleted_by_admin = 0";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':id', $report_id);
        $checkStmt->bindParam(':dept_id', $department_id);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() == 0) {
            echo json_encode(['success' => false, 'message' => 'Report not found or you do not have permission to edit it']);
            exit;
        }
    }
    
    $query = "UPDATE reports SET " . implode(', ', $updateFields) . " WHERE id = :id";
    $stmt = $db->prepare($query);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Report updated successfully'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update report']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>