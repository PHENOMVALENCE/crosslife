<?php
/**
 * Prayer Request Form Handler
 * Saves prayer requests to database
 */

// Prevent any output before JSON
ob_start();

// Disable error output for JSON responses
@error_reporting(0);
@ini_set('display_errors', 0);

require_once __DIR__ . '/../admin/config/config.php';
require_once __DIR__ . '/../includes/EmailService.php';

// Clear any output buffer
ob_clean();

header('Content-Type: application/json; charset=utf-8');

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
        
        // Send email notification + auto-reply via EmailService
        try {
            if (class_exists('EmailService')) {
                $emailService = new EmailService();
                $emailService->sendPrayerNotification([
                    'name' => $name,
                    'email' => $email,
                    'prayer_request' => $prayer_request,
                ]);
            }
        } catch (Throwable $e) {
            error_log('Prayer request email error: ' . $e->getMessage());
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

