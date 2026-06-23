<?php
// get_projects.php - Fetch projects including sent ones with daily work summaries

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
    // Get regular projects
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE 
        (department_id = ? OR sent_to_dept = ?) 
        AND (deleted_by_department != 1 OR deleted_by_department IS NULL)
        AND (deleted_by_admin != 1 OR deleted_by_admin IS NULL)
        ORDER BY id DESC");
    $stmt->execute([$departmentId, $departmentId]);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get sent projects (from sent_projects table)
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
    
    // Merge sent projects with daily work summaries
    foreach ($sentProjects as &$sent) {
        // Decode project data
        $projectData = json_decode($sent['project_data'], true);
        if ($projectData) {
            // Add daily work summary if exists
            if (isset($projectData['daily_work_summary'])) {
                $sent['daily_work_summary'] = $projectData['daily_work_summary'];
                $sent['daily_work_count'] = isset($projectData['daily_work_count']) ? $projectData['daily_work_count'] : 0;
            }
            
            // Merge other fields
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
    
    // Merge all projects
    $allProjects = array_merge($projects, $sentProjects);
    
    echo json_encode([
        'success' => true,
        'data' => $allProjects,
        'total' => count($allProjects),
        'sent_count' => count($sentProjects)
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Query failed: ' . $e->getMessage()]);
}
?>