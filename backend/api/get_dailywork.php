<?php
// backend/api/get_dailywork.php
// ============================================================
// FIXED: Super Admin with all=1 sees ALL daily work from ALL departments
// But individual departments only see their own
// ============================================================

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
$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage(),
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
// GET PARAMETERS
// ============================================================
$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;
$project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;
$project_name = isset($_GET['project_name']) ? trim($_GET['project_name']) : '';
$work_type = isset($_GET['work_type']) ? trim($_GET['work_type']) : '';
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
// GET DAILY WORK
// ============================================================
try {
    $dailyWork = [];
    $seenIds = [];

    // ============================================================
    // 1. ORIGINAL DAILY WORK (is_original = 1)
    // ============================================================
    $query = "SELECT 
                d.*,
                'original' as source_type,
                0 as is_received,
                NULL as sent_from_name,
                NULL as sent_to_name
              FROM dailywork d
              WHERE d.is_original = 1
                AND d.is_deleted = 0";
    
    // If not all=1, filter by department
    if ($all != 1) {
        $query .= " AND d.department_id = ?";
    }
    
    if ($project_id > 0) {
        $query .= " AND d.project_id = " . intval($project_id);
    }
    if (!empty($project_name)) {
        $query .= " AND d.project_name = '" . addslashes($project_name) . "'";
    }
    if (!empty($work_type)) {
        $query .= " AND d.work_type = '" . addslashes($work_type) . "'";
    }
    $query .= " ORDER BY d.date DESC";
    
    $stmt = $pdo->prepare($query);
    if ($all != 1) {
        $stmt->execute([$department_id]);
    } else {
        $stmt->execute();
    }
    
    while ($row = $stmt->fetch()) {
        // Skip if already seen
        if (isset($seenIds[$row['id']])) {
            continue;
        }
        $row['is_sent'] = ($row['sent_count'] > 0) ? 1 : 0;
        $dailyWork[] = $row;
        $seenIds[$row['id']] = true;
    }

    // ============================================================
    // 2. SENT DAILY WORK (is_sent_copy = 1)
    // ============================================================
    $query = "SELECT 
                d.*,
                'sent' as source_type,
                1 as is_received,
                d.sent_from_dept as sent_from_name,
                NULL as sent_to_name
              FROM dailywork d
              WHERE d.is_sent_copy = 1
                AND d.is_deleted = 0";
    
    // If not all=1, filter by department
    if ($all != 1) {
        $query .= " AND d.department_id = ?";
    }
    
    if ($project_id > 0) {
        $query .= " AND d.project_id = " . intval($project_id);
    }
    if (!empty($project_name)) {
        $query .= " AND d.project_name = '" . addslashes($project_name) . "'";
    }
    if (!empty($work_type)) {
        $query .= " AND d.work_type = '" . addslashes($work_type) . "'";
    }
    $query .= " ORDER BY d.date DESC";
    
    $stmt = $pdo->prepare($query);
    if ($all != 1) {
        $stmt->execute([$department_id]);
    } else {
        $stmt->execute();
    }
    
    while ($row = $stmt->fetch()) {
        // Skip duplicates
        if (isset($seenIds[$row['id']])) {
            continue;
        }
        
        // Get sender department name
        if ($row['sent_from_dept']) {
            try {
                $deptStmt = $pdo->prepare("SELECT name FROM departments WHERE id = ?");
                $deptStmt->execute([$row['sent_from_dept']]);
                $deptName = $deptStmt->fetchColumn();
                if ($deptName) {
                    $row['sent_from_name'] = $deptName;
                }
            } catch(PDOException $e) {
                // Ignore
            }
        }
        
        $row['is_sent'] = 1;
        $dailyWork[] = $row;
        $seenIds[$row['id']] = true;
    }

    // ============================================================
    // 3. SENT DAILY WORK TRACKING - For additional metadata
    // ============================================================
    $sentQuery = "SELECT 
                    sd.*,
                    d1.name as from_dept_name,
                    d2.name as to_dept_name
                  FROM sent_dailywork sd
                  LEFT JOIN departments d1 ON d1.id = sd.from_department_id
                  LEFT JOIN departments d2 ON d2.id = sd.to_department_id
                  WHERE sd.is_deleted = 0
                  ORDER BY sd.sent_at DESC";
    
    $sentStmt = $pdo->prepare($sentQuery);
    $sentStmt->execute();
    $sentDailyWork = $sentStmt->fetchAll();
    
    // Merge sent dailywork info with existing records
    foreach ($dailyWork as &$dw) {
        foreach ($sentDailyWork as $sent) {
            if ($sent['original_dailywork_id'] == $dw['id'] || $sent['copy_dailywork_id'] == $dw['id']) {
                $dw['sent_from_name'] = $sent['from_dept_name'] ?? $dw['sent_from_name'] ?? null;
                $dw['sent_to_name'] = $sent['to_dept_name'] ?? $dw['sent_to_name'] ?? null;
                $dw['sent_count'] = $sent['sent_count'] ?? $dw['sent_count'] ?? 0;
                $dw['sent_at'] = $sent['sent_at'] ?? $dw['sent_at'] ?? null;
                $dw['is_viewed'] = $sent['is_viewed'] ?? 0;
                break;
            }
        }
    }

    // ============================================================
    // 4. SORT BY DATE (newest first)
    // ============================================================
    usort($dailyWork, function($a, $b) {
        $dateA = strtotime($a['date'] ?? '1970-01-01');
        $dateB = strtotime($b['date'] ?? '1970-01-01');
        return $dateB - $dateA;
    });

    // ============================================================
    // 5. ADD DEBUG INFO
    // ============================================================
    $originalCount = count(array_filter($dailyWork, function($w) { 
        return $w['source_type'] === 'original'; 
    }));
    $sentCount = count(array_filter($dailyWork, function($w) { 
        return $w['source_type'] === 'sent'; 
    }));

    sendJson([
        'success' => true,
        'data' => $dailyWork,
        'count' => count($dailyWork),
        'department_id' => $department_id,
        'project_id' => $project_id,
        'project_name' => $project_name,
        'work_type' => $work_type,
        'all' => $all,
        'debug' => [
            'original_count' => $originalCount,
            'sent_count' => $sentCount,
            'note' => $all == 1 ? 'All departments included' : 'Filtered by department: ' . $department_id
        ],
        'message' => 'Daily work retrieved successfully'
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