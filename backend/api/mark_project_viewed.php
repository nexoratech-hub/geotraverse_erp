<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Database connection
$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Get input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$project_id = isset($data['project_id']) ? intval($data['project_id']) : 0;
$department_id = isset($data['department_id']) ? intval($data['department_id']) : 0;

if ($project_id == 0 || $department_id == 0) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

try {
    // ============================================================
    // 1. UPDATE PROJECT AS VIEWED BY DEPARTMENT
    // ============================================================
    $stmt = $pdo->prepare("
        UPDATE projects 
        SET is_viewed_by_department = 1, 
            viewed_by_department_at = NOW(),
            updated_at = NOW()
        WHERE id = ? AND sent_to_dept = ?
    ");
    $stmt->execute([$project_id, $department_id]);
    $affected = $stmt->rowCount();

    // ============================================================
    // 2. ALSO MARK RELATED NOTIFICATIONS AS VIEWED
    // ============================================================
    $notifStmt = $pdo->prepare("
        UPDATE notifications 
        SET is_viewed = 1, viewed_at = NOW(), updated_at = NOW() 
        WHERE item_type = 'project' AND item_id = ? AND department_id = ?
    ");
    $notifStmt->execute([$project_id, $department_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Project marked as viewed',
        'data' => [
            'project_id' => $project_id,
            'department_id' => $department_id,
            'affected' => $affected
        ]
    ]);

} catch(PDOException $e) {
    error_log("Mark project viewed error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>