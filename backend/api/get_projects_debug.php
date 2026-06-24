<?php
/**
 * DEBUG VERSION - get_projects_debug.php
 * 
 * This version shows detailed debug information to help identify the issue.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

// ================================================================
// DEBUG: LOG ALL INPUTS
// ================================================================
$debug = [
    'get_params' => $_GET,
    'server' => [
        'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
        'SCRIPT_FILENAME' => $_SERVER['SCRIPT_FILENAME'],
    ]
];

try {
    // ================================================================
    // TEST DATABASE CONNECTION
    // ================================================================
    $dbPath = '../../backend/config/database.php';
    if (!file_exists($dbPath)) {
        $dbPath = '../backend/config/database.php';
    }
    
    $debug['db_path'] = $dbPath;
    $debug['db_exists'] = file_exists($dbPath);
    
    if (!file_exists($dbPath)) {
        throw new Exception('Database config not found at: ' . $dbPath);
    }
    
    require_once $dbPath;
    
    if (!function_exists('getDBConnection')) {
        throw new Exception('getDBConnection function not found');
    }
    
    $pdo = getDBConnection();
    $debug['db_connected'] = true;
    
    // ================================================================
    // TEST QUERY
    // ================================================================
    $departmentId = isset($_GET['department_id']) ? (int)$_GET['department_id'] : null;
    $debug['department_id'] = $departmentId;
    
    if (!$departmentId) {
        throw new Exception('department_id is required');
    }
    
    // Test: Get all projects (no filter)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM projects WHERE is_deleted = 0");
    $totalProjects = $stmt->fetch(PDO::FETCH_ASSOC);
    $debug['total_projects'] = $totalProjects['total'] ?? 0;
    
    // Test: Get projects for this department
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE department_id = ? AND is_deleted = 0");
    $stmt->execute([$departmentId]);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $debug['dept_projects_count'] = count($projects);
    $debug['dept_projects'] = array_map(function($p) {
        return ['id' => $p['id'], 'name' => $p['name'], 'department_id' => $p['department_id']];
    }, $projects);
    
    // ================================================================
    // SUCCESS RESPONSE WITH DEBUG
    // ================================================================
    echo json_encode([
        'success' => true,
        'data' => $projects,
        'debug' => $debug
    ]);
    
} catch (Exception $e) {
    $debug['error'] = $e->getMessage();
    $debug['trace'] = $e->getTraceAsString();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => $debug
    ]);
}
?>