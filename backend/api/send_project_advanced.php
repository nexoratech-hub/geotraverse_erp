<?php
// backend/api/send_project_advanced.php
// ============================================================
// FIXED: Receiver anapofoward, haitumwi kwa Original Sender
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
// FETCH PROJECT DATA
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
        
        // Get original sender info from sent project
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
// FETCH DAILY WORK
// ============================================================
$dailyWorkRecords = [];

try {
    $dwStmt = $pdo->prepare("SELECT * FROM dailywork WHERE project_id = ? AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id ASC");
    $dwStmt->execute([$originalProjectId]);
    $dailyWorkRecords = $dwStmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Ignore
}

// ============================================================
// CALCULATE DAILY WORK TOTALS
// ============================================================
$totalBudget = 0;
$totalExpenses = 0;
$totalRecords = count($dailyWorkRecords);
$completedCount = 0;
$partialCount = 0;
$pendingCount = 0;

foreach ($dailyWorkRecords as $dw) {
    $budget = (float)($dw['budget'] ?? 0);
    $amount = (float)($dw['amount'] ?? 0);
    $status = $dw['status'] ?? 'pending';
    
    $totalBudget += $budget;
    $totalExpenses += $amount;
    
    if ($status === 'completed') $completedCount++;
    else if ($status === 'partial') $partialCount++;
    else $pendingCount++;
}

$remainingBudget = $totalBudget - $totalExpenses;
$dailySummary = [
    'total_records' => $totalRecords,
    'total_budget' => $totalBudget,
    'total_expenses' => $totalExpenses,
    'remaining_budget' => $remainingBudget,
    'completed' => $completedCount,
    'partial' => $partialCount,
    'pending' => $pendingCount
];

// ============================================================
// BUILD FORWARD CHAIN
// ============================================================
$forwardChain = isset($projectData['forward_chain']) ? $projectData['forward_chain'] : [];

// Only add to forward chain if this is a forward (not original send)
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
// CREATE SENT PROJECT DATA - HAIFANYI UPDATE KWA ORIGINAL SENDER
// ============================================================
$sentProjectData = $projectData;
$sentProjectData['sent_from_dept'] = $fromDeptId;
$sentProjectData['sent_from_name'] = $fromDeptName;
$sentProjectData['sent_to_dept'] = $toDeptId;
$sentProjectData['sent_to_name'] = $toDeptName;
$sentProjectData['sent_at'] = date('Y-m-d H:i:s');
$sentProjectData['is_sent'] = 1;
$sentProjectData['is_sent_copy'] = 1;
$sentProjectData['original_project_id'] = $originalProjectId;
$sentProjectData['original_sender_id'] = $originalSenderId;
$sentProjectData['original_sender_name'] = $originalSenderName;
$sentProjectData['daily_work_records'] = $dailyWorkRecords;
$sentProjectData['daily_work_summary'] = $dailySummary;
$sentProjectData['is_forward'] = $isForward || $isSentProject ? 1 : 0;
$sentProjectData['forward_chain'] = $forwardChain;
$sentProjectData['forward_count'] = count($forwardChain);

