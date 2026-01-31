<?php
/**
 * Email Configuration
 * PHPMailer Setup for CrossLife Mission Network
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Email Settings - Update these with your SMTP credentials
// IMPORTANT: Username must exactly match your Gmail address
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'mwiganivalence@gmail.com'); // Primary sending account
define('SMTP_PASSWORD', 'cwrg wxki urrn lgkn'); // App password provided by user
define('SMTP_ENCRYPTION', 'tls'); // 'tls' or 'ssl'
define('SMTP_FROM_EMAIL', 'mwiganivalence@gmail.com');
define('SMTP_FROM_NAME', 'CrossLife Mission Network');
define('SMTP_REPLY_TO_EMAIL', 'karibu@crosslife.org');
define('SMTP_REPLY_TO_NAME', 'CrossLife Mission Network');

// Recipient Email (where form submissions are sent)
// All submissions go to this mailbox as requested
define('CONTACT_EMAIL', 'mwiganivalence@gmail.com');
define('PRAYER_REQUEST_EMAIL', 'mwiganivalence@gmail.com');
define('FEEDBACK_EMAIL', 'mwiganivalence@gmail.com');

/**
 * Send Email using PHPMailer
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $body Email body (HTML)
 * @param string $altBody Plain text alternative
 * @param array $attachments Array of file paths to attach
 * @return bool True on success, false on failure
 */
function sendEmail($to, $subject, $body, $altBody = '', $attachments = []) {
    // Check if PHPMailer is available
    $phpmailerPath = __DIR__ . '/../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    
    if (!file_exists($phpmailerPath)) {
        error_log('PHPMailer not found. Please install it via Composer or download manually.');
        return false;
    }
    
    require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/SMTP.php';
    require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/Exception.php';
    
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';
        
        // Enable verbose debug output (disable in production)
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        
        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to);
        $mail->addReplyTo(SMTP_REPLY_TO_EMAIL, SMTP_REPLY_TO_NAME);
        
        // Attachments
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                if (file_exists($attachment)) {
                    $mail->addAttachment($attachment);
                }
            }
        }
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = $altBody ?: strip_tags($body);
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Send Contact Form Notification
 */
