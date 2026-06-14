<?php
/**
 * update_dailywork.php - Update existing daily work record
 * Inasaidia ALL DASHBOARDS
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
if (empty($input['id'])) {
    echo json_encode(['success' => false, 'message' => 'Record ID is required']);
    exit();
}

if (empty($input['date'])) {
    echo json_encode(['success' => false, 'message' => 'Date is required']);
    exit();
}

try {
    // Get existing record first
    $checkStmt = $pdo->prepare("SELECT * FROM dailywork WHERE id = :id");
    $checkStmt->bindValue(':id', (int)$input['id'], PDO::PARAM_INT);
    $checkStmt->execute();
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existing) {
        echo json_encode(['success' => false, 'message' => 'Record not found']);
        exit();
    }
    
    $department_id = (int)($input['department_id'] ?? $existing['department_id']);
    
    // Calculate values
    $quantity_produced = isset($input['quantity_produced']) ? (int)$input['quantity_produced'] : (int)($existing['quantity_produced'] ?? 0);
    $quantity_sold = isset($input['quantity_sold']) ? (int)$input['quantity_sold'] : (int)($existing['quantity_sold'] ?? 0);
    $price_per_unit = isset($input['price_per_unit']) ? (float)$input['price_per_unit'] : (float)($existing['price_per_unit'] ?? 0);
    $total_amount = $quantity_sold * $price_per_unit;
    
    $expenses = isset($input['expenses']) ? (float)$input['expenses'] : (float)($existing['expenses'] ?? 0);
    $income = isset($input['income']) ? (float)$input['income'] : (float)($existing['income'] ?? 0);
    $budget = isset($input['budget']) ? (float)$input['budget'] : (float)($existing['budget'] ?? 0);
    $amount = isset($input['amount']) ? (float)$input['amount'] : (float)($existing['amount'] ?? 0);
    $partial_amount = isset($input['partial_amount']) ? (float)$input['partial_amount'] : (float)($existing['partial_amount'] ?? 0);
    $amount_paid = isset($input['amount_paid']) ? (float)$input['amount_paid'] : (float)($existing['amount_paid'] ?? 0);
    $payment_status = isset($input['payment_status']) ? $input['payment_status'] : ($existing['payment_status'] ?? 'pending');
    
    // Calculate profit
    $profit = 0;
    if ($department_id == 6) { // Bricks & Timber
        $profit = $income - $expenses;
    } elseif ($department_id == 7) { // Aluminium
        $profit = ($total_amount > 0 ? $total_amount : $income) - $expenses;
    } elseif ($department_id == 3) { // Sales & Marketing
        $profit = 0;
    } else {
        $profit = $amount - $budget;
    }
    
    // Set status based on payment
    $status = $existing['status'] ?? 'pending';
    if ($payment_status == 'partial' && $partial_amount > 0) {
        $status = 'partial';
    } elseif ($payment_status == 'completed' || ($total_amount > 0 && $partial_amount >= $total_amount)) {
        $payment_status = 'completed';
        $status = 'completed';
    }
    
    // Build update query dynamically
    $updateFields = [
        "date = :date",
        "work_description = :work_description",
        "work_type = :work_type",
        "project_id = :project_id",
        "project_name = :project_name",
        "campaign_id = :campaign_id",
        "campaign_name = :campaign_name",
        "department_id = :department_id",
        "budget = :budget",
        "amount = :amount",
        "expenses = :expenses",
        "income = :income",
        "profit = :profit",
        "quantity_produced = :quantity_produced",
        "quantity_sold = :quantity_sold",
        "price_per_unit = :price_per_unit",
        "total_amount = :total_amount",
        "payment_status = :payment_status",
        "partial_amount = :partial_amount",
        "amount_paid = :amount_paid",
        "status = :status",
        "updated_by = :updated_by",
        "updated_at = NOW()"
    ];
    
    $sql = "UPDATE dailywork SET " . implode(", ", $updateFields) . " WHERE id = :id";
    
    $stmt = $pdo->prepare($sql);
    
    $stmt->bindValue(':id', (int)$input['id'], PDO::PARAM_INT);
    $stmt->bindValue(':date', $input['date']);
    $stmt->bindValue(':work_description', $input['work_description'] ?? $existing['work_description']);
    $stmt->bindValue(':work_type', $input['work_type'] ?? $existing['work_type']);
    $stmt->bindValue(':project_id', isset($input['project_id']) && $input['project_id'] > 0 ? (int)$input['project_id'] : null, PDO::PARAM_INT);
    $stmt->bindValue(':project_name', $input['project_name'] ?? $existing['project_name']);
    $stmt->bindValue(':campaign_id', isset($input['campaign_id']) && $input['campaign_id'] > 0 ? (int)$input['campaign_id'] : null, PDO::PARAM_INT);
    $stmt->bindValue(':campaign_name', $input['campaign_name'] ?? $existing['campaign_name']);
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
    $stmt->bindValue(':updated_by', $input['updated_by'] ?? $input['created_by'] ?? 'System');
    
    $stmt->execute();
    
    // Get updated record
    $selectStmt = $pdo->prepare("SELECT * FROM dailywork WHERE id = :id");
    $selectStmt->bindValue(':id', (int)$input['id'], PDO::PARAM_INT);
    $selectStmt->execute();
    $updatedRecord = $selectStmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => 'Daily work updated successfully',
        'id' => (int)$input['id'],
        'data' => $updatedRecord
    ]);
    
} catch(PDOException $e) {
    error_log("Update dailywork error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>