<?php
/**
 * API: Soft Delete a Report
 * Method: POST
 * Parameters:
 *   - report_id (required)
 *   - department_id (required)
 *   - is_admin (0 or 1)
 * 
 * Soft delete sets deleted_by_department=1 (for department users)
 * or deleted_by_admin=1 (for super admin)
 * 
 * Response: { "success": true, "message": "Report moved to recycle bin" }
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
        // Super Admin: soft delete by admin
        $query = "UPDATE reports SET deleted_by_admin = 1, deleted_at = NOW() WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $report_id);
        
        if ($stmt->execute()) {
            // Also add to recycle_bin table
            $reportQuery = "SELECT title FROM reports WHERE id = :id";
            $reportStmt = $db->prepare($reportQuery);
            $reportStmt->bindParam(':id', $report_id);
            $reportStmt->execute();
            $report = $reportStmt->fetch(PDO::FETCH_ASSOC);
            
            $binQuery = "INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_admin, deleted_at) 
                         VALUES (:item_id, 'report', :item_name, 1, NOW())";
            $binStmt = $db->prepare($binQuery);
            $binStmt->bindParam(':item_id', $report_id);
            $binStmt->bindParam(':item_name', $report['title']);
            $binStmt->execute();
            
            echo json_encode(['success' => true, 'message' => 'Report permanently deleted by Admin']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete report']);
        }
    } else {
        // Department user: soft delete for their department only
        // Verify this report belongs to this department or was sent to them
        $checkQuery = "SELECT id, title FROM reports 
                       WHERE id = :id 
                       AND (department_id = :dept_id OR sent_to_department = :dept_id)
                       AND deleted_by_department = 0 
                       AND deleted_by_admin = 0";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':id', $report_id);
        $checkStmt->bindParam(':dept_id', $department_id);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() == 0) {
            echo json_encode(['success' => false, 'message' => 'Report not found or you do not have permission to delete it']);
            exit;
        }
        
        $report = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        $query = "UPDATE reports SET deleted_by_department = 1, deleted_at = NOW() WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $report_id);
        
        if ($stmt->execute()) {
            // Add to recycle_bin for this department
            $binQuery = "INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_at) 
                         VALUES (:item_id, 'report', :item_name, :dept_id, NOW())";
            $binStmt = $db->prepare($binQuery);
            $binStmt->bindParam(':item_id', $report_id);
            $binStmt->bindParam(':item_name', $report['title']);
            $binStmt->bindParam(':dept_id', $department_id);
            $binStmt->execute();
            
            echo json_encode([
                'success' => true,
                'message' => 'Report moved to recycle bin',
                'item' => ['id' => $report_id, 'type' => 'report', 'name' => $report['title']]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete report']);
        }
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>