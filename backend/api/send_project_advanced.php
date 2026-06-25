<?php
// backend/api/send_project_advanced.php
// ============================================================
// FIXED: Daily work inabaki kwa sender, receiver haioni
// ============================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

error_reporting(0);
ini_set('display_errors', 0);

// ============================================================
// DATABASE CONNECTION
// ============================================================
$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// ============================================================
// GET INPUT
// ============================================================
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Required fields
if (!isset($input['project_id']) || !isset($input['to_department_id']) || !isset($input['from_department_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$projectId = (int)$input['project_id'];
$toDeptId = (int)$input['to_department_id'];
$fromDeptId = (int)$input['from_department_id'];
$sentBy = isset($input['sent_by']) ? $input['sent_by'] : 'System';
$isForward = isset($input['is_forward']) ? (int)$input['is_forward'] : 0;
$originalSenderId = isset($input['original_sender_id']) ? (int)$input['original_sender_id'] : $fromDeptId;
$projectData = isset($input['project_data']) ? $input['project_data'] : [];

// ============================================================
// GET DEPARTMENT NAMES
// ============================================================
$fromDeptName = 'Department ' . $fromDeptId;
$toDeptName = 'Department ' . $toDeptId;
$originalSenderName = 'Department ' . $originalSenderId;

try {
    $deptStmt = $pdo->prepare("SELECT name FROM departments WHERE id = ?");
    $deptStmt->execute([$fromDeptId]);
    $fromDeptName = $deptStmt->fetchColumn() ?: $fromDeptName;
    $deptStmt->execute([$toDeptId]);
    $toDeptName = $deptStmt->fetchColumn() ?: $toDeptName;
    $deptStmt->execute([$originalSenderId]);
    $originalSenderName = $deptStmt->fetchColumn() ?: $originalSenderName;
} catch(PDOException $e) {
    // Ignore
}

// ============================================================
// FETCH PROJECT DATA - DETERMINE IF SENT PROJECT
// ============================================================
$originalProjectId = $projectId;
$isSentProject = false;
$sentProjectId = null;

if (empty($projectData)) {
    // Check if it's a sent project (forwarding)
    $sentCheck = $pdo->prepare("SELECT * FROM sent_projects WHERE id = ? AND is_deleted = 0");
    $sentCheck->execute([$projectId]);
    $sentProject = $sentCheck->fetch();
    
    if ($sentProject && $sentProject['project_data']) {
        $projectData = json_decode($sentProject['project_data'], true);
        if (!$projectData || !is_array($projectData)) {
            $projectData = [];
        }
        if (empty($projectData['name'])) {
            $projectData['name'] = $sentProject['project_name'] ?? 'Sent Project';
        }
        if (empty($projectData['amount'])) {
            $projectData['amount'] = $sentProject['amount'] ?? 0;
        }
        $originalProjectId = $sentProject['original_project_id'] ?? $projectId;
        $isSentProject = true;
        $sentProjectId = $sentProject['id'];
        
        if (isset($sentProject['original_sender_id'])) {
            $originalSenderId = (int)$sentProject['original_sender_id'];
        }
        if (isset($sentProject['original_sender_name'])) {
            $originalSenderName = $sentProject['original_sender_name'];
        }
    } else {
        // Check if it's an original project
        $origCheck = $pdo->prepare("SELECT * FROM projects WHERE id = ? AND is_deleted = 0");
        $origCheck->execute([$projectId]);
        $origProject = $origCheck->fetch();
        if ($origProject) {
            $projectData = $origProject;
            $originalProjectId = $projectId;
            $originalSenderId = $fromDeptId;
        }
    }
}

// If still no project data
if (empty($projectData) || !isset($projectData['name'])) {
    echo json_encode(['success' => false, 'message' => 'Project data is required']);
    exit;
}

$projectName = $projectData['name'] ?? 'Untitled Project';
$projectType = $projectData['project_type'] ?? 'general';
$amount = (float)($projectData['amount'] ?? 0);
$image = $projectData['image'] ?? '';
$imagePath = $projectData['image_path'] ?? '';

// ============================================================
// BUILD FORWARD CHAIN
// ============================================================
$forwardChain = isset($projectData['forward_chain']) ? $projectData['forward_chain'] : [];

if ($isForward || $isSentProject) {
    $forwardChain[] = [
        'from_department_id' => $fromDeptId,
        'from_department_name' => $fromDeptName,
        'to_department_id' => $toDeptId,
        'to_department_name' => $toDeptName,
        'forwarded_at' => date('Y-m-d H:i:s'),
        'forwarded_by' => $sentBy
    ];
}

// ============================================================
// CREATE SENT PROJECT DATA - WITHOUT DAILY WORK
// ============================================================
// Important: Daily work inabaki kwa sender, receiver haioni
// ============================================================
$sentProjectData = [
    'name' => $projectName,
    'client_name' => $projectData['client_name'] ?? '',
    'amount' => $amount,
    'status' => $projectData['status'] ?? 'pending',
    'progress' => $projectData['progress'] ?? 0,
    'location' => $projectData['location'] ?? '',
    'description' => $projectData['description'] ?? '',
    'image' => $image,
    'image_path' => $imagePath,
    'project_type' => $projectType,
    'created_by' => $projectData['created_by'] ?? $sentBy,
    'created_at' => $projectData['created_at'] ?? date('Y-m-d H:i:s'),
    'original_project_id' => $originalProjectId,
    'original_sender_id' => $originalSenderId,
    'original_sender_name' => $originalSenderName,
    'sent_from_dept' => $fromDeptId,
    'sent_from_name' => $fromDeptName,
    'sent_to_dept' => $toDeptId,
    'sent_to_name' => $toDeptName,
    'sent_at' => date('Y-m-d H:i:s'),
    'is_sent' => 1,
    'is_sent_copy' => 1,
    'is_forward' => $isForward || $isSentProject ? 1 : 0,
    'forward_chain' => $forwardChain,
    'forward_count' => count($forwardChain)
    // NO DAILY WORK - inabaki kwa sender
];

// ============================================================
// CHECK IF ALREADY SENT TO THIS DEPARTMENT (Prevent Duplicate)
// ============================================================
$checkStmt = $pdo->prepare("SELECT id FROM sent_projects 
                            WHERE original_project_id = ? AND to_department_id = ? AND is_deleted = 0");
$checkStmt->execute([$originalProjectId, $toDeptId]);
$existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    // Update existing sent project
    $stmt = $pdo->prepare("UPDATE sent_projects SET 
        project_data = ?,
        sent_by = ?,
        sent_at = NOW(),
        is_viewed = 0,
        sent_count = sent_count + 1,
        is_sent = 1,
        last_sent_at = NOW(),
        from_department_id = ?,
        from_department_name = ?,
        to_department_name = ?,
        project_name = ?,
        project_type = ?,
        amount = ?,
        forward_count = ?,
        last_forwarded_from = ?,
        is_forward = ?,
        original_sender_id = ?,
        original_sender_name = ?
        WHERE id = ?");
    
    $stmt->execute([
        json_encode($sentProjectData),
        $sentBy,
        $fromDeptId,
        $fromDeptName,
        $toDeptName,
        $projectName,
        $projectType,
        $amount,
        count($forwardChain),
        $fromDeptId,
        $isForward || $isSentProject ? 1 : 0,
        $originalSenderId,
        $originalSenderName,
        $existing['id']
    ]);
    $sentProjectId = $existing['id'];
    
} else {
    // Insert new sent project
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
        amount,
        forward_count,
        last_forwarded_from,
        is_forward,
        original_sender_id,
        original_sender_name
    ) VALUES (?, ?, ?, ?, ?, NOW(), 0, 0, 1, 1, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $originalProjectId,
        json_encode($sentProjectData),
        $fromDeptId,
        $toDeptId,
        $sentBy,
        $fromDeptName,
        $toDeptName,
        $projectName,
        $projectType,
        $amount,
        count($forwardChain),
        $fromDeptId,
        $isForward || $isSentProject ? 1 : 0,
        $originalSenderId,
        $originalSenderName
    ]);
    $sentProjectId = $pdo->lastInsertId();
}

// ============================================================
// DO NOT SAVE DAILY WORK TO RECEIVER
// ============================================================
// Daily work inabaki kwa sender tu
// Receiver haioni daily work
// ============================================================

// ============================================================
// UPDATE ORIGINAL PROJECT - ONLY IF SENDER IS ORIGINAL OWNER
// ============================================================
if ($fromDeptId == $originalSenderId && !$isSentProject) {
    try {
        $updateStmt = $pdo->prepare("UPDATE projects SET 
            sent_to_dept = ?,
            sent_from_dept = ?,
            is_viewed_by_department = 0,
            sent_count = COALESCE(sent_count, 0) + 1,
            is_sent = 1,
            last_sent_at = NOW()
            WHERE id = ?");
        $updateStmt->execute([$toDeptId, $fromDeptId, $originalProjectId]);
    } catch(PDOException $e) {
        // Ignore
    }
}

// ============================================================
// ADD NOTIFICATION - ONLY TO RECEIVER
// ============================================================
try {
    $notifCheck = $pdo->query("SHOW TABLES LIKE 'notifications'");
    if ($notifCheck->rowCount() > 0) {
        $dupNotif = $pdo->prepare("SELECT id FROM notifications 
                                   WHERE department_id = ? AND item_type = 'project' AND item_id = ?");
        $dupNotif->execute([$toDeptId, $sentProjectId]);
        
        if ($dupNotif->rowCount() == 0) {
            $notifStmt = $pdo->prepare("INSERT INTO notifications (
                department_id,
                from_department_id,
                item_type,
                item_id,
                item_title,
                message,
                created_at,
                is_viewed
            ) VALUES (?, ?, 'project', ?, ?, ?, NOW(), 0)");
            
            $forwardText = ($isForward || $isSentProject) ? " (Forwarded from {$fromDeptName})" : "";
            $message = "📋 Project \"{$projectName}\" sent from {$fromDeptName} to {$toDeptName}{$forwardText}";
            $notifStmt->execute([$toDeptId, $fromDeptId, $sentProjectId, $projectName, $message]);
        }
    }
} catch(PDOException $e) {
    // Ignore
}

// ============================================================
// RESPONSE
// ============================================================
echo json_encode([
    'success' => true,
    'message' => 'Project sent successfully',
    'sent_project_id' => $sentProjectId,
    'original_project_id' => $originalProjectId,
    'from_department' => $fromDeptId,
    'from_department_name' => $fromDeptName,
    'to_department' => $toDeptId,
    'to_department_name' => $toDeptName,
    'original_sender_id' => $originalSenderId,
    'original_sender_name' => $originalSenderName,
    'project_name' => $projectName,
    'is_forward' => $isForward || $isSentProject ? 1 : 0,
    'forward_chain' => $forwardChain,
    'note' => 'Daily work remains with the sender only. Receiver does not see daily work.'
]);
?>