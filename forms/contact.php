<?php
/**
 * Contact Form Handler
 * Saves contact inquiries to database
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
        $phone = sanitize($_POST['phone'] ?? '');
        $subject = sanitize($_POST['subject'] ?? '');
        $message = sanitize($_POST['message'] ?? '');
        
        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            $response['message'] = 'Please fill in all required fields.';
            ob_end_clean();
            echo json_encode($response);
            exit;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['message'] = 'Please enter a valid email address.';
            ob_end_clean();
            echo json_encode($response);
            exit;
        }
        
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO contact_inquiries (name, email, phone, subject, message, status) VALUES (?, ?, ?, ?, ?, 'new')");
        $stmt->execute([$name, $email, $phone, $subject, $message]);
        
        // Send email notification + auto-reply via EmailService (will fail silently on error)
        try {
            if (class_exists('EmailService')) {
                $emailService = new EmailService();
                $emailService->sendContactNotification([
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'subject' => $subject,
                    'message' => $message,
                ]);
            }
        } catch (Throwable $e) {
            error_log('Contact email error: ' . $e->getMessage());
        }
        
        $response['status'] = 'success';
        $response['message'] = 'Your message has been sent. Thank you!';
    } catch (Exception $e) {
        $response['message'] = 'Failed to send message. Please try again later.';
        error_log('Contact form error: ' . $e->getMessage());
    }
} else {
    $response['message'] = 'Invalid request method.';
}

// Clean output buffer and send JSON
ob_end_clean();
echo json_encode($response);
exit;
