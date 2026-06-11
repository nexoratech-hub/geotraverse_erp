<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

try {
    $query = "INSERT INTO daily_work (work_type, project_id, project_name, campaign_id, campaign_name, date, work_description, 
              quantity_produced, quantity_sold, price_per_unit, income, expenses, budget, amount, status, department_id, created_by) 
              VALUES (:work_type, :project_id, :project_name, :campaign_id, :campaign_name, :date, :work_description,
              :quantity_produced, :quantity_sold, :price_per_unit, :income, :expenses, :budget, :amount, :status, :dept_id, :created_by)";
    
    $stmt = $db->prepare($query);
    
    $workType = isset($data['work_type']) ? $data['work_type'] : 'general';
    $projectId = isset($data['project_id']) ? $data['project_id'] : null;
    $projectName = isset($data['project_name']) ? $data['project_name'] : '';
    $campaignId = isset($data['campaign_id']) ? $data['campaign_id'] : null;
    $campaignName = isset($data['campaign_name']) ? $data['campaign_name'] : null;
    $date = isset($data['date']) ? $data['date'] : date('Y-m-d H:i:s');
    $description = isset($data['work_description']) ? $data['work_description'] : '';
    $quantityProduced = isset($data['quantity_produced']) ? intval($data['quantity_produced']) : 0;
    $quantitySold = isset($data['quantity_sold']) ? intval($data['quantity_sold']) : 0;
    $pricePerUnit = isset($data['price_per_unit']) ? floatval($data['price_per_unit']) : 0;
    $income = isset($data['income']) ? floatval($data['income']) : 0;
    $expenses = isset($data['expenses']) ? floatval($data['expenses']) : 0;
    $budget = isset($data['budget']) ? floatval($data['budget']) : 0;
    $amount = isset($data['amount']) ? floatval($data['amount']) : 0;
    $status = isset($data['status']) ? $data['status'] : 'pending';
    $departmentId = isset($data['department_id']) ? intval($data['department_id']) : null;
    $createdBy = isset($data['created_by']) ? $data['created_by'] : 'System';
    
    $stmt->bindParam(':work_type', $workType);
    $stmt->bindParam(':project_id', $projectId);
    $stmt->bindParam(':project_name', $projectName);
    $stmt->bindParam(':campaign_id', $campaignId);
    $stmt->bindParam(':campaign_name', $campaignName);
    $stmt->bindParam(':date', $date);
    $stmt->bindParam(':work_description', $description);
    $stmt->bindParam(':quantity_produced', $quantityProduced);
    $stmt->bindParam(':quantity_sold', $quantitySold);
    $stmt->bindParam(':price_per_unit', $pricePerUnit);
    $stmt->bindParam(':income', $income);
    $stmt->bindParam(':expenses', $expenses);
    $stmt->bindParam(':budget', $budget);
    $stmt->bindParam(':amount', $amount);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':dept_id', $departmentId);
    $stmt->bindParam(':created_by', $createdBy);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Daily work added successfully', 'id' => $db->lastInsertId()]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add daily work']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>