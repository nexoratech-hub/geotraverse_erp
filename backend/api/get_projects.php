<?php
// backend/api/get_projects.php

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=geotraverse_erp;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage(), 'data' => []]);
    exit;
}

function sendJson($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;
$all = isset($_GET['all']) ? intval($_GET['all']) : 0;

if ($department_id <= 0 && $all == 0) {
    sendJson(['success' => false, 'message' => 'Department ID required', 'data' => []]);
}

try {
    $projects = [];
    $seenIds = [];

    // ============================================================
    // 1. ORIGINAL PROJECTS - SENDER ANAONA ZAKE TU
    // ============================================================
    // Sender anaona original projects zake (zisizofutwa)
    $query = "SELECT 
                p.*,
                'original' as source_type,
                0 as is_sent_copy,
                NULL as sent_from_name,
                NULL as sent_to_name,
                NULL as daily_work_summary,
                0 as _is_unviewed
              FROM projects p
              WHERE p.is_deleted = 0
                AND p.department_id = ?
              ORDER BY p.id DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$department_id]);
    while ($row = $stmt->fetch()) {
        $row['is_sent'] = 0;
        $row['is_received'] = 0;
        $row['is_original'] = 1;
        $row['source_type'] = 'original';
        $projects[] = $row;
        $seenIds[$row['id']] = true;
    }

    // ============================================================
    // 2. SENT PROJECTS - RECEIVER ANAONA ZILIZOTUMWA KWAKE
    // ============================================================
    // Receiver anaona sent projects zilizotumwa kwake (is_deleted = 0)
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
                'sent' as source_type,
                1 as is_sent_copy,
                1 as is_received,
                0 as is_original,
                sp.from_department_name as sent_from_name,
                sp.to_department_name as sent_to_name,
                CASE WHEN (sp.is_viewed = 0 OR sp.is_viewed IS NULL) THEN 1 ELSE 0 END as _is_unviewed
              FROM sent_projects sp
              WHERE sp.to_department_id = ?
                AND sp.is_deleted = 0
              ORDER BY sp.sent_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$department_id]);
    while ($row = $stmt->fetch()) {
        $row['is_sent'] = 1;
        $row['id'] = $row['original_project_id']; // Use original ID for reference
        
        // Extract data from project_data
        if ($row['project_data']) {
            $projectData = json_decode($row['project_data'], true);
            if ($projectData) {
                foreach ($projectData as $key => $value) {
                    if (!isset($row[$key]) || $row[$key] === null) {
                        $row[$key] = $value;
                    }
                }
            }
        }
        unset($row['project_data']);
        
        // Check if original project exists and is not deleted
        $origCheck = $pdo->prepare("SELECT id FROM projects WHERE id = ? AND is_deleted = 0");
        $origCheck->execute([$row['original_project_id']]);
        $origExists = $origCheck->fetch();
        
        if ($origExists) {
            // Receiver does NOT see original project - only sent copy
            // So we add the sent project as a separate entry
            $row['is_original_exists'] = true;
        } else {
            $row['is_original_exists'] = false;
        }
        
        // Skip if this original project already added (for receiver)
        if (isset($seenIds[$row['original_project_id']])) {
            // If original exists, we still want to show sent version separately
            // So we don't skip - we add as sent project
        }
        
        $projects[] = $row;
        $seenIds['sent_' . $row['sent_id']] = true;
    }

    // ============================================================
    // 3. SENT PROJECTS - SENDER ANAONA ZILE ALIZOTUMA
    // ============================================================
    // Sender anaona sent projects zake (zile alizotuma)
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
                'sent_from' as source_type,
                1 as is_sent_copy,
                0 as is_received,
                0 as is_original,
                sp.from_department_name as sent_from_name,
                sp.to_department_name as sent_to_name,
                0 as _is_unviewed
              FROM sent_projects sp
              WHERE sp.from_department_id = ?
                AND sp.is_deleted = 0
              ORDER BY sp.sent_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$department_id]);
    while ($row = $stmt->fetch()) {
        $row['is_sent'] = 1;
        $row['id'] = $row['original_project_id'];
        
        if ($row['project_data']) {
            $projectData = json_decode($row['project_data'], true);
            if ($projectData) {
                foreach ($projectData as $key => $value) {
                    if (!isset($row[$key]) || $row[$key] === null) {
                        $row[$key] = $value;
                    }
                }
            }
        }
        unset($row['project_data']);
        
        // Skip if already added as original
        if (isset($seenIds[$row['original_project_id']])) {
            continue;
        }
        
        $projects[] = $row;
        $seenIds['sent_from_' . $row['sent_id']] = true;
    }

    // ============================================================
    // 4. GET DAILY WORK FOR PROJECTS
    // ============================================================
    foreach ($projects as &$project) {
        $projectId = $project['id'];
        
        // For sent projects, get daily work from sent_dailywork
        if ($project['source_type'] === 'sent' || $project['source_type'] === 'sent_from') {
            // Get daily work from sent_dailywork
            $dwQuery = "SELECT 
                          sd.*,
                          'sent' as source_type
                        FROM sent_dailywork sd
                        WHERE sd.original_dailywork_id IN (
                            SELECT id FROM dailywork WHERE project_id = ? 
                            UNION 
                            SELECT original_dailywork_id FROM sent_dailywork WHERE dailywork_project_name = ? AND to_department_id = ?
                        )
                        AND sd.is_deleted = 0
                        ORDER BY sd.sent_at DESC";
            
            $dwStmt = $pdo->prepare($dwQuery);
            $dwStmt->execute([$projectId, $project['project_name'] ?? '', $department_id]);
            $dailyWork = $dwStmt->fetchAll();
            
            // If no sent dailywork, try original dailywork
            if (empty($dailyWork)) {
                $dwQuery = "SELECT * FROM dailywork WHERE project_id = ? AND is_deleted = 0 ORDER BY date DESC";
                $dwStmt = $pdo->prepare($dwQuery);
                $dwStmt->execute([$projectId]);
                $dailyWork = $dwStmt->fetchAll();
            }
        } else {
            // Original project - get original dailywork
            $dwQuery = "SELECT * FROM dailywork WHERE project_id = ? AND is_deleted = 0 ORDER BY date DESC";
            $dwStmt = $pdo->prepare($dwQuery);
            $dwStmt->execute([$projectId]);
            $dailyWork = $dwStmt->fetchAll();
        }
        
        $totalBudget = 0;
        $totalExpenses = 0;
        
        foreach ($dailyWork as $dw) {
            $totalBudget += floatval($dw['budget'] ?? 0);
            $totalExpenses += floatval($dw['amount'] ?? 0);
        }
        
        $project['daily_work'] = $dailyWork;
        $project['daily_work_summary'] = [
            'total_budget' => $totalBudget,
            'total_expenses' => $totalExpenses,
            'remaining_budget' => $totalBudget - $totalExpenses,
            'records_count' => count($dailyWork)
        ];
        $project['daily_work_count'] = count($dailyWork);
    }

    // ============================================================
    // 5. SEND RESPONSE
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
            'note' => 'Sender sees original projects + sent projects. Receiver sees sent projects only.'
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