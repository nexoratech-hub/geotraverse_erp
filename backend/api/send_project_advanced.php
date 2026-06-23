<?php
// send_project_advanced.php - Send project WITH daily work records

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
        $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->execute([$projectId]);
        $projectData = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($projectData) {
            $projectData['sent_count'] = ($projectData['sent_count'] ?? 0) + 1;
            $projectData['is_sent'] = 1;
            $projectData['last_sent_at'] = date('Y-m-d H:i:s');
        }
    } catch(PDOException $e) {
        // Ignore
    }
}

if (empty($projectData) || !isset($projectData['name'])) {
    echo json_encode(['success' => false, 'message' => 'Project data is required']);
    exit;
}

// ============================================================
// 🔥 1. FETCH DAILY WORK RECORDS FOR THIS PROJECT
// ============================================================
$dailyWorkRecords = [];
try {
    $dwStmt = $pdo->prepare("SELECT * FROM dailywork WHERE project_id = ? AND is_deleted = 0");
    $dwStmt->execute([$projectId]);
    $dailyWorkRecords = $dwStmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Daily work table might not exist
    $dailyWorkRecords = [];
}

// ============================================================
// 🔥 2. CALCULATE DAILY WORK SUMMARY
// ============================================================
$totalBudget = 0;
$totalExpenses = 0;
$totalIncome = 0;
$completedCount = 0;
$partialCount = 0;
$pendingCount = 0;
$totalProduced = 0;
$totalSold = 0;

foreach ($dailyWorkRecords as $dw) {
    $totalBudget += (float)($dw['budget'] ?? 0);
    $totalExpenses += (float)($dw['amount'] ?? 0);
    $totalIncome += (float)($dw['income'] ?? 0);
    $totalProduced += (int)($dw['quantity_produced'] ?? 0);
    $totalSold += (int)($dw['quantity_sold'] ?? 0);
    
    if ($dw['status'] === 'completed') $completedCount++;
    else if ($dw['status'] === 'partial') $partialCount++;
    else $pendingCount++;
}
$remainingBudget = $totalBudget - $totalExpenses;
$totalProfit = $totalIncome - $totalExpenses;

// ============================================================
// 3. GET DEPARTMENT NAMES
// ============================================================
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
// 4. CREATE TABLES IF NOT EXISTS
// ============================================================
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
    PRIMARY KEY (`id`)
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
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

