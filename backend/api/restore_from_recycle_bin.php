<?php
// backend/api/restore_from_recycle_bin.php

// ============================================================
// RESTORE - INAHITAJI DEPARTMENT ID NA INARESTORE KWA DEPARTMENT HUSIKA TU
// ============================================================

// ... (headers, database connection) ...

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['recycle_id']) || !isset($input['department_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields: recycle_id, department_id'
    ]);
    exit;
}

$recycle_id = intval($input['recycle_id']);
$department_id = intval($input['department_id']);

try {
    // ============================================================
    // 1. GET RECYCLE BIN ITEM - HAKIKISHA NI YA DEPARTMENT HII
    // ============================================================
    $query = "SELECT * FROM recycle_bin 
              WHERE id = ? 
                AND restored = 0 
                AND deleted_by_department_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$recycle_id, $department_id]);
    $item = $stmt->fetch();

    if (!$item) {
        echo json_encode([
            'success' => false,
            'message' => 'Item not found, already restored, or not from this department'
        ]);
        exit;
    }

    $item_id = intval($item['item_id']);
    $item_type = $item['item_type'];
    $item_name = $item['item_name'] ?? 'Item';

    $restored = false;
    $dailywork_restored = 0;

    // ============================================================
    // 2. RESTORE BASED ON ITEM TYPE - KWA DEPARTMENT HII TU
    // ============================================================
    if ($item_type === 'project') {
        // Restore original project - KWA DEPARTMENT HII TU
        $query = "UPDATE projects SET 
                  is_deleted = 0,
                  deleted_by_department = 0,
                  deleted_by_admin = 0,
                  deleted_at = NULL,
                  deleted_by_department_id = NULL
                  WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$item_id]);
        $restored = true;

        // Restore daily work
        $query = "UPDATE dailywork SET 
                  is_deleted = 0,
                  deleted_at = NULL
                  WHERE project_id = ? AND is_deleted = 1";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$item_id]);
        $dailywork_restored = $stmt->rowCount();

    } elseif ($item_type === 'sent_project') {
        // Restore sent project - KWA DEPARTMENT HII TU
        $query = "UPDATE sent_projects SET 
                  is_deleted = 0,
                  deleted_at = NULL
                  WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$item_id]);
        $restored = true;

        // Restore sent daily work
        if ($item_name) {
            $query = "UPDATE sent_dailywork SET 
                      is_deleted = 0,
                      deleted_at = NULL
                      WHERE dailywork_project_name = ? 
                        AND to_department_id = ? 
                        AND is_deleted = 1";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$item_name, $department_id]);
            $sent_dailywork_restored = $stmt->rowCount();
        }
    }

    // ============================================================
    // 3. MARK AS RESTORED
    // ============================================================
    if ($restored) {
        $query = "UPDATE recycle_bin SET 
                  restored = 1,
                  restored_at = NOW()
                  WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$recycle_id]);

        echo json_encode([
            'success' => true,
            'message' => 'Item restored successfully for department ' . $department_id,
            'data' => [
                'item_id' => $item_id,
                'item_type' => $item_type,
                'item_name' => $item_name,
                'department_id' => $department_id,
                'dailywork_restored' => $dailywork_restored,
                'sent_dailywork_restored' => $sent_dailywork_restored ?? 0,
                'note' => 'Restored only for department ' . $department_id
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to restore item'
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>