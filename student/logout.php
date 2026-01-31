<?php
require_once __DIR__ . '/../admin/config/config.php';
unset($_SESSION['student_id'], $_SESSION['student_last_activity']);
$_SESSION['flash_message'] = 'You have been logged out.';
$_SESSION['flash_type'] = 'success';
header('Location: login.php');
exit;
