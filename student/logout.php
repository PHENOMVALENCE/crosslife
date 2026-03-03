<?php
/**
 * Student Logout - School of Christ Academy
 */
require_once __DIR__ . '/../admin/config/config.php';

// Clear student session
unset($_SESSION['student_id'], $_SESSION['student_last_activity']);

// Redirect to login
$_SESSION['flash_message'] = 'You have been logged out successfully.';
$_SESSION['flash_type'] = 'success';
header('Location: login.php');
exit;
