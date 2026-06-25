<?php
// backend/api/get_projects.php
// ============================================================
// COMPLETE VERSION - Kamili na Safi
// ============================================================
// Mahitaji:
// 1. Sender anaona ORIGINAL projects zake tu
// 2. Receiver anaona SENT projects zilizotumwa kwake tu
// 3. Sender HAONI sent copies (zina skip)
// 4. Inaonyesha forwarding chain
// 5. Inaonyesha daily work summary
// 6. Inaonyesha unviewed status
// 7. Soft delete - is_deleted = 0 ONLY
// ============================================================

error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

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
} catch (PDOException $e) {
    if (ob_get_length()) ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => []
    ]);
    exit;
}

function sendJson($data) {
    if (ob_get_length()) ob_clean();
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// ============================================================
// GET PARAMETERS
// ============================================================
$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;

if ($department_id <= 0) {
    sendJson([
        'success' => false,
        'message' => 'Department ID required',
        'data' => []
    ]);
}

try {
    $projects = [];
    $originalIds = [];
    $sentIds = [];

    // ============================================================
    // PART 1: ORIGINAL PROJECTS - SENDER ANAONA ZAKE TU
    // ============================================================
    // Department iliyounda project (department_id = owner)
    // Hawa ndio SENDER wa original project
    // is_deleted = 0 ONLY
    
    $query = "SELECT 
                p.id,
                p.name,
                p.client_name,
                p.amount,
                p.location,
                p.description,
                p.status,
                p.progress,
                p.start_date,
                p.end_date,
                p.image,
                p.image_path,
                p.project_type,
                p.department_id,
                p.created_by,
                p.created_at,
                p.updated_at,
                p.is_deleted,
                p.deleted_at,
                p.sent_from_dept,
                p.sent_to_dept,
                p.is_viewed_by_department,
                p.is_original,
                p.is_sent_copy,
                p.original_project_id,
                p.sent_count,
                p.last_sent_at,
                'original' as source_type,
                0 as is_sent,
                0 as is_received,
                1 as is_original,
                0 as is_sent_copy,
                0 as _is_unviewed,
                NULL as sent_id,
                NULL as sent_from_name,
                NULL as sent_to_name,
                NULL as original_sender_id,
                NULL as original_sender_name,
                0 as forward_count,
                NULL as forward_chain,
                NULL as daily_work_summary,
                0 as daily_work_count,
                NULL as project_data
              FROM projects p
              WHERE p.department_id = ?
                AND (p.is_deleted = 0 OR p.is_deleted IS NULL)
                AND (p.deleted_by_department = 0 OR p.deleted_by_department IS NULL)
                AND (p.deleted_by_admin = 0 OR p.deleted_by_admin IS NULL)
              ORDER BY p.id DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$department_id]);
    while ($row = $stmt->fetch()) {
        $projects[] = $row;
        $originalIds[$row['id']] = true;
    }

    // ============================================================
    // PART 2: SENT PROJECTS - RECEIVER ANAONA ZILIZOTUMWA KWAKE
    // ============================================================
    // Department ambayo imepokea project (to_department_id = receiver)
    // Hawa ndio RECEIVER wa sent project
    // is_deleted = 0 ONLY
    
    $query = "SELECT 
                sp.id as sent_id,
                sp.original_project_id,
                sp.project_data,
                sp.from_department_id,
                sp.to_department_id,
                sp.sent_by,
                sp.sent_at,
                sp.is_viewed,
                sp.sent_count,
                sp.from_department_name,
                sp.to_department_name,
                sp.project_name,
                sp.project_type,
                sp.amount,
                sp.daily_work_count,
                sp.daily_work_summary,
                sp.is_deleted,
                sp.deleted_at,
                sp.original_sender_id,
                sp.original_sender_name,
                sp.forward_count,
                sp.last_forwarded_from,
                sp.is_forward,
                'sent' as source_type,
                1 as is_sent,
                1 as is_received,
                0 as is_original,
                1 as is_sent_copy,
                sp.from_department_name as sent_from_name,
                sp.to_department_name as sent_to_name,
                CASE 
                    WHEN (sp.is_viewed = 0 OR sp.is_viewed IS NULL) THEN 1 
                    ELSE 0 
                END as _is_unviewed
              FROM sent_projects sp
              WHERE sp.to_department_id = ?
                AND (sp.is_deleted = 0 OR sp.is_deleted IS NULL)
              ORDER BY sp.sent_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$department_id]);
    while ($row = $stmt->fetch()) {
        // Extract data from project_data
        if ($row['project_data']) {
            $projectData = json_decode($row['project_data'], true);
            if ($projectData && is_array($projectData)) {
                // Merge project data into row
                foreach ($projectData as $key => $value) {
                    if (!isset($row[$key]) || $row[$key] === null || $row[$key] === '') {
                        $row[$key] = $value;
                    }
                }
                
                // Extract forward chain if exists
                if (isset($projectData['forward_chain'])) {
                    $row['forward_chain'] = $projectData['forward_chain'];
                }
                if (isset($projectData['original_sender_name'])) {
                    $row['original_sender_name'] = $projectData['original_sender_name'];
                }
                if (isset($projectData['daily_work_records'])) {
                    $row['daily_work_records'] = $projectData['daily_work_records'];
                }
            }
        }
        unset($row['project_data']);
        
        // Set id to original_project_id for compatibility
        $row['id'] = $row['original_project_id'];
        $row['_display_id'] = $row['sent_id'];
        
        // Store sent_id for reference
        $sentIds[$row['sent_id']] = true;
        
        // Add to projects array (receiver sees sent copy)
        $projects[] = $row;
    }

    // ============================================================
    // PART 3: SENT PROJECTS - SENDER ANAONA ZILE ALIZOTUMA (HISTORY)
    // ============================================================
    // Department ambayo imetuma project (from_department_id = sender)
    // Hawa ndio SENDER wa sent project (wanaona historia ya kutuma)
    // LAKINI - SKIP IKIWA ORIGINAL PROJECT IPO
    // is_deleted = 0 ONLY
    
    $query = "SELECT 
                sp.id as sent_id,
                sp.original_project_id,
                sp.project_data,
                sp.from_department_id,
                sp.to_department_id,
                sp.sent_by,
                sp.sent_at,
                sp.is_viewed,
                sp.sent_count,
                sp.from_department_name,
                sp.to_department_name,
                sp.project_name,
                sp.project_type,
                sp.amount,
                sp.daily_work_count,
                sp.daily_work_summary,
                sp.is_deleted,
                sp.deleted_at,
                sp.original_sender_id,
                sp.original_sender_name,
                sp.forward_count,
                sp.last_forwarded_from,
                sp.is_forward,
                'sent_from' as source_type,
                1 as is_sent,
                0 as is_received,
                0 as is_original,
                1 as is_sent_copy,
                sp.from_department_name as sent_from_name,
                sp.to_department_name as sent_to_name,
                0 as _is_unviewed
              FROM sent_projects sp
              WHERE sp.from_department_id = ?
                AND (sp.is_deleted = 0 OR sp.is_deleted IS NULL)
              ORDER BY sp.sent_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$department_id]);
    while ($row = $stmt->fetch()) {
        // ============================================================
        // SKIP if original project already exists (sender sees original)
        // Hii inazuia sender kuona sent copy - anabaki na original tu
        // ============================================================
        if (isset($originalIds[$row['original_project_id']])) {
            // Sender anaona original, haitaki kuona duplicate
            // Tuna skip hii sent record
            continue;
        }
        
        // SKIP if already added as sent (receiver view)
        if (isset($sentIds[$row['sent_id']])) {
            continue;
        }
        
        // Extract data from project_data
        if ($row['project_data']) {
            $projectData = json_decode($row['project_data'], true);
            if ($projectData && is_array($projectData)) {
                foreach ($projectData as $key => $value) {
                    if (!isset($row[$key]) || $row[$key] === null || $row[$key] === '') {
                        $row[$key] = $value;
                    }
                }
                if (isset($projectData['forward_chain'])) {
                    $row['forward_chain'] = $projectData['forward_chain'];
                }
                if (isset($projectData['original_sender_name'])) {
                    $row['original_sender_name'] = $projectData['original_sender_name'];
                }
            }
        }
        unset($row['project_data']);
        
        $row['id'] = $row['original_project_id'];
        $row['_display_id'] = $row['sent_id'];
        
        // Add to projects array (sender sees sent history)
        $projects[] = $row;
    }

    // ============================================================
    // PART 4: GET DAILY WORK FOR EACH PROJECT
    // ============================================================
    foreach ($projects as &$project) {
        $projectId = $project['id'];
        $projectName = $project['name'] ?? $project['project_name'] ?? '';
        $sourceType = $project['source_type'];
        
        $dailyWork = [];
        
        if ($sourceType === 'original') {
            // Original project - get from dailywork table
            $dwQuery = "SELECT 
                          d.*,
                          'original' as source_type,
                          0 as is_sent_copy
                        FROM dailywork d
                        WHERE d.project_id = ? 
                          AND (d.is_deleted = 0 OR d.is_deleted IS NULL)
                        ORDER BY d.date DESC";
            $dwStmt = $pdo->prepare($dwQuery);
            $dwStmt->execute([$projectId]);
            $dailyWork = $dwStmt->fetchAll();
            
        } else if ($sourceType === 'sent' || $sourceType === 'sent_from') {
            // Sent project - first try to get from sent_dailywork
            $dwQuery = "SELECT 
                          sd.*,
                          'sent' as source_type,
                          1 as is_sent_copy
                        FROM sent_dailywork sd
                        WHERE sd.dailywork_project_name = ?
                          AND sd.to_department_id = ?
                          AND (sd.is_deleted = 0 OR sd.is_deleted IS NULL)
                        ORDER BY sd.sent_at DESC";
            $dwStmt = $pdo->prepare($dwQuery);
            $dwStmt->execute([$projectName, $department_id]);
            $dailyWork = $dwStmt->fetchAll();
            
            // If no sent dailywork found, try original dailywork
            if (empty($dailyWork)) {
                $dwQuery = "SELECT 
                              d.*,
                              'original' as source_type,
                              0 as is_sent_copy
                            FROM dailywork d
                            WHERE d.project_id = ? 
                              AND (d.is_deleted = 0 OR d.is_deleted IS NULL)
                            ORDER BY d.date DESC";
                $dwStmt = $pdo->prepare($dwQuery);
                $dwStmt->execute([$projectId]);
                $dailyWork = $dwStmt->fetchAll();
            }
            
            // If still no dailywork, try from project_data
            if (empty($dailyWork) && isset($project['daily_work_records'])) {
                $dailyWork = $project['daily_work_records'];
            }
        }
        
        // Calculate totals
        $totalBudget = 0;
        $totalExpenses = 0;
        $totalIncome = 0;
        $totalAmount = 0;
        $completedCount = 0;
        $partialCount = 0;
        $pendingCount = 0;
        
        foreach ($dailyWork as $dw) {
            $budget = floatval($dw['budget'] ?? $dw['dailywork_budget'] ?? 0);
            $amount = floatval($dw['amount'] ?? $dw['dailywork_amount'] ?? 0);
            $income = floatval($dw['income'] ?? 0);
            $status = $dw['status'] ?? $dw['dailywork_status'] ?? 'pending';
            
            $totalBudget += $budget;
            $totalExpenses += $amount;
            $totalIncome += $income;
            $totalAmount += $amount;
            
            if ($status === 'completed' || $status === 'Completed') {
                $completedCount++;
            } else if ($status === 'partial' || $status === 'Partial') {
                $partialCount++;
            } else {
                $pendingCount++;
            }
        }
        
        $remainingBudget = $totalBudget - $totalExpenses;
        
        $project['daily_work'] = $dailyWork;
        $project['daily_work_summary'] = [
            'total_records' => count($dailyWork),
            'total_budget' => $totalBudget,
            'total_expenses' => $totalExpenses,
            'total_income' => $totalIncome,
            'total_amount' => $totalAmount,
            'remaining_budget' => $remainingBudget,
            'completed' => $completedCount,
            'partial' => $partialCount,
            'pending' => $pendingCount
        ];
        $project['daily_work_count'] = count($dailyWork);
    }

    // ============================================================
    // PART 5: SEND RESPONSE
    // ============================================================
    $originalCount = count(array_filter($projects, function($p) { 
        return $p['source_type'] === 'original'; 
    }));
    $sentCount = count(array_filter($projects, function($p) { 
        return $p['source_type'] === 'sent' || $p['source_type'] === 'sent_from'; 
    }));
    
    // Count unviewed
    $unviewedCount = count(array_filter($projects, function($p) { 
        return isset($p['_is_unviewed']) && $p['_is_unviewed'] == 1; 
    }));

    sendJson([
        'success' => true,
        'data' => $projects,
        'count' => count($projects),
        'department_id' => $department_id,
        'unviewed_count' => $unviewedCount,
        'debug' => [
            'original_count' => $originalCount,
            'sent_count' => $sentCount,
            'note' => 'Sender sees original only. Receiver sees sent copies only. Deleted projects are hidden.',
            'forwarding_supported' => true
        ],
        'message' => 'Projects retrieved successfully'
    ]);

} catch (PDOException $e) {
    sendJson([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => []
    ]);
} catch (Exception $e) {
    sendJson([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>