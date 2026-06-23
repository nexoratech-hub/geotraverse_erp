<?php
// get_projects.php - Fetch projects including sent ones with daily work records

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

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

$departmentId = isset($_GET['department_id']) ? (int)$_GET['department_id'] : 0;

if (!$departmentId) {
    echo json_encode(['success' => false, 'message' => 'Department ID required']);
    exit;
}

try {
    // ============================================================
    // 1. GET REGULAR PROJECTS (from projects table)
    // ============================================================
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE 
        (department_id = ? OR sent_to_dept = ?) 
        AND (deleted_by_department != 1 OR deleted_by_department IS NULL)
        AND (deleted_by_admin != 1 OR deleted_by_admin IS NULL)
        ORDER BY id DESC");
    $stmt->execute([$departmentId, $departmentId]);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ============================================================
    // 2. GET DAILY WORK FOR REGULAR PROJECTS
    // ============================================================
    foreach ($projects as &$project) {
        $projectId = $project['id'];
        $dailyWork = [];
        
        try {
            $dwStmt = $pdo->prepare("SELECT * FROM dailywork WHERE project_id = ? AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY date DESC");
            $dwStmt->execute([$projectId]);
            $dailyWork = $dwStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            $dailyWork = [];
        }
        
        $summary = calculateDailyWorkSummary($dailyWork);
        $project['daily_work'] = $dailyWork;
        $project['daily_work_summary'] = $summary;
        $project['daily_work_count'] = count($dailyWork);
    }

    // ============================================================
    // 3. GET SENT PROJECTS (from sent_projects table)
    // ============================================================
    $sentStmt = $pdo->prepare("SELECT 
        sp.*,
        sp.id as sent_id,
        sp.project_name as name,
        sp.amount,
        sp.project_type,
        sp.from_department_name as sent_from_name,
        sp.to_department_name as sent_to_name,
        sp.sent_at,
        sp.is_viewed,
        sp.sent_count
        FROM sent_projects sp
        WHERE sp.to_department_id = ? 
        AND sp.is_deleted = 0
        ORDER BY sp.sent_at DESC");
    $sentStmt->execute([$departmentId]);
    $sentProjects = $sentStmt->fetchAll(PDO::FETCH_ASSOC);

    // ============================================================
    // 4. GET DAILY WORK FOR SENT PROJECTS - FIXED!
    // ============================================================
    foreach ($sentProjects as &$sent) {
        // Decode project data
        $projectData = json_decode($sent['project_data'], true);
        
        // ============================================================
        // GET DAILY WORK FROM sent_dailywork TABLE - MAIN FIX
        // ============================================================
        $dailyWork = [];
        
        // Method 1: By project_name
        try {
            $dwStmt = $pdo->prepare("SELECT * FROM sent_dailywork WHERE 
                to_department_id = ? AND 
                dailywork_project_name = ? AND 
                is_deleted = 0 
                ORDER BY sent_at DESC");
            $dwStmt->execute([$departmentId, $sent['project_name']]);
            $dailyWork = $dwStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decode dailywork_data if found
            if (!empty($dailyWork)) {
                $decodedDailyWork = [];
                foreach ($dailyWork as $dw) {
                    if (isset($dw['dailywork_data'])) {
                        $decoded = json_decode($dw['dailywork_data'], true);
                        if ($decoded) {
                            // Add sent metadata
                            $decoded['sent_from_department'] = $dw['from_department_id'];
                            $decoded['sent_to_department'] = $dw['to_department_id'];
                            $decoded['sent_by'] = $dw['sent_by'];
                            $decoded['sent_at'] = $dw['sent_at'];
                            $decoded['is_sent_record'] = true;
                            $decodedDailyWork[] = $decoded;
                        }
                    }
                }
                $dailyWork = $decodedDailyWork;
            }
        } catch(PDOException $e) {
            // Ignore
        }
        
        // Method 2: If no records, try using original_project_id
        if (empty($dailyWork) && isset($sent['original_project_id']) && $sent['original_project_id'] > 0) {
            try {
                $dwStmt = $pdo->prepare("SELECT * FROM dailywork WHERE project_id = ? AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY date DESC");
                $dwStmt->execute([$sent['original_project_id']]);
                $dailyWork = $dwStmt->fetchAll(PDO::FETCH_ASSOC);
            } catch(PDOException $e) {
                // Ignore
            }
        }
        
        // ============================================================
        // CALCULATE SUMMARY
        // ============================================================
        $summary = calculateDailyWorkSummary($dailyWork);
        
        // ADD TO PROJECT
        $sent['daily_work'] = $dailyWork;
        $sent['daily_work_summary'] = $summary;
        $sent['daily_work_count'] = count($dailyWork);
        
        // Merge project data fields
        if ($projectData) {
            $sent['client_name'] = isset($projectData['client_name']) ? $projectData['client_name'] : '';
            $sent['location'] = isset($projectData['location']) ? $projectData['location'] : '';
            $sent['description'] = isset($projectData['description']) ? $projectData['description'] : '';
            $sent['status'] = isset($projectData['status']) ? $projectData['status'] : 'pending';
            $sent['progress'] = isset($projectData['progress']) ? $projectData['progress'] : 0;
            $sent['start_date'] = isset($projectData['start_date']) ? $projectData['start_date'] : '';
            $sent['end_date'] = isset($projectData['end_date']) ? $projectData['end_date'] : '';
            $sent['image'] = isset($projectData['image']) ? $projectData['image'] : '';
            $sent['created_by'] = isset($projectData['created_by']) ? $projectData['created_by'] : 'System';
            $sent['created_at'] = isset($projectData['created_at']) ? $projectData['created_at'] : $sent['sent_at'];
            $sent['is_sent_project'] = true;
            $sent['sent_from_dept'] = $sent['from_department_id'];
            $sent['sent_to_dept'] = $sent['to_department_id'];
            $sent['sent_from_name'] = $sent['from_department_name'];
            $sent['sent_to_name'] = $sent['to_department_name'];
        }
    }

    // ============================================================
    // 5. MERGE ALL PROJECTS
    // ============================================================
    $allProjects = array_merge($projects, $sentProjects);

    // ============================================================
    // 6. RETURN RESPONSE
    // ============================================================
    echo json_encode([
        'success' => true,
        'data' => $allProjects,
        'total' => count($allProjects),
        'sent_count' => count($sentProjects),
        'sent_daily_work_total' => array_sum(array_column($sentProjects, 'daily_work_count'))
    ]);

} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Query failed: ' . $e->getMessage()]);
}

// ============================================================
// HELPER FUNCTION: Calculate Daily Work Summary
// ============================================================
function calculateDailyWorkSummary($dailyWorkRecords) {
    $summary = [
        'total_records' => count($dailyWorkRecords),
        'total_budget' => 0,
        'total_expenses' => 0,
        'total_income' => 0,
        'total_amount' => 0,
        'total_profit' => 0,
        'total_produced' => 0,
        'total_sold' => 0,
        'remaining_budget' => 0,
        'completed' => 0,
        'partial' => 0,
        'pending' => 0
    ];
    
    foreach ($dailyWorkRecords as $dw) {
        $summary['total_budget'] += (float)($dw['budget'] ?? 0);
        $summary['total_expenses'] += (float)($dw['expenses'] ?? $dw['amount'] ?? 0);
        $summary['total_income'] += (float)($dw['income'] ?? $dw['total_amount'] ?? 0);
        $summary['total_amount'] += (float)($dw['amount'] ?? 0);
        $summary['total_produced'] += (int)($dw['quantity_produced'] ?? 0);
        $summary['total_sold'] += (int)($dw['quantity_sold'] ?? 0);
        
        $status = $dw['status'] ?? 'pending';
        if ($status === 'completed' || $status === 'Completed') {
            $summary['completed']++;
        } else if ($status === 'partial' || $status === 'Partial') {
            $summary['partial']++;
        } else {
            $summary['pending']++;
        }
    }
    
    $summary['total_profit'] = $summary['total_income'] - $summary['total_expenses'];
    $summary['remaining_budget'] = $summary['total_budget'] - $summary['total_expenses'];
    
    return $summary;
}
?>