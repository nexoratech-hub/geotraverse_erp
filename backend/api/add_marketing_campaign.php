<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['campaign_name']) || empty(trim($data['campaign_name']))) {
    echo json_encode([
        'success' => false,
        'message' => 'Campaign name is required'
    ]);
    exit;
}

if (!isset($data['department_id']) || $data['department_id'] <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Department ID is required'
    ]);
    exit;
}

try {
    $query = "INSERT INTO marketing_campaigns 
              (campaign_name, campaign_type, budget, spent, start_date, end_date, 
               target_audience, description, status, department_id, created_by, created_at, updated_at)
              VALUES 
              (:campaign_name, :campaign_type, :budget, :spent, :start_date, :end_date,
               :target_audience, :description, :status, :department_id, :created_by, NOW(), NOW())";
    
    $stmt = $db->prepare($query);
    
    $campaign_name = trim($data['campaign_name']);
    $campaign_type = isset($data['campaign_type']) ? $data['campaign_type'] : 'digital';
    $budget = isset($data['budget']) ? floatval($data['budget']) : 0;
    $spent = isset($data['spent']) ? floatval($data['spent']) : 0;
    $start_date = isset($data['start_date']) && !empty($data['start_date']) ? $data['start_date'] : null;
    $end_date = isset($data['end_date']) && !empty($data['end_date']) ? $data['end_date'] : null;
    $target_audience = isset($data['target_audience']) ? $data['target_audience'] : null;
    $description = isset($data['description']) ? $data['description'] : null;
    $status = isset($data['status']) ? $data['status'] : 'planned';
    $department_id = intval($data['department_id']);
    $created_by = isset($data['created_by']) ? intval($data['created_by']) : null;
    
    $stmt->bindParam(':campaign_name', $campaign_name);
    $stmt->bindParam(':campaign_type', $campaign_type);
    $stmt->bindParam(':budget', $budget);
    $stmt->bindParam(':spent', $spent);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    $stmt->bindParam(':target_audience', $target_audience);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':department_id', $department_id);
    $stmt->bindParam(':created_by', $created_by);
    
    if ($stmt->execute()) {
        $new_id = $db->lastInsertId();
        
        // Return the newly created campaign
        $fetch_query = "SELECT id, campaign_name, campaign_type, budget, spent, start_date, end_date,
                               target_audience, description, status, department_id, created_by, created_at
                        FROM marketing_campaigns WHERE id = :id";
        $fetch_stmt = $db->prepare($fetch_query);
        $fetch_stmt->bindParam(':id', $new_id);
        $fetch_stmt->execute();
        $new_campaign = $fetch_stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'message' => 'Campaign added successfully',
            'data' => $new_campaign,
            'id' => $new_id
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to add campaign'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>