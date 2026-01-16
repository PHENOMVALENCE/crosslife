<?php
/**
 * Feedback Form Handler
 * Saves feedback to database
 */

require_once __DIR__ . '/../admin/config/config.php';

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'An error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $feedback_type = $_POST['feedback_type'] ?? 'other';
        $message = sanitize($_POST['message'] ?? '');
        
        if (empty($message)) {
            $response['message'] = 'Please enter your feedback.';
            echo json_encode($response);
            exit;
        }
        
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['message'] = 'Please enter a valid email address.';
            echo json_encode($response);
            exit;
        }
        
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO feedback (name, email, feedback_type, message, status) VALUES (?, ?, ?, ?, 'new')");
        $stmt->execute([$name ?: null, $email ?: null, $feedback_type, $message]);
        
        $response['status'] = 'success';
        $response['message'] = 'Thank you for your feedback! We appreciate your input.';
    } catch (Exception $e) {
        $response['message'] = 'Failed to submit feedback. Please try again later.';
        error_log('Feedback form error: ' . $e->getMessage());
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);

