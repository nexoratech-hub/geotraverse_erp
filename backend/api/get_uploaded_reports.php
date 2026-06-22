<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
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

// Get parameters
$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;
$include_deleted = isset($_GET['include_deleted']) ? intval($_GET['include_deleted']) : 0;

if ($department_id == 0) {
    echo json_encode(['success' => false, 'message' => 'Department ID required']);
    exit;
}

try {
    // ============================================================
    // BUILD QUERY - For Super Admin (department_id = 1), show all
    // ============================================================
    $sql = "SELECT * FROM uploaded_reports WHERE 1=1";
    $params = [];
    
    // For Super Admin (department_id = 1), show all reports
    if ($department_id == 1) {
        // Super Admin can see all reports, but exclude deleted ones
        $sql .= " AND is_deleted = 0";
    } else {
        // Other departments can only see their own reports or sent to them
        $sql .= " AND (department_id = ? OR sent_to_department = ?) AND is_deleted = 0";
        $params[] = $department_id;
        $params[] = $department_id;
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $reports,
        'total' => count($reports),
        'department_id' => $department_id
    ]);

} catch(PDOException $e) {
    error_log("Get uploaded reports error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>