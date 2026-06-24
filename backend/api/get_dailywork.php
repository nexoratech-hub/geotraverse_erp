<?php
// backend/api/get_dailywork.php

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
$project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;
$project_name = isset($_GET['project_name']) ? trim($_GET['project_name']) : '';

if ($department_id <= 0) {
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
// GET DAILY WORK - KILA DEPARTMENT INAONA YAAKE TU
// ============================================================
try {
    $dailyWork = [];
    $seenIds = [];

    // ============================================================
    // 1. ORIGINAL DAILY WORK (kwa department iliyounda - SENDER)
    // ============================================================
    // Sender anaona daily work zake zote (original)
    $query = "SELECT 
                d.*,
                'original' as source_type,
                0 as is_sent,
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
        $dailyWork[] = $row;
        $seenIds[$row['id']] = true;
    }

    // ============================================================
    // 2. SENT DAILY WORK (kwa department iliyopokea - RECEIVER)
    // ============================================================
    // Receiver anaona SENT daily work tu (zilizotumwa kwake)
    // HAZIONYESHI daily work mpya ambazo hazijatumwa
    $query = "SELECT 
                sd.id,
                sd.original_dailywork_id,
                sd.dailywork_data,
                sd.from_department_id,
                sd.to_department_id,
                sd.sent_by,
                sd.sent_at,
                sd.is_viewed,
                sd.sent_count,
                sd.is_sent,
                sd.last_sent_at,
                sd.from_department_name,
                sd.to_department_name,
                sd.dailywork_project_name,
                sd.dailywork_date,
                sd.dailywork_amount,
                sd.dailywork_budget,
                sd.dailywork_status,
                'sent' as source_type,
                1 as is_sent,
                sd.from_department_name as sent_from_name,
                sd.to_department_name as sent_to_name
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
        // Extract dailywork_data
        if ($row['dailywork_data']) {
            $dwData = json_decode($row['dailywork_data'], true);
            if ($dwData) {
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
        if (isset($seenIds[$row['id']])) {
            continue;
        }
        $seenIds[$row['id']] = true;
        $dailyWork[] = $row;
    }

    // ============================================================
    // 3. SENT DAILY WORK (kwa department iliyotuma - SENDER)
    // ============================================================
    // Sender pia anaona sent daily work zake (zile alizotuma)
    $query = "SELECT 
                sd.id,
                sd.original_dailywork_id,
                sd.dailywork_data,
                sd.from_department_id,
                sd.to_department_id,
                sd.sent_by,
                sd.sent_at,
                sd.is_viewed,
                sd.sent_count,
                sd.is_sent,
                sd.last_sent_at,
                sd.from_department_name,
                sd.to_department_name,
                sd.dailywork_project_name,
                sd.dailywork_date,
                sd.dailywork_amount,
                sd.dailywork_budget,
                sd.dailywork_status,
                'sent' as source_type,
                1 as is_sent,
                sd.from_department_name as sent_from_name,
                sd.to_department_name as sent_to_name
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
        if ($row['dailywork_data']) {
            $dwData = json_decode($row['dailywork_data'], true);
            if ($dwData) {
                foreach ($dwData as $key => $value) {
                    if (!isset($row[$key]) || $row[$key] === null) {
                        $row[$key] = $value;
                    }
                }
            }
        }
        unset($row['dailywork_data']);
        
        if (isset($seenIds[$row['original_dailywork_id']]) || isset($seenIds[$row['id']])) {
            continue;
        }
        $seenIds[$row['id']] = true;
        $dailyWork[] = $row;
    }

    // ============================================================
    // 4. FILTER - ONDOA DUPLICATES
    // ============================================================
    $uniqueWork = [];
    $uniqueIds = [];
    foreach ($dailyWork as $dw) {
        $id = $dw['original_dailywork_id'] ?? $dw['id'];
        if (!in_array($id, $uniqueIds)) {
            $uniqueIds[] = $id;
            $uniqueWork[] = $dw;
        }
    }
    $dailyWork = $uniqueWork;

    // ============================================================
    // 5. SORT BY DATE (newest first)
    // ============================================================
    usort($dailyWork, function($a, $b) {
        $dateA = strtotime($a['date'] ?? $a['dailywork_date'] ?? '1970-01-01');
        $dateB = strtotime($b['date'] ?? $b['dailywork_date'] ?? '1970-01-01');
        return $dateB - $dateA;
    });

    // ============================================================
    // 6. ADD DEBUG INFO
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
        'debug' => [
            'original_count' => $originalCount,
            'sent_count' => $sentCount,
            'note' => 'Receiver sees ONLY sent daily work (not new ones until sent again)'
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