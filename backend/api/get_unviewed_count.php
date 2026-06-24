<?php
// backend/api/get_unviewed_count.php

// ============================================================
// ERROR REPORTING - KWA DEBUGGING
// ============================================================
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// ============================================================
// HEADERS
// ============================================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ============================================================
// DATABASE CONNECTION
// ============================================================
try {
    $pdo = new PDO("mysql:host=localhost;dbname=geotraverse_erp;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => []
    ]);
    exit;
}

// ============================================================
// GET PARAMETERS
// ============================================================
$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;

if ($department_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Department ID required',
        'data' => []
    ]);
    exit;
}

// ============================================================
// GET UNVIEWED COUNTS
// ============================================================
try {
    $counts = [
        'projects' => 0,
        'reports' => 0,
        'fund_requests' => 0,
        'documents' => 0
    ];

    // 1. Unviewed Projects (sent to this department)
    $query = "SELECT COUNT(*) as count FROM projects 
              WHERE sent_to_dept = ? 
                AND (is_viewed_by_department = 0 OR is_viewed_by_department IS NULL)
                AND (is_deleted = 0 OR is_deleted IS NULL)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$department_id]);
    $result = $stmt->fetch();
    $counts['projects'] = intval($result['count'] ?? 0);

    // Also check sent_projects table
    $query = "SELECT COUNT(*) as count FROM sent_projects 
              WHERE to_department_id = ? 
                AND (is_viewed = 0 OR is_viewed IS NULL)
                AND (is_deleted = 0 OR is_deleted IS NULL)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$department_id]);
    $result = $stmt->fetch();
    $counts['projects'] += intval($result['count'] ?? 0);

    // 2. Unviewed Reports
    $query = "SELECT COUNT(*) as count FROM reports 
              WHERE sent_to_department = ? 
                AND (is_viewed_by_department = 0 OR is_viewed_by_department IS NULL)
                AND (is_deleted = 0 OR is_deleted IS NULL)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$department_id]);
    $result = $stmt->fetch();
    $counts['reports'] = intval($result['count'] ?? 0);

    // Also check sent_reports table
    $query = "SELECT COUNT(*) as count FROM sent_reports 
              WHERE to_department_id = ? 
                AND (is_viewed = 0 OR is_viewed IS NULL)
                AND (is_deleted = 0 OR is_deleted IS NULL)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$department_id]);
    $result = $stmt->fetch();
    $counts['reports'] += intval($result['count'] ?? 0);

    // 3. Unviewed Fund Requests
    $query = "SELECT COUNT(*) as count FROM fund_requests 
              WHERE department_id = ? 
                AND (is_viewed_by_department = 0 OR is_viewed_by_department IS NULL)
                AND status = 'pending'
                AND (is_deleted = 0 OR is_deleted IS NULL)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$department_id]);
    $result = $stmt->fetch();
    $counts['fund_requests'] = intval($result['count'] ?? 0);

    // 4. Unviewed Documents (project_documents)
    $query = "SELECT COUNT(*) as count FROM project_documents 
              WHERE sent_to_department = ? 
                AND (is_viewed_by_department = 0 OR is_viewed_by_department IS NULL)
                AND (is_deleted = 0 OR is_deleted IS NULL)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$department_id]);
    $result = $stmt->fetch();
    $counts['documents'] = intval($result['count'] ?? 0);

    // Also check sent_documents table
    $query = "SELECT COUNT(*) as count FROM sent_documents 
              WHERE to_department_id = ? 
                AND (is_viewed = 0 OR is_viewed IS NULL)
                AND (is_deleted = 0 OR is_deleted IS NULL)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$department_id]);
    $result = $stmt->fetch();
    $counts['documents'] += intval($result['count'] ?? 0);

    // 5. Unviewed Uploaded Reports
    $query = "SELECT COUNT(*) as count FROM uploaded_reports 
              WHERE sent_to_department = ? 
                AND (is_viewed_by_department = 0 OR is_viewed_by_department IS NULL)
                AND (is_deleted = 0 OR is_deleted IS NULL)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$department_id]);
    $result = $stmt->fetch();
    $counts['uploaded_reports'] = intval($result['count'] ?? 0);

    // Also check sent_uploaded_reports table
    $query = "SELECT COUNT(*) as count FROM sent_uploaded_reports 
              WHERE to_department_id = ? 
                AND (is_viewed = 0 OR is_viewed IS NULL)
                AND (is_deleted = 0 OR is_deleted IS NULL)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$department_id]);
    $result = $stmt->fetch();
    $counts['uploaded_reports'] += intval($result['count'] ?? 0);

    // ============================================================
    // SEND RESPONSE
    // ============================================================
    echo json_encode([
        'success' => true,
        'message' => 'Unviewed counts retrieved',
        'data' => $counts,
        'department_id' => $department_id
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => []
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>