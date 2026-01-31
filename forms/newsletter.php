<?php
/**
 * Newsletter Subscription Handler
 * Saves newsletter subscriptions to database
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
        $email = sanitize($_POST['email'] ?? '');
        $name = sanitize($_POST['name'] ?? '');
        
        if (empty($email)) {
            $response['message'] = 'Please enter your email address.';
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
        
        // Check if email already exists
        $checkStmt = $db->prepare("SELECT id, status FROM newsletter_subscriptions WHERE email = ?");
        $checkStmt->execute([$email]);
        $existing = $checkStmt->fetch();
        
        if ($existing) {
            if ($existing['status'] === 'unsubscribed') {
                // Resubscribe
                $stmt = $db->prepare("UPDATE newsletter_subscriptions SET status = 'active', name = ?, unsubscribed_at = NULL, updated_at = NOW() WHERE email = ?");
                $stmt->execute([$name ?: null, $email]);
                $response['status'] = 'success';
                $response['message'] = 'Thank you for resubscribing to our newsletter!';
            } else {
                // Already subscribed
                $response['status'] = 'success';
                $response['message'] = 'You are already subscribed to our newsletter.';
            }
        } else {
            // New subscription
            $stmt = $db->prepare("INSERT INTO newsletter_subscriptions (email, name, status) VALUES (?, ?, 'active')");
            $stmt->execute([$email, $name ?: null]);
            $response['status'] = 'success';
            $response['message'] = 'Thank you for subscribing to our newsletter!';
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Duplicate entry
            $response['message'] = 'This email is already subscribed.';
        } else {
            $response['message'] = 'Failed to subscribe. Please try again later.';
            error_log('Newsletter subscription error: ' . $e->getMessage());
        }
    } catch (Exception $e) {
        $response['message'] = 'Failed to subscribe. Please try again later.';
        error_log('Newsletter subscription error: ' . $e->getMessage());
    }
} else {
    $response['message'] = 'Invalid request method.';
}

// Clean output buffer and send JSON
ob_end_clean();
echo json_encode($response);
exit;
