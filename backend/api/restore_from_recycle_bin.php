<?php
// restore_from_recycle_bin.php - Restore ALL item types including sent_report

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB Connection Error: ' . $e->getMessage()]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['recycle_id'])) {
    echo json_encode(['success' => false, 'message' => 'Recycle ID required']);
    exit;
}

$recycleId = (int)$data['recycle_id'];

try {
    // ============================================================
    // GET ITEM FROM RECYCLE BIN
    // ============================================================
    $stmt = $pdo->prepare("SELECT * FROM recycle_bin WHERE id = ?");
    $stmt->execute([$recycleId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$item) {
        echo json_encode(['success' => false, 'message' => 'Item not found in recycle bin']);
        exit;
    }
    
    $itemId = (int)$item['item_id'];
    $itemType = $item['item_type'];
    $itemName = $item['item_name'];
    
    $restored = false;
    $message = '';
    
    // ============================================================
    // LOG FOR DEBUGGING
    // ============================================================
    error_log("RESTORE: item_type={$itemType}, item_id={$itemId}, name={$itemName}");
    
    // ============================================================
    // RESTORE BY ITEM TYPE
    // ============================================================
    
    // ---- ADDED PROJECTS ----
    if ($itemType === 'project' || $itemType === 'added_project') {
        $restoreStmt = $pdo->prepare("
            UPDATE projects 
            SET is_deleted = 0, deleted_by_department = NULL, deleted_by_admin = NULL, deleted_at = NULL
            WHERE id = ?
        ");
        $restoreStmt->execute([$itemId]);
        if ($restoreStmt->rowCount() > 0 || $restoreStmt->errorCode() === '00000') {
            $restored = true;
            $message = 'Project restored successfully';
        } else {
            $check = $pdo->prepare("SELECT id FROM projects WHERE id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
            $check->execute([$itemId]);
            if ($check->fetch()) {
                $restored = true;
                $message = 'Project already restored';
            } else {
                $message = 'Project not found';
            }
        }
    }
    
    // ---- SENT PROJECTS ----
    elseif ($itemType === 'sent_project' || $itemType === 'sent_added_project') {
        $restoreStmt = $pdo->prepare("
            UPDATE sent_projects 
            SET is_deleted = 0, deleted_by_department = NULL, deleted_by_admin = NULL, deleted_at = NULL
            WHERE id = ?
        ");
        $restoreStmt->execute([$itemId]);
        if ($restoreStmt->rowCount() > 0 || $restoreStmt->errorCode() === '00000') {
            $restored = true;
            $message = 'Sent project restored successfully';
        } else {
            $check = $pdo->prepare("SELECT id FROM sent_projects WHERE id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
            $check->execute([$itemId]);
            if ($check->fetch()) {
                $restored = true;
                $message = 'Sent project already restored';
            } else {
                $message = 'Sent project not found';
            }
        }
    }
    
    // ---- ADDED REPORTS ----
    elseif ($itemType === 'report' || $itemType === 'added_report') {
        $restoreStmt = $pdo->prepare("
            UPDATE reports 
            SET is_deleted = 0, deleted_by_department = NULL, deleted_by_admin = NULL, deleted_at = NULL
            WHERE id = ?
        ");
        $restoreStmt->execute([$itemId]);
        if ($restoreStmt->rowCount() > 0 || $restoreStmt->errorCode() === '00000') {
            $restored = true;
            $message = 'Report restored successfully';
        } else {
            $check = $pdo->prepare("SELECT id FROM reports WHERE id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
            $check->execute([$itemId]);
            if ($check->fetch()) {
                $restored = true;
                $message = 'Report already restored';
            } else {
                $message = 'Report not found';
            }
        }
    }
    
    // ---- SENT REPORTS - ✅ FIXED ----
    elseif ($itemType === 'sent_report' || $itemType === 'sent_added_report') {
        $restoreStmt = $pdo->prepare("
            UPDATE sent_reports 
            SET 
                is_deleted = 0, 
                deleted_by_department = NULL, 
                deleted_by_admin = NULL, 
                deleted_at = NULL
            WHERE id = ?
        ");
        $restoreStmt->execute([$itemId]);
        
        // Check if updated or already restored
        if ($restoreStmt->rowCount() > 0) {
            $restored = true;
            $message = 'Sent report restored successfully';
        } else {
            // Check if already restored
            $check = $pdo->prepare("
                SELECT id FROM sent_reports 
                WHERE id = ? AND (is_deleted = 0 OR is_deleted IS NULL)
            ");
            $check->execute([$itemId]);
            if ($check->fetch()) {
                $restored = true;
                $message = 'Sent report already restored';
            } else {
                // Check if report exists at all
                $exists = $pdo->prepare("SELECT id FROM sent_reports WHERE id = ?");
                $exists->execute([$itemId]);
                if ($exists->fetch()) {
                    // Report exists but still deleted - force update
                    $forceStmt = $pdo->prepare("
                        UPDATE sent_reports 
                        SET is_deleted = 0 
                        WHERE id = ?
                    ");
                    $forceStmt->execute([$itemId]);
                    if ($forceStmt->rowCount() > 0) {
                        $restored = true;
                        $message = 'Sent report force restored';
                    } else {
                        $message = 'Could not restore sent report';
                    }
                } else {
                    $message = 'Sent report not found in sent_reports table';
                }
            }
        }
    }
    
    // ---- UPLOADED REPORTS ----
    elseif ($itemType === 'uploaded_report') {
        $restoreStmt = $pdo->prepare("
            UPDATE uploaded_reports 
            SET is_deleted = 0, deleted_by_department = NULL, deleted_by_admin = NULL, deleted_at = NULL
            WHERE id = ?
        ");
        $restoreStmt->execute([$itemId]);
        if ($restoreStmt->rowCount() > 0 || $restoreStmt->errorCode() === '00000') {
            $restored = true;
            $message = 'Uploaded report restored successfully';
        } else {
            $check = $pdo->prepare("SELECT id FROM uploaded_reports WHERE id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
            $check->execute([$itemId]);
            if ($check->fetch()) {
                $restored = true;
                $message = 'Uploaded report already restored';
            } else {
                $message = 'Uploaded report not found';
            }
        }
    }
    
    // ---- SENT UPLOADED REPORTS ----
    elseif ($itemType === 'sent_uploaded_report') {
        $restoreStmt = $pdo->prepare("
            UPDATE sent_uploaded_reports 
            SET is_deleted = 0, deleted_by_department = NULL, deleted_by_admin = NULL, deleted_at = NULL
            WHERE id = ?
        ");
        $restoreStmt->execute([$itemId]);
        if ($restoreStmt->rowCount() > 0 || $restoreStmt->errorCode() === '00000') {
            $restored = true;
            $message = 'Sent uploaded report restored successfully';
        } else {
            $check = $pdo->prepare("SELECT id FROM sent_uploaded_reports WHERE id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
            $check->execute([$itemId]);
            if ($check->fetch()) {
                $restored = true;
                $message = 'Sent uploaded report already restored';
            } else {
                $message = 'Sent uploaded report not found';
            }
        }
    }
    
    // ---- PROJECT DOCUMENTS ----
    elseif ($itemType === 'project_document') {
        $restoreStmt = $pdo->prepare("
            UPDATE project_documents 
            SET is_deleted = 0, deleted_by = NULL, deleted_by_department = NULL, deleted_by_admin = NULL, deleted_at = NULL
            WHERE id = ?
        ");
        $restoreStmt->execute([$itemId]);
        if ($restoreStmt->rowCount() > 0 || $restoreStmt->errorCode() === '00000') {
            $restored = true;
            $message = 'Project document restored successfully';
        } else {
            $check = $pdo->prepare("SELECT id FROM project_documents WHERE id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
            $check->execute([$itemId]);
            if ($check->fetch()) {
                $restored = true;
                $message = 'Project document already restored';
            } else {
                $message = 'Project document not found';
            }
        }
    }
    
    // ---- SENT PROJECT DOCUMENTS ----
    elseif ($itemType === 'sent_project_document') {
        $restoreStmt = $pdo->prepare("
            UPDATE sent_project_documents 
            SET is_deleted = 0, deleted_by = NULL, deleted_by_department = NULL, deleted_by_admin = NULL, deleted_at = NULL
            WHERE id = ?
        ");
        $restoreStmt->execute([$itemId]);
        if ($restoreStmt->rowCount() > 0 || $restoreStmt->errorCode() === '00000') {
            $restored = true;
            $message = 'Sent project document restored successfully';
        } else {
            $check = $pdo->prepare("SELECT id FROM sent_project_documents WHERE id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
            $check->execute([$itemId]);
            if ($check->fetch()) {
                $restored = true;
                $message = 'Sent project document already restored';
            } else {
                $message = 'Sent project document not found';
            }
        }
    }
    
    // ---- BUDGET REQUESTS ----
    elseif ($itemType === 'budget_request' || $itemType === 'fund_request') {
        $restoreStmt = $pdo->prepare("
            UPDATE fund_requests 
            SET is_deleted = 0, deleted_by_department = NULL, deleted_by_admin = NULL, deleted_at = NULL
            WHERE id = ?
        ");
        $restoreStmt->execute([$itemId]);
        if ($restoreStmt->rowCount() > 0 || $restoreStmt->errorCode() === '00000') {
            $restored = true;
            $message = 'Budget request restored successfully';
        } else {
            $check = $pdo->prepare("SELECT id FROM fund_requests WHERE id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
            $check->execute([$itemId]);
            if ($check->fetch()) {
                $restored = true;
                $message = 'Budget request already restored';
            } else {
                $message = 'Budget request not found';
            }
        }
    }
    
    // ---- TRANSACTIONS ----
    elseif ($itemType === 'transaction') {
        $restoreStmt = $pdo->prepare("
            UPDATE transactions 
            SET is_deleted = 0, deleted_by_department = NULL, deleted_by_admin = NULL, deleted_at = NULL
            WHERE id = ?
        ");
        $restoreStmt->execute([$itemId]);
        if ($restoreStmt->rowCount() > 0 || $restoreStmt->errorCode() === '00000') {
            $restored = true;
            $message = 'Transaction restored successfully';
        } else {
            $check = $pdo->prepare("SELECT id FROM transactions WHERE id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
            $check->execute([$itemId]);
            if ($check->fetch()) {
                $restored = true;
                $message = 'Transaction already restored';
            } else {
                $message = 'Transaction not found';
            }
        }
    }
    
    // ---- EMPLOYEES ----
    elseif ($itemType === 'employee') {
        $restoreStmt = $pdo->prepare("
            UPDATE employees 
            SET is_deleted = 0, deleted_by_department = NULL, deleted_by_admin = NULL, deleted_at = NULL
            WHERE id = ?
        ");
        $restoreStmt->execute([$itemId]);
        if ($restoreStmt->rowCount() > 0 || $restoreStmt->errorCode() === '00000') {
            $restored = true;
            $message = 'Employee restored successfully';
        } else {
            $check = $pdo->prepare("SELECT id FROM employees WHERE id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
            $check->execute([$itemId]);
            if ($check->fetch()) {
                $restored = true;
                $message = 'Employee already restored';
            } else {
                $message = 'Employee not found';
            }
        }
    }
    
    // ---- VISITORS ----
    elseif ($itemType === 'visitor') {
        $restoreStmt = $pdo->prepare("
            UPDATE visitors 
            SET is_deleted = 0, deleted_by_department = NULL, deleted_by_admin = NULL, deleted_at = NULL
            WHERE id = ?
        ");
        $restoreStmt->execute([$itemId]);
        if ($restoreStmt->rowCount() > 0 || $restoreStmt->errorCode() === '00000') {
            $restored = true;
            $message = 'Visitor restored successfully';
        } else {
            $check = $pdo->prepare("SELECT id FROM visitors WHERE id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
            $check->execute([$itemId]);
            if ($check->fetch()) {
                $restored = true;
                $message = 'Visitor already restored';
            } else {
                $message = 'Visitor not found';
            }
        }
    }
    
    // ---- CAMPAIGNS ----
    elseif ($itemType === 'campaign' || $itemType === 'marketing_campaign') {
        $restoreStmt = $pdo->prepare("
            UPDATE marketing_campaigns 
            SET is_deleted = 0, deleted_by_department = NULL, deleted_by_admin = NULL, deleted_at = NULL
            WHERE id = ?
        ");
        $restoreStmt->execute([$itemId]);
        if ($restoreStmt->rowCount() > 0 || $restoreStmt->errorCode() === '00000') {
            $restored = true;
            $message = 'Campaign restored successfully';
        } else {
            $check = $pdo->prepare("SELECT id FROM marketing_campaigns WHERE id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
            $check->execute([$itemId]);
            if ($check->fetch()) {
                $restored = true;
                $message = 'Campaign already restored';
            } else {
                $message = 'Campaign not found';
            }
        }
    }
    
    // ---- DAILY WORK ----
    elseif ($itemType === 'daily_work' || $itemType === 'dailywork') {
        $restoreStmt = $pdo->prepare("
            UPDATE dailywork 
            SET is_deleted = 0, deleted_by_department = NULL, deleted_by_admin = NULL, deleted_at = NULL
            WHERE id = ?
        ");
        $restoreStmt->execute([$itemId]);
        if ($restoreStmt->rowCount() > 0 || $restoreStmt->errorCode() === '00000') {
            $restored = true;
            $message = 'Daily work restored successfully';
        } else {
            $check = $pdo->prepare("SELECT id FROM dailywork WHERE id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
            $check->execute([$itemId]);
            if ($check->fetch()) {
                $restored = true;
                $message = 'Daily work already restored';
            } else {
                $message = 'Daily work not found';
            }
        }
    }
    
    // ---- UNSUPPORTED ----
    else {
        echo json_encode([
            'success' => false,
            'message' => 'Unsupported item type: ' . $itemType,
            'debug' => [
                'item_type' => $itemType,
                'item_id' => $itemId,
                'item_name' => $itemName,
                'supported_types' => [
                    'project', 'sent_project',
                    'report', 'sent_report',
                    'uploaded_report', 'sent_uploaded_report',
                    'project_document', 'sent_project_document',
                    'budget_request', 'fund_request',
                    'transaction', 'employee',
                    'visitor', 'campaign', 'marketing_campaign',
                    'daily_work', 'dailywork'
                ]
            ]
        ]);
        exit;
    }
    
    // ============================================================
    // REMOVE FROM RECYCLE BIN IF RESTORED
    // ============================================================
    if ($restored) {
        $delStmt = $pdo->prepare("DELETE FROM recycle_bin WHERE id = ?");
        $delStmt->execute([$recycleId]);
        
        echo json_encode([
            'success' => true,
            'message' => $message,
            'item_type' => $itemType,
            'item_id' => $itemId,
            'item_name' => $itemName
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $message,
            'item_type' => $itemType,
            'item_id' => $itemId
        ]);
    }
    
} catch(PDOException $e) {
    error_log('Restore error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>