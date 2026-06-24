<?php
// api/config.php

// ============================================================
// ONDOA OUTPUT YOYOTE ILIYOPO
// ============================================================
ob_start();

// ============================================================
// HEADERS - ANGAZIA KAMA ZIMETUMWA TAYARI
// ============================================================
if (!headers_sent()) {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Allow-Credentials: true');
}

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ============================================================
// ERROR REPORTING
// ============================================================
error_reporting(E_ALL);
ini_set('display_errors', 0); // Usionyeshe errors kwenye browser
ini_set('log_errors', 1);     // Weka errors kwenye log

// ============================================================
// DATABASE CONNECTION
// ============================================================
$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch(PDOException $e) {
    ob_clean();
    echo json_encode([
        'success' => false, 
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit;
}

// ============================================================
// FUNCTION: SEND JSON RESPONSE
// ============================================================
function sendResponse($success, $message = '', $data = []) {
    // Futa output yoyote iliyopo
    if (ob_get_length()) ob_clean();
    
    // Hakikisha headers hazijatumwa tayari
    if (!headers_sent()) {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
    }
    
    $response = [
        'success' => $success,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    if ($message !== '') $response['message'] = $message;
    if (!empty($data)) $response['data'] = $data;
    if (isset($data['data']) && is_array($data['data'])) {
        $response['count'] = count($data['data']);
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// ============================================================
// FUNCTION: SOFT DELETE
// ============================================================
function softDelete($pdo, $table, $id, $column = 'is_deleted', $deletedByDept = null, $deletedByAdmin = null, $deletedBy = 'System') {
    try {
        // Get item name first
        $nameField = 'name';
        if ($table === 'reports') $nameField = 'title';
        if ($table === 'project_documents') $nameField = 'title';
        if ($table === 'uploaded_reports') $nameField = 'title';
        if ($table === 'fund_requests') $nameField = 'source';
        
        $query = "SELECT id, $nameField as item_name FROM $table WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        $itemName = $item['item_name'] ?? 'Item';
        
        // Soft delete
        $sql = "UPDATE $table SET $column = 1, deleted_at = NOW()";
        if ($deletedByDept !== null) $sql .= ", deleted_by_department = 1";
        if ($deletedByAdmin !== null) $sql .= ", deleted_by_admin = 1";
        $sql .= " WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute(['id' => $id]);
        
        if ($result) {
            // Add to recycle bin
            $itemType = $table;
            $recycleQuery = "INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin, deleted_by_name, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $recycleStmt = $pdo->prepare($recycleQuery);
            $recycleStmt->execute([
                $id, 
                $itemType, 
                $itemName, 
                $deletedByDept, 
                $deletedByAdmin ? 1 : 0,
                $deletedBy
            ]);
        }
        
        return $result;
    } catch (Exception $e) {
        error_log("Soft delete error: " . $e->getMessage());
        return false;
    }
}

// ============================================================
// FUNCTION: ADD TO RECYCLE BIN
// ============================================================
function addToRecycleBin($pdo, $itemId, $itemType, $itemName, $departmentId, $deletedBy = 'System') {
    try {
        $query = "INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin, deleted_by_name, created_at) 
                  VALUES (?, ?, ?, ?, 0, ?, NOW())";
        $stmt = $pdo->prepare($query);
        return $stmt->execute([$itemId, $itemType, $itemName, $departmentId, $deletedBy]);
    } catch (Exception $e) {
        error_log("Recycle bin error: " . $e->getMessage());
        return false;
    }
}

// ============================================================
// FUNCTION: GET TABLE DATA
// ============================================================
function getTableData($pdo, $table, $conditions = [], $orderBy = 'id DESC', $limit = null) {
    $query = "SELECT * FROM $table";
    $params = [];
    
    if (!empty($conditions)) {
        $where = [];
        foreach ($conditions as $key => $value) {
            $where[] = "$key = ?";
            $params[] = $value;
        }
        $query .= " WHERE " . implode(' AND ', $where);
    }
    
    $query .= " ORDER BY $orderBy";
    
    if ($limit !== null) {
        $query .= " LIMIT " . intval($limit);
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// ============================================================
// FUNCTION: GET SINGLE RECORD
// ============================================================
function getRecordById($pdo, $table, $id) {
    $query = "SELECT * FROM $table WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// ============================================================
// FUNCTION: FORMAT MONEY
// ============================================================
function formatMoney($amount) {
    return number_format(floatval($amount), 0, '.', ',');
}

// ============================================================
// FUNCTION: PARSE MONEY
// ============================================================
function parseMoney($value) {
    return floatval(str_replace(',', '', $value));
}

// ============================================================
// FUNCTION: ESCAPE HTML
// ============================================================
function escapeHtml($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}
?>