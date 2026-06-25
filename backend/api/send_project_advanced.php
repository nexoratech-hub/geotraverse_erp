<?php
// send_project_advanced.php - FIXED VERSION

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

error_reporting(0);
ini_set('display_errors', 0);
ob_clean();

$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Required fields
if (!isset($input['project_id']) || !isset($input['to_department_id']) || !isset($input['from_department_id'])) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$projectId = (int)$input['project_id'];
$toDeptId = (int)$input['to_department_id'];
$fromDeptId = (int)$input['from_department_id'];
$sentBy = isset($input['sent_by']) ? $input['sent_by'] : 'System';
$projectData = isset($input['project_data']) ? $input['project_data'] : [];

// If project_data is empty, fetch from database
if (empty($projectData)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ? AND is_deleted = 0");
        $stmt->execute([$projectId]);
        $projectData = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        // Ignore
    }
}

if (empty($projectData) || !isset($projectData['name'])) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Project data is required']);
    exit;
}

$projectName = $projectData['name'] ?? 'Untitled Project';

// ============================================================
// 1. CREATE TABLES IF NOT EXISTS - FIXED COLUMNS
// ============================================================
try {
    // Check if dailywork_budget column exists, if not add it
    try {
        $checkColumn = $pdo->query("SHOW COLUMNS FROM sent_dailywork LIKE 'dailywork_budget'");
        if ($checkColumn->rowCount() == 0) {
            $pdo->exec("ALTER TABLE sent_dailywork ADD COLUMN dailywork_budget decimal(15,2) DEFAULT 0.00");
        }
    } catch(PDOException $e) {
        // Column might already exist
    }
    
    try {
        $checkColumn = $pdo->query("SHOW COLUMNS FROM sent_dailywork LIKE 'dailywork_status'");
        if ($checkColumn->rowCount() == 0) {
            $pdo->exec("ALTER TABLE sent_dailywork ADD COLUMN dailywork_status varchar(50) DEFAULT 'pending'");
        }
    } catch(PDOException $e) {
        // Column might already exist
    }

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
        KEY `idx_from_dept` (`from_department_id`),
        KEY `idx_to_dept` (`to_department_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
} catch(PDOException $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Table creation failed: ' . $e->getMessage()]);
    exit;
}

// ============================================================
// 2. FETCH DAILY WORK - ORIGINAL RECORDS ONLY
// ============================================================
$dailyWorkRecords = [];

try {
    $dwStmt = $pdo->prepare("SELECT * FROM dailywork WHERE project_id = ? AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id ASC");
    $dwStmt->execute([$projectId]);
    $dailyWorkRecords = $dwStmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Ignore
}

// ============================================================
// 3. GET DEPARTMENT NAMES
// ============================================================
$fromDeptName = 'Department ' . $fromDeptId;
$toDeptName = 'Department ' . $toDeptId;
try {
    $deptStmt = $pdo->prepare("SELECT name FROM departments WHERE id = ?");
    $deptStmt->execute([$fromDeptId]);
    $fromDeptName = $deptStmt->fetchColumn() ?: $fromDeptName;
    $deptStmt->execute([$toDeptId]);
    $toDeptName = $deptStmt->fetchColumn() ?: $toDeptName;
} catch(PDOException $e) {
    // Ignore
}

$projectType = $projectData['project_type'] ?? 'general';
$amount = (float)($projectData['amount'] ?? 0);

// Get image path
$image = $projectData['image'] ?? '';
$imagePath = $projectData['image_path'] ?? '';

// ============================================================
// 4. CREATE SENT PROJECT COPY (NEW ID)
// ============================================================
$sentProjectData = $projectData;
$sentProjectData['sent_from_dept'] = $fromDeptId;
$sentProjectData['sent_to_dept'] = $toDeptId;
$sentProjectData['sent_at'] = date('Y-m-d H:i:s');
$sentProjectData['is_sent'] = 1;
$sentProjectData['is_sent_copy'] = 1;
$sentProjectData['original_project_id'] = $projectId;
$sentProjectData['daily_work_records'] = [];

// ============================================================
// 5. CALCULATE DAILY WORK TOTALS
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

$sentProjectData['daily_work_summary'] = $dailySummary;

// ============================================================
// 6. INSERT SENT PROJECT (WITH NEW ID)
// ============================================================
try {
    // Check if already sent to this department (update if exists)
    $checkStmt = $pdo->prepare("SELECT id, sent_count FROM sent_projects 
                                WHERE original_project_id = ? AND to_department_id = ? AND is_deleted = 0");
    $checkStmt->execute([$projectId, $toDeptId]);
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
            from_department_name = ?,
            to_department_name = ?,
            project_name = ?,
            project_type = ?,
            amount = ?,
            daily_work_count = ?,
            daily_work_summary = ?
            WHERE id = ?");
        
        $stmt->execute([
            json_encode($sentProjectData),
            $sentBy,
            $fromDeptName,
            $toDeptName,
            $projectName,
            $projectType,
            $amount,
            $totalRecords,
            json_encode($dailySummary),
            $existing['id']
        ]);
        $sentProjectId = $existing['id'];
    } else {
        // Insert new sent project (NEW ID)
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
            daily_work_summary
        ) VALUES (?, ?, ?, ?, ?, NOW(), 0, 0, 1, 1, NOW(), ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $projectId,
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
            json_encode($dailySummary)
        ]);
        $sentProjectId = $pdo->lastInsertId();
    }
    
    // ============================================================
    // 7. SAVE DAILY WORK COPIES (WITH NEW IDS) - FIXED COLUMNS
    // ============================================================
    $dailyWorkInserted = 0;
    $sentDailyworkIds = [];
    
    foreach ($dailyWorkRecords as $dw) {
        // Check if this daily work already sent to this department
        $checkDwStmt = $pdo->prepare("SELECT id FROM sent_dailywork 
                                      WHERE original_dailywork_id = ? AND to_department_id = ? AND is_deleted = 0");
        $checkDwStmt->execute([$dw['id'], $toDeptId]);
        $existingDw = $checkDwStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingDw) {
            // Update existing sent dailywork
            $updateDwStmt = $pdo->prepare("UPDATE sent_dailywork SET 
                sent_count = sent_count + 1,
                last_sent_at = NOW(),
                is_sent = 1
                WHERE id = ?");
            $updateDwStmt->execute([$existingDw['id']]);
            $sentDailyworkIds[] = $existingDw['id'];
            $dailyWorkInserted++;
            continue;
        }
        
        // Prepare dailywork data for sending (copy)
        $dwData = $dw;
        $dwData['sent_from_dept'] = $fromDeptId;
        $dwData['sent_to_dept'] = $toDeptId;
        $dwData['is_sent'] = 1;
        $dwData['is_sent_copy'] = 1;
        $dwData['original_dailywork_id'] = $dw['id'];
        
        // Insert new sent dailywork (NEW ID) - FIXED: Use correct column names
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
            $dw['id'],
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
            $sentDailyworkIds[] = $pdo->lastInsertId();
            $dailyWorkInserted++;
        }
    }
    
    // ============================================================
    // 8. UPDATE ORIGINAL PROJECT - MARK AS SENT
    // ============================================================
    try {
        $updateStmt = $pdo->prepare("UPDATE projects SET 
            sent_to_dept = ?,
            sent_from_dept = ?,
            is_viewed_by_department = 0,
            sent_count = COALESCE(sent_count, 0) + 1,
            is_sent = 1,
            last_sent_at = NOW()
            WHERE id = ?");
        $updateStmt->execute([$toDeptId, $fromDeptId, $projectId]);
    } catch(PDOException $e) {
        // Ignore
    }
    
    // ============================================================
    // 9. UPDATE ORIGINAL DAILY WORK - MARK AS SENT
    // ============================================================
    foreach ($dailyWorkRecords as $dw) {
        try {
            $updateDwStmt = $pdo->prepare("UPDATE dailywork SET 
                sent_to_dept = ?,
                sent_from_dept = ?,
                is_sent = 1,
                sent_count = COALESCE(sent_count, 0) + 1,
                last_sent_at = NOW(),
                is_viewed_by_department = 0
                WHERE id = ?");
            $updateDwStmt->execute([$toDeptId, $fromDeptId, $dw['id']]);
        } catch(PDOException $e) {
            // Ignore
        }
    }
    
    // ============================================================
    // 10. ADD NOTIFICATION
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
            
            $message = "📋 Project \"{$projectName}\" sent from {$fromDeptName} with {$totalRecords} daily work records";
            $notifStmt->execute([$toDeptId, $fromDeptId, $sentProjectId, $projectName, $message]);
        }
    } catch(PDOException $e) {
        // Ignore
    }
    
    // ============================================================
    // 11. RETURN RESPONSE
    // ============================================================
    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Project and ' . $dailyWorkInserted . ' daily work records sent successfully',
        'sent_project_id' => $sentProjectId,
        'original_project_id' => $projectId,
        'to_department' => $toDeptId,
        'to_department_name' => $toDeptName,
        'from_department' => $fromDeptId,
        'from_department_name' => $fromDeptName,
        'project_name' => $projectName,
        'daily_work_count' => $dailyWorkInserted,
        'sent_dailywork_ids' => $sentDailyworkIds,
        'daily_work_summary' => $dailySummary
    ]);
    
} catch(PDOException $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>