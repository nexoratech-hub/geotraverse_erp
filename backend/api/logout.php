<?php
session_start();
session_destroy();
sendResponse(true, 'Logged out successfully');
?>