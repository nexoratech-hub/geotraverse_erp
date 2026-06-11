<?php
require_once 'config.php';

$sql = "SELECT * FROM recycle_bin WHERE restored = 0 ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$items = $stmt->fetchAll();

sendResponse(true, 'Recycle bin items retrieved', $items);
?>