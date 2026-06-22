<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
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
    exit;
}

// Get input
$input = file_get_contents('php://input');
if (empty($input)) {
    echo json_encode(['success' => false, 'message' => 'No input data received']);
    exit;
}

$data = json_decode($input, true);
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input: ' . json_last_error_msg()]);
    exit;
}

// Required parameters
$project_id = isset($data['project_id']) ? intval($data['project_id']) : 0;
$to_department_id = isset($data['to_department_id']) ? intval($data['to_department_id']) : 0;
$from_department_id = isset($data['from_department_id']) ? intval($data['from_department_id']) : 0;
$sent_by = isset($data['sent_by']) ? trim($data['sent_by']) : 'System';
$project_data = isset($data['project_data']) ? $data['project_data'] : null;

if ($project_id == 0 || $to_department_id == 0 || $from_department_id == 0) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters: project_id, to_department_id, from_department_id']);
    exit;
}

if ($to_department_id == $from_department_id) {
    echo json_encode(['success' => false, 'message' => 'Cannot send to your own department']);
    exit;
}

try {
    // Check if project exists in projects table
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ? AND is_deleted = 0");
    $stmt->execute([$project_id]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    // If not found, check in sent_projects
    if (!$project) {
        $sentStmt = $pdo->prepare("
            SELECT * FROM sent_projects 
            WHERE original_project_id = ? OR id = ?
            ORDER BY sent_at DESC LIMIT 1
        ");
        $sentStmt->execute([$project_id, $project_id]);
        $sent_project = $sentStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($sent_project) {
            $project_data_from_sent = json_decode($sent_project['project_data'], true);
            $project = [
                'id' => $sent_project['original_project_id'],
                'name' => $sent_project['project_name'] ?? $project_data_from_sent['name'] ?? 'Project',
                'client_name' => $project_data_from_sent['client_name'] ?? '',
                'amount' => $sent_project['amount'] ?? $project_data_from_sent['amount'] ?? 0,
                'location' => $project_data_from_sent['location'] ?? '',
                'description' => $project_data_from_sent['description'] ?? '',
                'status' => $project_data_from_sent['status'] ?? 'pending',
                'progress' => $project_data_from_sent['progress'] ?? 0,
                'start_date' => $project_data_from_sent['start_date'] ?? '',
                'end_date' => $project_data_from_sent['end_date'] ?? '',
                'image' => $project_data_from_sent['image'] ?? '',
                'project_type' => $project_data_from_sent['project_type'] ?? 'general',
                'department_id' => $project_data_from_sent['department_id'] ?? $from_department_id,
                'created_by' => $project_data_from_sent['created_by'] ?? $sent_by,
                'created_at' => $project_data_from_sent['created_at'] ?? date('Y-m-d H:i:s'),
                'sent_count' => ($sent_project['sent_count'] ?? 0) + 1,
                'daily_work_records' => $project_data_from_sent['daily_work_records'] ?? []
            ];
        }
    }

    if (!$project) {
        echo json_encode(['success' => false, 'message' => 'Project not found']);
        exit;
    }

    // Check permission - FORWARDING ALLOWED
    $is_owner = ($project['department_id'] == $from_department_id);
    $is_receiver = ($project['sent_to_dept'] == $from_department_id);
    
    if (!$is_owner && !$is_receiver && $from_department_id != 0) {
        echo json_encode(['success' => false, 'message' => 'You do not have permission to send this project']);
        exit;
    }

    // Get daily work records
    $dailywork_records = [];
    if (isset($project['daily_work_records']) && is_array($project['daily_work_records'])) {
        $dailywork_records = $project['daily_work_records'];
    } else {
        $dailyworkStmt = $pdo->prepare("SELECT * FROM dailywork WHERE project_id = ? AND is_deleted = 0 ORDER BY date DESC");
        $dailyworkStmt->execute([$project_id]);
        $dailywork_records = $dailyworkStmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Calculate summary
    $total_budget = 0;
    $total_expenses = 0;
    $total_income = 0;
    $total_quantity_produced = 0;
    $total_quantity_sold = 0;
    $completed_count = 0;
    $partial_count = 0;
    $pending_count = 0;

    foreach ($dailywork_records as $dw) {
        $total_budget += floatval($dw['budget'] ?? 0);
        $total_expenses += floatval($dw['expenses'] ?? 0);
        $total_income += floatval($dw['income'] ?? $dw['total_amount'] ?? 0);
        $total_quantity_produced += intval($dw['quantity_produced'] ?? 0);
        $total_quantity_sold += intval($dw['quantity_sold'] ?? 0);
        
        $status = $dw['status'] ?? 'pending';
        if ($status === 'completed') $completed_count++;
        elseif ($status === 'partial') $partial_count++;
        else $pending_count++;
    }

    $remaining_budget = $total_budget - $total_expenses;

    // Build project data
    $full_project_data = [
        'id' => $project['id'],
        'name' => $project['name'],
        'client_name' => $project['client_name'] ?? '',
        'amount' => floatval($project['amount'] ?? 0),
        'location' => $project['location'] ?? '',
        'description' => $project['description'] ?? '',
        'status' => $project['status'] ?? 'pending',
        'progress' => intval($project['progress'] ?? 0),
        'start_date' => $project['start_date'] ?? '',
        'end_date' => $project['end_date'] ?? '',
        'image' => $project['image'] ?? '',
        'project_type' => $project['project_type'] ?? 'general',
        'department_id' => intval($project['department_id']),
        'created_by' => $project['created_by'] ?? $sent_by,
        'created_at' => $project['created_at'] ?? date('Y-m-d H:i:s'),
        'sent_from_dept' => $from_department_id,
        'sent_to_dept' => $to_department_id,
        'sent_count' => intval($project['sent_count'] ?? 0) + 1,
        'is_sent' => 1,
        'last_sent_at' => date('Y-m-d H:i:s'),
        'daily_work_records' => $dailywork_records,
        'daily_summary' => [
            'total_budget' => $total_budget,
            'total_expenses' => $total_expenses,
            'total_income' => $total_income,
            'total_quantity_produced' => $total_quantity_produced,
            'total_quantity_sold' => $total_quantity_sold,
            'remaining_budget' => $remaining_budget,
            'records_count' => count($dailywork_records),
            'completed_count' => $completed_count,
            'partial_count' => $partial_count,
            'pending_count' => $pending_count
        ]
    ];

    if ($project_data && is_array($project_data)) {
        $full_project_data = array_merge($full_project_data, $project_data);
    }

    // Save to sent_projects
    $sentStmt = $pdo->prepare("
        INSERT INTO sent_projects (
            original_project_id, project_data, from_department_id, to_department_id,
            sent_by, sent_at, is_viewed, sent_count, is_sent,
            from_department_name, to_department_name, project_name, project_type, amount
        ) VALUES (
            ?, ?, ?, ?, ?, NOW(), 0, ?, 1, ?, ?, ?, ?, ?
        )
    ");

    $from_dept_name = getDepartmentName($pdo, $from_department_id);
    $to_dept_name = getDepartmentName($pdo, $to_department_id);

    $sentStmt->execute([
        $project['id'],
        json_encode($full_project_data),
        $from_department_id,
        $to_department_id,
        $sent_by,
        intval($full_project_data['sent_count']),
        $from_dept_name,
        $to_dept_name,
        $project['name'],
        $project['project_type'] ?? 'general',
        floatval($project['amount'] ?? 0)
    ]);

    $sent_id = $pdo->lastInsertId();

    // Add notification
    $notifStmt = $pdo->prepare("
        INSERT INTO notifications (
            department_id, from_department_id, item_type, item_id, item_title, message, is_viewed, created_at
        ) VALUES (?, ?, 'project', ?, ?, ?, 0, NOW())
    ");
    
    $message = "Project \"{$project['name']}\" sent from {$from_dept_name} with " . count($dailywork_records) . " daily work records";
    $notifStmt->execute([
        $to_department_id,
        $from_department_id,
        $project['id'],
        $project['name'],
        $message
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Project sent successfully with ' . count($dailywork_records) . ' daily work records',
        'data' => [
            'sent_id' => $sent_id,
            'project_id' => $project['id'],
            'sent_to' => $to_department_id,
            'sent_from' => $from_department_id,
            'sent_at' => date('Y-m-d H:i:s'),
            'daily_work_count' => count($dailywork_records)
        ]
    ]);

} catch(PDOException $e) {
    error_log("Send project error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
} catch(Exception $e) {
    error_log("Send project error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
}

function getDepartmentName($pdo, $dept_id) {
    try {
        $stmt = $pdo->prepare("SELECT name FROM departments WHERE id = ?");
        $stmt->execute([$dept_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['name'] : 'Department ' . $dept_id;
    } catch(Exception $e) {
        return 'Department ' . $dept_id;
    }
}
?>