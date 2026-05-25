<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

session_start();

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit;
}

$email = isset($data->email) ? trim($data->email) : '';
$password = isset($data->password) ? $data->password : '';
$department_id = isset($data->department_id) ? intval($data->department_id) : 0;

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email and password required']);
    exit;
}

try {
    // Check user in database
    $query = "SELECT id, name, email, password, role, department_id, is_active FROM users WHERE email = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Check if user is active
            if ($user['is_active'] != 1) {
                echo json_encode(['success' => false, 'message' => 'Your account is deactivated. Contact administrator.']);
                exit;
            }
            
            // Determine redirect based on role
            $redirect = '';
            
            // SUPER ADMIN (role_id = 1 or role = 'Super Administrator')
            if ($user['role'] == 'Super Administrator' || $user['role'] == 'Super Admin' || $user['department_id'] == 1) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['department_id'] = $user['department_id'];
                $_SESSION['is_admin'] = true;
                $_SESSION['logged_in'] = true;
                
                $redirect = 'super_admin.html';
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'redirect' => $redirect,
                    'user' => [
                        'id' => $user['id'],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'role' => $user['role'],
                        'is_admin' => true
                    ]
                ]);
                exit;
            }
            
            // DEPARTMENT USERS - Must have department_id and match selected department
            if ($department_id > 0 && $department_id == $user['department_id']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['department_id'] = $user['department_id'];
                $_SESSION['is_admin'] = false;
                $_SESSION['logged_in'] = true;
                
                // Department page mapping
                $deptPages = [
                    2 => 'finance.html',
                    3 => 'sales_marketing.html',
                    4 => 'manager.html',
                    5 => 'secretary.html',
                    6 => 'bricks_timber.html',
                    7 => 'aluminium.html',
                    8 => 'town_planning.html',
                    9 => 'architectural.html',
                    10 => 'survey.html',
                    11 => 'construction.html',
                    12 => 'hatimiliki.html'
                ];
                
                $redirect = isset($deptPages[$user['department_id']]) ? $deptPages[$user['department_id']] : 'dashboard.html';
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'redirect' => $redirect,
                    'user' => [
                        'id' => $user['id'],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'role' => $user['role'],
                        'department_id' => $user['department_id']
                    ]
                ]);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'You are not authorized for this department. Please select the correct department.']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>