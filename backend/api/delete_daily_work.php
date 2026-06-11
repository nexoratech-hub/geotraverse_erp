<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, DELETE, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = $data->id ?? null;
    $is_deleted = $data->is_deleted ?? 1;

    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID is required']);
        exit;
    }

    // Soft delete - just mark as deleted
    $query = "UPDATE daily_work SET is_deleted = :is_deleted, updated_at = NOW() WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':is_deleted', $is_deleted);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Daily work deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete daily work']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>