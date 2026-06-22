<?php
header('Content-Type: application/json');

$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die(json_encode(['error' => 'DB Connection failed: ' . $e->getMessage()]));
}

// Test with Jackson's email
$email = 'jacksonmyula773@gmail.com';
$test_password = '123456';

$stmt = $pdo->prepare("SELECT id, name, email, password, department_id, role, is_admin FROM employees WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die(json_encode(['error' => 'User not found']));
}

$hash = $user['password'];
$verified = password_verify($test_password, $hash);

echo json_encode([
    'user' => [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'department_id' => $user['department_id'],
        'role' => $user['role'],
        'is_admin' => $user['is_admin']
    ],
    'hash_preview' => substr($hash, 0, 30) . '...',
    'hash_length' => strlen($hash),
    'password_verified' => $verified,
    'result' => $verified ? '✅ SUCCESS - Login will work!' : '❌ FAILED - Password hash mismatch'
]);
?>