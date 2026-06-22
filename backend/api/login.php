<?php
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Database connection
$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    error_log("DB Connection failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Get input
$input = file_get_contents('php://input');
if (empty($input)) {
    echo json_encode(['success' => false, 'message' => 'No input data received']);
    exit;
}

$data = json_decode($input, true);
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON format']);
    exit;
}

$email = isset($data['email']) ? trim($data['email']) : '';
$password_input = isset($data['password']) ? $data['password'] : '';
$department_id = isset($data['department_id']) ? intval($data['department_id']) : 0;

if (empty($email) || empty($password_input)) {
    echo json_encode(['success' => false, 'message' => 'Email and password required']);
    exit;
}

try {
    // ============================================================
    // CHECK employees TABLE FIRST
    // ============================================================
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE email = ? AND is_deleted = 0 LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // If not found in employees, check users table
    if (!$user) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_deleted = 0 LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if (!$user) {
        error_log("User not found: $email");
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        exit;
    }

    // Check if password is set
    if (!isset($user['password']) || empty($user['password'])) {
        error_log("No password set for: $email");
        echo json_encode(['success' => false, 'message' => 'Password not set for this account']);
        exit;
    }

    // Verify password
    $password_verified = password_verify($password_input, $user['password']);
    
    if (!$password_verified) {
        error_log("Password mismatch for: $email");
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        exit;
    }

    // Check department
    if ($department_id > 0 && $user['department_id'] != $department_id) {
        error_log("Department mismatch: user_dept={$user['department_id']}, requested=$department_id");
        echo json_encode(['success' => false, 'message' => 'Invalid department']);
        exit;
    }

    // Check if admin
    $is_admin = (
        $user['role'] === 'Super Administrator' || 
        $user['role'] === 'Admin' || 
        $user['role'] === 'admin' ||
        $user['role'] === 'Super Admin' ||
        $user['role'] === 'Administrator' ||
        (isset($user['is_admin']) && $user['is_admin'] == 1) ||
        $user['department_id'] == 1
    );

    error_log("Login successful: $email, is_admin: " . ($is_admin ? 'YES' : 'NO'));

    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['name'] ?? $user['full_name'] ?? 'User';
    $_SESSION['department_id'] = $user['department_id'];
    $_SESSION['role'] = $user['role'] ?? 'User';
    $_SESSION['is_admin'] = $is_admin;
    $_SESSION['logged_in'] = true;

    // Update login count
    try {
        $updateStmt = $pdo->prepare("UPDATE employees SET login_count = login_count + 1, last_login = NOW() WHERE id = ?");
        $updateStmt->execute([$user['id']]);
    } catch(PDOException $e) {
        // Column might not exist, try users table
        try {
            $updateStmt = $pdo->prepare("UPDATE users SET login_count = login_count + 1, last_login = NOW() WHERE id = ?");
            $updateStmt->execute([$user['id']]);
        } catch(PDOException $e2) {
            // Ignore
        }
    }

    $response = [
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'] ?? $user['full_name'] ?? 'User',
            'email' => $user['email'],
            'department_id' => $user['department_id'],
            'role' => $user['role'] ?? 'User',
            'is_admin' => $is_admin,
            'last_login' => date('Y-m-d H:i:s')
        ]
    ];

    if ($is_admin) {
        $response['redirect'] = 'super_admin.html';
    } else {
        $response['redirect'] = 'dashboard.html';
    }

    echo json_encode($response);

} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>