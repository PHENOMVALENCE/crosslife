<?php
require_once 'config/config.php';

// Destroy session
session_destroy();

// Redirect to login
redirect('login.php', 'You have been logged out successfully.', 'success');

