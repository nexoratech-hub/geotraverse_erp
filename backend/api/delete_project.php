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

$project_id = isset($data['project_id']) ? intval($data['project_id']) : 0;
$department_id = isset($data['department_id']) ? intval($data['department_id']) : 0;
$is_deleted = isset($data['is_deleted']) ? intval($data['is_deleted']) : 1;
$deleted_by = isset($data['deleted_by']) ? trim($data['deleted_by']) : 'System';

if ($project_id == 0) {
    echo json_encode(['success' => false, 'message' => 'Project ID required']);
    exit;
}

try {
    // ============================================================
    // 1. GET PROJECT DATA
    // ============================================================
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$project_id]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        echo json_encode(['success' => false, 'message' => 'Project not found']);
        exit;
    }

    // ============================================================
    // 2. CHECK IF DEPARTMENT CAN DELETE (OWNER OR RECEIVER)
    // ============================================================
    $is_owner = ($project['department_id'] == $department_id);
    $is_receiver = ($project['sent_to_dept'] == $department_id);
    
    $can_delete = ($department_id == 0 || $is_owner || $is_receiver);
    
    if (!$can_delete) {
        echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this project']);
        exit;
    }

    // ============================================================
    // 3. SOFT DELETE THE PROJECT (ONLY is_deleted)
    // ============================================================
    $updateStmt = $pdo->prepare("
        UPDATE projects 
        SET 
            is_deleted = ?,
            deleted_at = NOW()
        WHERE id = ?
    ");
    $updateStmt->execute([$is_deleted, $project_id]);

    // ============================================================
    // 4. ADD TO RECYCLE BIN
    // ============================================================
    $recycleStmt = $pdo->prepare("
        INSERT INTO recycle_bin (
            item_id,
            item_type,
            item_name,
            deleted_by_department_id,
            deleted_at
        ) VALUES (
            ?, 'project', ?, ?, NOW()
        )
    ");
    
    $recycleStmt->execute([
        $project_id,
        $project['name'],
        $department_id > 0 ? $department_id : 0
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Project deleted successfully',
        'data' => [
            'project_id' => $project_id
        ]
    ]);

} catch(PDOException $e) {
    error_log("Delete project error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>