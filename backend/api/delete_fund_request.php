<?php
// backend/api/delete_fund_request.php - FINANCE SOFT DELETE (Department-Specific)

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
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

// Get input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit();
}

$id = isset($data['id']) ? intval($data['id']) : 0;
$department_id = isset($data['department_id']) ? intval($data['department_id']) : 0;
$deleted_by = isset($data['deleted_by']) ? $data['deleted_by'] : 'System';
$is_admin = isset($data['is_admin']) ? intval($data['is_admin']) : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit();
}

try {
    // ============================================================
    // 1. GET THE REQUEST DETAILS
    // ============================================================
    $checkStmt = $pdo->prepare("SELECT id, title, department_id, is_deleted, 
                                deleted_by_department, deleted_by_admin,
                                is_visible_to_super_admin, is_visible_to_finance, is_visible_to_own_department
                                FROM fund_requests WHERE id = ?");
    $checkStmt->execute([$id]);
    $request = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$request) {
        echo json_encode(['success' => false, 'message' => 'Request not found']);
        exit();
    }
    
    // ============================================================
    // 2. DETERMINE WHO IS DELETING
    // ============================================================
    $isSuperAdmin = ($department_id == 1 || $is_admin == 1);
    $isFinance = ($department_id == 2);
    $isOwnDepartment = ($department_id == $request['department_id']);
    
    error_log("🗑️ Delete request - ID: $id, Dept: $department_id, IsAdmin: $is_admin, RequestDept: {$request['department_id']}");
    
    // ============================================================
    // 3. BUILD UPDATE QUERY - SOFT DELETE
    // ============================================================
    $sql = "UPDATE fund_requests SET";
    $params = [];
    $deleteType = '';
    $message = '';
    $queryLog = '';
    
    // ============================================================
    // SUPER ADMIN DELETES - Hides from Super Admin only
    // ============================================================
    if ($isSuperAdmin) {
        $sql .= " is_visible_to_super_admin = 0,
                  is_visible_to_finance = 1,
                  is_visible_to_own_department = 1,
                  deleted_by_admin = 1,
                  deleted_by_department = 0,
                  deleted_by = :deleted_by,
                  deleted_at = NOW(),
                  is_deleted = 0";
        
        $params[':deleted_by'] = $deleted_by;
        $deleteType = 'Super Admin soft delete';
        $message = 'Budget request hidden from Super Admin dashboard only';
        $queryLog = "🗑️ Super Admin soft delete - ID: $id (Hidden from Super Admin only)";
        error_log($queryLog);
    }
    
    // ============================================================
    // FINANCE DELETES - Hides from Finance only ✅
    // ============================================================
    else if ($isFinance) {
        // ✅ Finance soft delete - only hides from Finance dashboard
        // Request remains visible to Super Admin and Own Department
        $sql .= " is_visible_to_finance = 0,
                  is_visible_to_super_admin = 1,
                  is_visible_to_own_department = 1,
                  deleted_by_department = 0,
                  deleted_by_admin = 0,
                  deleted_by = :deleted_by,
                  deleted_at = NOW(),
                  is_deleted = 0";
        
        $params[':deleted_by'] = $deleted_by;
        $deleteType = 'Finance soft delete';
        $message = 'Budget request hidden from Finance dashboard only';
        $queryLog = "🗑️ Finance soft delete - ID: $id (Hidden from Finance only)";
        error_log($queryLog);
    }
    
    // ============================================================
    // OWN DEPARTMENT DELETES - Hides from own department only
    // ============================================================
    else if ($isOwnDepartment) {
        $sql .= " is_visible_to_own_department = 0,
                  is_visible_to_super_admin = 1,
                  is_visible_to_finance = 1,
                  deleted_by_department = 1,
                  deleted_by_admin = 0,
                  deleted_by = :deleted_by,
                  deleted_at = NOW(),
                  is_deleted = 0";
        
        $params[':deleted_by'] = $deleted_by;
        $deleteType = 'Department soft delete';
        $message = 'Budget request hidden from own department dashboard only';
        $queryLog = "🗑️ Department soft delete - ID: $id, Dept: $department_id (Hidden from own department only)";
        error_log($queryLog);
    }
    
    // ============================================================
    // OTHERS - Cannot delete
    // ============================================================
    else {
        echo json_encode([
            'success' => false, 
            'message' => 'You do not have permission to delete this request'
        ]);
        exit();
    }
    
    $sql .= " WHERE id = :id";
    $params[':id'] = $id;
    
    error_log("🗑️ Delete SQL: $sql");
    error_log("🗑️ Delete params: " . json_encode($params));
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    // ============================================================
    // 4. RETURN RESPONSE
    // ============================================================
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => [
            'id' => $id,
            'title' => $request['title'],
            'deleted_by' => $deleted_by,
            'deleted_by_department' => $department_id,
            'is_admin' => $isSuperAdmin,
            'delete_type' => $deleteType,
            'is_super_admin' => $isSuperAdmin,
            'is_finance' => $isFinance,
            'is_own_department' => $isOwnDepartment,
            'visible_to_super_admin' => $isSuperAdmin ? 0 : 1,
            'visible_to_finance' => ($isFinance || $isSuperAdmin) ? 0 : 1,
            'visible_to_own_dept' => $isOwnDepartment ? 0 : 1,
            'fully_deleted' => false,
            'note' => 'Request is still visible to other departments'
        ]
    ]);
    
} catch(PDOException $e) {
    error_log("❌ Delete fund request error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>