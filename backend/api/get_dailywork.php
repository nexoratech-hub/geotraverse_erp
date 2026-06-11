<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$department_id = isset($_GET['department_id']) ? $_GET['department_id'] : null;
$work_type = isset($_GET['work_type']) ? $_GET['work_type'] : null;
$campaign_id = isset($_GET['campaign_id']) ? $_GET['campaign_id'] : null;
$project_id = isset($_GET['project_id']) ? $_GET['project_id'] : null;

try {
    $query = "SELECT * FROM dailywork WHERE 1=1";
    $params = [];
    
    if ($department_id) {
        $query .= " AND department_id = :department_id";
        $params[':department_id'] = $department_id;
    }
    
    if ($work_type) {
        $query .= " AND work_type = :work_type";
        $params[':work_type'] = $work_type;
    }
    
    if ($campaign_id) {
        $query .= " AND campaign_id = :campaign_id";
        $params[':campaign_id'] = $campaign_id;
    }
    
    if ($project_id) {
        $query .= " AND project_id = :project_id";
        $params[':project_id'] = $project_id;
    }
    
    $query .= " AND (is_deleted IS NULL OR is_deleted != 1)";
    $query .= " ORDER BY date DESC";
    
    $stmt = $db->prepare($query);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process results to ensure all columns exist
    $processedResults = [];
    foreach ($results as $row) {
        $processedRow = [
            'id' => $row['id'],
            'date' => $row['date'],
            'project_id' => $row['project_id'] ?? null,
            'project_name' => $row['project_name'] ?? null,
            'campaign_id' => $row['campaign_id'] ?? null,
            'campaign_name' => $row['campaign_name'] ?? null,
            'work_type' => $row['work_type'] ?? 'general',
            'work_description' => $row['work_description'] ?? null,
            'quantity_produced' => $row['quantity_produced'] ?? 0,
            'quantity_sold' => $row['quantity_sold'] ?? 0,
            'price_per_unit' => $row['price_per_unit'] ?? 0,
            'budget' => $row['budget'] ?? 0,
            'amount' => $row['amount'] ?? 0,
            'total_amount' => $row['total_amount'] ?? 0,
            'income' => $row['income'] ?? 0,
            'expenses' => $row['expenses'] ?? 0,
            'profit' => $row['profit'] ?? 0,
            'status' => $row['status'] ?? 'pending',
            'payment_status' => $row['payment_status'] ?? 'pending',
            'partial_amount' => $row['partial_amount'] ?? 0,
            'is_deleted' => $row['is_deleted'] ?? 0,
            'created_by' => $row['created_by'] ?? 'System',
            'updated_by' => $row['updated_by'] ?? null,
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at'] ?? null,
            'department_id' => $row['department_id']
        ];
        
        // Add Bricks & Timber specific fields if they exist
        if (isset($row['amount_paid'])) {
            $processedRow['amount_paid'] = $row['amount_paid'];
        }
        
        $processedResults[] = $processedRow;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $processedResults,
        'count' => count($processedResults)
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>