try {
    // ============================================================
    // 5. SAVE PROJECT TO sent_projects TABLE
    // ============================================================
    $projectDataJson = json_encode($projectData);
    $dailyWorkSummary = json_encode([
        'total_budget' => $totalBudget,
        'total_expenses' => $totalExpenses,
        'total_income' => $totalIncome,
        'total_profit' => $totalProfit,
        'remaining_budget' => $remainingBudget,
        'total_produced' => $totalProduced,
        'total_sold' => $totalSold,
        'completed_count' => $completedCount,
        'partial_count' => $partialCount,
        'pending_count' => $pendingCount,
        'records_count' => count($dailyWorkRecords)
    ]);
    
    $checkStmt = $pdo->prepare("SELECT id FROM sent_projects WHERE original_project_id = ? AND to_department_id = ? AND is_deleted = 0");
    $checkStmt->execute([$projectId, $toDeptId]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
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
            amount = ?,
            daily_work_count = ?,
            daily_work_summary = ?
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
            count($dailyWorkRecords),
            $dailyWorkSummary,
            $existing['id']
        ]);
        $sentId = $existing['id'];
    } else {
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
            $projectDataJson,
            $fromDeptId,
            $toDeptId,
            $sentBy,
            $fromDeptName,
            $toDeptName,
            $projectName,
            $projectType,
            $amount,
            count($dailyWorkRecords),
            $dailyWorkSummary
        ]);
        $sentId = $pdo->lastInsertId();
    }
    
    // ============================================================
    // 🔥 6. SAVE DAILY WORK RECORDS TO sent_dailywork TABLE
    // ============================================================
    $dailyWorkInserted = 0;
    if (!empty($dailyWorkRecords)) {
        foreach ($dailyWorkRecords as $dw) {
            $dwData = json_encode([
                'id' => $dw['id'],
                'date' => $dw['date'],
                'project_id' => $dw['project_id'],
                'project_name' => $dw['project_name'],
                'work_description' => $dw['work_description'],
                'budget' => $dw['budget'],
                'amount' => $dw['amount'],
                'income' => $dw['income'] ?? 0,
                'expenses' => $dw['expenses'] ?? 0,
                'quantity_produced' => $dw['quantity_produced'] ?? 0,
                'quantity_sold' => $dw['quantity_sold'] ?? 0,
                'price_per_unit' => $dw['price_per_unit'] ?? 0,
                'payment_status' => $dw['payment_status'] ?? 'pending',
                'partial_amount' => $dw['partial_amount'] ?? 0,
                'status' => $dw['status'] ?? 'pending',
                'work_type' => $dw['work_type'] ?? 'general',
                'created_by' => $dw['created_by'],
                'created_at' => $dw['created_at']
            ]);
            
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
                dailywork_amount
            ) VALUES (?, ?, ?, ?, ?, NOW(), 0, 0, 1, 1, NOW(), ?, ?, ?, ?, ?)");
            
            $result = $dwStmt->execute([
                $dw['id'],
                $dwData,
                $fromDeptId,
                $toDeptId,
                $sentBy,
                $fromDeptName,
                $toDeptName,
                $dw['project_name'] ?? $projectName,
                $dw['date'],
                $dw['amount'] ?? 0
            ]);
            
            if ($result) {
                $dailyWorkInserted++;
            }
        }
    }
    
    // ============================================================
    // 7. UPDATE ORIGINAL PROJECT
    // ============================================================
    try {
        $updateStmt = $pdo->prepare("UPDATE projects SET 
            sent_to_department = ?,
            sent_from_department = ?,
            is_viewed_by_department = 0,
            sent_count = COALESCE(sent_count, 0) + 1,
            is_sent = 1,
            last_sent_at = NOW()
            WHERE id = ?");
        $updateStmt->execute([$toDeptId, $fromDeptId, $projectId]);
    } catch(PDOException $e) {
        // Columns might not exist
    }
    
    // ============================================================
    // 8. UPDATE ORIGINAL DAILY WORK RECORDS
    // ============================================================
    if (!empty($dailyWorkRecords)) {
        foreach ($dailyWorkRecords as $dw) {
            try {
                $updateDwStmt = $pdo->prepare("UPDATE dailywork SET 
                    sent_to_department = ?,
                    sent_from_department = ?,
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
    }
    
    // ============================================================
    // 9. ADD NOTIFICATION
    // ============================================================
    try {
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
        
        $dwCount = count($dailyWorkRecords);
        $message = "📋 Project \"{$projectName}\" sent from {$fromDeptName} with {$dwCount} daily work records";
        $notifStmt->execute([$toDeptId, $fromDeptId, $projectId, $projectName, $message]);
    } catch(PDOException $e) {
        // Notifications table might not exist
    }
    
    // ============================================================
    // 10. RETURN RESPONSE
    // ============================================================
    echo json_encode([
        'success' => true,
        'message' => 'Project and ' . $dailyWorkInserted . ' daily work records sent successfully',
        'sent_id' => $sentId,
        'to_department' => $toDeptId,
        'to_department_name' => $toDeptName,
        'from_department' => $fromDeptId,
        'from_department_name' => $fromDeptName,
        'project_name' => $projectName,
        'daily_work_count' => $dailyWorkInserted,
        'daily_work_summary' => [
            'total_budget' => $totalBudget,
            'total_expenses' => $totalExpenses,
            'total_income' => $totalIncome,
            'total_profit' => $totalProfit,
            'remaining_budget' => $remainingBudget,
            'total_produced' => $totalProduced,
            'total_sold' => $totalSold,
            'completed' => $completedCount,
            'partial' => $partialCount,
            'pending' => $pendingCount
        ]
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>