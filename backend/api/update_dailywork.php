<?php
// update_dailywork.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// Get input data
$input = json_decode(file_get_contents('php://input'), true);

// CRITICAL: Check if id exists
if (!isset($input['id']) || empty($input['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID is required for update']);
    exit();
}

$id = intval($input['id']);

// Build update query
$fields = [];
$params = [];

$allowed_fields = [
    'date', 'work_description', 'work_type', 'project_id', 'project_name',
    'campaign_id', 'campaign_name', 'department_id', 'status', 'payment_status',
    'budget', 'amount', 'expenses', 'income', 'profit',
    'quantity_produced', 'quantity_sold', 'price_per_unit', 'total_amount',
    'partial_amount', 'amount_paid', 'updated_by'
];

foreach ($allowed_fields as $field) {
    if (isset($input[$field])) {
        $fields[] = "$field = :$field";
        $params[":$field"] = $input[$field];
    }
}

if (empty($fields)) {
    echo json_encode(['success' => false, 'message' => 'No fields to update']);
    exit();
}

$params[':id'] = $id;
$sql = "UPDATE dailywork SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = :id";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Daily work updated successfully', 'id' => $id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or record not found']);
    }
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>