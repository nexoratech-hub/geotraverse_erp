<?php
// backend/api/delete_project_document.php

// ============================================================
// HEADERS
// ============================================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// ============================================================
// DATABASE CONNECTION - KWA PDO
// ============================================================
try {
    $host = 'localhost';
    $dbname = 'geotraverse_erp';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit;
}

// ============================================================
// GET INPUT DATA
// ============================================================
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['id']) || !isset($input['department_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields: id, department_id'
    ]);
    exit;
}

$document_id = intval($input['id']);
$department_id = intval($input['department_id']);
$deleted_by = isset($input['deleted_by']) ? $input['deleted_by'] : 'System';

// ============================================================
// FUNCTION TO ADD TO RECYCLE BIN
// ============================================================
function addToRecycleBin($pdo, $item_id, $item_type, $item_name, $department_id, $deleted_by) {
    try {
        $query = "INSERT INTO recycle_bin (item_id, item_type, item_name, deleted_by_department_id, deleted_by_admin, deleted_by_name, created_at) 
                  VALUES (?, ?, ?, ?, 0, ?, NOW())";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$item_id, $item_type, $item_name, $department_id, $deleted_by]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// ============================================================
// DELETE DOCUMENTS
// ============================================================
try {
    $deleted_count = 0;
    $deleted_ids = [];
    $deleted_items = [];
    
    // ============================================================
    // 1. DELETE ORIGINAL DOCUMENT
    // ============================================================
    $query = "SELECT id, title FROM project_documents WHERE id = ? AND is_deleted = 0";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$document_id]);
    $doc = $stmt->fetch();
    
    if ($doc) {
        $query = "UPDATE project_documents SET 
                  is_deleted = 1,
                  deleted_at = NOW(),
                  deleted_by = ?
                  WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$deleted_by, $document_id]);
        $deleted_count++;
        $deleted_ids[] = $document_id;
        $deleted_items[] = [
            'table' => 'project_documents',
            'id' => $document_id,
            'name' => $doc['title'],
            'type' => 'original'
        ];
        
        addToRecycleBin($pdo, $document_id, 'project_document', $doc['title'], $department_id, $deleted_by);
    }
    
    // ============================================================
    // 2. DELETE SENT DOCUMENTS (Kwa original_document_id)
    // ============================================================
    $query = "SELECT id, document_title FROM sent_documents 
              WHERE original_document_id = ? AND is_deleted = 0";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$document_id]);
    $sentDocs = $stmt->fetchAll();
    
    foreach ($sentDocs as $sent) {
        $query = "UPDATE sent_documents SET 
                  is_deleted = 1,
                  deleted_at = NOW()
                  WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$sent['id']]);
        $deleted_count++;
        $deleted_ids[] = $sent['id'];
        $deleted_items[] = [
            'table' => 'sent_documents',
            'id' => $sent['id'],
            'name' => $sent['document_title'],
            'type' => 'sent'
        ];
        
        addToRecycleBin($pdo, $sent['id'], 'sent_project_document', $sent['document_title'], $department_id, $deleted_by);
    }
    
    // ============================================================
    // 3. DELETE SENT DOCUMENT KWA ID (Kama original haipo)
    // ============================================================
    if ($deleted_count == 0) {
        $query = "SELECT id, document_title FROM sent_documents 
                  WHERE id = ? AND is_deleted = 0";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$document_id]);
        $sent = $stmt->fetch();
        
        if ($sent) {
            $query = "UPDATE sent_documents SET 
                      is_deleted = 1,
                      deleted_at = NOW()
                      WHERE id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$document_id]);
            $deleted_count++;
            $deleted_ids[] = $document_id;
            $deleted_items[] = [
                'table' => 'sent_documents',
                'id' => $document_id,
                'name' => $sent['document_title'],
                'type' => 'sent'
            ];
            
            addToRecycleBin($pdo, $document_id, 'sent_project_document', $sent['document_title'], $department_id, $deleted_by);
        }
    }
    
    // ============================================================
    // SEND RESPONSE
    // ============================================================
    if ($deleted_count > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Document(s) deleted successfully',
            'deleted_count' => $deleted_count,
            'deleted_ids' => $deleted_ids,
            'deleted_items' => $deleted_items
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Document not found',
            'deleted_count' => 0
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>