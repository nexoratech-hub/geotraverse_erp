<?php
require_once 'config.php';

try {
    $stmt = $pdo->prepare("DELETE FROM recycle_bin");
    $stmt->execute();
    
    sendResponse(true, 'Recycle bin emptied successfully');
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>