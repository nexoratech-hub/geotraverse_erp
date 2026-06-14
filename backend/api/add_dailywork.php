<?php
/**
 * add_dailywork.php - Add new daily work record
 * Inasaidia ALL DASHBOARDS:
 * - Bricks & Timber (work_type, quantity_produced, quantity_sold, etc)
 * - Sales & Marketing (campaign_id, budget, amount)
 * - Aluminium (project_id, quantity_produced, quantity_sold, expenses, income)
 * - Construction/Survey/Architectural (project_id, budget, amount)
 * - Secretary (department_id, budget, amount)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
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
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit();
}

// Required fields
if (empty($input['date'])) {
    echo json_encode(['success' => false, 'message' => 'Date is required']);
    exit();
}

if (empty($input['department_id'])) {
    echo json_encode(['success' => false, 'message' => 'Department ID is required']);
    exit();
}

try {
    // Prepare insert statement
    $sql = "INSERT INTO dailywork (
        date,
        work_description,
        work_type,
        project_id,
        project_name,
        campaign_id,
        campaign_name,
        department_id,
        budget,
        amount,
        expenses,
        income,
        profit,
        quantity_produced,
        quantity_sold,
        price_per_unit,
        total_amount,
        payment_status,
        partial_amount,
        amount_paid,
        status,
        created_by,
        created_at
    ) VALUES (
        :date,
        :work_description,
        :work_type,
        :project_id,
        :project_name,
        :campaign_id,
        :campaign_name,
        :department_id,
        :budget,
        :amount,
        :expenses,
        :income,
        :profit,
        :quantity_produced,
        :quantity_sold,
        :price_per_unit,
        :total_amount,
        :payment_status,
        :partial_amount,
        :amount_paid,
        :status,
        :created_by,
        NOW()
    )";
    
    // Calculate values based on dashboard type
    $department_id = (int)$input['department_id'];
    
    // Default values
    $budget = isset($input['budget']) ? (float)$input['budget'] : 0;
    $amount = isset($input['amount']) ? (float)$input['amount'] : 0;
    $expenses = isset($input['expenses']) ? (float)$input['expenses'] : 0;
    $income = isset($input['income']) ? (float)$input['income'] : 0;
    $quantity_produced = isset($input['quantity_produced']) ? (int)$input['quantity_produced'] : 0;
    $quantity_sold = isset($input['quantity_sold']) ? (int)$input['quantity_sold'] : 0;
    $price_per_unit = isset($input['price_per_unit']) ? (float)$input['price_per_unit'] : 0;
    $partial_amount = isset($input['partial_amount']) ? (float)$input['partial_amount'] : 0;
    $amount_paid = isset($input['amount_paid']) ? (float)$input['amount_paid'] : 0;
    $payment_status = isset($input['payment_status']) ? $input['payment_status'] : 'pending';
    $status = isset($input['status']) ? $input['status'] : 'pending';
    
    // Calculate total_amount (quantity_sold * price_per_unit)
    $total_amount = $quantity_sold * $price_per_unit;
    
    // Calculate profit based on what's available
    $profit = 0;
    if ($department_id == 6) { // Bricks & Timber
        // Profit = income - expenses
        $profit = $income - $expenses;
    } elseif ($department_id == 7) { // Aluminium
        // Profit = total_amount - expenses OR income - expenses
        $profit = ($total_amount > 0 ? $total_amount : $income) - $expenses;
    } elseif ($department_id == 3) { // Sales & Marketing
        // No profit calculation needed
        $profit = 0;
    } else { // Construction, Survey, Architectural, etc
        // Profit = amount - budget
        $profit = $amount - $budget;
    }
    
    // Set payment status based on partial amount
    if ($payment_status == 'partial' && $partial_amount > 0) {
        $status = 'partial';
    } elseif ($payment_status == 'completed' || ($total_amount > 0 && $partial_amount >= $total_amount)) {
        $payment_status = 'completed';
        $status = 'completed';
    } elseif ($payment_status == 'paid' && $income > 0) {
        $payment_status = 'completed';
        $status = 'completed';
    }
    
    $stmt = $pdo->prepare($sql);
    
    $stmt->bindValue(':date', $input['date']);
    $stmt->bindValue(':work_description', $input['work_description'] ?? null);
    $stmt->bindValue(':work_type', $input['work_type'] ?? 'general');
    $stmt->bindValue(':project_id', isset($input['project_id']) && $input['project_id'] > 0 ? (int)$input['project_id'] : null, PDO::PARAM_INT);
    $stmt->bindValue(':project_name', $input['project_name'] ?? null);
    $stmt->bindValue(':campaign_id', isset($input['campaign_id']) && $input['campaign_id'] > 0 ? (int)$input['campaign_id'] : null, PDO::PARAM_INT);
    $stmt->bindValue(':campaign_name', $input['campaign_name'] ?? null);
    $stmt->bindValue(':department_id', $department_id, PDO::PARAM_INT);
    $stmt->bindValue(':budget', $budget);
    $stmt->bindValue(':amount', $amount);
    $stmt->bindValue(':expenses', $expenses);
    $stmt->bindValue(':income', $income);
    $stmt->bindValue(':profit', $profit);
    $stmt->bindValue(':quantity_produced', $quantity_produced, PDO::PARAM_INT);
    $stmt->bindValue(':quantity_sold', $quantity_sold, PDO::PARAM_INT);
    $stmt->bindValue(':price_per_unit', $price_per_unit);
    $stmt->bindValue(':total_amount', $total_amount);
    $stmt->bindValue(':payment_status', $payment_status);
    $stmt->bindValue(':partial_amount', $partial_amount);
    $stmt->bindValue(':amount_paid', $amount_paid > 0 ? $amount_paid : $partial_amount);
    $stmt->bindValue(':status', $status);
    $stmt->bindValue(':created_by', $input['created_by'] ?? 'System');
    
    $stmt->execute();
    $inserted_id = $pdo->lastInsertId();
    
    // Return the inserted record
    $selectStmt = $pdo->prepare("SELECT * FROM dailywork WHERE id = :id");
    $selectStmt->bindValue(':id', $inserted_id, PDO::PARAM_INT);
    $selectStmt->execute();
    $newRecord = $selectStmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => 'Daily work added successfully',
        'id' => $inserted_id,
        'data' => $newRecord
    ]);
    
} catch(PDOException $e) {
    error_log("Add dailywork error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>