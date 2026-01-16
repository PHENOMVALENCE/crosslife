<?php
/**
 * Prayer Request Form Handler
 * Saves prayer requests to database
 */

// Prevent any output before JSON
ob_start();

require_once __DIR__ . '/../admin/config/config.php';

// Clear any output buffer
ob_clean();

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'An error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $prayer_request = sanitize($_POST['prayer_request'] ?? '');
        
        if (empty($prayer_request)) {
            $response['message'] = 'Please enter your prayer request.';
            ob_end_clean();
            echo json_encode($response);
            exit;
        }
        
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['message'] = 'Please enter a valid email address.';
            ob_end_clean();
            echo json_encode($response);
            exit;
        }
        
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO prayer_requests (name, email, prayer_request, status) VALUES (?, ?, ?, 'new')");
        $stmt->execute([$name ?: null, $email ?: null, $prayer_request]);
        
        // Send email notification (optional - will fail silently if PHPMailer not configured)
        if (file_exists(__DIR__ . '/../admin/config/email.php')) {
            require_once __DIR__ . '/../admin/config/email.php';
            try {
                sendPrayerRequestNotification($name ?: 'Anonymous', $email, $prayer_request);
            } catch (Exception $e) {
                // Log but don't fail the form submission
                error_log('Email notification failed: ' . $e->getMessage());
            }
        }
        
        $response['status'] = 'success';
        $response['message'] = 'Thank you for your prayer request. We will be praying with you.';
    } catch (Exception $e) {
        $response['message'] = 'Failed to submit prayer request. Please try again later.';
        error_log('Prayer request form error: ' . $e->getMessage());
    }
} else {
    $response['message'] = 'Invalid request method.';
}

// Clean output buffer and send JSON
ob_end_clean();
echo json_encode($response);
exit;

