<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $data->id ?? null;
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID is required']);
        exit;
    }
    
    // Get data from request
    $date = $data->date ?? null;
    $project_name = $data->project_name ?? '';
    $work_description = $data->work_description ?? '';
    $quantity_produced = $data->quantity_produced ?? 0;
    $quantity_sold = $data->quantity_sold ?? 0;
    $price_per_unit = $data->price_per_unit ?? 0;
    $total_amount = $data->total_amount ?? 0;
    $income = $data->income ?? 0;
    $expenses = $data->expenses ?? 0;
    $payment_status = $data->payment_status ?? 'pending';
    $partial_amount = $data->partial_amount ?? 0;
    $status = $data->status ?? 'pending';
    $updated_by = $data->updated_by ?? 'System';

    // Prepare SQL statement
    $query = "UPDATE daily_work SET 
              date = :date,
              project_name = :project_name,
              work_description = :work_description,
              quantity_produced = :quantity_produced,
              quantity_sold = :quantity_sold,
              price_per_unit = :price_per_unit,
              total_amount = :total_amount,
              income = :income,
              expenses = :expenses,
              payment_status = :payment_status,
              partial_amount = :partial_amount,
              status = :status,
              updated_by = :updated_by,
              updated_at = NOW()
              WHERE id = :id";

    $stmt = $db->prepare($query);

    // Bind parameters
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':date', $date);
    $stmt->bindParam(':project_name', $project_name);
    $stmt->bindParam(':work_description', $work_description);
    $stmt->bindParam(':quantity_produced', $quantity_produced);
    $stmt->bindParam(':quantity_sold', $quantity_sold);
    $stmt->bindParam(':price_per_unit', $price_per_unit);
    $stmt->bindParam(':total_amount', $total_amount);
    $stmt->bindParam(':income', $income);
    $stmt->bindParam(':expenses', $expenses);
    $stmt->bindParam(':payment_status', $payment_status);
    $stmt->bindParam(':partial_amount', $partial_amount);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':updated_by', $updated_by);

    // Execute query
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Daily work updated successfully',
            'id' => $id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update daily work']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>