function sendContactNotification($name, $email, $phone, $subject, $message) {
    $to = CONTACT_EMAIL;
    $emailSubject = "New Contact Inquiry: " . $subject;
    
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #c85716; color: white; padding: 20px; text-align: center; }
            .content { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; color: #c85716; }
            .message-box { background: white; padding: 15px; border-left: 4px solid #c85716; margin-top: 15px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>New Contact Inquiry</h2>
            </div>
            <div class='content'>
                <div class='field'>
                    <span class='label'>Name:</span> " . htmlspecialchars($name) . "
                </div>
                <div class='field'>
                    <span class='label'>Email:</span> <a href='mailto:" . htmlspecialchars($email) . "'>" . htmlspecialchars($email) . "</a>
                </div>
                " . (!empty($phone) ? "<div class='field'><span class='label'>Phone:</span> " . htmlspecialchars($phone) . "</div>" : "") . "
                <div class='field'>
                    <span class='label'>Subject:</span> " . htmlspecialchars($subject) . "
                </div>
                <div class='message-box'>
                    <div class='label'>Message:</div>
                    <p>" . nl2br(htmlspecialchars($message)) . "</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $altBody = "New Contact Inquiry\n\n";
    $altBody .= "Name: $name\n";
    $altBody .= "Email: $email\n";
    if (!empty($phone)) $altBody .= "Phone: $phone\n";
    $altBody .= "Subject: $subject\n\n";
    $altBody .= "Message:\n$message\n";
    
    // Send to admin
    $adminSent = sendEmail($to, $emailSubject, $body, $altBody);
    
    // Optional auto‑reply to visitor
    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $replySubject = 'Thank you for contacting CrossLife Mission Network';
        $replyBody = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #c85716; color: white; padding: 20px; text-align: center; }
                .content { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
                .message-box { background: white; padding: 15px; border-left: 4px solid #c85716; margin-top: 15px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Thank You for Reaching Out</h2>
                </div>
                <div class='content'>
                    <p>Dear " . htmlspecialchars($name ?: 'Beloved in Christ') . ",</p>
                    <p>Thank you for contacting CrossLife Mission Network. We have received your message and a member of our team will respond as soon as possible.</p>
                    <div class='message-box'>
                        <p><strong>Your message:</strong></p>
                        <p>" . nl2br(htmlspecialchars($message)) . "</p>
                    </div>
                    <p>Grace and Peace be multiplied to you in Jesus' Name.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        $replyAlt = "Thank you for contacting CrossLife Mission Network.\n\nYour message:\n$message\n";
        sendEmail($email, $replySubject, $replyBody, $replyAlt);
    }
    
    return $adminSent;
}

/**
 * Send Prayer Request Notification
 */
function sendPrayerRequestNotification($name, $email, $prayer_request) {
    $to = PRAYER_REQUEST_EMAIL;
    $emailSubject = "New Prayer Request" . (!empty($name) ? " from " . $name : "");
    
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #dc3545; color: white; padding: 20px; text-align: center; }
            .content { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; color: #dc3545; }
            .prayer-box { background: white; padding: 15px; border-left: 4px solid #dc3545; margin-top: 15px; font-style: italic; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>New Prayer Request</h2>
            </div>
            <div class='content'>
                " . (!empty($name) ? "<div class='field'><span class='label'>Name:</span> " . htmlspecialchars($name) . "</div>" : "<div class='field'><span class='label'>Name:</span> Anonymous</div>") . "
                " . (!empty($email) ? "<div class='field'><span class='label'>Email:</span> <a href='mailto:" . htmlspecialchars($email) . "'>" . htmlspecialchars($email) . "</a></div>" : "") . "
                <div class='prayer-box'>
                    <div class='label'>Prayer Request:</div>
                    <p>" . nl2br(htmlspecialchars($prayer_request)) . "</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $altBody = "New Prayer Request\n\n";
    if (!empty($name)) $altBody .= "Name: $name\n";
    if (!empty($email)) $altBody .= "Email: $email\n";
    $altBody .= "\nPrayer Request:\n$prayer_request\n";
    
    // Send to admin
    $adminSent = sendEmail($to, $emailSubject, $body, $altBody);
    
    // Optional auto‑reply to requester
    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $replySubject = 'Thank you for your prayer request';
        $replyBody = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #dc3545; color: white; padding: 20px; text-align: center; }
                .content { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
                .prayer-box { background: white; padding: 15px; border-left: 4px solid #dc3545; margin-top: 15px; font-style: italic; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>We Are Praying With You</h2>
                </div>
                <div class='content'>
                    <p>Dear " . htmlspecialchars($name ?: 'Beloved in Christ') . ",</p>
                    <p>Thank you for sharing your prayer request with CrossLife Mission Network. Our team is standing with you in prayer.</p>
                    <div class='prayer-box'>
                        <p><strong>Your prayer request:</strong></p>
                        <p>" . nl2br(htmlspecialchars($prayer_request)) . "</p>
                    </div>
                    <p>Grace and Peace be multiplied to you in Jesus' Name.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        $replyAlt = "We are praying with you.\n\nYour prayer request:\n$prayer_request\n";
        sendEmail($email, $replySubject, $replyBody, $replyAlt);
    }
    
    return $adminSent;
}

/**
 * Send Feedback Notification
 */
function sendFeedbackNotification($name, $email, $feedback_type, $message) {
    $to = FEEDBACK_EMAIL;
    $emailSubject = "New Feedback: " . ucfirst($feedback_type);
    
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #ffc107; color: #333; padding: 20px; text-align: center; }
            .content { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; color: #ffc107; }
            .feedback-box { background: white; padding: 15px; border-left: 4px solid #ffc107; margin-top: 15px; }
            .type-badge { display: inline-block; background: #ffc107; color: #333; padding: 5px 10px; border-radius: 3px; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>New Feedback Submission</h2>
            </div>
            <div class='content'>
                " . (!empty($name) ? "<div class='field'><span class='label'>Name:</span> " . htmlspecialchars($name) . "</div>" : "<div class='field'><span class='label'>Name:</span> Anonymous</div>") . "
                " . (!empty($email) ? "<div class='field'><span class='label'>Email:</span> <a href='mailto:" . htmlspecialchars($email) . "'>" . htmlspecialchars($email) . "</a></div>" : "") . "
                <div class='field'>
                    <span class='label'>Type:</span> <span class='type-badge'>" . ucfirst($feedback_type) . "</span>
                </div>
                <div class='feedback-box'>
                    <div class='label'>Feedback:</div>
                    <p>" . nl2br(htmlspecialchars($message)) . "</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $altBody = "New Feedback Submission\n\n";
    if (!empty($name)) $altBody .= "Name: $name\n";
    if (!empty($email)) $altBody .= "Email: $email\n";
    $altBody .= "Type: " . ucfirst($feedback_type) . "\n\n";
    $altBody .= "Feedback:\n$message\n";
    
    // Send to admin
    $adminSent = sendEmail($to, $emailSubject, $body, $altBody);
    
    // Optional auto‑reply to sender
    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $replySubject = 'Thank you for your feedback';
        $replyBody = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #ffc107; color: #333; padding: 20px; text-align: center; }
                .content { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
                .feedback-box { background: white; padding: 15px; border-left: 4px solid #ffc107; margin-top: 15px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Thank You for Your Feedback</h2>
                </div>
                <div class='content'>
                    <p>Dear " . htmlspecialchars($name ?: 'Beloved in Christ') . ",</p>
                    <p>Thank you for sharing your feedback with CrossLife Mission Network. We truly appreciate your input.</p>
                    <div class='feedback-box'>
                        <p><strong>Your feedback:</strong></p>
                        <p>" . nl2br(htmlspecialchars($message)) . "</p>
                    </div>
                    <p>Grace and Peace be multiplied to you in Jesus' Name.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        $replyAlt = "Thank you for your feedback.\n\nYour feedback:\n$message\n";
        sendEmail($email, $replySubject, $replyBody, $replyAlt);
    }
    
    return $adminSent;
}

