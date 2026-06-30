<?php
/**
 * get_current_user.php - Get currently logged in user details
 * Inachukua user moja kwa moja kutoka database kwa kutumia session user_id
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ============================================================
// ERROR REPORTING
// ============================================================
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

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
} catch(PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit();
}

// ============================================================
// SESSION START
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================
// GET USER ID FROM SESSION
// ============================================================
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

if ($user_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in',
        'data' => null
    ]);
    exit();
}

// ============================================================
// CHUKUA USER KUTOKA DATABASE - MUHIMU SANA!
// ============================================================
try {
    $stmt = $pdo->prepare("SELECT 
        id, 
        name, 
        email, 
        department_id, 
        role, 
        is_admin, 
        status,
        created_at,
        updated_at
        FROM users 
        WHERE id = ? AND status = 'active'");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // User hapatikani au amefutwa
        session_destroy();
        echo json_encode([
            'success' => false,
            'message' => 'User not found or inactive',
            'data' => null
        ]);
        exit();
    }

    // ============================================================
    // UPDATE SESSION KAMA KUNA MABADILIKO
    // ============================================================
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['department_id'] = (int)$user['department_id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['is_admin'] = (int)$user['is_admin'];

    // ============================================================
    // RETURN USER DATA
    // ============================================================
    echo json_encode([
        'success' => true,
        'data' => [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'department_id' => (int)$user['department_id'],
            'role' => $user['role'],
            'is_admin' => (int)$user['is_admin'],
            'status' => $user['status'],
            'created_at' => $user['created_at'],
            'updated_at' => $user['updated_at']
        ]
    ]);

} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => null
    ]);
}
?>