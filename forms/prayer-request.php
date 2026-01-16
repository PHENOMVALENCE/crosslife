<?php
/**
 * Prayer Request Form Handler
 * Saves prayer requests to database
 */

require_once __DIR__ . '/../admin/config/config.php';

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'An error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $prayer_request = sanitize($_POST['prayer_request'] ?? '');
        
        if (empty($prayer_request)) {
            $response['message'] = 'Please enter your prayer request.';
            echo json_encode($response);
            exit;
        }
        
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['message'] = 'Please enter a valid email address.';
            echo json_encode($response);
            exit;
        }
        
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO prayer_requests (name, email, prayer_request, status) VALUES (?, ?, ?, 'new')");
        $stmt->execute([$name ?: null, $email ?: null, $prayer_request]);
        
        $response['status'] = 'success';
        $response['message'] = 'Thank you for your prayer request. We will be praying with you.';
    } catch (Exception $e) {
        $response['message'] = 'Failed to submit prayer request. Please try again later.';
        error_log('Prayer request form error: ' . $e->getMessage());
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);

