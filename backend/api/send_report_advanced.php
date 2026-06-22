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

$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$input = file_get_contents('php://input');
if (empty($input)) {
    echo json_encode(['success' => false, 'message' => 'No input data']);
    exit;
}

$data = json_decode($input, true);
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit;
}

$report_id = isset($data['report_id']) ? intval($data['report_id']) : 0;
$to_department_id = isset($data['to_department_id']) ? intval($data['to_department_id']) : 0;
$from_department_id = isset($data['from_department_id']) ? intval($data['from_department_id']) : 0;
$sent_by = isset($data['sent_by']) ? trim($data['sent_by']) : 'System';

if ($report_id == 0 || $to_department_id == 0 || $from_department_id == 0) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

if ($to_department_id == $from_department_id) {
    echo json_encode(['success' => false, 'message' => 'Cannot send to own department']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM reports WHERE id = ? AND is_deleted = 0");
    $stmt->execute([$report_id]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$report) {
        $sentStmt = $pdo->prepare("SELECT * FROM sent_reports WHERE original_report_id = ? OR id = ? ORDER BY sent_at DESC LIMIT 1");
        $sentStmt->execute([$report_id, $report_id]);
        $sent_report = $sentStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($sent_report) {
            $rd = json_decode($sent_report['report_data'], true);
            $report = [
                'id' => $sent_report['original_report_id'],
                'title' => $sent_report['report_title'] ?? $rd['title'] ?? 'Report',
                'period' => $sent_report['report_period'] ?? $rd['period'] ?? 'monthly',
                'content' => $rd['content'] ?? '',
                'status' => $sent_report['report_status'] ?? $rd['status'] ?? 'draft',
                'department_id' => $rd['department_id'] ?? $from_department_id,
                'created_by' => $rd['created_by'] ?? $sent_by,
                'created_at' => $rd['created_at'] ?? date('Y-m-d H:i:s'),
                'sent_count' => ($sent_report['sent_count'] ?? 0) + 1
            ];
        }
    }

    if (!$report) {
        echo json_encode(['success' => false, 'message' => 'Report not found']);
        exit;
    }

    $is_owner = ($report['department_id'] == $from_department_id);
    $is_receiver = ($report['sent_to_department'] == $from_department_id);
    
    if (!$is_owner && !$is_receiver && $from_department_id != 0) {
        echo json_encode(['success' => false, 'message' => 'Permission denied']);
        exit;
    }

    $full_data = [
        'id' => $report['id'],
        'title' => $report['title'],
        'period' => $report['period'] ?? 'monthly',
        'content' => $report['content'] ?? '',
        'status' => $report['status'] ?? 'draft',
        'department_id' => intval($report['department_id']),
        'created_by' => $report['created_by'] ?? $sent_by,
        'created_at' => $report['created_at'] ?? date('Y-m-d H:i:s'),
        'sent_from_department' => $from_department_id,
        'sent_to_department' => $to_department_id,
        'sent_count' => intval($report['sent_count'] ?? 0) + 1,
        'is_sent' => 1,
        'last_sent_at' => date('Y-m-d H:i:s')
    ];

    if (isset($data['report_data']) && is_array($data['report_data'])) {
        $full_data = array_merge($full_data, $data['report_data']);
    }

    $from_name = getDeptName($pdo, $from_department_id);
    $to_name = getDeptName($pdo, $to_department_id);

    $sentStmt = $pdo->prepare("
        INSERT INTO sent_reports (
            original_report_id, report_data, from_department_id, to_department_id,
            sent_by, sent_at, is_viewed, sent_count, is_sent,
            from_department_name, to_department_name, report_title, report_period, report_status
        ) VALUES (?, ?, ?, ?, ?, NOW(), 0, ?, 1, ?, ?, ?, ?, ?)
    ");
    $sentStmt->execute([
        $report['id'],
        json_encode($full_data),
        $from_department_id,
        $to_department_id,
        $sent_by,
        intval($full_data['sent_count']),
        $from_name,
        $to_name,
        $report['title'],
        $report['period'] ?? 'monthly',
        $report['status'] ?? 'draft'
    ]);

    $sent_id = $pdo->lastInsertId();

    $notifStmt = $pdo->prepare("
        INSERT INTO notifications (department_id, from_department_id, item_type, item_id, item_title, message, is_viewed, created_at)
        VALUES (?, ?, 'report', ?, ?, ?, 0, NOW())
    ");
    $notifStmt->execute([
        $to_department_id,
        $from_department_id,
        $report['id'],
        $report['title'],
        "Report \"{$report['title']}\" sent from {$from_name}"
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Report sent successfully',
        'data' => [
            'sent_id' => $sent_id,
            'report_id' => $report['id'],
            'sent_to' => $to_department_id,
            'sent_from' => $from_department_id,
            'sent_at' => date('Y-m-d H:i:s')
        ]
    ]);

} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error']);
    exit;
}

function getDeptName($pdo, $dept_id) {
    try {
        $stmt = $pdo->prepare("SELECT name FROM departments WHERE id = ?");
        $stmt->execute([$dept_id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ? $r['name'] : 'Department ' . $dept_id;
    } catch(Exception $e) {
        return 'Department ' . $dept_id;
    }
}
?>