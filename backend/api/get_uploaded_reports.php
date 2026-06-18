<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// ============ DATABASE CONNECTION ============
$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage(), 'data' => []]);
    exit;
}

// ============ GET PARAMETERS ============
$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;

try {
    $sql = "SELECT * FROM uploaded_reports WHERE is_deleted = 0";
    $params = [];
    
    if ($department_id > 0) {
        $sql .= " AND department_id = ?";
        $params[] = $department_id;
    }
    
    $sql .= " ORDER BY id DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add file_url for download
    foreach ($reports as &$report) {
        $report['download_url'] = '/geotraverse/backend/api/download_report_document.php?id=' . $report['id'];
        // For display, use file_path (unique name) for file name display
        if (empty($report['display_name'])) {
            $report['display_name'] = $report['file_name'];
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $reports,
        'count' => count($reports)
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage(), 'data' => []]);
}
?>