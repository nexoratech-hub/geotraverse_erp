<?php
/**
 * get_recycle_bin.php - Get all items in recycle bin
 * 
 * GET Parameters:
 * - user_id: For Super Admin/Employee
 * - department_id: For department
 * - is_admin: 1 for super admin (can see all)
 * - original_table: (optional) Filter by table name
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$departmentId = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;
$isAdmin = isset($_GET['is_admin']) ? intval($_GET['is_admin']) : 0;
$originalTable = isset($_GET['original_table']) ? $_GET['original_table'] : '';

try {
    $query = "SELECT * FROM recycle_bin WHERE permanently_deleted = 0";
    $params = [];
    
    if (!$isAdmin) {
        if ($userId > 0) {
            $query .= " AND (deleted_by_user_id = :user_id OR deleted_by_user_id IS NULL)";
            $params[':user_id'] = $userId;
        } else if ($departmentId > 0) {
            $query .= " AND (deleted_by_department_id = :dept_id OR deleted_by_department_id IS NULL)";
            $params[':dept_id'] = $departmentId;
        }
    }
    
    if (!empty($originalTable)) {
        $query .= " AND original_table = :original_table";
        $params[':original_table'] = $originalTable;
    }
    
    $query .= " ORDER BY deleted_at DESC";
    
    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Parse the JSON data for each item
    foreach ($items as &$item) {
        $item['deleted_data'] = json_decode($item['deleted_data'], true);
    }
    
    echo json_encode([
        'success' => true,
        'data' => $items,
        'count' => count($items)
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>