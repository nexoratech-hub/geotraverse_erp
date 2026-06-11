<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$departmentId = isset($_GET['department_id']) ? intval($_GET['department_id']) : null;
$workType = isset($_GET['work_type']) ? $_GET['work_type'] : null;
$campaignId = isset($_GET['campaign_id']) ? intval($_GET['campaign_id']) : null;
$projectId = isset($_GET['project_id']) ? intval($_GET['project_id']) : null;

try {
    $sql = "SELECT * FROM daily_work WHERE is_deleted = 0 OR is_deleted IS NULL";
    $params = [];
    
    if ($departmentId) {
        $sql .= " AND department_id = :dept_id";
        $params[':dept_id'] = $departmentId;
    }
    
    if ($workType) {
        $sql .= " AND work_type = :work_type";
        $params[':work_type'] = $workType;
    }
    
    if ($campaignId) {
        $sql .= " AND campaign_id = :campaign_id";
        $params[':campaign_id'] = $campaignId;
    }
    
    if ($projectId) {
        $sql .= " AND project_id = :project_id";
        $params[':project_id'] = $projectId;
    }
    
    $sql .= " ORDER BY date DESC";
    
    $stmt = $db->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $results
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>