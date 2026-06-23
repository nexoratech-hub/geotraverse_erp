<?php
/**
 * get_dailywork.php - API inayofanya kazi na ALL DASHBOARDS
 * INAONYESHA DAILY WORK ZOTE ZILIZOTUMWA NA ZA MWENYEWE
 * HAIKURUDISHI DUPLICATES
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection
$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database connection failed: ' . $e->getMessage(), 
        'data' => []
    ]);
    exit();
}

// Get parameters
$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;
$work_type = isset($_GET['work_type']) ? trim($_GET['work_type']) : '';
$project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;
$project_name = isset($_GET['project_name']) ? trim($_GET['project_name']) : '';
$campaign_id = isset($_GET['campaign_id']) ? intval($_GET['campaign_id']) : 0;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

try {
    // ============================================================
    // BUILD QUERY WITH DISTINCT - AVOID DUPLICATES
    // ============================================================
    $sql = "SELECT DISTINCT 
                d.*,
                COALESCE(d.work_type, 'general') as work_type,
                COALESCE(d.status, 'pending') as status,
                COALESCE(d.payment_status, 'pending') as payment_status,
                COALESCE(d.budget, 0) as budget,
                COALESCE(d.amount, 0) as amount,
                COALESCE(d.expenses, 0) as expenses,
                COALESCE(d.income, 0) as income,
                COALESCE(d.profit, 0) as profit,
                COALESCE(d.quantity_produced, 0) as quantity_produced,
                COALESCE(d.quantity_sold, 0) as quantity_sold,
                COALESCE(d.price_per_unit, 0) as price_per_unit,
                COALESCE(d.total_amount, 0) as total_amount,
                COALESCE(d.partial_amount, 0) as partial_amount,
                COALESCE(d.amount_paid, 0) as amount_paid,
                d.is_sent,
                d.sent_from_dept,
                d.sent_to_dept,
                d.is_viewed_by_department,
                0 as is_sent_record
            FROM dailywork d
            WHERE (d.is_deleted = 0 OR d.is_deleted IS NULL)";
    
    $conditions = [];
    $params = [];
    
    // ============================================================
    // FILTER: department_id (own AND sent to)
    // ============================================================
    if ($department_id > 0) {
        $conditions[] = "(d.department_id = :department_id OR d.sent_to_dept = :department_id)";
        $params[':department_id'] = $department_id;
    }
    
    // ============================================================
    // FILTER: project_id
    // ============================================================
    if ($project_id > 0) {
        $conditions[] = "d.project_id = :project_id";
        $params[':project_id'] = $project_id;
    }
    
    // ============================================================
    // FILTER: project_name
    // ============================================================
    if (!empty($project_name)) {
        $conditions[] = "d.project_name = :project_name";
        $params[':project_name'] = $project_name;
    }
    
    // ============================================================
    // FILTER: work_type
    // ============================================================
    if (!empty($work_type) && $work_type !== 'undefined' && $work_type !== 'null') {
        if ($work_type === 'bricks' || $work_type === 'timber') {
            $conditions[] = "d.work_type = :work_type";
            $params[':work_type'] = $work_type;
        } elseif ($work_type === 'bricks_timber') {
            $conditions[] = "d.work_type IN ('bricks', 'timber')";
        }
    }
    
    // ============================================================
    // FILTER: campaign_id
    // ============================================================
    if ($campaign_id > 0) {
        $conditions[] = "d.campaign_id = :campaign_id";
        $params[':campaign_id'] = $campaign_id;
    }
    
    // Append conditions
    if (count($conditions) > 0) {
        $sql .= " AND " . implode(" AND ", $conditions);
    }
    
    // ============================================================
    // ORDER BY date DESC - most recent first
    // ============================================================
    $sql .= " ORDER BY d.date DESC, d.id DESC";
    
    // ============================================================
    // LIMIT and OFFSET
    // ============================================================
    if ($limit > 0) {
        $sql .= " LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
    }
    
    $stmt = $pdo->prepare($sql);
    
    foreach ($params as $key => $value) {
        if ($key === ':limit' || $key === ':offset') {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($key, $value);
        }
    }
    
    $stmt->execute();
    $results = $stmt->fetchAll();
    
    // ============================================================
    // GET SENT DAILY WORK FROM sent_dailywork - NO DUPLICATES
    // ============================================================
    $sentResults = [];
    $sentIds = [];
    
    if ($department_id > 0) {
        $sentSql = "SELECT DISTINCT 
                        sd.*,
                        sd.id as sent_dailywork_id,
                        sd.dailywork_data,
                        sd.from_department_id,
                        sd.to_department_id,
                        sd.sent_by,
                        sd.sent_at,
                        sd.dailywork_project_name,
                        sd.dailywork_date,
                        sd.dailywork_amount,
                        sd.dailywork_budget,
                        sd.dailywork_status,
                        1 as is_sent_record
                    FROM sent_dailywork sd
                    WHERE sd.to_department_id = :to_dept_id
                    AND (sd.is_deleted = 0 OR sd.is_deleted IS NULL)";
        
        $sentParams = [':to_dept_id' => $department_id];
        
        // Filter by project_name
        if (!empty($project_name)) {
            $sentSql .= " AND sd.dailywork_project_name = :project_name";
            $sentParams[':project_name'] = $project_name;
        }
        
        // Filter by project_id (via sent_projects)
        if ($project_id > 0) {
            $projStmt = $pdo->prepare("SELECT project_name FROM sent_projects WHERE original_project_id = ? AND to_department_id = ? AND is_deleted = 0 LIMIT 1");
            $projStmt->execute([$project_id, $department_id]);
            $sentProject = $projStmt->fetch();
            if ($sentProject && isset($sentProject['project_name'])) {
                $sentSql .= " AND sd.dailywork_project_name = :sent_project_name";
                $sentParams[':sent_project_name'] = $sentProject['project_name'];
            }
        }
        
        $sentSql .= " ORDER BY sd.sent_at DESC";
        
        if ($limit > 0) {
            $sentSql .= " LIMIT :limit OFFSET :offset";
            $sentParams[':limit'] = $limit;
            $sentParams[':offset'] = $offset;
        }
        
        $sentStmt = $pdo->prepare($sentSql);
        
        foreach ($sentParams as $key => $value) {
            if ($key === ':limit' || $key === ':offset') {
                $sentStmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $sentStmt->bindValue($key, $value);
            }
        }
        
        $sentStmt->execute();
        $sentResults = $sentStmt->fetchAll();
        
        // Track IDs to avoid duplicates
        foreach ($sentResults as $sr) {
            if (isset($sr['original_dailywork_id'])) {
                $sentIds[] = $sr['original_dailywork_id'];
            }
        }
    }
    
    // ============================================================
    // PROCESS AND MERGE RESULTS - REMOVE DUPLICATES
    // ============================================================
    $response_data = [];
    $seenIds = [];
    
    // Process main daily work - skip if already in sent results
    foreach ($results as $row) {
        $dwId = (int)($row['id'] ?? 0);
        if ($dwId > 0 && in_array($dwId, $sentIds)) {
            // Skip - this daily work is already in sent results
            continue;
        }
        if (in_array($dwId, $seenIds)) {
            // Skip duplicate
            continue;
        }
        $seenIds[] = $dwId;
        $normalized = normalizeDailyWorkRow($row);
        $response_data[] = $normalized;
    }
    
    // Process sent daily work - check for duplicates
    foreach ($sentResults as $row) {
        $decoded = json_decode($row['dailywork_data'], true);
        if ($decoded) {
            $dwId = (int)($decoded['id'] ?? $row['original_dailywork_id'] ?? 0);
            if ($dwId > 0 && in_array($dwId, $seenIds)) {
                // Skip duplicate
                continue;
            }
            if ($dwId > 0) {
                $seenIds[] = $dwId;
            }
            $normalized = normalizeSentDailyWorkRow($row, $decoded);
            $response_data[] = $normalized;
        }
    }
    
    // ============================================================
    // SORT BY DATE (most recent first)
    // ============================================================
    usort($response_data, function($a, $b) {
        $dateA = isset($a['date']) ? strtotime($a['date']) : 0;
        $dateB = isset($b['date']) ? strtotime($b['date']) : 0;
        return $dateB - $dateA;
    });
    
    // ============================================================
    // RETURN RESPONSE
    // ============================================================
    echo json_encode([
        'success' => true,
        'data' => $response_data,
        'count' => count($response_data),
        'total' => count($response_data),
        'department_id' => $department_id,
        'project_id' => $project_id,
        'project_name' => $project_name,
        'campaign_id' => $campaign_id,
        'work_type' => $work_type,
        'sent_count' => count($sentResults),
        'own_count' => count($results) - count($sentIds),
        'limit' => $limit,
        'offset' => $offset
    ]);
    
} catch(PDOException $e) {
    error_log("Database error in get_dailywork.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage(), 
        'data' => []
    ]);
}

// ============================================================
// HELPER FUNCTIONS
// ============================================================

function normalizeDailyWorkRow($row) {
    $total_amount = (float)($row['total_amount'] ?? 0);
    $income = (float)($row['income'] ?? 0);
    $expenses = (float)($row['expenses'] ?? 0);
    $budget = (float)($row['budget'] ?? 0);
    $amount = (float)($row['amount'] ?? 0);
    $partial_amount = (float)($row['partial_amount'] ?? 0);
    $amount_paid = (float)($row['amount_paid'] ?? 0);
    $profit = (float)($row['profit'] ?? 0);
    $quantity_sold = (int)($row['quantity_sold'] ?? 0);
    
    // Calculate profit if missing
    if ($profit == 0 && ($income > 0 || $expenses > 0)) {
        $profit = $income - $expenses;
    } elseif ($profit == 0 && ($total_amount > 0 || $expenses > 0)) {
        $profit = $total_amount - $expenses;
    }
    
    // Calculate remaining amount
    $remaining = 0;
    $paid = $amount_paid > 0 ? $amount_paid : $partial_amount;
    if ($total_amount > 0) {
        $remaining = max(0, $total_amount - $paid);
    } elseif ($budget > 0) {
        $remaining = max(0, $budget - $amount);
    } elseif ($income > 0) {
        $remaining = max(0, $income - $paid);
    }
    
    return [
        'id' => (int)$row['id'],
        'date' => $row['date'],
        'work_description' => $row['work_description'] ?? '',
        'work_type' => $row['work_type'] ?? 'general',
        'project_id' => $row['project_id'] ? (int)$row['project_id'] : null,
        'project_name' => $row['project_name'] ?? '',
        'campaign_id' => $row['campaign_id'] ? (int)$row['campaign_id'] : null,
        'campaign_name' => $row['campaign_name'] ?? '',
        'department_id' => (int)$row['department_id'],
        'status' => $row['status'] ?? 'pending',
        'payment_status' => $row['payment_status'] ?? 'pending',
        'budget' => $budget,
        'amount' => $amount,
        'expenses' => $expenses,
        'income' => $income,
        'profit' => $profit,
        'quantity_produced' => (int)($row['quantity_produced'] ?? 0),
        'quantity_sold' => $quantity_sold,
        'price_per_unit' => (float)($row['price_per_unit'] ?? 0),
        'total_amount' => $total_amount,
        'partial_amount' => $partial_amount,
        'amount_paid' => $amount_paid,
        'remaining_amount' => $remaining,
        'created_by' => $row['created_by'] ?? 'System',
        'updated_by' => $row['updated_by'] ?? null,
        'created_at' => $row['created_at'],
        'updated_at' => $row['updated_at'],
        'is_sent' => (int)($row['is_sent'] ?? 0),
        'sent_from_dept' => $row['sent_from_dept'] ? (int)$row['sent_from_dept'] : null,
        'sent_to_dept' => $row['sent_to_dept'] ? (int)$row['sent_to_dept'] : null,
        'is_sent_record' => false
    ];
}

function normalizeSentDailyWorkRow($row, $decoded) {
    $total_amount = (float)($decoded['total_amount'] ?? $row['dailywork_amount'] ?? 0);
    $income = (float)($decoded['income'] ?? 0);
    $expenses = (float)($decoded['expenses'] ?? 0);
    $budget = (float)($row['dailywork_budget'] ?? $decoded['budget'] ?? 0);
    $amount = (float)($row['dailywork_amount'] ?? $decoded['amount'] ?? 0);
    $profit = (float)($decoded['profit'] ?? 0);
    $quantity_sold = (int)($decoded['quantity_sold'] ?? 0);
    
    if ($profit == 0 && ($income > 0 || $expenses > 0)) {
        $profit = $income - $expenses;
    } elseif ($profit == 0 && ($total_amount > 0 || $expenses > 0)) {
        $profit = $total_amount - $expenses;
    }
    
    return [
        'id' => (int)($row['sent_dailywork_id'] ?? $row['id']),
        'date' => $row['dailywork_date'] ?? $decoded['date'] ?? null,
        'work_description' => $decoded['work_description'] ?? '',
        'work_type' => $decoded['work_type'] ?? 'general',
        'project_id' => $decoded['project_id'] ?? null,
        'project_name' => $decoded['project_name'] ?? $row['dailywork_project_name'] ?? '',
        'campaign_id' => $decoded['campaign_id'] ?? null,
        'campaign_name' => $decoded['campaign_name'] ?? '',
        'department_id' => (int)($decoded['department_id'] ?? $row['from_department_id'] ?? 0),
        'status' => $decoded['status'] ?? $row['dailywork_status'] ?? 'pending',
        'payment_status' => $decoded['payment_status'] ?? 'pending',
        'budget' => $budget,
        'amount' => $amount,
        'expenses' => $expenses,
        'income' => $income,
        'profit' => $profit,
        'quantity_produced' => (int)($decoded['quantity_produced'] ?? 0),
        'quantity_sold' => $quantity_sold,
        'price_per_unit' => (float)($decoded['price_per_unit'] ?? 0),
        'total_amount' => $total_amount,
        'partial_amount' => (float)($decoded['partial_amount'] ?? 0),
        'amount_paid' => (float)($decoded['amount_paid'] ?? 0),
        'remaining_amount' => max(0, $total_amount - ((float)($decoded['amount_paid'] ?? 0))),
        'created_by' => $decoded['created_by'] ?? $row['sent_by'] ?? 'System',
        'updated_by' => $decoded['updated_by'] ?? null,
        'created_at' => $decoded['created_at'] ?? $row['sent_at'],
        'updated_at' => $decoded['updated_at'] ?? null,
        'is_sent' => 1,
        'sent_from_dept' => (int)$row['from_department_id'],
        'sent_to_dept' => (int)$row['to_department_id'],
        'is_sent_record' => true,
        'sent_by' => $row['sent_by'],
        'sent_at' => $row['sent_at']
    ];
}
?>