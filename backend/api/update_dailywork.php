<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

try {
    $query = "UPDATE dailywork SET 
                date = :date,
                project_id = :project_id,
                project_name = :project_name,
                campaign_id = :campaign_id,
                campaign_name = :campaign_name,
                work_type = :work_type,
                work_description = :work_description,
                quantity_produced = :quantity_produced,
                quantity_sold = :quantity_sold,
                price_per_unit = :price_per_unit,
                budget = :budget,
                amount = :amount,
                total_amount = :total_amount,
                income = :income,
                expenses = :expenses,
                profit = :profit,
                status = :status,
                payment_status = :payment_status,
                partial_amount = :partial_amount,
                updated_by = :updated_by,
                updated_at = NOW()
              WHERE id = :id";
    
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':id', $data->id);
    $stmt->bindParam(':date', $data->date);
    $stmt->bindParam(':project_id', $data->project_id ?? null);
    $stmt->bindParam(':project_name', $data->project_name ?? null);
    $stmt->bindParam(':campaign_id', $data->campaign_id ?? null);
    $stmt->bindParam(':campaign_name', $data->campaign_name ?? null);
    $stmt->bindParam(':work_type', $data->work_type ?? 'general');
    $stmt->bindParam(':work_description', $data->work_description ?? null);
    $stmt->bindParam(':quantity_produced', $data->quantity_produced ?? 0);
    $stmt->bindParam(':quantity_sold', $data->quantity_sold ?? 0);
    $stmt->bindParam(':price_per_unit', $data->price_per_unit ?? 0);
    $stmt->bindParam(':budget', $data->budget ?? 0);
    $stmt->bindParam(':amount', $data->amount ?? 0);
    $stmt->bindParam(':total_amount', $data->total_amount ?? 0);
    $stmt->bindParam(':income', $data->income ?? 0);
    $stmt->bindParam(':expenses', $data->expenses ?? 0);
    $stmt->bindParam(':profit', $data->profit ?? 0);
    $stmt->bindParam(':status', $data->status ?? 'pending');
    $stmt->bindParam(':payment_status', $data->payment_status ?? 'pending');
    $stmt->bindParam(':partial_amount', $data->partial_amount ?? 0);
    $stmt->bindParam(':updated_by', $data->updated_by ?? $data->created_by);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Daily work updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update daily work']);
    }
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>