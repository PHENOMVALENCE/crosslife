<?php
/**
 * Feedback Form Handler
 * Saves feedback to database
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
        
        // Validate and set feedback type
        $validTypes = ['praise', 'suggestion', 'concern', 'testimony', 'other'];
        $feedback_type = !empty($_POST['feedback_type']) ? sanitize($_POST['feedback_type']) : 'other';
        if (!in_array($feedback_type, $validTypes)) {
            $feedback_type = 'other';
        }
        
        $message = sanitize($_POST['message'] ?? '');
        
        if (empty($message)) {
            $response['message'] = 'Please enter your feedback.';
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
        $stmt = $db->prepare("INSERT INTO feedback (name, email, feedback_type, message, status) VALUES (?, ?, ?, ?, 'new')");
        $stmt->execute([$name ?: null, $email ?: null, $feedback_type, $message]);
        
        // Send email notification (optional - will fail silently if PHPMailer not configured)
        if (file_exists(__DIR__ . '/../admin/config/email.php')) {
            require_once __DIR__ . '/../admin/config/email.php';
            try {
                sendFeedbackNotification($name ?: 'Anonymous', $email, $feedback_type, $message);
            } catch (Throwable $e) {
                // Log but don't fail the form submission
                error_log('Email notification failed: ' . $e->getMessage());
            }
        }
        
        $response['status'] = 'success';
        $response['message'] = 'Thank you for your feedback! We appreciate your input.';
    } catch (Exception $e) {
        $response['message'] = 'Failed to submit feedback. Please try again later.';
        error_log('Feedback form error: ' . $e->getMessage());
    }
} else {
    $response['message'] = 'Invalid request method.';
}

// Clean output buffer and send JSON
ob_end_clean();
echo json_encode($response);
exit;

