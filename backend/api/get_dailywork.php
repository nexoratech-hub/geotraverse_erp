<?php
// backend/api/get_dailywork.php - FIXED VERSION

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
$project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;
$project_name = isset($_GET['project_name']) ? trim($_GET['project_name']) : '';

if ($department_id <= 0) {
    sendJson(['success' => false, 'message' => 'Department ID required', 'data' => []]);
}

try {
    $dailyWork = [];
    $seenIds = [];

    // ============================================================
    // 1. ORIGINAL DAILY WORK - SENDER ANAONA ZAKE TU
    // ============================================================
    $query = "SELECT 
                d.*,
                'original' as source_type,
                0 as is_sent_copy,
                0 as is_received,
                NULL as sent_from_name,
                NULL as sent_to_name
              FROM dailywork d
              WHERE d.department_id = ?
                AND (d.is_deleted = 0 OR d.is_deleted IS NULL)";
    
    if ($project_id > 0) {
        $query .= " AND d.project_id = " . intval($project_id);
    }
    if (!empty($project_name)) {
        $query .= " AND d.project_name = '" . addslashes($project_name) . "'";
    }
    $query .= " ORDER BY d.date DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$department_id]);
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
    // First, let's check what columns exist in sent_dailywork
    try {
        $columns = $pdo->query("SHOW COLUMNS FROM sent_dailywork")->fetchAll(PDO::FETCH_COLUMN);
    } catch(PDOException $e) {
        $columns = [];
    }
    
    // Build SELECT dynamically based on existing columns
    $selectFields = "sd.id as sent_id,
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
                     'sent' as source_type,
                     1 as is_sent_copy,
                     1 as is_received,
                     0 as is_original,
                     sd.from_department_name as sent_from_name,
                     sd.to_department_name as sent_to_name,
                     CASE WHEN (sd.is_viewed = 0 OR sd.is_viewed IS NULL) THEN 1 ELSE 0 END as _is_unviewed";
    
    // Add optional columns if they exist
    if (in_array('dailywork_project_name', $columns)) {
        $selectFields .= ", sd.dailywork_project_name";
    }
    if (in_array('dailywork_date', $columns)) {
        $selectFields .= ", sd.dailywork_date";
    }
    if (in_array('dailywork_amount', $columns)) {
        $selectFields .= ", sd.dailywork_amount";
    }
    if (in_array('dailywork_budget', $columns)) {
        $selectFields .= ", sd.dailywork_budget";
    }
    if (in_array('dailywork_status', $columns)) {
        $selectFields .= ", sd.dailywork_status";
    }
    
    $query = "SELECT {$selectFields} 
              FROM sent_dailywork sd
              WHERE sd.to_department_id = ?
                AND (sd.is_deleted = 0 OR sd.is_deleted IS NULL)";
    
    if ($project_id > 0) {
        $query .= " AND sd.original_dailywork_id IN (SELECT id FROM dailywork WHERE project_id = " . intval($project_id) . ")";
    }
    if (!empty($project_name)) {
        $query .= " AND sd.dailywork_project_name = '" . addslashes($project_name) . "'";
    }
    $query .= " ORDER BY sd.sent_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$department_id]);
    while ($row = $stmt->fetch()) {
        $row['is_sent'] = 1;
        
        // Extract data from dailywork_data
        if ($row['dailywork_data']) {
            $dwData = json_decode($row['dailywork_data'], true);
            if ($dwData && is_array($dwData)) {
                foreach ($dwData as $key => $value) {
                    if (!isset($row[$key]) || $row[$key] === null) {
                        $row[$key] = $value;
                    }
                }
            }
        }
        unset($row['dailywork_data']);
        
        // Skip if already seen as original
        if (isset($seenIds[$row['original_dailywork_id']])) {
            continue;
        }
        
        $dailyWork[] = $row;
        $seenIds['sent_' . $row['sent_id']] = true;
    }

    // ============================================================
    // 3. SENT DAILY WORK - SENDER ANAONA ZILE ALIZOTUMA
    // ============================================================
    $selectFields = "sd.id as sent_id,
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
                     'sent_from' as source_type,
                     1 as is_sent_copy,
                     0 as is_received,
                     0 as is_original,
                     sd.from_department_name as sent_from_name,
                     sd.to_department_name as sent_to_name,
                     0 as _is_unviewed";
    
    if (in_array('dailywork_project_name', $columns)) {
        $selectFields .= ", sd.dailywork_project_name";
    }
    if (in_array('dailywork_date', $columns)) {
        $selectFields .= ", sd.dailywork_date";
    }
    if (in_array('dailywork_amount', $columns)) {
        $selectFields .= ", sd.dailywork_amount";
    }
    if (in_array('dailywork_budget', $columns)) {
        $selectFields .= ", sd.dailywork_budget";
    }
    if (in_array('dailywork_status', $columns)) {
        $selectFields .= ", sd.dailywork_status";
    }
    
    $query = "SELECT {$selectFields} 
              FROM sent_dailywork sd
              WHERE sd.from_department_id = ?
                AND (sd.is_deleted = 0 OR sd.is_deleted IS NULL)";
    
    if ($project_id > 0) {
        $query .= " AND sd.original_dailywork_id IN (SELECT id FROM dailywork WHERE project_id = " . intval($project_id) . ")";
    }
    if (!empty($project_name)) {
        $query .= " AND sd.dailywork_project_name = '" . addslashes($project_name) . "'";
    }
    $query .= " ORDER BY sd.sent_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$department_id]);
    while ($row = $stmt->fetch()) {
        $row['is_sent'] = 1;
        
        if ($row['dailywork_data']) {
            $dwData = json_decode($row['dailywork_data'], true);
            if ($dwData && is_array($dwData)) {
                foreach ($dwData as $key => $value) {
                    if (!isset($row[$key]) || $row[$key] === null) {
                        $row[$key] = $value;
                    }
                }
            }
        }
        unset($row['dailywork_data']);
        
        // Skip if already seen
        if (isset($seenIds[$row['original_dailywork_id']]) || isset($seenIds['sent_' . $row['sent_id']])) {
            continue;
        }
        
        $dailyWork[] = $row;
        $seenIds['sent_from_' . $row['sent_id']] = true;
    }

    // ============================================================
    // 4. REMOVE DUPLICATES
    // ============================================================
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

    // ============================================================
    // 5. SORT BY DATE (newest first)
    // ============================================================
    usort($dailyWork, function($a, $b) {
        $dateA = strtotime($a['date'] ?? $a['dailywork_date'] ?? $a['sent_at'] ?? '1970-01-01');
        $dateB = strtotime($b['date'] ?? $b['dailywork_date'] ?? $b['sent_at'] ?? '1970-01-01');
        return $dateB - $dateA;
    });

    // ============================================================
    // 6. SEND RESPONSE
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
        'project_id' => $project_id,
        'project_name' => $project_name,
        'debug' => [
            'original_count' => $originalCount,
            'sent_count' => $sentCount,
            'note' => 'Sender sees original + sent daily work. Receiver sees sent only.'
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