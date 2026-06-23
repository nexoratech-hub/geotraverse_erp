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
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Project data is required']);
    exit;
}

$projectName = $projectData['name'] ?? 'Untitled Project';

// ============================================================
// 1. CREATE TABLES IF NOT EXISTS
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
        PRIMARY KEY (`id`),
        KEY `idx_original_project` (`original_project_id`),
        KEY `idx_to_dept` (`to_department_id`)
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
        KEY `idx_from_dept` (`from_department_id`),
        KEY `idx_to_dept` (`to_department_id`),
        KEY `idx_project_name` (`dailywork_project_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
} catch(PDOException $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Table creation failed: ' . $e->getMessage()]);
    exit;
}

// ============================================================
// 2. FETCH DAILY WORK - USE DISTINCT/UNIQUE RECORDS
// ============================================================
$dailyWorkRecords = [];

// Try dailywork table
try {
    $dwStmt = $pdo->prepare("SELECT DISTINCT * FROM dailywork WHERE project_id = ? AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id ASC");
    $dwStmt->execute([$projectId]);
    $dailyWorkRecords = $dwStmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Table might not exist
}

// If no records, try daily_work table
if (empty($dailyWorkRecords)) {
    try {
        $dwStmt = $pdo->prepare("SELECT DISTINCT * FROM daily_work WHERE project_id = ? AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id ASC");
        $dwStmt->execute([$projectId]);
        $dailyWorkRecords = $dwStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        // Table might not exist
    }
}

// If still no records, try by project_name
if (empty($dailyWorkRecords)) {
    try {
        $dwStmt = $pdo->prepare("SELECT DISTINCT * FROM dailywork WHERE project_name = ? AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id ASC");
        $dwStmt->execute([$projectName]);
        $dailyWorkRecords = $dwStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        // Ignore
    }
}

if (empty($dailyWorkRecords)) {
    try {
        $dwStmt = $pdo->prepare("SELECT DISTINCT * FROM daily_work WHERE project_name = ? AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY id ASC");
        $dwStmt->execute([$projectName]);
        $dailyWorkRecords = $dwStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        // Ignore
    }
}

// ============================================================
// 3. REMOVE DUPLICATES BY ID
// ============================================================
$uniqueRecords = [];
$seenIds = [];

foreach ($dailyWorkRecords as $dw) {
    $dwId = (int)($dw['id'] ?? 0);
    if ($dwId > 0 && !in_array($dwId, $seenIds)) {
        $seenIds[] = $dwId;
        $uniqueRecords[] = $dw;
    }
}
$dailyWorkRecords = $uniqueRecords;

// ============================================================
// 4. NORMALIZE DAILY WORK - SUPPORT ALL COLUMN NAMES
// ============================================================
function getColumnValue($data, $keys, $default = 0) {
    foreach ($keys as $key) {
        if (isset($data[$key]) && $data[$key] !== '' && $data[$key] !== null) {
            return $data[$key];
        }
    }
    return $default;
}

$normalizedDailyWork = [];
$totalBudget = 0;
$totalExpenses = 0;
$totalIncome = 0;
$totalAmount = 0;
$totalProduced = 0;
$totalSold = 0;
$completedCount = 0;
$partialCount = 0;
$pendingCount = 0;

foreach ($dailyWorkRecords as $dw) {
    // Get values with multiple possible column names
    $budget = (float)getColumnValue($dw, ['budget', 'budget_allocated', 'total_budget', 'allocated_budget'], 0);
    $amount = (float)getColumnValue($dw, ['amount', 'amount_spent', 'spent', 'spent_amount'], 0);
    $expenses = (float)getColumnValue($dw, ['expenses', 'expense', 'cost', 'amount_spent', 'spent'], $amount);
    $income = (float)getColumnValue($dw, ['income', 'total_amount', 'total_income', 'revenue', 'earned'], 0);
    $profit = (float)getColumnValue($dw, ['profit', 'net_profit', 'gain'], 0);
    $total_amount = (float)getColumnValue($dw, ['total_amount', 'total', 'grand_total', 'amount_total'], $income);
    $quantity_produced = (int)getColumnValue($dw, ['quantity_produced', 'produced', 'qty_produced', 'production_qty'], 0);
    $quantity_sold = (int)getColumnValue($dw, ['quantity_sold', 'sold', 'qty_sold', 'sales_qty'], 0);
    $price_per_unit = (float)getColumnValue($dw, ['price_per_unit', 'unit_price', 'price', 'rate'], 0);
    $status = getColumnValue($dw, ['status', 'work_status', 'progress_status', 'payment_status'], 'pending');
    $payment_status = getColumnValue($dw, ['payment_status', 'pay_status', 'status'], 'pending');
    $work_description = getColumnValue($dw, ['work_description', 'description', 'work_desc', 'desc'], '');
    $work_type = getColumnValue($dw, ['work_type', 'type', 'category'], 'general');
    $date = getColumnValue($dw, ['date', 'work_date', 'created_date', 'entry_date'], date('Y-m-d H:i:s'));
    $created_by = getColumnValue($dw, ['created_by', 'uploaded_by', 'entered_by', 'user'], 'System');
    $created_at = getColumnValue($dw, ['created_at', 'uploaded_at', 'entry_at', 'date_created'], date('Y-m-d H:i:s'));
    
    // Calculate profit if missing
    if ($profit == 0) {
        if ($income > 0 && $expenses > 0) {
            $profit = $income - $expenses;
        } elseif ($total_amount > 0 && $expenses > 0) {
            $profit = $total_amount - $expenses;
        } elseif ($amount > 0 && $budget > 0) {
            $profit = $amount - $budget;
        }
    }
    
    // Determine payment status
    if ($payment_status === 'pending' || $payment_status === '') {
        $paid = (float)getColumnValue($dw, ['amount_paid', 'partial_amount', 'paid_amount', 'paid'], 0);
        if ($paid >= $total_amount && $total_amount > 0) {
            $payment_status = 'paid';
        } elseif ($paid > 0) {
            $payment_status = 'partial';
        }
    }
    
    // Determine main status
    if ($status === 'pending' || $status === '') {
        if ($payment_status === 'paid' || $payment_status === 'completed') {
            $status = 'completed';
        } elseif ($payment_status === 'partial') {
            $status = 'partial';
        }
    }
    
    // Build normalized record - ONE RECORD PER ID
    $normalized = [
        'id' => (int)($dw['id'] ?? 0),
        'date' => $date,
        'work_description' => $work_description,
        'work_type' => $work_type,
        'project_id' => $projectId,
        'project_name' => $projectName,
        'department_id' => $fromDeptId,
        'budget' => $budget,
        'amount' => $amount,
        'expenses' => $expenses,
        'income' => $income,
        'profit' => $profit,
        'total_amount' => $total_amount,
        'quantity_produced' => $quantity_produced,
        'quantity_sold' => $quantity_sold,
        'price_per_unit' => $price_per_unit,
        'payment_status' => $payment_status,
        'status' => $status,
        'created_by' => $created_by,
        'created_at' => $created_at,
        'updated_by' => $dw['updated_by'] ?? null,
        'updated_at' => $dw['updated_at'] ?? null,
        'sent_from_dept' => $fromDeptId,
        'sent_to_dept' => null,
        'is_sent' => 0
    ];
    
    $normalizedDailyWork[] = $normalized;
    
    // Calculate totals
    $totalBudget += $budget;
    $totalExpenses += $expenses;
    $totalIncome += $income;
    $totalAmount += $amount;
    $totalProduced += $quantity_produced;
    $totalSold += $quantity_sold;
    
    if ($status === 'completed' || $status === 'Completed') {
        $completedCount++;
    } else if ($status === 'partial' || $status === 'Partial') {
        $partialCount++;
    } else {
        $pendingCount++;
    }
}

$remainingBudget = $totalBudget - $totalExpenses;
$totalProfit = $totalIncome - $totalExpenses;
$totalRecords = count($normalizedDailyWork);

// ============================================================
// 5. GET DEPARTMENT NAMES
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

// ============================================================
// 6. ADD DAILY WORK TO PROJECT DATA (ONLY ONCE)
// ============================================================
$projectData['daily_work_records'] = $normalizedDailyWork;
$projectData['daily_work_summary'] = [
    'total_records' => $totalRecords,
    'total_budget' => $totalBudget,
    'total_expenses' => $totalExpenses,
    'total_income' => $totalIncome,
    'total_amount' => $totalAmount,
    'total_profit' => $totalProfit,
    'remaining_budget' => $remainingBudget,
    'total_produced' => $totalProduced,
    'total_sold' => $totalSold,
    'completed' => $completedCount,
    'partial' => $partialCount,
    'pending' => $pendingCount
];

// ============================================================
// 7. CHECK IF ALREADY SENT - AVOID DUPLICATES
// ============================================================
try {
    $checkStmt = $pdo->prepare("SELECT id, sent_count FROM sent_projects WHERE original_project_id = ? AND to_department_id = ? AND is_deleted = 0");
    $checkStmt->execute([$projectId, $toDeptId]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    // If already sent, just update sent_count - DON'T CREATE DUPLICATE
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
            json_encode($projectData),
            $fromDeptId,
            $sentBy,
            $fromDeptName,
            $toDeptName,
            $projectName,
            $projectType,
            $amount,
            $totalRecords,
            json_encode($projectData['daily_work_summary']),
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
            json_encode($projectData),
            $fromDeptId,
            $toDeptId,
            $sentBy,
            $fromDeptName,
            $toDeptName,
            $projectName,
            $projectType,
            $amount,
            $totalRecords,
            json_encode($projectData['daily_work_summary'])
        ]);
        $sentId = $pdo->lastInsertId();
    }
    
    // ============================================================
    // 8. SAVE DAILY WORK - CHECK IF ALREADY SENT (AVOID DUPLICATES)
    // ============================================================
    $dailyWorkInserted = 0;
    if (!empty($normalizedDailyWork)) {
        foreach ($normalizedDailyWork as $dw) {
            // Check if this daily work already sent to this department
            $checkDwStmt = $pdo->prepare("SELECT id FROM sent_dailywork WHERE original_dailywork_id = ? AND to_department_id = ? AND is_deleted = 0 LIMIT 1");
            $checkDwStmt->execute([$dw['id'], $toDeptId]);
            $existingDw = $checkDwStmt->fetch(PDO::FETCH_ASSOC);
            
            // Skip if already sent
            if ($existingDw) {
                // Update sent count instead
                $updateDwStmt = $pdo->prepare("UPDATE sent_dailywork SET 
                    sent_count = sent_count + 1,
                    last_sent_at = NOW(),
                    is_sent = 1
                WHERE id = ?");
                $updateDwStmt->execute([$existingDw['id']]);
                continue;
            }
            
            $dwData = json_encode($dw);
            
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
                $dwData,
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
    }
    
    // ============================================================
    // 9. UPDATE ORIGINAL PROJECT
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
    // 10. UPDATE ORIGINAL DAILY WORK - MARK AS SENT
    // ============================================================
    if (!empty($dailyWorkRecords)) {
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
    }
    
    // ============================================================
    // 11. ADD NOTIFICATION
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
            
            $dwCount = count($normalizedDailyWork);
            $message = "📋 Project \"{$projectName}\" sent from {$fromDeptName} with {$dwCount} daily work records";
            $notifStmt->execute([$toDeptId, $fromDeptId, $projectId, $projectName, $message]);
        }
    } catch(PDOException $e) {
        // Ignore
    }
    
    // ============================================================
    // 12. RETURN RESPONSE
    // ============================================================
    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Project and ' . $dailyWorkInserted . ' daily work records sent successfully',
        'sent_id' => $sentId,
        'to_department' => $toDeptId,
        'to_department_name' => $toDeptName,
        'from_department' => $fromDeptId,
        'from_department_name' => $fromDeptName,
        'project_name' => $projectName,
        'project_type' => $projectType,
        'daily_work_count' => $dailyWorkInserted,
        'daily_work_summary' => [
            'total_budget' => $totalBudget,
            'total_expenses' => $totalExpenses,
            'total_income' => $totalIncome,
            'total_amount' => $totalAmount,
            'total_profit' => $totalProfit,
            'remaining_budget' => $remainingBudget,
            'total_produced' => $totalProduced,
            'total_sold' => $totalSold,
            'completed' => $completedCount,
            'partial' => $partialCount,
            'pending' => $pendingCount,
            'total_records' => $totalRecords
        ],
        'daily_work_records' => $normalizedDailyWork
    ]);
    
} catch(PDOException $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>