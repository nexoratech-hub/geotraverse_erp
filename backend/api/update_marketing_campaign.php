<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id']) || $data['id'] <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Campaign ID is required'
    ]);
    exit;
}

try {
    $query = "UPDATE marketing_campaigns SET 
              campaign_name = :campaign_name,
              campaign_type = :campaign_type,
              budget = :budget,
              spent = :spent,
              start_date = :start_date,
              end_date = :end_date,
              target_audience = :target_audience,
              description = :description,
              status = :status,
              updated_at = NOW()
              WHERE id = :id";
    
    $stmt = $db->prepare($query);
    
    $id = intval($data['id']);
    $campaign_name = trim($data['campaign_name']);
    $campaign_type = isset($data['campaign_type']) ? $data['campaign_type'] : 'digital';
    $budget = isset($data['budget']) ? floatval($data['budget']) : 0;
    $spent = isset($data['spent']) ? floatval($data['spent']) : 0;
    $start_date = isset($data['start_date']) && !empty($data['start_date']) ? $data['start_date'] : null;
    $end_date = isset($data['end_date']) && !empty($data['end_date']) ? $data['end_date'] : null;
    $target_audience = isset($data['target_audience']) ? $data['target_audience'] : null;
    $description = isset($data['description']) ? $data['description'] : null;
    $status = isset($data['status']) ? $data['status'] : 'planned';
    
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':campaign_name', $campaign_name);
    $stmt->bindParam(':campaign_type', $campaign_type);
    $stmt->bindParam(':budget', $budget);
    $stmt->bindParam(':spent', $spent);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    $stmt->bindParam(':target_audience', $target_audience);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':status', $status);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Campaign updated successfully',
            'id' => $id
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update campaign'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>