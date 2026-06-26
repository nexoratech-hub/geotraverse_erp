<?php
// backend/api/get_transactions.php - COMPLETE FIX

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

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
    exit();
}

// Get parameters
$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;
$all = isset($_GET['all']) ? intval($_GET['all']) : 0;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 1000; // Increased limit
$source = isset($_GET['source']) ? $_GET['source'] : '';

try {
    // ============================================================
    // BUILD QUERY
    // ============================================================
    $query = "SELECT 
                t.*,
                d.name as department_name,
                (t.amount - t.paid_amount) as pending_amount
              FROM transactions t
              LEFT JOIN departments d ON d.id = t.department_id
              WHERE t.is_deleted = 0";
    
    $params = [];
    
    // ============================================================
    // IF ALL=1, SHOW ALL TRANSACTIONS
    // ============================================================
    if ($all == 1) {
        // Show all transactions from all departments
        // No department filter
        error_log("📊 Fetching ALL transactions (all departments)");
    } 
    // ============================================================
    // ELSE FILTER BY DEPARTMENT
    // ============================================================
    else if ($department_id > 0) {
        $query .= " AND t.department_id = ?";
        $params[] = $department_id;
    }
    
    // Filter by source if provided
    if (!empty($source)) {
        $query .= " AND t.source LIKE ?";
        $params[] = '%' . $source . '%';
    }
    
    $query .= " ORDER BY t.id DESC LIMIT " . intval($limit);
    
    error_log("📊 Transactions Query: " . $query);
    error_log("📊 Params: " . json_encode($params));
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $transactions,
        'count' => count($transactions),
        'department_id' => $department_id,
        'all' => $all,
        'message' => 'Transactions retrieved successfully'
    ]);
    
} catch(PDOException $e) {
    error_log("Get transactions error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>