<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!$data || !isset($data->id)) {
    echo json_encode(['success' => false, 'message' => 'Report ID required']);
    exit;
}

$report_id = intval($data->id);
$title = isset($data->title) ? $data->title : null;
$period = isset($data->period) ? $data->period : null;
$content = isset($data->content) ? $data->content : null;

try {
    $updateFields = [];
    $params = [];
    
    if ($title) {
        $updateFields[] = "title = ?";
        $params[] = $title;
    }
    if ($period) {
        $updateFields[] = "period = ?";
        $params[] = $period;
    }
    if ($content) {
        $updateFields[] = "content = ?";
        $params[] = $content;
    }
    
    if (empty($updateFields)) {
        echo json_encode(['success' => false, 'message' => 'No fields to update']);
        exit;
    }
    
    $params[] = $report_id;
    $query = "UPDATE reports SET " . implode(", ", $updateFields) . " WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    
    echo json_encode(['success' => true, 'message' => 'Report updated successfully']);
    
} catch (PDOException $e) {
    error_log("Update report error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>