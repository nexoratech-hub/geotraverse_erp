<?php
require_once 'config.php';

$departmentId = $_GET['department_id'] ?? null;

if (!$departmentId) {
    sendResponse(false, 'Department ID required');
}

try {
    // Count unviewed projects sent to this department
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM projects 
        WHERE is_deleted = 0 
        AND sent_to_dept = :dept_id 
        AND is_viewed_by_department = 0 
        AND sent_from_dept != :dept_id");
    $stmt->execute(['dept_id' => $departmentId]);
    $projectCount = $stmt->fetch()['count'];
    
    // Count unviewed budget requests for finance/admin
    $requestCount = 0;
    if ($departmentId == 2 || $departmentId == 1) {
        $stmt2 = $pdo->prepare("SELECT COUNT(*) as count FROM budget_requests 
            WHERE is_deleted = 0 
            AND is_viewed_by_finance = 0 
            AND status = 'pending'");
        $stmt2->execute();
        $requestCount = $stmt2->fetch()['count'];
    } else {
        // For other departments, count their own pending requests that they haven't viewed
        $stmt2 = $pdo->prepare("SELECT COUNT(*) as count FROM budget_requests 
            WHERE is_deleted = 0 
            AND department_id = :dept_id 
            AND is_viewed_by_admin = 0 
            AND status = 'pending'");
        $stmt2->execute(['dept_id' => $departmentId]);
        $requestCount = $stmt2->fetch()['count'];
    }
    
    // Count unviewed reports
    $stmt3 = $pdo->prepare("SELECT COUNT(*) as count FROM reports 
        WHERE is_deleted = 0 
        AND sent_to_department = :dept_id 
        AND is_viewed_by_department = 0 
        AND sent_from_department != :dept_id");
    $stmt3->execute(['dept_id' => $departmentId]);
    $reportCount = $stmt3->fetch()['count'];
    
    sendResponse(true, 'Badge counts retrieved', [
        'projects' => $projectCount,
        'requests' => $requestCount,
        'reports' => $reportCount
    ]);
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>