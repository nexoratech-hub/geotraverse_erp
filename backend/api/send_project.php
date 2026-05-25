<?php
// File: backend/api/send_project.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit;
}

$project_id = isset($data->project_id) ? intval($data->project_id) : 0;
$to_department_id = isset($data->to_department_id) ? intval($data->to_department_id) : 0;
$from_department_id = isset($data->from_department_id) ? intval($data->from_department_id) : 0;
$message_text = isset($data->message) ? $data->message : '';

if ($project_id == 0 || $to_department_id == 0 || $from_department_id == 0) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

if ($from_department_id == $to_department_id) {
    echo json_encode(['success' => false, 'message' => 'Cannot send to your own department']);
    exit;
}

try {
    // Get project data
    $stmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$project_id]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$project) {
        echo json_encode(['success' => false, 'message' => 'Project not found']);
        exit;
    }
    
    // FIRST: Check if this project has already been sent to this department
    $check_stmt = $db->prepare("SELECT id FROM projects WHERE sent_from_dept = ? AND sent_to_department = ? AND name = ? LIMIT 1");
    $check_stmt->execute([$from_department_id, $to_department_id, $project['name']]);
    
    if ($check_stmt->rowCount() > 0) {
        // Already sent, just send notification message
        $conv_id = findOrCreateConversation($db, $from_department_id, $to_department_id, "Project: " . $project['name']);
        
        $msg_stmt = $db->prepare("INSERT INTO messages (conversation_id, sender_dept, receiver_dept, message, is_read, created_at) VALUES (?, ?, ?, ?, 0, NOW())");
        $msg_stmt->execute([$conv_id, $from_department_id, $to_department_id, $message_text]);
        
        echo json_encode(['success' => true, 'message' => 'Notification sent (project already shared)', 'already_sent' => true]);
        exit;
    }
    
    // Create copy for receiving department
    $insert = $db->prepare("INSERT INTO projects (name, client_name, amount, status, progress, location, description, image, start_date, end_date, department_id, sent_from_dept, sent_to_department, is_viewed_by_department, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())");
    $insert->execute([
        $project['name'],
        $project['client_name'],
        $project['amount'],
        $project['status'],
        $project['progress'],
        $project['location'],
        $project['description'],
        $project['image'],
        $project['start_date'],
        $project['end_date'],
        $to_department_id,
        $from_department_id,
        $to_department_id
    ]);
    
    $new_project_id = $db->lastInsertId();
    
    // Send notification message (short version)
    $short_message = "📋 NEW PROJECT: " . $project['name'] . "\n\n";
    $short_message .= "From: " . getDepartmentName($db, $from_department_id) . "\n";
    $short_message .= "Client: " . ($project['client_name'] ?? 'N/A') . "\n";
    $short_message .= "Amount: TZS " . number_format($project['amount'], 2) . "\n";
    $short_message .= "Please check the Projects section to view full details.";
    
    $conv_id = findOrCreateConversation($db, $from_department_id, $to_department_id, "Project: " . $project['name']);
    
    $msg_stmt = $db->prepare("INSERT INTO messages (conversation_id, sender_dept, receiver_dept, message, is_read, created_at) VALUES (?, ?, ?, ?, 0, NOW())");
    $msg_stmt->execute([$conv_id, $from_department_id, $to_department_id, $short_message]);
    
    // Update conversation timestamp
    $update_conv = $db->prepare("UPDATE conversations SET updated_at = NOW() WHERE id = ?");
    $update_conv->execute([$conv_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Project sent successfully',
        'data' => [
            'project_id' => $new_project_id,
            'conversation_id' => $conv_id
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function getDepartmentName($db, $dept_id) {
    $query = "SELECT name FROM departments WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$dept_id]);
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['name'];
    }
    return "Department " . $dept_id;
}

function findOrCreateConversation($db, $sender_dept, $receiver_dept, $subject) {
    $check_query = "SELECT id FROM conversations 
                    WHERE ((sender_dept = ? AND receiver_dept = ?) 
                       OR (sender_dept = ? AND receiver_dept = ?))
                    AND status = 'active'
                    LIMIT 1";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->execute([$sender_dept, $receiver_dept, $receiver_dept, $sender_dept]);
    
    if ($check_stmt->rowCount() > 0) {
        $row = $check_stmt->fetch(PDO::FETCH_ASSOC);
        return $row['id'];
    }
    
    $insert_conv = $db->prepare("INSERT INTO conversations (sender_dept, receiver_dept, subject, status, created_at) VALUES (?, ?, ?, 'active', NOW())");
    $insert_conv->execute([$sender_dept, $receiver_dept, $subject]);
    
    return $db->lastInsertId();
}
?>