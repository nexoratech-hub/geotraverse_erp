<?php
// backend/api/send_report.php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Get input
$raw_input = file_get_contents('php://input');
$input = json_decode($raw_input, true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit();
}

// Get parameters (support multiple naming conventions)
$report_id = $input['report_id'] ?? $input['id'] ?? null;
$to_department_id = $input['to_department_id'] ?? $input['receiver_dept'] ?? $input['department_id'] ?? null;
$from_department_id = $input['from_department_id'] ?? $input['sender_dept'] ?? 4;
$sender_id = $input['sender_id'] ?? $input['user_id'] ?? 6;

// Validate
if (!$report_id) {
    echo json_encode(['success' => false, 'message' => 'Report ID is required']);
    exit();
}

if (!$to_department_id) {
    echo json_encode(['success' => false, 'message' => 'Destination department ID is required']);
    exit();
}

// Get report details
$stmt = $conn->prepare("SELECT * FROM reports WHERE id = ?");
$stmt->execute([$report_id]);
$report = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$report) {
    echo json_encode(['success' => false, 'message' => 'Report not found']);
    exit();
}

// Get sender department name
$sender_name = "Manager Department";
$stmt2 = $conn->prepare("SELECT name FROM departments WHERE id = ?");
$stmt2->execute([$from_department_id]);
$dept = $stmt2->fetch(PDO::FETCH_ASSOC);
if ($dept) $sender_name = $dept['name'];

// Get receiver department name
$receiver_name = "Department";
$stmt3 = $conn->prepare("SELECT name FROM departments WHERE id = ?");
$stmt3->execute([$to_department_id]);
$receiver = $stmt3->fetch(PDO::FETCH_ASSOC);
if ($receiver) $receiver_name = $receiver['name'];

// Create unique share ID (allows multiple sends)
$share_id = date('YmdHis') . '-' . rand(1000, 9999) . '-' . rand(100, 999);
$timestamp = date('Y-m-d H:i:s');

// Build message content
$message = "📊 REPORT SHARED\n";
$message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$message .= "📄 Title: " . ($report['title'] ?? 'Untitled') . "\n";
$message .= "📅 Period: " . ($report['period'] ?? 'N/A') . "\n";
$message .= "👤 From: $sender_name\n";
$message .= "🕒 Sent: $timestamp\n";
$message .= "🆔 Share ID: $share_id\n";
$message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
$message .= "📝 CONTENT:\n";
$message .= ($report['content'] ?? 'No content') . "\n";
$message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
$message .= "🔁 Multiple sharing allowed - this is share #$share_id\n";

// Get or create conversation
$convStmt = $conn->prepare("SELECT id FROM conversations 
                           WHERE (sender_dept = ? AND receiver_dept = ?) 
                           OR (sender_dept = ? AND receiver_dept = ?)");
$convStmt->execute([$from_department_id, $to_department_id, $to_department_id, $from_department_id]);
$conversation = $convStmt->fetch(PDO::FETCH_ASSOC);

$conversation_id = null;
if ($conversation) {
    $conversation_id = $conversation['id'];
    $updateConv = $conn->prepare("UPDATE conversations SET updated_at = NOW() WHERE id = ?");
    $updateConv->execute([$conversation_id]);
} else {
    $newConv = $conn->prepare("INSERT INTO conversations (sender_dept, receiver_dept, subject, status, created_at, updated_at) 
                              VALUES (?, ?, ?, 'active', NOW(), NOW())");
    $newConv->execute([$from_department_id, $to_department_id, "Report: " . substr($report['title'], 0, 50)]);
    $conversation_id = $conn->lastInsertId();
}

// Insert message - THIS ALLOWS MULTIPLE SENDS!
$insertStmt = $conn->prepare("INSERT INTO messages 
                              (conversation_id, sender_dept, receiver_dept, sender_id, message, is_read, status, created_at) 
                              VALUES (?, ?, ?, ?, ?, 0, 'sent', NOW())");
$insertStmt->execute([$conversation_id, $from_department_id, $to_department_id, $sender_id, $message]);

if ($insertStmt->rowCount() > 0) {
    // Update report status only once (does not block multiple sends)
    $checkStmt = $conn->prepare("SELECT status FROM reports WHERE id = ?");
    $checkStmt->execute([$report_id]);
    $currentStatus = $checkStmt->fetchColumn();
    
    if ($currentStatus !== 'sent') {
        $updateStmt = $conn->prepare("UPDATE reports SET status = 'sent' WHERE id = ?");
        $updateStmt->execute([$report_id]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => "✅ Report sent successfully to $receiver_name",
        'report_id' => $report_id,
        'to_department' => $receiver_name,
        'share_id' => $share_id,
        'conversation_id' => $conversation_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send report']);
}
?>