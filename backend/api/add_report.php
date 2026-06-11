<?php
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['title'])) {
    sendResponse(false, 'Report title required');
}

try {
    // If there's a file attached, save to uploaded_reports table
    if (isset($data['file_name']) && !empty($data['file_name'])) {
        $stmt = $pdo->prepare("INSERT INTO uploaded_reports (title, period, file_name, file_path, file_size, file_type, department_id, uploaded_by, created_by, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $data['title'],
            $data['period'] ?? 'monthly',
            $data['file_name'],
            $data['file_path'] ?? null,
            $data['file_size'] ?? 0,
            $data['file_type'] ?? null,
            $data['department_id'] ?? 1,
            $data['created_by'] ?? 'System',
            $data['created_by'] ?? 'System',
            $data['description'] ?? null
        ]);
    } else {
        // Save to reports table
        $stmt = $pdo->prepare("INSERT INTO reports (title, period, content, department_id, status, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $data['title'],
            $data['period'] ?? 'monthly',
            $data['content'] ?? null,
            $data['department_id'] ?? 1,
            $data['status'] ?? 'draft',
            $data['created_by'] ?? 'System'
        ]);
    }
    
    sendResponse(true, 'Report added successfully', ['id' => $pdo->lastInsertId()]);
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>