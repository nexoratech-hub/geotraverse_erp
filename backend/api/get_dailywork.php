<?php
// backend/api/get_dailywork.php
// ============================================================
// FIXED: Inarudisha daily work kwa SUPER ADMIN na Departments
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
$project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;
$project_name = isset($_GET['project_name']) ? trim($_GET['project_name']) : '';
$all = isset($_GET['all']) ? intval($_GET['all']) : 0;

// ============================================================
// KWA SUPER ADMIN (department_id = 1) - ONESHA ZOTE
// ============================================================
// Ikiwa department_id ni 1 (Super Admin) au all=1, onesha daily work zote
$isSuperAdmin = ($department_id == 1 || $all == 1);

if ($department_id <= 0 && !$isSuperAdmin) {
    sendJson([
        'success' => false,
        'message' => 'Department ID required',
        'data' => []
    ]);
}

try {
    $dailyWork = [];
    $seenIds = [];

    // ============================================================
    // 1. ORIGINAL DAILY WORK - ORIGINAL RECORDS
    // ============================================================
    $query = "SELECT 
                d.*,
                'original' as source_type,
                0 as is_sent_copy,
                0 as is_received,
                NULL as sent_from_name,
                NULL as sent_to_name,
                d.department_id as owner_department_id
              FROM dailywork d
              WHERE (d.is_deleted = 0 OR d.is_deleted IS NULL)";
    
    // Kama siyo Super Admin, filter kwa department
    if (!$isSuperAdmin) {
        $query .= " AND d.department_id = " . intval($department_id);
    }
    
    if ($project_id > 0) {
        $query .= " AND d.project_id = " . intval($project_id);
    }
    if (!empty($project_name)) {
        $query .= " AND d.project_name = '" . addslashes($project_name) . "'";
    }
    $query .= " ORDER BY d.date DESC, d.id DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    while ($row = $stmt->fetch()) {
        $row['is_sent'] = 0;
        $row['is_original'] = 1;
        $row['source_type'] = 'original';
        $dailyWork[] = $row;
        $seenIds[$row['id']] = true;
    }

    // ============================================================
    // 2. SENT DAILY WORK - RECEIVER ANAONA ZILIZOTUMWA KWAKE
    // ============================================================
    $query = "SELECT 
                sd.id as sent_id,
                sd.original_dailywork_id,
                sd.dailywork_data,
                sd.from_department_id,
                sd.to_department_id,
                sd.sent_by,
                sd.sent_at,
                sd.is_viewed,
                sd.sent_count,
                sd.from_department_name,
                sd.to_department_name,
                sd.dailywork_project_name,
                sd.dailywork_date,
                sd.dailywork_amount,
                sd.dailywork_budget,
                sd.dailywork_status,
                'sent' as source_type,
                1 as is_sent_copy,
                1 as is_received,
                0 as is_original,
                sd.from_department_name as sent_from_name,
                sd.to_department_name as sent_to_name,
                CASE WHEN (sd.is_viewed = 0 OR sd.is_viewed IS NULL) THEN 1 ELSE 0 END as _is_unviewed,
                sd.from_department_id as owner_department_id
              FROM sent_dailywork sd
              WHERE (sd.is_deleted = 0 OR sd.is_deleted IS NULL)";
    
    // Kama siyo Super Admin, filter kwa department (receiver)
    if (!$isSuperAdmin) {
        $query .= " AND sd.to_department_id = " . intval($department_id);
    }
    // Kama ni Super Admin, ona zote (no filter)
    
    if ($project_id > 0) {
        $query .= " AND sd.original_dailywork_id IN (SELECT id FROM dailywork WHERE project_id = " . intval($project_id) . ")";
    }
    if (!empty($project_name)) {
        $query .= " AND sd.dailywork_project_name = '" . addslashes($project_name) . "'";
    }
    $query .= " ORDER BY sd.sent_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    while ($row = $stmt->fetch()) {
        $row['is_sent'] = 1;
        
        // Extract data from dailywork_data
        if ($row['dailywork_data']) {
            $dwData = json_decode($row['dailywork_data'], true);
            if ($dwData && is_array($dwData)) {
                foreach ($dwData as $key => $value) {
                    if (!isset($row[$key]) || $row[$key] === null || $row[$key] === '') {
                        $row[$key] = $value;
                    }
                }
            }
        }
        unset($row['dailywork_data']);
        
        // Skip if already seen as original (only for non-superadmin)
        if (!$isSuperAdmin && isset($seenIds[$row['original_dailywork_id']])) {
            continue;
        }
        
        $dailyWork[] = $row;
        $seenIds['sent_' . $row['sent_id']] = true;
    }

    // ============================================================
    // 3. SENT DAILY WORK - SENDER ANAONA ZILE ALIZOTUMA
    // ============================================================
    $query = "SELECT 
                sd.id as sent_id,
                sd.original_dailywork_id,
                sd.dailywork_data,
                sd.from_department_id,
                sd.to_department_id,
                sd.sent_by,
                sd.sent_at,
                sd.is_viewed,
                sd.sent_count,
                sd.from_department_name,
                sd.to_department_name,
                sd.dailywork_project_name,
                sd.dailywork_date,
                sd.dailywork_amount,
                sd.dailywork_budget,
                sd.dailywork_status,
                'sent_from' as source_type,
                1 as is_sent_copy,
                0 as is_received,
                0 as is_original,
                sd.from_department_name as sent_from_name,
                sd.to_department_name as sent_to_name,
                0 as _is_unviewed,
                sd.from_department_id as owner_department_id
              FROM sent_dailywork sd
              WHERE (sd.is_deleted = 0 OR sd.is_deleted IS NULL)";
    
    // Kama siyo Super Admin, filter kwa department (sender)
    if (!$isSuperAdmin) {
        $query .= " AND sd.from_department_id = " . intval($department_id);
    }
    // Kama ni Super Admin, ona zote (no filter)
    
    if ($project_id > 0) {
        $query .= " AND sd.original_dailywork_id IN (SELECT id FROM dailywork WHERE project_id = " . intval($project_id) . ")";
    }
    if (!empty($project_name)) {
        $query .= " AND sd.dailywork_project_name = '" . addslashes($project_name) . "'";
    }
    $query .= " ORDER BY sd.sent_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    while ($row = $stmt->fetch()) {
        $row['is_sent'] = 1;
        
        if ($row['dailywork_data']) {
            $dwData = json_decode($row['dailywork_data'], true);
            if ($dwData && is_array($dwData)) {
                foreach ($dwData as $key => $value) {
                    if (!isset($row[$key]) || $row[$key] === null || $row[$key] === '') {
                        $row[$key] = $value;
                    }
                }
            }
        }
        unset($row['dailywork_data']);
        
        // Skip if already seen (only for non-superadmin)
        if (!$isSuperAdmin) {
            if (isset($seenIds[$row['original_dailywork_id']]) || isset($seenIds['sent_' . $row['sent_id']])) {
                continue;
            }
        }
        
        $dailyWork[] = $row;
        $seenIds['sent_from_' . $row['sent_id']] = true;
    }

    // ============================================================
    // 4. REMOVE DUPLICATES (KWA SUPER ADMIN)
    // ============================================================
    if ($isSuperAdmin) {
        $uniqueWork = [];
        $uniqueIds = [];
        foreach ($dailyWork as $dw) {
            $id = $dw['original_dailywork_id'] ?? $dw['id'] ?? $dw['sent_id'] ?? 0;
            $key = $dw['source_type'] . '_' . $id;
            if (!in_array($key, $uniqueIds)) {
                $uniqueIds[] = $key;
                $uniqueWork[] = $dw;
            }
        }
        $dailyWork = $uniqueWork;
    }

    // ============================================================
    // 5. SORT BY DATE (newest first)
    // ============================================================
    usort($dailyWork, function($a, $b) {
        $dateA = strtotime($a['date'] ?? $a['dailywork_date'] ?? $a['sent_at'] ?? '1970-01-01');
        $dateB = strtotime($b['date'] ?? $b['dailywork_date'] ?? $b['sent_at'] ?? '1970-01-01');
        return $dateB - $dateA;
    });

    // ============================================================
    // 6. GET DEPARTMENT NAMES FOR DISPLAY
    // ============================================================
    $deptNames = [];
    try {
        $deptStmt = $pdo->query("SELECT id, name FROM departments");
        while ($dept = $deptStmt->fetch()) {
            $deptNames[$dept['id']] = $dept['name'];
        }
    } catch(PDOException $e) {
        // Ignore
    }

    // Add department names to each record
    foreach ($dailyWork as &$dw) {
        $deptId = $dw['department_id'] ?? $dw['owner_department_id'] ?? 0;
        $dw['department_name'] = $deptNames[$deptId] ?? 'Unknown Department';
        
        // Add sent from/to names if missing
        if (empty($dw['sent_from_name']) && isset($dw['from_department_id'])) {
            $dw['sent_from_name'] = $deptNames[$dw['from_department_id']] ?? 'Unknown';
        }
        if (empty($dw['sent_to_name']) && isset($dw['to_department_id'])) {
            $dw['sent_to_name'] = $deptNames[$dw['to_department_id']] ?? 'Unknown';
        }
    }

    // ============================================================
    // 7. SEND RESPONSE
    // ============================================================
    $originalCount = count(array_filter($dailyWork, function($w) { 
        return $w['source_type'] === 'original'; 
    }));
    $sentCount = count(array_filter($dailyWork, function($w) { 
        return $w['source_type'] === 'sent' || $w['source_type'] === 'sent_from'; 
    }));

    sendJson([
        'success' => true,
        'data' => $dailyWork,
        'count' => count($dailyWork),
        'department_id' => $department_id,
        'is_super_admin' => $isSuperAdmin,
        'project_id' => $project_id,
        'project_name' => $project_name,
        'debug' => [
            'original_count' => $originalCount,
            'sent_count' => $sentCount,
            'note' => $isSuperAdmin ? 'Super Admin sees ALL daily work records' : 'Department sees own records only'
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