<?php
/**
 * get_dailywork.php - API inayofanya kazi na ALL DASHBOARDS
 * 
 * Inasaidia:
 * - Bricks & Timber (work_type = 'bricks' au 'timber')
 * - Sales & Marketing (campaign_id)
 * - Aluminium (project_id + quantity_produced, quantity_sold)
 * - Construction (project_id + budget/amount)
 * - Survey (project_id + budget/amount)
 * - Architectural (project_id + budget/amount)
 * - Town Planning (project_id + budget/amount)
 * - Hatimiliki (project_id + budget/amount)
 * - Manager (project_id + budget/amount)
 * - Secretary (department_id = 5)
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
$campaign_id = isset($_GET['campaign_id']) ? intval($_GET['campaign_id']) : 0;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

try {
    // Build base query - SELECT all columns needed for all dashboards
    $sql = "SELECT 
                id,
                date,
                work_description,
                COALESCE(work_type, 'general') as work_type,
                
                -- Project reference
                project_id,
                COALESCE(project_name, '') as project_name,
                
                -- Campaign reference  
                campaign_id,
                COALESCE(campaign_name, '') as campaign_name,
                
                -- Department
                department_id,
                
                -- Status fields
                COALESCE(status, 'pending') as status,
                COALESCE(payment_status, 'pending') as payment_status,
                
                -- Financial fields
                COALESCE(budget, 0) as budget,
                COALESCE(amount, 0) as amount,
                COALESCE(expenses, 0) as expenses,
                COALESCE(income, 0) as income,
                COALESCE(profit, 0) as profit,
                
                -- Production fields (Bricks, Timber, Aluminium)
                COALESCE(quantity_produced, 0) as quantity_produced,
                COALESCE(quantity_sold, 0) as quantity_sold,
                COALESCE(price_per_unit, 0) as price_per_unit,
                COALESCE(total_amount, 0) as total_amount,
                
                -- Payment fields
                COALESCE(partial_amount, 0) as partial_amount,
                COALESCE(amount_paid, 0) as amount_paid,
                
                -- User tracking
                created_by,
                updated_by,
                created_at,
                updated_at,
                
                -- Soft delete
                is_deleted
                
            FROM dailywork 
            WHERE (is_deleted = 0 OR is_deleted IS NULL)";
    
    $params = [];
    $conditions = [];
    
    // ============================================================
    // 1. FILTER KWA DEPARTMENT (MUHIMU ZAIDI)
    // ============================================================
    if ($department_id > 0) {
        $conditions[] = "department_id = :department_id";
        $params[':department_id'] = $department_id;
    }
    
    // ============================================================
    // 2. FILTER KWA WORK_TYPE (kwa Bricks & Timber)
    // ============================================================
    if (!empty($work_type) && $work_type !== 'undefined' && $work_type !== 'null') {
        if ($work_type === 'bricks' || $work_type === 'timber') {
            $conditions[] = "work_type = :work_type";
            $params[':work_type'] = $work_type;
        } elseif ($work_type === 'bricks_timber') {
            // Kwa Bricks & Timber dashboard - onyesha zote bricks na timber
            $conditions[] = "work_type IN ('bricks', 'timber')";
        }
    }
    
    // ============================================================
    // 3. FILTER KWA PROJECT_ID (kwa Construction, Survey, Architectural, Aluminium, etc)
    // ============================================================
    if ($project_id > 0) {
        $conditions[] = "project_id = :project_id";
        $params[':project_id'] = $project_id;
    }
    
    // ============================================================
    // 4. FILTER KWA CAMPAIGN_ID (kwa Sales & Marketing)
    // ============================================================
    if ($campaign_id > 0) {
        $conditions[] = "campaign_id = :campaign_id";
        $params[':campaign_id'] = $campaign_id;
    }
    
    // Append conditions to SQL
    if (count($conditions) > 0) {
        $sql .= " AND " . implode(" AND ", $conditions);
    }
    
    // Order by date descending (most recent first)
    $sql .= " ORDER BY date DESC, id DESC";
    
    // Add limit and offset
    if ($limit > 0) {
        $sql .= " LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
    }
    
    $stmt = $pdo->prepare($sql);
    
    // Bind parameters
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
    // PROCESS RESULTS - ADD CALCULATED FIELDS
    // ============================================================
    $response_data = [];
    
    foreach ($results as $row) {
        // Get values
        $total_amount = (float)$row['total_amount'];
        $income = (float)$row['income'];
        $expenses = (float)$row['expenses'];
        $budget = (float)$row['budget'];
        $amount = (float)$row['amount'];
        $partial_amount = (float)$row['partial_amount'];
        $amount_paid = (float)$row['amount_paid'];
        $profit = (float)$row['profit'];
        $quantity_sold = (int)$row['quantity_sold'];
        
        // ============================================================
        // CALCULATE PROFIT (kama haipo kwenye database)
        // ============================================================
        if ($profit == 0 && ($income > 0 || $expenses > 0)) {
            $profit = $income - $expenses;
        } elseif ($profit == 0 && ($total_amount > 0 || $expenses > 0)) {
            $profit = $total_amount - $expenses;
        } elseif ($profit == 0 && ($budget > 0 || $amount > 0)) {
            $profit = $amount - $budget;
        }
        
        // ============================================================
        // CALCULATE PRICE PER UNIT (kwa Bricks, Timber, Aluminium)
        // ============================================================
        $price_per_unit = (float)$row['price_per_unit'];
        if ($price_per_unit == 0 && $quantity_sold > 0 && $total_amount > 0) {
            $price_per_unit = $total_amount / $quantity_sold;
        } elseif ($price_per_unit == 0 && $quantity_sold > 0 && $income > 0) {
            $price_per_unit = $income / $quantity_sold;
        }
        
        // ============================================================
        // CALCULATE REMAINING AMOUNT
        // ============================================================
        $remaining = 0;
        $paid = $amount_paid > 0 ? $amount_paid : $partial_amount;
        
        if ($total_amount > 0) {
            $remaining = max(0, $total_amount - $paid);
        } elseif ($budget > 0) {
            $remaining = max(0, $budget - $amount);
        } elseif ($income > 0) {
            $remaining = max(0, $income - $paid);
        }
        
        // ============================================================
        // DETERMINE PAYMENT STATUS
        // ============================================================
        $payment_status = $row['payment_status'];
        if ($total_amount > 0 && $paid >= $total_amount) {
            $payment_status = 'completed';
        } elseif ($income > 0 && $paid >= $income) {
            $payment_status = 'completed';
        } elseif ($paid > 0) {
            $payment_status = 'partial';
        } else {
            $payment_status = $payment_status ?: 'pending';
        }
        
        // ============================================================
        // DETERMINE CATEGORY (kwa Bricks & Timber)
        // ============================================================
        $category = '';
        if ($row['department_id'] == 6) {
            if ($row['work_type'] == 'bricks') {
                $category = 'Bricks';
            } elseif ($row['work_type'] == 'timber') {
                $category = 'Timber';
            } else {
                $category = 'General';
            }
        }
        
        // ============================================================
        // BUILD RESPONSE OBJECT
        // ============================================================
        $normalized = [
            // Basic info
            'id' => (int)$row['id'],
            'date' => $row['date'],
            'work_description' => $row['work_description'] ?? '',
            'work_type' => $row['work_type'],
            'category' => $category,
            
            // References
            'project_id' => $row['project_id'] ? (int)$row['project_id'] : null,
            'project_name' => $row['project_name'] ?? '',
            'campaign_id' => $row['campaign_id'] ? (int)$row['campaign_id'] : null,
            'campaign_name' => $row['campaign_name'] ?? '',
            'department_id' => (int)$row['department_id'],
            
            // Status
            'status' => $row['status'],
            'payment_status' => $payment_status,
            
            // Financial (all dashboards)
            'budget' => $budget,
            'amount' => $amount,
            'expenses' => $expenses,
            'income' => $income,
            'profit' => $profit,
            
            // Production (Bricks, Timber, Aluminium)
            'quantity_produced' => (int)$row['quantity_produced'],
            'quantity_sold' => $quantity_sold,
            'price_per_unit' => $price_per_unit,
            'total_amount' => $total_amount,
            
            // Payment
            'partial_amount' => $partial_amount,
            'amount_paid' => $amount_paid,
            'remaining_amount' => $remaining,
            
            // Tracking
            'created_by' => $row['created_by'] ?? 'System',
            'updated_by' => $row['updated_by'] ?? null,
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
        ];
        
        $response_data[] = $normalized;
    }
    
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
        'campaign_id' => $campaign_id,
        'work_type' => $work_type,
        'limit' => $limit,
        'offset' => $offset,
        'query' => $sql
    ]);
    
} catch(PDOException $e) {
    error_log("Database error in get_dailywork.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage(), 
        'data' => []
    ]);
}
?>