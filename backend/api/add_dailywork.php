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
    // Get existing columns from the table
    $columnsQuery = "SHOW COLUMNS FROM dailywork";
    $columnsStmt = $db->prepare($columnsQuery);
    $columnsStmt->execute();
    $existingColumns = $columnsStmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Build dynamic query based on existing columns
    $fields = [];
    $params = [];
    
    foreach ($data as $key => $value) {
        if (in_array($key, $existingColumns) && $key !== 'id') {
            $fields[] = "$key = :$key";
            $params[":$key"] = $value;
        }
    }
    
    if (isset($data->id) && $data->id) {
        // UPDATE
        $fields[] = "updated_at = NOW()";
        $query = "UPDATE dailywork SET " . implode(", ", $fields) . " WHERE id = :id";
        $params[':id'] = $data->id;
        
        $stmt = $db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Daily work updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update daily work']);
        }
    } else {
        // INSERT
        $fields[] = "created_at = NOW()";
        $query = "INSERT INTO dailywork SET " . implode(", ", $fields);
        
        $stmt = $db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        if ($stmt->execute()) {
            $newId = $db->lastInsertId();
            echo json_encode(['success' => true, 'message' => 'Daily work added successfully', 'id' => $newId]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add daily work']);
        }
    }
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>