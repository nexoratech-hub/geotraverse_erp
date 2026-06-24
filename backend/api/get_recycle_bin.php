<?php
// backend/api/get_recycle_bin.php

// ============================================================
// GET RECYCLE BIN - INAONYESHA DATA ZA DEPARTMENT HUSIKA TU
// ============================================================

// ... (headers, database connection, parameters) ...

try {
    $items = [];

    // ============================================================
    // CHAGUA RECORDS ZILIZOFUTWA NA DEPARTMENT HII TU
    // ============================================================
    $query = "SELECT 
                rb.id,
                rb.item_id,
                rb.item_type,
                rb.item_name,
                rb.deleted_by_department_id,
                rb.deleted_by_admin,
                rb.deleted_by_name,
                rb.restored,
                rb.restored_at,
                rb.created_at
              FROM recycle_bin rb
              WHERE rb.restored = 0
                AND rb.deleted_by_department_id = ?
              ORDER BY rb.created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$department_id]);
    $items = $stmt->fetchAll();

    // ============================================================
    // ONGEZA MAELEZO KWA KILA ITEM
    // ============================================================
    foreach ($items as &$item) {
        if ($item['item_type'] === 'project') {
            $query = "SELECT name, amount, status FROM projects WHERE id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$item['item_id']]);
            $details = $stmt->fetch();
            if ($details) {
                $item['details'] = $details;
            }
        } elseif ($item['item_type'] === 'sent_project') {
            $query = "SELECT project_name, amount FROM sent_projects WHERE id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$item['item_id']]);
            $details = $stmt->fetch();
            if ($details) {
                $item['details'] = $details;
            }
        }
    }

    sendJson([
        'success' => true,
        'data' => $items,
        'count' => count($items),
        'department_id' => $department_id,
        'message' => 'Recycle bin items retrieved successfully'
    ]);

} catch (PDOException $e) {
    sendJson([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => []
    ]);
} catch (Exception $e) {
    sendJson([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>