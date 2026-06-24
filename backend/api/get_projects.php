<?php
// backend/api/get_projects.php

// ============================================================
// ERROR REPORTING
// ============================================================
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// ============================================================
// HEADERS
// ============================================================
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
try {
    $pdo = new PDO("mysql:host=localhost;dbname=geotraverse_erp;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => []
    ]);
    exit;
}

// ============================================================
// GET PARAMETERS
// ============================================================
$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;
$all = isset($_GET['all']) ? intval($_GET['all']) : 0;

if ($department_id <= 0 && $all == 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Department ID required',
        'data' => []
    ]);
    exit;
}

// ============================================================
// FUNCTION TO SEND JSON
// ============================================================
function sendJson($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// ============================================================
// GET PROJECTS - ZISIZOFUTWA TU (is_deleted = 0)
// ============================================================
try {
    $projects = [];
    $seenIds = [];

    // ============================================================
    // 1. ORIGINAL PROJECTS - is_deleted = 0 TU
    // ============================================================
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
                'original' as source_type,
                0 as is_sent,
                0 as is_received,
                NULL as sent_from_name,
                NULL as sent_to_name,
                0 as sent_count,
                0 as _is_unviewed,
                NULL as daily_work_summary
              FROM projects p
              WHERE p.is_deleted = 0
                AND (p.deleted_by_department = 0 OR p.deleted_by_department IS NULL)
                AND (p.deleted_by_admin = 0 OR p.deleted_by_admin IS NULL)";
    
    if ($department_id > 0 && $all == 0) {
        $query .= " AND p.department_id = " . intval($department_id);
    }
    $query .= " ORDER BY p.id DESC";
    
    $stmt = $pdo->query($query);
    while ($row = $stmt->fetch()) {
        $row['is_sent'] = 0;
        $projects[] = $row;
        $seenIds[$row['id']] = true;
    }

    // ============================================================
    // 2. SENT PROJECTS - SENT TO THIS DEPARTMENT (is_deleted = 0 TU)
    // ============================================================
    if ($department_id > 0 && $all == 0) {
        $query = "SELECT 
                    sp.original_project_id as id,
                    sp.project_name as name,
                    NULL as client_name,
                    sp.amount,
                    NULL as location,
                    NULL as description,
                    'pending' as status,
                    0 as progress,
                    NULL as start_date,
                    NULL as end_date,
                    NULL as image,
                    NULL as image_path,
                    sp.project_type,
                    sp.from_department_id as department_id,
                    sp.sent_by as created_by,
                    sp.sent_at as created_at,
                    sp.last_sent_at as updated_at,
                    sp.is_deleted,
                    sp.deleted_at,
                    sp.from_department_id as sent_from_dept,
                    sp.to_department_id as sent_to_dept,
                    sp.is_viewed as is_viewed_by_department,
                    sp.sent_count,
                    sp.from_department_name as sent_from_name,
                    sp.to_department_name as sent_to_name,
                    sp.daily_work_summary,
                    'sent' as source_type,
                    1 as is_sent,
                    1 as is_received,
                    1 as is_sent_copy,
                    sp.original_project_id,
                    CASE WHEN (sp.is_viewed = 0 OR sp.is_viewed IS NULL) THEN 1 ELSE 0 END as _is_unviewed,
                    sp.project_data
                  FROM sent_projects sp
                  WHERE sp.to_department_id = ?
                    AND sp.is_deleted = 0
                  ORDER BY sp.sent_at DESC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$department_id]);
        while ($row = $stmt->fetch()) {
            $row['is_sent'] = 1;
            
            // ============================================================
            // GET IMAGE FROM project_data (kwa sent projects)
            // ============================================================
            if ($row['project_data']) {
                $projectData = json_decode($row['project_data'], true);
                if ($projectData && isset($projectData['image'])) {
                    $row['image'] = $projectData['image'];
                }
                if ($projectData && isset($projectData['image_path'])) {
                    $row['image_path'] = $projectData['image_path'];
                }
            }
            unset($row['project_data']);
            
            // If already exists as original, update with sent metadata
            if (isset($seenIds[$row['id']])) {
                foreach ($projects as &$existing) {
                    if ($existing['id'] == $row['id'] && $existing['source_type'] == 'original') {
                        $existing['sent_from_dept'] = $row['sent_from_dept'];
                        $existing['sent_to_dept'] = $row['sent_to_dept'];
                        $existing['sent_from_name'] = $row['sent_from_name'];
                        $existing['sent_to_name'] = $row['sent_to_name'];
                        $existing['is_viewed_by_department'] = $row['is_viewed_by_department'];
                        $existing['sent_count'] = $row['sent_count'];
                        $existing['is_sent'] = 1;
                        $existing['is_received'] = 1;
                        $existing['_is_unviewed'] = $row['_is_unviewed'];
                        $existing['is_sent_copy'] = 1;
                        $existing['original_project_id'] = $row['original_project_id'];
                        $existing['source_type'] = 'original_with_sent';
                        $existing['daily_work_summary'] = $row['daily_work_summary'];
                        // Update image if missing
                        if (empty($existing['image']) && !empty($row['image'])) {
                            $existing['image'] = $row['image'];
                        }
                        break;
                    }
                }
                continue;
            }
            
            // Check if already added as sent
            $exists = false;
            foreach ($projects as $existing) {
                if ($existing['id'] == $row['id'] && $existing['source_type'] == 'sent') {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $projects[] = $row;
            }
        }
    }

    // ============================================================
    // 3. SENT PROJECTS - SENT FROM THIS DEPARTMENT (is_deleted = 0 TU)
    // ============================================================
    if ($department_id > 0 && $all == 0) {
        $query = "SELECT 
                    sp.original_project_id as id,
                    sp.project_name as name,
                    NULL as client_name,
                    sp.amount,
                    NULL as location,
                    NULL as description,
                    'pending' as status,
                    0 as progress,
                    NULL as start_date,
                    NULL as end_date,
                    NULL as image,
                    NULL as image_path,
                    sp.project_type,
                    sp.from_department_id as department_id,
                    sp.sent_by as created_by,
                    sp.sent_at as created_at,
                    sp.last_sent_at as updated_at,
                    sp.is_deleted,
                    sp.deleted_at,
                    sp.from_department_id as sent_from_dept,
                    sp.to_department_id as sent_to_dept,
                    sp.is_viewed as is_viewed_by_department,
                    sp.sent_count,
                    sp.from_department_name as sent_from_name,
                    sp.to_department_name as sent_to_name,
                    sp.daily_work_summary,
                    'sent_from' as source_type,
                    1 as is_sent,
                    0 as is_received,
                    1 as is_sent_copy,
                    sp.original_project_id,
                    0 as _is_unviewed,
                    sp.project_data
                  FROM sent_projects sp
                  WHERE sp.from_department_id = ?
                    AND sp.is_deleted = 0
                  ORDER BY sp.sent_at DESC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$department_id]);
        while ($row = $stmt->fetch()) {
            $row['is_sent'] = 1;
            
            // Get image from project_data
            if ($row['project_data']) {
                $projectData = json_decode($row['project_data'], true);
                if ($projectData && isset($projectData['image'])) {
                    $row['image'] = $projectData['image'];
                }
                if ($projectData && isset($projectData['image_path'])) {
                    $row['image_path'] = $projectData['image_path'];
                }
            }
            unset($row['project_data']);
            
            if (isset($seenIds[$row['id']])) continue;
            
            $exists = false;
            foreach ($projects as $existing) {
                if ($existing['id'] == $row['id'] && ($existing['source_type'] == 'sent' || $existing['source_type'] == 'sent_from')) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $projects[] = $row;
            }
        }
    }

    // ============================================================
    // 4. GET ALL SENT PROJECTS (Ikiwa all=1) - is_deleted = 0 TU
    // ============================================================
    if ($all == 1) {
        $query = "SELECT 
                    sp.original_project_id as id,
                    sp.project_name as name,
                    NULL as client_name,
                    sp.amount,
                    NULL as location,
                    NULL as description,
                    'pending' as status,
                    0 as progress,
                    NULL as start_date,
                    NULL as end_date,
                    NULL as image,
                    NULL as image_path,
                    sp.project_type,
                    sp.from_department_id as department_id,
                    sp.sent_by as created_by,
                    sp.sent_at as created_at,
                    sp.last_sent_at as updated_at,
                    sp.is_deleted,
                    sp.deleted_at,
                    sp.from_department_id as sent_from_dept,
                    sp.to_department_id as sent_to_dept,
                    sp.is_viewed as is_viewed_by_department,
                    sp.sent_count,
                    sp.from_department_name as sent_from_name,
                    sp.to_department_name as sent_to_name,
                    sp.daily_work_summary,
                    'sent_all' as source_type,
                    1 as is_sent,
                    CASE WHEN sp.to_department_id = ? THEN 1 ELSE 0 END as is_received,
                    1 as is_sent_copy,
                    sp.original_project_id,
                    0 as _is_unviewed,
                    sp.project_data
                  FROM sent_projects sp
                  WHERE sp.is_deleted = 0
                  ORDER BY sp.sent_at DESC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$department_id]);
        while ($row = $stmt->fetch()) {
            $row['is_sent'] = 1;
            
            // Get image from project_data
            if ($row['project_data']) {
                $projectData = json_decode($row['project_data'], true);
                if ($projectData && isset($projectData['image'])) {
                    $row['image'] = $projectData['image'];
                }
                if ($projectData && isset($projectData['image_path'])) {
                    $row['image_path'] = $projectData['image_path'];
                }
            }
            unset($row['project_data']);
            
            if (isset($seenIds[$row['id']])) continue;
            
            $exists = false;
            foreach ($projects as $existing) {
                if ($existing['id'] == $row['id']) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $projects[] = $row;
            }
        }
    }

    // ============================================================
    // 5. GET DAILY WORK FOR PROJECTS
    // ============================================================
    foreach ($projects as &$project) {
        $projectId = $project['id'];
        $query = "SELECT * FROM dailywork WHERE project_id = ? AND is_deleted = 0 ORDER BY date DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$projectId]);
        $dailyWork = $stmt->fetchAll();
        
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

    sendJson([
        'success' => true,
        'data' => $projects,
        'count' => count($projects),
        'department_id' => $department_id,
        'all' => $all,
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