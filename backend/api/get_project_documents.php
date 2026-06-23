<?php
// get_project_documents.php - FIXED VERSION

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

error_reporting(0);
ini_set('display_errors', 0);

$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB Error']);
    exit;
}

$departmentId = isset($_GET['department_id']) ? (int)$_GET['department_id'] : 0;

if (!$departmentId) {
    echo json_encode(['success' => false, 'message' => 'Department ID required']);
    exit;
}

try {
    $documents = [];
    
    // ============================================================
    // 1. Get sent documents from sent_documents table
    // ============================================================
    $sentStmt = $pdo->prepare("SELECT 
        sd.*,
        sd.is_viewed as viewed_status,
        sd.is_viewed_by_department as viewed_by_dept
        FROM sent_documents sd
        WHERE sd.to_department_id = ?
        AND (sd.is_deleted = 0 OR sd.is_deleted IS NULL)
        ORDER BY sd.sent_at DESC");
    $sentStmt->execute([$departmentId]);
    $sentDocs = $sentStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($sentDocs as $doc) {
        // Decode document_data
        $docData = [];
        if (isset($doc['document_data']) && $doc['document_data']) {
            $decoded = json_decode($doc['document_data'], true);
            if ($decoded) {
                $docData = $decoded;
            }
        }
        
        // ============================================================
        // FIX: Check is_viewed from database
        // ============================================================
        $isViewed = ($doc['is_viewed'] == 1);
        $isViewedByDept = ($doc['is_viewed_by_department'] == 1);
        $hasViewedAt = ($doc['viewed_at'] !== null);
        
        // Document is UNVIEWED if:
        // - is_viewed = 0 OR
        // - is_viewed_by_department = 0 OR
        // - viewed_at IS NULL
        $isUnviewed = (!$isViewed || !$isViewedByDept || !$hasViewedAt);
        
        // Build document object
        $sentDoc = [
            'id' => $doc['id'],
            'original_document_id' => $doc['original_document_id'] ?? null,
            'title' => $doc['document_title'] ?? $docData['title'] ?? 'Sent Document',
            'description' => $docData['description'] ?? '',
            'file_name' => $doc['document_file'] ?? $docData['file_name'] ?? '',
            'file_path' => $docData['file_path'] ?? $doc['document_file'] ?? '',
            'file_size' => $docData['file_size'] ?? 0,
            'file_type' => $docData['file_type'] ?? '',
            'uploaded_by' => $docData['uploaded_by'] ?? $doc['sent_by'] ?? 'System',
            'department_id' => $doc['to_department_id'],
            'doc_type' => $doc['document_type'] ?? $docData['doc_type'] ?? 'general',
            'created_at' => $doc['sent_at'],
            'updated_at' => $doc['last_sent_at'] ?? $doc['sent_at'],
            'is_deleted' => $doc['is_deleted'] ?? 0,
            'sent_from_department' => $doc['from_department_id'],
            'sent_to_department' => $doc['to_department_id'],
            'sent_count' => $doc['sent_count'] ?? 0,
            'is_sent' => 1,
            'last_sent_at' => $doc['last_sent_at'],
            'is_original' => false,
            'is_sent_document' => true,
            'sent_from_name' => $doc['from_department_name'] ?? '',
            'sent_to_name' => $doc['to_department_name'] ?? '',
            'source_type' => 'sent',
            'sent_by' => $doc['sent_by'] ?? 'System',
            'sent_at' => $doc['sent_at'],
            
            // ============================================================
            // FIX: Set unviewed flags based on database values
            // ============================================================
            'is_viewed' => $doc['is_viewed'] ?? 0,
            'is_viewed_by_department' => $doc['is_viewed_by_department'] ?? 0,
            'viewed_at' => $doc['viewed_at'] ?? null,
            'is_unviewed' => $isUnviewed,
            '_is_unviewed' => $isUnviewed,
            'is_new' => $isUnviewed
        ];
        
        // Merge additional data
        foreach ($docData as $key => $value) {
            if (!isset($sentDoc[$key])) {
                $sentDoc[$key] = $value;
            }
        }
        
        $documents[] = $sentDoc;
    }
    
    // ============================================================
    // 2. Get original documents from project_documents
    // ============================================================
    $origStmt = $pdo->prepare("SELECT 
        *,
        'original' as source_type
        FROM project_documents 
        WHERE department_id = ?
        AND (is_deleted = 0 OR is_deleted IS NULL)
        ORDER BY created_at DESC");
    $origStmt->execute([$departmentId]);
    $origDocs = $origStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($origDocs as $doc) {
        $doc['is_original'] = true;
        $doc['is_sent_document'] = false;
        $doc['is_unviewed'] = false;
        $doc['_is_unviewed'] = false;
        $doc['is_new'] = false;
        $doc['is_viewed'] = 1;
        $documents[] = $doc;
    }
    
    // ============================================================
    // 3. Sort: Unviewed first
    // ============================================================
    usort($documents, function($a, $b) {
        $aUnviewed = isset($a['is_unviewed']) ? $a['is_unviewed'] : false;
        $bUnviewed = isset($b['is_unviewed']) ? $b['is_unviewed'] : false;
        
        if ($aUnviewed && !$bUnviewed) return -1;
        if (!$aUnviewed && $bUnviewed) return 1;
        
        $aDate = isset($a['created_at']) ? strtotime($a['created_at']) : 0;
        $bDate = isset($b['created_at']) ? strtotime($b['created_at']) : 0;
        return $bDate - $aDate;
    });
    
    // ============================================================
    // 4. Count unviewed
    // ============================================================
    $unviewedCount = 0;
    foreach ($documents as $doc) {
        if (isset($doc['is_unviewed']) && $doc['is_unviewed']) {
            $unviewedCount++;
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $documents,
        'total' => count($documents),
        'unviewed_count' => $unviewedCount,
        'sent_count' => count($sentDocs),
        'original_count' => count($origDocs),
        'department_id' => $departmentId
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Query error: ' . $e->getMessage()]);
}
?>