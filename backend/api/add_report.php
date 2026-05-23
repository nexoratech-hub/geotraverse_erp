<?php
/**
 * API: Add a new Report
 * Method: POST
 * Parameters: 
 *   - title (required)
 *   - period (daily/weekly/monthly/quarterly/annual)
 *   - content (required)
 *   - department_id (required)
 *   - status (draft/sent) - optional, default 'draft'
 * 
 * Response: { "success": true, "message": "Report added successfully", "report_id": 123 }
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

$title = isset($data['title']) ? trim($data['title']) : '';
$period = isset($data['period']) ? $data['period'] : 'monthly';
$content = isset($data['content']) ? trim($data['content']) : '';
$department_id = isset($data['department_id']) ? intval($data['department_id']) : 0;
$status = isset($data['status']) ? $data['status'] : 'draft';

if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Report title is required']);
    exit;
}

if (empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Report content is required']);
    exit;
}

if ($department_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Department ID is required']);
    exit;
}

// Validate period
$allowedPeriods = ['daily', 'weekly', 'monthly', 'quarterly', 'annual'];
if (!in_array($period, $allowedPeriods)) {
    $period = 'monthly';
}

// Validate status
$allowedStatus = ['draft', 'sent'];
if (!in_array($status, $allowedStatus)) {
    $status = 'draft';
}

try {
    $query = "INSERT INTO reports (title, period, content, status, department_id, created_at) 
              VALUES (:title, :period, :content, :status, :department_id, NOW())";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':period', $period);
    $stmt->bindParam(':content', $content);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':department_id', $department_id);
    
    if ($stmt->execute()) {
        $report_id = $db->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Report added successfully',
            'report_id' => $report_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add report']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>