<?php
// backend/api/get_projects.php
// ============================================================
// FIXED: Receiver haoni daily work
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
    $seenSentIds = [];

    // ============================================================
    // PART 1: ORIGINAL PROJECTS - SENDER ANAONA ZAKE TU
    // ============================================================
    $query = "SELECT 
                p.*,
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
                0 as daily_work_count
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
    // Receiver haoni daily work - only project details
    // ============================================================
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
                1 as is_sent_copy,
                0 as is_original,
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
        
        // NO DAILY WORK for receiver
        $row['daily_work'] = [];
        $row['daily_work_count'] = 0;
        $row['daily_work_summary'] = [
            'total_records' => 0,
            'total_budget' => 0,
            'total_expenses' => 0,
            'remaining_budget' => 0
        ];
        
        $seenSentIds[$row['sent_id']] = true;
        $projects[] = $row;
    }

    // ============================================================
    // PART 3: SENT PROJECTS - SENDER ANAONA ZILE ALIZOTUMA (HISTORY)
    // ============================================================
    // SKIP IKIWA ORIGINAL PROJECT IPO - SENDER HAONI COPY
    // ============================================================
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
                1 as is_sent_copy,
                0 as is_original,
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
        // SKIP if original exists (sender sees original instead)
        if (isset($originalIds[$row['original_project_id']])) {
            continue;
        }
        
        // SKIP if already added as sent
        if (isset($seenSentIds[$row['sent_id']])) {
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
            }
        }
        unset($row['project_data']);
        
        $row['id'] = $row['original_project_id'];
        $row['_display_id'] = $row['sent_id'];
        
        // NO DAILY WORK
        $row['daily_work'] = [];
        $row['daily_work_count'] = 0;
        $row['daily_work_summary'] = [
            'total_records' => 0,
            'total_budget' => 0,
            'total_expenses' => 0,
            'remaining_budget' => 0
        ];
        
        $projects[] = $row;
    }

    // ============================================================
    // PART 4: GET DAILY WORK - ONLY FOR ORIGINAL PROJECTS (SENDER)
    // ============================================================
    foreach ($projects as &$project) {
        if ($project['source_type'] === 'original') {
            // Only original projects get daily work
            $projectId = $project['id'];
            $dwQuery = "SELECT * FROM dailywork 
                        WHERE project_id = ? 
                          AND (is_deleted = 0 OR is_deleted IS NULL)
                        ORDER BY date DESC";
            $dwStmt = $pdo->prepare($dwQuery);
            $dwStmt->execute([$projectId]);
            $dailyWork = $dwStmt->fetchAll();
            
            $totalBudget = 0;
            $totalExpenses = 0;
            foreach ($dailyWork as $dw) {
                $totalBudget += floatval($dw['budget'] ?? 0);
                $totalExpenses += floatval($dw['amount'] ?? 0);
            }
            
            $project['daily_work'] = $dailyWork;
            $project['daily_work_summary'] = [
                'total_records' => count($dailyWork),
                'total_budget' => $totalBudget,
                'total_expenses' => $totalExpenses,
                'remaining_budget' => $totalBudget - $totalExpenses
            ];
            $project['daily_work_count'] = count($dailyWork);
        }
        // Sent projects already have no daily work
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

    sendJson([
        'success' => true,
        'data' => $projects,
        'count' => count($projects),
        'department_id' => $department_id,
        'debug' => [
            'original_count' => $originalCount,
            'sent_count' => $sentCount,
            'note' => 'Sender sees original with daily work. Receiver sees sent project WITHOUT daily work.'
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