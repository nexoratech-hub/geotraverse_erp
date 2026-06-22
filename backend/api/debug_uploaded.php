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

echo json_encode([
    'uploaded_reports' => $pdo->query("SELECT id, title, file_name, is_deleted FROM uploaded_reports ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC),
    'sent_uploaded_reports' => $pdo->query("SELECT id, original_uploaded_report_id, uploaded_report_title FROM sent_uploaded_reports ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC)
]);
?>