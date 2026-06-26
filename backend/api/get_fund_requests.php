<?php
// backend/api/get_fund_requests.php

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
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;

try {
    // ============================================================
    // BUILD QUERY BASED ON DEPARTMENT
    // ============================================================
    $query = "SELECT fr.*, 
              d.name as department_name,
              u.name as requested_by_name
              FROM fund_requests fr
              LEFT JOIN departments d ON d.id = fr.department_id
              LEFT JOIN users u ON u.id = fr.requested_by";
    
    $where = [];
    $params = [];
    
    // ============================================================
    // SUPER ADMIN (department_id = 1) - sees all undeleted requests
    // ============================================================
    if ($department_id == 1) {
        // Super Admin sees all requests that are NOT deleted by admin
        $where[] = "fr.is_deleted = 0";
        $where[] = "(fr.deleted_by_admin = 0 OR fr.deleted_by_admin IS NULL)";
    }
    
    // ============================================================
    // FINANCE (department_id = 2) - sees own + others not deleted
    // ============================================================
    else if ($department_id == 2) {
        // Finance sees:
        // 1. Requests from their department
        // 2. Requests from other departments that are NOT deleted by finance
        $where[] = "fr.is_deleted = 0";
        $where[] = "(fr.deleted_by_department = 0 OR fr.deleted_by_department IS NULL)";
        $where[] = "fr.department_id = ? OR fr.department_id != ?";
        $params[] = $department_id;
        $params[] = $department_id;
    }
    
    // ============================================================
    // OTHER DEPARTMENTS - sees own requests only
    // ============================================================
    else if ($department_id > 0) {
        $where[] = "fr.is_deleted = 0";
        $where[] = "fr.department_id = ?";
        $where[] = "(fr.deleted_by_department = 0 OR fr.deleted_by_department IS NULL)";
        $params[] = $department_id;
    }
    
    // ============================================================
    // ALL (if specified)
    // ============================================================
    if ($all == 1) {
        // Show all undeleted requests
        $where = ["fr.is_deleted = 0"];
        $params = [];
    }
    
    if (!empty($where)) {
        $query .= " WHERE " . implode(" AND ", $where);
    }
    
    $query .= " ORDER BY fr.id DESC LIMIT " . intval($limit);
    
    error_log("get_fund_requests query: " . $query);
    error_log("get_fund_requests params: " . json_encode($params));
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $requests,
        'count' => count($requests),
        'department_id' => $department_id,
        'all' => $all,
        'message' => 'Fund requests retrieved successfully'
    ]);
    
} catch(PDOException $e) {
    error_log("Get fund requests error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>