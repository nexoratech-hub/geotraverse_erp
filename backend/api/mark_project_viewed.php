<?php
// backend/api/mark_project_viewed.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['project_id']) || !isset($data['department_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

$project_id = intval($data['project_id']);
$dept_id = intval($data['department_id']);

try {
    $pdo = new PDO("mysql:host=localhost;dbname=geotraverse_erp;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}

try {
    // Check if it's an original project
    $stmt = $pdo->prepare("SELECT id, sent_to_dept, sent_from_dept, department_id FROM projects WHERE id = ?");
    $stmt->execute([$project_id]);
    $project = $stmt->fetch();
    
    if ($project) {
        // Mark original project as viewed for this department
        $stmt = $pdo->prepare("UPDATE projects SET 
            is_viewed_by_department = 1,
            viewed_by_department_at = NOW()
            WHERE id = ?");
        $stmt->execute([$project_id]);
        echo json_encode(['success' => true, 'message' => 'Project marked as viewed']);
        exit;
    }
    
    // Check if it's a sent project
    $stmt = $pdo->prepare("SELECT id, to_department_id FROM sent_projects WHERE id = ? OR original_project_id = ?");
    $stmt->execute([$project_id, $project_id]);
    $sentProject = $stmt->fetch();
    
    if ($sentProject) {
        $stmt = $pdo->prepare("UPDATE sent_projects SET 
            is_viewed = 1,
            viewed_at = NOW()
            WHERE id = ? OR original_project_id = ?");
        $stmt->execute([$project_id, $project_id]);
        echo json_encode(['success' => true, 'message' => 'Sent project marked as viewed']);
        exit;
    }
    
    echo json_encode(['success' => false, 'message' => 'Project not found']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>