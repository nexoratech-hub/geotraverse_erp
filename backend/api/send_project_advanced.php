<?php
// send_project_advanced.php - Send project with daily work summaries

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

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
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Required fields
if (!isset($input['project_id']) || !isset($input['to_department_id']) || !isset($input['from_department_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields: project_id, to_department_id, from_department_id']);
    exit;
}

$projectId = (int)$input['project_id'];
$toDeptId = (int)$input['to_department_id'];
$fromDeptId = (int)$input['from_department_id'];
$sentBy = isset($input['sent_by']) ? $input['sent_by'] : 'System';
$projectData = isset($input['project_data']) ? $input['project_data'] : [];

// If project_data is empty, try to fetch from database
if (empty($projectData)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->execute([$projectId]);
        $projectData = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($projectData) {
            $projectData['sent_count'] = ($projectData['sent_count'] ?? 0) + 1;
            $projectData['is_sent'] = 1;
        }
    } catch(PDOException $e) {
        // Ignore
    }
}

if (empty($projectData) || !isset($projectData['name'])) {
    echo json_encode(['success' => false, 'message' => 'Project data is required or incomplete']);
    exit;
}

// Get department names
try {
    $deptStmt = $pdo->prepare("SELECT name FROM departments WHERE id = ?");
    $deptStmt->execute([$fromDeptId]);
    $fromDeptName = $deptStmt->fetchColumn();
    $deptStmt->execute([$toDeptId]);
    $toDeptName = $deptStmt->fetchColumn();
} catch(PDOException $e) {
    $fromDeptName = 'Department ' . $fromDeptId;
    $toDeptName = 'Department ' . $toDeptId;
}

$projectName = $projectData['name'] ?? 'Untitled Project';
$projectType = $projectData['project_type'] ?? 'general';
$amount = (float)($projectData['amount'] ?? 0);

// ============================================================
// Create sent_projects table if not exists
// ============================================================
$tableCheck = $pdo->query("SHOW TABLES LIKE 'sent_projects'");
if ($tableCheck->rowCount() == 0) {
    $createTable = "
    CREATE TABLE IF NOT EXISTS `sent_projects` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `original_project_id` int(11) NOT NULL,
        `project_data` longtext DEFAULT NULL,
        `from_department_id` int(11) NOT NULL,
        `to_department_id` int(11) NOT NULL,
        `sent_by` varchar(100) DEFAULT NULL,
        `sent_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `is_viewed` tinyint(4) DEFAULT 0,
        `viewed_at` timestamp NULL DEFAULT NULL,
        `is_deleted` tinyint(4) DEFAULT 0,
        `deleted_at` timestamp NULL DEFAULT NULL,
        `sent_count` int(11) DEFAULT 0,
        `is_sent` tinyint(4) DEFAULT 0,
        `last_sent_at` timestamp NULL DEFAULT NULL,
        `from_department_name` varchar(100) DEFAULT NULL,
        `to_department_name` varchar(100) DEFAULT NULL,
        `project_name` varchar(255) DEFAULT NULL,
        `project_type` varchar(50) DEFAULT NULL,
        `amount` decimal(15,2) DEFAULT 0.00,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $pdo->exec($createTable);
}

// Prepare project data JSON
$projectDataJson = json_encode($projectData);

try {
    // Check if already exists
    $checkStmt = $pdo->prepare("SELECT id FROM sent_projects WHERE original_project_id = ? AND to_department_id = ? AND is_deleted = 0");
    $checkStmt->execute([$projectId, $toDeptId]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        // Update existing
        $stmt = $pdo->prepare("UPDATE sent_projects SET 
            project_data = ?,
            from_department_id = ?,
            sent_by = ?,
            sent_at = NOW(),
            is_viewed = 0,
            sent_count = sent_count + 1,
            is_sent = 1,
            last_sent_at = NOW(),
            from_department_name = ?,
            to_department_name = ?,
            project_name = ?,
            project_type = ?,
            amount = ?
            WHERE id = ?");
        
        $stmt->execute([
            $projectDataJson,
            $fromDeptId,
            $sentBy,
            $fromDeptName,
            $toDeptName,
            $projectName,
            $projectType,
            $amount,
            $existing['id']
        ]);
        $sentId = $existing['id'];
    } else {
        // Insert new
        $stmt = $pdo->prepare("INSERT INTO sent_projects (
            original_project_id,
            project_data,
            from_department_id,
            to_department_id,
            sent_by,
            sent_at,
            is_viewed,
            is_deleted,
            sent_count,
            is_sent,
            last_sent_at,
            from_department_name,
            to_department_name,
            project_name,
            project_type,
            amount
        ) VALUES (?, ?, ?, ?, ?, NOW(), 0, 0, 1, 1, NOW(), ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $projectId,
            $projectDataJson,
            $fromDeptId,
            $toDeptId,
            $sentBy,
            $fromDeptName,
            $toDeptName,
            $projectName,
            $projectType,
            $amount
        ]);
        $sentId = $pdo->lastInsertId();
    }
    
    // Update original project
    try {
        $updateStmt = $pdo->prepare("UPDATE projects SET 
            sent_to_dept = ?,
            sent_from_dept = ?,
            is_viewed_by_department = 0,
            sent_count = sent_count + 1,
            is_sent = 1,
            last_sent_at = NOW()
            WHERE id = ?");
        $updateStmt->execute([$toDeptId, $fromDeptId, $projectId]);
    } catch(PDOException $e) {
        // Columns might not exist
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Project sent successfully',
        'sent_id' => $sentId,
        'to_department' => $toDeptId,
        'to_department_name' => $toDeptName,
        'from_department' => $fromDeptId,
        'from_department_name' => $fromDeptName,
        'project_name' => $projectName
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>