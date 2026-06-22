<?php
header('Content-Type: text/plain');
echo password_hash('123456', PASSWORD_DEFAULT);
?>