<?php
/**
 * Email Configuration
 * PHPMailer Setup for CrossLife Mission Network
 */

// Email Settings - Update these with your SMTP credentials
define('SMTP_HOST', 'smtp.gmail.com'); // Change to your SMTP server
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com'); // Change to your email
define('SMTP_PASSWORD', 'your-app-password'); // Change to your email password or app password
define('SMTP_ENCRYPTION', 'tls'); // 'tls' or 'ssl'
define('SMTP_FROM_EMAIL', 'karibu@crosslife.org');
define('SMTP_FROM_NAME', 'CrossLife Mission Network');
define('SMTP_REPLY_TO_EMAIL', 'karibu@crosslife.org');
define('SMTP_REPLY_TO_NAME', 'CrossLife Mission Network');

// Recipient Email (where form submissions are sent)
define('CONTACT_EMAIL', 'karibu@crosslife.org');
define('PRAYER_REQUEST_EMAIL', 'karibu@crosslife.org');
define('FEEDBACK_EMAIL', 'karibu@crosslife.org');

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
    
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
    
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
    
    return sendEmail($to, $emailSubject, $body, $altBody);
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
    
    return sendEmail($to, $emailSubject, $body, $altBody);
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
    
    return sendEmail($to, $emailSubject, $body, $altBody);
}