// ============================================================
// CREATE TABLES IF NOT EXISTS
// ============================================================
try {
    $pdo->exec("
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
        `daily_work_count` int(11) DEFAULT 0,
        `daily_work_summary` longtext DEFAULT NULL,
        `original_sender_id` int(11) DEFAULT NULL,
        `original_sender_name` varchar(100) DEFAULT NULL,
        `forward_count` int(11) DEFAULT 0,
        `last_forwarded_from` int(11) DEFAULT NULL,
        `is_forward` tinyint(4) DEFAULT 0,
        PRIMARY KEY (`id`),
        KEY `idx_original_project` (`original_project_id`),
        KEY `idx_to_dept` (`to_department_id`),
        KEY `idx_from_dept` (`from_department_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `sent_dailywork` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `original_dailywork_id` int(11) NOT NULL,
        `dailywork_data` longtext NOT NULL,
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
        `dailywork_project_name` varchar(255) DEFAULT NULL,
        `dailywork_date` datetime DEFAULT NULL,
        `dailywork_amount` decimal(15,2) DEFAULT 0.00,
        `dailywork_budget` decimal(15,2) DEFAULT 0.00,
        `dailywork_status` varchar(50) DEFAULT 'pending',
        PRIMARY KEY (`id`),
        KEY `idx_original_dailywork` (`original_dailywork_id`),
        KEY `idx_to_dept` (`to_department_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Table creation failed: ' . $e->getMessage()]);
    exit;
}

// ============================================================
// INSERT SENT PROJECT
// ============================================================
try {
    // Check if already sent to this department
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
            daily_work_count = ?,
            daily_work_summary = ?,
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
            $totalRecords,
            json_encode($dailySummary),
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
            daily_work_count,
            daily_work_summary,
            forward_count,
            last_forwarded_from,
            is_forward,
            original_sender_id,
            original_sender_name
        ) VALUES (?, ?, ?, ?, ?, NOW(), 0, 0, 1, 1, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
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
            $totalRecords,
            json_encode($dailySummary),
            count($forwardChain),
            $fromDeptId,
            $isForward || $isSentProject ? 1 : 0,
            $originalSenderId,
            $originalSenderName
        ]);
        $sentProjectId = $pdo->lastInsertId();
    }
    
    // ============================================================
    // SAVE DAILY WORK COPIES
    // ============================================================
    $dailyWorkInserted = 0;
    
    foreach ($dailyWorkRecords as $dw) {
        $checkDwStmt = $pdo->prepare("SELECT id FROM sent_dailywork 
                                      WHERE original_dailywork_id = ? AND to_department_id = ? AND is_deleted = 0");
        $checkDwStmt->execute([$dw['id'] ?? 0, $toDeptId]);
        $existingDw = $checkDwStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingDw) {
            $updateDwStmt = $pdo->prepare("UPDATE sent_dailywork SET 
                sent_count = sent_count + 1,
                last_sent_at = NOW(),
                is_sent = 1
                WHERE id = ?");
            $updateDwStmt->execute([$existingDw['id']]);
            $dailyWorkInserted++;
            continue;
        }
        
        $dwData = $dw;
        $dwData['sent_from_dept'] = $fromDeptId;
        $dwData['sent_from_name'] = $fromDeptName;
        $dwData['sent_to_dept'] = $toDeptId;
        $dwData['sent_to_name'] = $toDeptName;
        $dwData['is_sent'] = 1;
        $dwData['is_sent_copy'] = 1;
        $dwData['original_dailywork_id'] = $dw['id'] ?? 0;
        
        $dwStmt = $pdo->prepare("INSERT INTO sent_dailywork (
            original_dailywork_id,
            dailywork_data,
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
            dailywork_project_name,
            dailywork_date,
            dailywork_amount,
            dailywork_budget,
            dailywork_status
        ) VALUES (?, ?, ?, ?, ?, NOW(), 0, 0, 1, 1, NOW(), ?, ?, ?, ?, ?, ?, ?)");
        
        $result = $dwStmt->execute([
            $dw['id'] ?? 0,
            json_encode($dwData),
            $fromDeptId,
            $toDeptId,
            $sentBy,
            $fromDeptName,
            $toDeptName,
            $dw['project_name'] ?? $projectName,
            $dw['date'] ?? null,
            $dw['amount'] ?? 0,
            $dw['budget'] ?? 0,
            $dw['status'] ?? 'pending'
        ]);
        
        if ($result) {
            $dailyWorkInserted++;
        }
    }
    
    // ============================================================
    // UPDATE ORIGINAL PROJECT - ONLY IF SENDER IS ORIGINAL OWNER
    // ============================================================
    // IMPORTANT: Only update original project if:
    // 1. The sender is the original owner (fromDeptId == originalSenderId)
    // 2. AND this is NOT a forward (isSentProject == false)
    // Hii inazuia receiver kufanya update kwenye original project
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
    // ADD NOTIFICATION - ONLY TO RECEIVER (SI ORIGINAL SENDER)
    // ============================================================
    try {
        $notifCheck = $pdo->query("SHOW TABLES LIKE 'notifications'");
        if ($notifCheck->rowCount() > 0) {
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
            
            // ============================================================
            // DO NOT SEND NOTIFICATION TO ORIGINAL SENDER WHEN FORWARDING
            // ============================================================
            // Hii inazuia original sender kupata notification wakati receiver anafoward
        }
    } catch(PDOException $e) {
        // Ignore
    }
    
    // ============================================================
    // RESPONSE
    // ============================================================
    echo json_encode([
        'success' => true,
        'message' => 'Project sent successfully with ' . $dailyWorkInserted . ' daily work records',
        'sent_project_id' => $sentProjectId,
        'original_project_id' => $originalProjectId,
        'from_department' => $fromDeptId,
        'from_department_name' => $fromDeptName,
        'to_department' => $toDeptId,
        'to_department_name' => $toDeptName,
        'original_sender_id' => $originalSenderId,
        'original_sender_name' => $originalSenderName,
        'project_name' => $projectName,
        'daily_work_count' => $dailyWorkInserted,
        'is_forward' => $isForward || $isSentProject ? 1 : 0,
        'forward_chain' => $forwardChain,
        'daily_work_summary' => $dailySummary,
        'note' => $isSentProject ? 'Forwarded from receiver - original sender not updated' : 'Sent from original sender'
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>