<?php
// get_projects.php - Get all projects (EXCLUDING DELETED)

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB Connection Error: ' . $e->getMessage()]);
    exit;
}

$departmentId = isset($_GET['department_id']) ? (int)$_GET['department_id'] : 0;
$debug = isset($_GET['debug']) && $_GET['debug'] == '1' ? true : false;

try {
    $projects = [];
    
    // ============================================================
    // 1. GET PROJECTS FROM projects TABLE - EXCLUDE DELETED
    // ============================================================
    $sql = "
        SELECT 
            p.*,
            d.name as department_name,
            sd.name as sent_from_name,
            td.name as sent_to_name
        FROM projects p
        LEFT JOIN departments d ON p.department_id = d.id
        LEFT JOIN departments sd ON p.sent_from_dept = sd.id
        LEFT JOIN departments td ON p.sent_to_dept = td.id
        WHERE 1=1
    ";
    
    $params = [];
    
    // Filter by department
    if ($departmentId > 0) {
        $sql .= " AND (p.department_id = :dept_id OR p.sent_to_dept = :dept_id2)";
        $params['dept_id'] = $departmentId;
        $params['dept_id2'] = $departmentId;
    }
    
    // ============================================================
    // !!! EXCLUDE DELETED PROJECTS !!!
    // ============================================================
    $sql .= " AND (p.is_deleted = 0 OR p.is_deleted IS NULL)";
    $sql .= " AND (p.deleted_by_department = 0 OR p.deleted_by_department IS NULL)";
    $sql .= " AND (p.deleted_by_admin = 0 OR p.deleted_by_admin IS NULL)";
    $sql .= " AND p.deleted_at IS NULL";
    
    $sql .= " ORDER BY p.id DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // ============================================================
    // 2. GET SENT PROJECTS FROM sent_projects TABLE - EXCLUDE DELETED
    // ============================================================
    $sentSql = "
        SELECT 
            sp.*,
            fd.name as from_department_name,
            td.name as to_department_name
        FROM sent_projects sp
        LEFT JOIN departments fd ON sp.from_department_id = fd.id
        LEFT JOIN departments td ON sp.to_department_id = td.id
        WHERE 1=1
    ";
    
    $sentParams = [];
    
    // Filter by department
    if ($departmentId > 0) {
        $sentSql .= " AND (sp.from_department_id = :dept_id3 OR sp.to_department_id = :dept_id4)";
        $sentParams['dept_id3'] = $departmentId;
        $sentParams['dept_id4'] = $departmentId;
    }
    
    // ============================================================
    // !!! EXCLUDE DELETED SENT PROJECTS !!!
    // ============================================================
    $sentSql .= " AND (sp.is_deleted = 0 OR sp.is_deleted IS NULL)";
    $sentSql .= " AND sp.deleted_at IS NULL";
    
    $sentSql .= " ORDER BY sp.id DESC";
    
    $sentStmt = $pdo->prepare($sentSql);
    $sentStmt->execute($sentParams);
    $sentProjects = $sentStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // ============================================================
    // 3. DEBUG
    // ============================================================
    if ($debug) {
        echo json_encode([
            'debug' => true,
            'department_id' => $departmentId,
            'projects_count' => count($projects),
            'sent_projects_count' => count($sentProjects),
            'projects' => $projects,
            'sent_projects' => $sentProjects
        ]);
        exit;
    }
    
    // ============================================================
    // 4. COMBINE RESULTS
    // ============================================================
    $combinedProjects = [];
    $seenIds = [];
    
    foreach ($projects as $project) {
        $project['is_sent_project'] = 0;
        $project['source'] = 'projects';
        
        if (!empty($project['daily_work_records'])) {
            $project['daily_work_records'] = json_decode($project['daily_work_records'], true);
        }
        if (!empty($project['daily_work_summary'])) {
            $project['daily_work_summary'] = json_decode($project['daily_work_summary'], true);
        }
        if (!isset($project['is_viewed_by_department'])) {
            $project['is_viewed_by_department'] = 0;
        }
        
        $key = $project['id'] . '_projects';
        if (!in_array($key, $seenIds)) {
            $seenIds[] = $key;
            $combinedProjects[] = $project;
        }
    }
    
    foreach ($sentProjects as $sentProject) {
        $mappedProject = [
            'id' => $sentProject['id'],
            'name' => $sentProject['project_name'] ?? 'Unknown Project',
            'client_name' => $sentProject['client_name'] ?? '',
            'amount' => $sentProject['amount'] ?? 0,
            'location' => $sentProject['location'] ?? '',
            'description' => $sentProject['description'] ?? '',
            'status' => $sentProject['status'] ?? 'pending',
            'progress' => $sentProject['progress'] ?? 0,
            'image' => $sentProject['image'] ?? '',
            'start_date' => $sentProject['start_date'] ?? '',
            'end_date' => $sentProject['end_date'] ?? '',
            'department_id' => $sentProject['from_department_id'] ?? 0,
            'department_name' => $sentProject['from_department_name'] ?? '',
            'created_by' => $sentProject['sent_by'] ?? 'System',
            'created_at' => $sentProject['sent_at'] ?? date('Y-m-d H:i:s'),
            'sent_from_dept' => $sentProject['from_department_id'] ?? null,
            'sent_from_name' => $sentProject['from_department_name'] ?? '',
            'sent_to_dept' => $sentProject['to_department_id'] ?? null,
            'sent_to_name' => $sentProject['to_department_name'] ?? '',
            'is_viewed_by_department' => $sentProject['is_viewed'] ?? 0,
            'is_sent_project' => 1,
            'source' => 'sent_projects',
            'is_deleted' => $sentProject['is_deleted'] ?? 0,
            'deleted_at' => $sentProject['deleted_at'] ?? null,
            'daily_work_records' => [],
            'daily_work_summary' => null
        ];
        
        if (!empty($sentProject['daily_work_summary'])) {
            if (is_string($sentProject['daily_work_summary'])) {
                $mappedProject['daily_work_summary'] = json_decode($sentProject['daily_work_summary'], true);
            } else {
                $mappedProject['daily_work_summary'] = $sentProject['daily_work_summary'];
            }
        }
        
        if (!empty($sentProject['project_data'])) {
            if (is_string($sentProject['project_data'])) {
                $projectData = json_decode($sentProject['project_data'], true);
                if ($projectData && !empty($projectData['daily_work_records'])) {
                    $mappedProject['daily_work_records'] = $projectData['daily_work_records'];
                }
                if ($projectData && !empty($projectData['image'])) {
                    $mappedProject['image'] = $projectData['image'];
                }
            }
        }
        
        $key = $sentProject['id'] . '_sent_projects';
        if (!in_array($key, $seenIds)) {
            $seenIds[] = $key;
            $combinedProjects[] = $mappedProject;
        }
    }
    
    // ============================================================
    // 5. FINAL FILTER - REMOVE ANY DELETED PROJECTS
    // ============================================================
    $finalProjects = [];
    foreach ($combinedProjects as $project) {
        // Skip if is_deleted = 1
        if (isset($project['is_deleted']) && $project['is_deleted'] == 1) {
            continue;
        }
        // Skip if deleted_by_department = 1
        if (isset($project['deleted_by_department']) && $project['deleted_by_department'] == 1) {
            continue;
        }
        // Skip if deleted_by_admin = 1
        if (isset($project['deleted_by_admin']) && $project['deleted_by_admin'] == 1) {
            continue;
        }
        // Skip if deleted_at is not null
        if (isset($project['deleted_at']) && $project['deleted_at'] !== null) {
            continue;
        }
        $finalProjects[] = $project;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $finalProjects,
        'total' => count($finalProjects)
    ]);
    
} catch(PDOException $e) {
    error_log('Get projects error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>