<?php
require_once __DIR__ . '/includes/session_manager.php';

$session = new SessionManager();
$session->destroy();

header("Location: login.php");
exit;
?>