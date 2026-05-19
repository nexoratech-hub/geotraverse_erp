<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, PUT');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;
    
    $query = "SELECT * FROM marketing_campaigns WHERE deleted_by_department = 0 AND deleted_by_admin = 0";
    if ($department_id > 0) {
        $query .= " AND department_id = :department_id";
    }
    $query .= " ORDER BY created_at DESC";
    
    $stmt = $db->prepare($query);
    if ($department_id > 0) {
        $stmt->bindParam(':department_id', $department_id);
    }
    $stmt->execute();
    
    $campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $campaigns
    ]);
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $campaign_name = $data['campaign_name'] ?? '';
    $campaign_type = $data['campaign_type'] ?? 'digital';
    $budget = $data['budget'] ?? 0;
    $spent = $data['spent'] ?? 0;
    $start_date = $data['start_date'] ?? null;
    $end_date = $data['end_date'] ?? null;
    $target_audience = $data['target_audience'] ?? '';
    $description = $data['description'] ?? '';
    $status = $data['status'] ?? 'planned';
    $department_id = $data['department_id'] ?? 0;
    $created_by = $data['created_by'] ?? 1;
    
    if (!$campaign_name) {
        echo json_encode(['success' => false, 'message' => 'Campaign name required']);
        exit;
    }
    
    $query = "INSERT INTO marketing_campaigns (campaign_name, campaign_type, budget, spent, start_date, end_date, target_audience, description, status, department_id, created_by) 
              VALUES (:campaign_name, :campaign_type, :budget, :spent, :start_date, :end_date, :target_audience, :description, :status, :department_id, :created_by)";
    
    $stmt = $db->prepare($query);
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
        echo json_encode(['success' => true, 'message' => 'Campaign added', 'id' => $db->lastInsertId()]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add campaign']);
    }
} elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? 0;
    
    $campaign_name = $data['campaign_name'] ?? '';
    $campaign_type = $data['campaign_type'] ?? 'digital';
    $budget = $data['budget'] ?? 0;
    $spent = $data['spent'] ?? 0;
    $start_date = $data['start_date'] ?? null;
    $end_date = $data['end_date'] ?? null;
    $target_audience = $data['target_audience'] ?? '';
    $description = $data['description'] ?? '';
    $status = $data['status'] ?? 'planned';
    
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
    $stmt->bindParam(':campaign_name', $campaign_name);
    $stmt->bindParam(':campaign_type', $campaign_type);
    $stmt->bindParam(':budget', $budget);
    $stmt->bindParam(':spent', $spent);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    $stmt->bindParam(':target_audience', $target_audience);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Campaign updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update campaign']);
    }
} elseif ($method === 'DELETE') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    $query = "UPDATE marketing_campaigns SET deleted_by_department = 1, deleted_at = NOW() WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Campaign deleted']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete campaign']);
    }
}
?>