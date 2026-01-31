<?php
/**
 * Configuration File
 * CrossLife Mission Network Admin Panel
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Site Configuration
define('SITE_NAME', 'CrossLife Mission Network');
define('SITE_URL', 'http://localhost/crosslife_2');
define('ADMIN_URL', SITE_URL . '/admin');
define('UPLOAD_DIR', '../assets/img/uploads/');
define('UPLOAD_URL', SITE_URL . '/assets/img/uploads/');
// Audio sermons: stored under project root /uploads/audio/
define('AUDIO_UPLOAD_DIR', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'audio' . DIRECTORY_SEPARATOR);
define('AUDIO_UPLOAD_WEB', '/uploads/audio/');

// Security
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
define('PASSWORD_MIN_LENGTH', 8);

// Pagination
define('ITEMS_PER_PAGE', 10);

// Timezone
date_default_timezone_set('Africa/Dar_es_Salaam');

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once __DIR__ . '/database.php';

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    if (!isset($_SESSION['admin_id'])) {
        return false;
    }
    
    // Check session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_destroy();
        return false;
    }
    
    // Update last activity
    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Require login - redirect if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . ADMIN_URL . '/login.php');
        exit;
    }
}

/**
 * Get current admin user
 */
function getCurrentAdmin() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT id, username, email, full_name, role FROM admins WHERE id = ? AND status = 'active'");
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch();
}

/**
 * Sanitize input
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'F j, Y') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * Format datetime for display
 */
function formatDateTime($datetime, $format = 'F j, Y g:i A') {
    if (empty($datetime)) return '';
    return date($format, strtotime($datetime));
}

/**
 * Redirect with message
 */
function redirect($url, $message = '', $type = 'success') {
    if ($message) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    header('Location: ' . $url);
    exit;
}

/**
 * Get and clear flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'success';
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

/**
 * Validate required fields
 */
function validateRequired($fields, $data) {
    $errors = [];
    foreach ($fields as $field) {
        if (empty($data[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
        }
    }
    return $errors;
}

/**
 * Validate email
 */
function validateEmail($email) {
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    return true;
}

/**
 * Handle database errors gracefully
 */
function handleDBError($e, $defaultMessage = 'A database error occurred.') {
    error_log("Database error: " . $e->getMessage());
    $message = $defaultMessage;
    if (defined('DEBUG') && DEBUG) {
        $message .= ' Error: ' . $e->getMessage();
    }
    return $message;
}

