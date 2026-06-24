<?php
// backend/api/get_project_documents.php

// ============================================================
// ERROR REPORTING
// ============================================================
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// ============================================================
// HEADERS
// ============================================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ============================================================
// DATABASE CONNECTION
// ============================================================
try {
    $pdo = new PDO("mysql:host=localhost;dbname=geotraverse_erp;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => []
    ]);
    exit;
}

// ============================================================
// GET PARAMETERS
// ============================================================
$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;

// ============================================================
// GET PROJECT DOCUMENTS
// ============================================================
try {
    $documents = [];

    // 1. Original documents
    $query = "SELECT 
                id, title, description, file_name, file_path,
                file_size, file_type, doc_type, department_id,
                uploaded_by, created_at, updated_at,
                is_deleted, deleted_at,
                'original' as source_type,
                0 as is_sent
              FROM project_documents 
              WHERE (is_deleted = 0 OR is_deleted IS NULL)
              ORDER BY id DESC";
    
    $stmt = $pdo->query($query);
    while ($row = $stmt->fetch()) {
        $row['is_sent'] = 0;
        $documents[] = $row;
    }

    // 2. Sent documents (to this department)
    $query = "SELECT 
                original_document_id as id,
                document_title as title,
                NULL as description,
                document_file as file_name,
                NULL as file_path,
                0 as file_size,
                NULL as file_type,
                document_type as doc_type,
                from_department_id as department_id,
                sent_by as uploaded_by,
                sent_at as created_at,
                last_sent_at as updated_at,
                is_deleted,
                'sent' as source_type,
                1 as is_sent,
                from_department_name as sent_from_name,
                to_department_name as sent_to_name
              FROM sent_documents 
              WHERE (is_deleted = 0 OR is_deleted IS NULL)";
    
    if ($department_id > 0) {
        $query .= " AND to_department_id = " . intval($department_id);
    }
    $query .= " ORDER BY sent_at DESC";
    
    $stmt = $pdo->query($query);
    while ($row = $stmt->fetch()) {
        $documents[] = $row;
    }

    echo json_encode([
        'success' => true,
        'data' => $documents,
        'count' => count($documents),
        'message' => 'Documents retrieved successfully'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => []
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>