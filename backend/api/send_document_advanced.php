<?php
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
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Get input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$document_id = isset($data['document_id']) ? intval($data['document_id']) : 0;
$to_department_id = isset($data['to_department_id']) ? intval($data['to_department_id']) : 0;
$from_department_id = isset($data['from_department_id']) ? intval($data['from_department_id']) : 0;
$sent_by = isset($data['sent_by']) ? trim($data['sent_by']) : 'System';
$doc_type = isset($data['doc_type']) ? trim($data['doc_type']) : 'project';
$doc_data = isset($data['doc_data']) ? $data['doc_data'] : null;

if ($document_id == 0 || $to_department_id == 0 || $from_department_id == 0) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

if ($to_department_id == $from_department_id) {
    echo json_encode(['success' => false, 'message' => 'Cannot send to your own department']);
    exit;
}

try {
    // ============================================================
    // 1. CHECK IF DOCUMENT EXISTS IN project_documents TABLE
    // ============================================================
    $stmt = $pdo->prepare("SELECT * FROM project_documents WHERE id = ? AND is_deleted = 0");
    $stmt->execute([$document_id]);
    $document = $stmt->fetch(PDO::FETCH_ASSOC);

    // ============================================================
    // 2. IF NOT FOUND, CHECK IN sent_documents TABLE
    // ============================================================
    if (!$document) {
        $sentStmt = $pdo->prepare("
            SELECT * FROM sent_documents 
            WHERE original_document_id = ? OR id = ?
            ORDER BY sent_at DESC LIMIT 1
        ");
        $sentStmt->execute([$document_id, $document_id]);
        $sent_doc = $sentStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($sent_doc) {
            // Use the sent document data
            $doc_data_from_sent = json_decode($sent_doc['document_data'], true);
            $document = [
                'id' => $sent_doc['original_document_id'],
                'title' => $sent_doc['document_title'] ?? $doc_data_from_sent['title'] ?? 'Document',
                'description' => $doc_data_from_sent['description'] ?? '',
                'file_name' => $sent_doc['document_file'] ?? $doc_data_from_sent['file_name'] ?? '',
                'file_path' => $doc_data_from_sent['file_path'] ?? '',
                'file_size' => $doc_data_from_sent['file_size'] ?? 0,
                'file_type' => $doc_data_from_sent['file_type'] ?? '',
                'uploaded_by' => $doc_data_from_sent['uploaded_by'] ?? $sent_by,
                'department_id' => $doc_data_from_sent['department_id'] ?? $from_department_id,
                'created_at' => $doc_data_from_sent['created_at'] ?? date('Y-m-d H:i:s'),
                'sent_count' => ($sent_doc['sent_count'] ?? 0) + 1
            ];
        }
    }

    if (!$document) {
        echo json_encode(['success' => false, 'message' => 'Document not found']);
        exit;
    }

    // ============================================================
    // 3. CHECK IF DEPARTMENT CAN SEND (OWNER OR RECEIVER)
    // ============================================================
    $is_owner = ($document['department_id'] == $from_department_id);
    $is_receiver = ($document['sent_to_department'] == $from_department_id);
    
    // FORWARDING ALLOWED - can send if owner or receiver
    if (!$is_owner && !$is_receiver && $from_department_id != 0) {
        echo json_encode(['success' => false, 'message' => 'You do not have permission to send this document']);
        exit;
    }

    // ============================================================
    // 4. BUILD DOCUMENT DATA FOR SENDING
    // ============================================================
    $full_doc_data = [
        'id' => $document['id'],
        'title' => $document['title'],
        'description' => $document['description'] ?? '',
        'file_name' => $document['file_name'] ?? '',
        'file_path' => $document['file_path'] ?? '',
        'file_size' => intval($document['file_size'] ?? 0),
        'file_type' => $document['file_type'] ?? '',
        'doc_type' => $doc_type,
        'uploaded_by' => $document['uploaded_by'] ?? $sent_by,
        'department_id' => intval($document['department_id']),
        'created_at' => $document['created_at'] ?? date('Y-m-d H:i:s'),
        'sent_from_department' => $from_department_id,
        'sent_to_department' => $to_department_id,
        'sent_count' => intval($document['sent_count'] ?? 0) + 1,
        'is_sent' => 1,
        'last_sent_at' => date('Y-m-d H:i:s')
    ];

    // Merge with provided doc_data if available
    if ($doc_data && is_array($doc_data)) {
        $full_doc_data = array_merge($full_doc_data, $doc_data);
    }

    // ============================================================
    // 5. SAVE TO sent_documents TABLE
    // ============================================================
    $sentStmt = $pdo->prepare("
        INSERT INTO sent_documents (
            original_document_id,
            document_data,
            from_department_id,
            to_department_id,
            sent_by,
            sent_at,
            is_viewed,
            is_viewed_by_department,
            sent_count,
            is_sent,
            from_department_name,
            to_department_name,
            document_title,
            document_type,
            document_file
        ) VALUES (
            ?, ?, ?, ?, ?, NOW(), 0, 0, ?, 1, ?, ?, ?, ?, ?
        )
    ");

    $from_dept_name = getDepartmentName($pdo, $from_department_id);
    $to_dept_name = getDepartmentName($pdo, $to_department_id);

    $sentStmt->execute([
        $document['id'],
        json_encode($full_doc_data),
        $from_department_id,
        $to_department_id,
        $sent_by,
        intval($full_doc_data['sent_count']),
        $from_dept_name,
        $to_dept_name,
        $document['title'],
        $doc_type,
        $document['file_name'] ?? ''
    ]);

    $sent_id = $pdo->lastInsertId();

    // ============================================================
    // 6. UPDATE ORIGINAL DOCUMENT - MARK AS SENT (if exists in project_documents)
    // ============================================================
    try {
        $updateStmt = $pdo->prepare("
            UPDATE project_documents 
            SET 
                sent_from_department = ?,
                sent_to_department = ?,
                sent_count = sent_count + 1,
                is_sent = 1,
                last_sent_at = NOW(),
                updated_at = NOW()
            WHERE id = ?
        ");
        $updateStmt->execute([$from_department_id, $to_department_id, $document['id']]);
    } catch(PDOException $e) {
        // Document might be from sent_documents only, ignore
    }

    // ============================================================
    // 7. ADD NOTIFICATION TO RECIPIENT
    // ============================================================
    $notifStmt = $pdo->prepare("
        INSERT INTO notifications (
            department_id,
            from_department_id,
            item_type,
            item_id,
            item_title,
            message,
            is_viewed,
            created_at
        ) VALUES (
            ?, ?, 'document', ?, ?, ?, 0, NOW()
        )
    ");
    
    $message = "Document \"{$document['title']}\" sent from {$from_dept_name}";
    $notifStmt->execute([
        $to_department_id,
        $from_department_id,
        $document['id'],
        $document['title'],
        $message
    ]);

    // ============================================================
    // 8. RESPONSE
    // ============================================================
    echo json_encode([
        'success' => true,
        'message' => 'Document sent successfully',
        'data' => [
            'sent_id' => $sent_id,
            'document_id' => $document['id'],
            'sent_to' => $to_department_id,
            'sent_from' => $from_department_id,
            'sent_at' => date('Y-m-d H:i:s')
        ]
    ]);

} catch(PDOException $e) {
    error_log("Send document error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}

// ============================================================
// HELPER FUNCTION: Get department name
// ============================================================
function getDepartmentName($pdo, $dept_id) {
    try {
        $stmt = $pdo->prepare("SELECT name FROM departments WHERE id = ?");
        $stmt->execute([$dept_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['name'] : 'Department ' . $dept_id;
    } catch(Exception $e) {
        return 'Department ' . $dept_id;
    }
}
?>