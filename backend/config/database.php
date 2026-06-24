<?php
// backend/config/database.php

// ============================================================
// ERROR REPORTING - KWA AJILI YA DEBUGGING
// ============================================================
error_reporting(E_ALL);
ini_set('display_errors', 0); // Usionyeshe errors kwenye browser
ini_set('log_errors', 1);     // Weka errors kwenye log

// ============================================================
// HEADERS - HIZI ZINAPASWA KUWA KWANZA
// ============================================================
// Angalia kama headers zimetumwa tayari
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
// DATABASE CLASS
// ============================================================
class Database {
    private $host = "localhost";
    private $db_name = "geotraverse_erp";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                )
            );
            $this->conn->exec("set names utf8mb4");
        } catch(PDOException $exception) {
            // Usitume headers hapa, tuache mwisho wa file
            $error = array(
                "success" => false, 
                "message" => "Database connection error: " . $exception->getMessage(),
                "error_code" => $exception->getCode()
            );
            // Futa output yoyote iliyopo
            if (ob_get_length()) ob_clean();
            echo json_encode($error);
            exit();
        }
        
        return $this->conn;
    }
}

/**
 * Send JSON response - HII NI MUHIMU SANA
 * Inafuta output yoyote kabla ya kutuma JSON
 */
function sendResponse($success, $data = null, $message = "", $unviewed_count = null) {
    // Futa output yoyote iliyotumwa kabla
    if (ob_get_length()) ob_clean();
    
    $response = array(
        "success" => $success,
        "timestamp" => date('Y-m-d H:i:s')
    );
    
    if ($data !== null) $response["data"] = $data;
    if ($data !== null && is_array($data)) $response["count"] = count($data);
    if ($message !== "") $response["message"] = $message;
    if ($unviewed_count !== null) $response["unviewed_count"] = $unviewed_count;
    
    // Hakikisha headers hazijatumwa tayari
    if (!headers_sent()) {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();
}

// ============================================================
// INITIALIZE DATABASE CONNECTION
// ============================================================
$database = new Database();
$db = $database->getConnection();

// ============================================================
// FUNCTIONS ZA ZIADA - KWA URAHISI WA API
// ============================================================

/**
 * Get all records from a table with filters
 */
function getTableData($pdo, $table, $conditions = [], $orderBy = 'id DESC') {
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
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Get a single record by ID
 */
function getRecordById($pdo, $table, $id) {
    $query = "SELECT * FROM $table WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Soft delete a record
 */
function softDelete($pdo, $table, $id, $deleted_by = 'System') {
    $query = "UPDATE $table SET 
              is_deleted = 1,
              deleted_at = NOW(),
              deleted_by = ?
              WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$deleted_by, $id]);
    return $stmt->rowCount();
}

/**
 * Insert into recycle bin
 */
function addToRecycleBin($pdo, $item_id, $item_type, $item_name, $department_id, $deleted_by = 'System') {
    $query = "INSERT INTO recycle_bin 
              (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin, deleted_by_name, created_at) 
              VALUES (?, ?, ?, ?, 0, ?, NOW())";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$item_id, $item_type, $item_name, $department_id, $deleted_by]);
    return $stmt->rowCount();
}
?>