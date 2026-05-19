<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;

try {
    // Get campaigns filtered by department_id (if provided)
    if ($department_id > 0) {
        $query = "SELECT id, campaign_name, campaign_type, budget, spent, start_date, end_date, 
                         target_audience, description, status, department_id, created_by, created_at, updated_at
                  FROM marketing_campaigns 
                  WHERE department_id = :department_id 
                    AND deleted_by_department = 0 
                    AND deleted_by_admin = 0
                  ORDER BY created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':department_id', $department_id);
    } else {
        $query = "SELECT id, campaign_name, campaign_type, budget, spent, start_date, end_date, 
                         target_audience, description, status, department_id, created_by, created_at, updated_at
                  FROM marketing_campaigns 
                  WHERE deleted_by_department = 0 
                    AND deleted_by_admin = 0
                  ORDER BY created_at DESC";
        $stmt = $db->prepare($query);
    }
    
    $stmt->execute();
    $campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get department names for each campaign
    $dept_query = "SELECT id, name FROM departments";
    $dept_stmt = $db->prepare($dept_query);
    $dept_stmt->execute();
    $departments = [];
    while ($row = $dept_stmt->fetch(PDO::FETCH_ASSOC)) {
        $departments[$row['id']] = $row['name'];
    }
    
    foreach ($campaigns as &$campaign) {
        $campaign['department_name'] = isset($departments[$campaign['department_id']]) ? $departments[$campaign['department_id']] : 'Unknown';
        $campaign['remaining'] = floatval($campaign['budget']) - floatval($campaign['spent']);
        $campaign['usage_percent'] = $campaign['budget'] > 0 ? round(($campaign['spent'] / $campaign['budget']) * 100, 2) : 0;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $campaigns,
        'count' => count($campaigns),
        'message' => 'Campaigns retrieved successfully'
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>