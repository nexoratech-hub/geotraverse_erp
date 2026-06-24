<?php
// backend/api/test_json.php

// ============================================================
// SIMPLE JSON TEST
// ============================================================

// Headers
header('Content-Type: application/json');

// Database
try {
    $pdo = new PDO("mysql:host=localhost;dbname=geotraverse_erp;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo json_encode(['error' => 'Database: ' . $e->getMessage()]);
    exit;
}

// Get data
try {
    $query = "SELECT id, title, is_deleted FROM project_documents LIMIT 10";
    $stmt = $pdo->query($query);
    $data = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'count' => count($data),
        'data' => $data
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Query: ' . $e->getMessage()]);
}
?>