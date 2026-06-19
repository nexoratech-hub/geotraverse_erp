<?php
// send_project_advanced.php - Fixed version with proper error handling

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
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

// Get input data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

// Log received data for debugging
error_log("=== SEND PROJECT REQUEST ===");
error_log("Input: " . print_r($input, true));

// Required fields
$required = ['project_id', 'to_department_id', 'from_department_id', 'project_data'];
foreach ($required as $field) {
    if (!isset($input[$field])) {
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

$projectId = (int)$input['project_id'];
$toDeptId = (int)$input['to_department_id'];
$fromDeptId = (int)$input['from_department_id'];
$sentBy = isset($input['sent_by']) ? $input['sent_by'] : 'System';
$projectData = $input['project_data'];

// ============================================================
// STEP 1: SAVE SENT PROJECT RECORD
// ============================================================

// Extract project data
$projectName = isset($projectData['name']) ? $projectData['name'] : '';
$projectType = isset($projectData['project_type']) ? $projectData['project_type'] : 'general';
$amount = isset($projectData['amount']) ? (float)$projectData['amount'] : 0;
$clientName = isset($projectData['client_name']) ? $projectData['client_name'] : '';
$location = isset($projectData['location']) ? $projectData['location'] : '';
$status = isset($projectData['status']) ? $projectData['status'] : 'pending';
$progress = isset($projectData['progress']) ? (int)$projectData['progress'] : 0;
$description = isset($projectData['description']) ? $projectData['description'] : '';
$image = isset($projectData['image']) ? $projectData['image'] : '';
$createdBy = isset($projectData['created_by']) ? $projectData['created_by'] : 'System';
$createdAt = isset($projectData['created_at']) ? $projectData['created_at'] : date('Y-m-d H:i:s');

// Extract daily work and create summary only
$dailyWork = isset($projectData['daily_work']) && is_array($projectData['daily_work']) ? $projectData['daily_work'] : [];
$dailyWorkSummary = [];

if (!empty($dailyWork)) {
    $totalBudget = 0;
    $totalAmount = 0;
    $totalIncome = 0;
    $totalExpenses = 0;
    $completedCount = 0;
    $partialCount = 0;
    $pendingCount = 0;
    $totalRecords = count($dailyWork);
    
    foreach ($dailyWork as $dw) {
        $budget = isset($dw['budget']) ? (float)$dw['budget'] : 0;
        $amountVal = isset($dw['amount']) ? (float)$dw['amount'] : 0;
        $income = isset($dw['income']) ? (float)$dw['income'] : 0;
        $expenses = isset($dw['expenses']) ? (float)$dw['expenses'] : 0;
        
        $totalBudget += $budget;
        $totalAmount += $amountVal;
        $totalIncome += $income;
        $totalExpenses += $expenses;
        
        $statusVal = isset($dw['status']) ? strtolower($dw['status']) : 'pending';
        if ($statusVal === 'completed') $completedCount++;
        elseif ($statusVal === 'partial') $partialCount++;
        else $pendingCount++;
    }
    
    $totalProfit = $totalIncome - $totalExpenses;
    $remainingBudget = $totalBudget - $totalAmount;
    
    $dailyWorkSummary = [
        'total_records' => $totalRecords,
        'total_budget' => $totalBudget,
        'total_amount_spent' => $totalAmount,
        'total_income' => $totalIncome,
        'total_expenses' => $totalExpenses,
        'total_profit' => $totalProfit,
        'remaining_budget' => $remainingBudget,
        'completed_count' => $completedCount,
        'partial_count' => $partialCount,
        'pending_count' => $pendingCount,
        'has_daily_work' => $totalRecords > 0
    ];
}

// Prepare project data with summary (remove full daily_work)
$projectDataToStore = [
    'id' => $projectId,
    'name' => $projectName,
    'client_name' => $clientName,
    'amount' => $amount,
    'location' => $location,
    'description' => $description,
    'status' => $status,
    'progress' => $progress,
    'image' => $image,
    'project_type' => $projectType,
    'created_by' => $createdBy,
    'created_at' => $createdAt,
    'daily_work_summary' => $dailyWorkSummary,
    'daily_work_count' => count($dailyWork),
    'sent_at' => date('Y-m-d H:i:s'),
    'sent_by' => $sentBy,
    'from_department_id' => $fromDeptId,
    'to_department_id' => $toDeptId
];

$projectDataJson = json_encode($projectDataToStore);

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

// Check if record already exists
try {
    $checkStmt = $pdo->prepare("SELECT id FROM sent_projects WHERE original_project_id = ? AND to_department_id = ? AND is_deleted = 0");
    $checkStmt->execute([$projectId, $toDeptId]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $existing = false;
}

try {
    if ($existing) {
        // Update existing record
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
        // Insert new record
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
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error (sent_projects): ' . $e->getMessage()]);
    exit;
}

// ============================================================
// STEP 2: SAVE DAILY WORK SUMMARIES
// ============================================================

try {
    // Delete old sent daily work
    $delStmt = $pdo->prepare("DELETE FROM sent_dailywork WHERE original_project_id = ? AND to_department_id = ?");
    $delStmt->execute([$projectId, $toDeptId]);
} catch(PDOException $e) {
    // Table might not exist, ignore
}

if (!empty($dailyWork)) {
    try {
        foreach ($dailyWork as $dw) {
            $dwSummary = [
                'id' => isset($dw['id']) ? $dw['id'] : null,
                'date' => isset($dw['date']) ? $dw['date'] : date('Y-m-d H:i:s'),
                'work_description' => isset($dw['work_description']) ? substr($dw['work_description'], 0, 100) : '',
                'budget' => isset($dw['budget']) ? (float)$dw['budget'] : 0,
                'amount' => isset($dw['amount']) ? (float)$dw['amount'] : 0,
                'status' => isset($dw['status']) ? $dw['status'] : 'pending',
                'created_by' => isset($dw['created_by']) ? $dw['created_by'] : 'System',
                'created_at' => isset($dw['created_at']) ? $dw['created_at'] : date('Y-m-d H:i:s'),
                'is_summary_only' => true
            ];
            
            $dwJson = json_encode($dwSummary);
            
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
                is_summary_only
            ) VALUES (?, ?, ?, ?, ?, NOW(), 0, 0, 1, 1, NOW(), ?, ?, ?, ?, ?, 1)");
            
            $dwStmt->execute([
                isset($dw['id']) ? $dw['id'] : null,
                $dwJson,
                $fromDeptId,
                $toDeptId,
                $sentBy,
                $fromDeptName,
                $toDeptName,
                $projectName,
                isset($dw['date']) ? $dw['date'] : null,
                isset($dw['amount']) ? (float)$dw['amount'] : 0
            ]);
        }
    } catch(PDOException $e) {
        // Ignore daily work errors - project was sent successfully
        error_log("Daily work save error: " . $e->getMessage());
    }
}

// ============================================================
// STEP 3: UPDATE ORIGINAL PROJECT
// ============================================================

try {
    // Check if columns exist
    $checkStmt = $pdo->query("SHOW COLUMNS FROM projects LIKE 'sent_to_dept'");
    if ($checkStmt->rowCount() > 0) {
        $updateStmt = $pdo->prepare("UPDATE projects SET 
            sent_to_dept = ?,
            sent_from_dept = ?,
            is_viewed_by_department = 0,
            sent_count = sent_count + 1,
            is_sent = 1,
            last_sent_at = NOW()
            WHERE id = ?");
        $updateStmt->execute([$toDeptId, $fromDeptId, $projectId]);
    }
} catch(PDOException $e) {
    // Ignore - project might not have these columns
}

// ============================================================
// STEP 4: RETURN SUCCESS
// ============================================================

echo json_encode([
    'success' => true,
    'message' => 'Project sent successfully with daily work summary',
    'sent_id' => $sentId,
    'to_department' => $toDeptId,
    'to_department_name' => $toDeptName,
    'from_department' => $fromDeptId,
    'from_department_name' => $fromDeptName,
    'daily_work_summary' => $dailyWorkSummary,
    'daily_work_count' => count($dailyWork)
]);

?>