<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

/**
 * Central email service for CrossLife forms
 * Uses SMTP / constants defined in admin/config/database.php
 */
class EmailService
{
    private $smtp_host;
    private $smtp_port;
    private $smtp_user;
    private $smtp_pass;
    private $from_email;
    private $from_name;
    private $admin_email;

    public function __construct()
    {
        $this->smtp_host = SMTP_HOST;
        $this->smtp_port = SMTP_PORT;
        $this->smtp_user = SMTP_USER;
        $this->smtp_pass = SMTP_PASS;
        $this->from_email = SMTP_FROM_EMAIL;
        $this->from_name = SMTP_FROM_NAME;
        $this->admin_email = ADMIN_EMAIL;
    }

    /**
     * Initialize PHPMailer instance
     */
    private function initMailer(): PHPMailer
    {
        // PHPMailer is in the root directory: PHPMailer/src/PHPMailer.php
        $phpmailerPath = __DIR__ . '/../PHPMailer/src/PHPMailer.php';
        if (!file_exists($phpmailerPath)) {
            throw new Exception('PHPMailer is not found at PHPMailer/src/PHPMailer.php in project root.');
        }

        require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
        require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
        require_once __DIR__ . '/../PHPMailer/src/Exception.php';

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $this->smtp_host;
        $mail->SMTPAuth = true;
        $mail->Username = $this->smtp_user;
        $mail->Password = $this->smtp_pass;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $this->smtp_port;
        $mail->CharSet = 'UTF-8';
        $mail->SMTPDebug = SMTP::DEBUG_OFF; // set to DEBUG_SERVER for troubleshooting

        $mail->setFrom($this->from_email, $this->from_name);
        $mail->addReplyTo(SITE_EMAIL, SITE_NAME);

        return $mail;
    }

    /**
     * Core send method
     * All messages are addressed to ADMIN_EMAIL, with $to as CC (if different)
     */
    public function send(string $to, string $subject, string $message, bool $isHTML = true): bool
    {
        try {
            $mail = $this->initMailer();

            // Primary recipient is always the admin
            $mail->addAddress($this->admin_email, SITE_NAME);
            if (!empty($to) && filter_var($to, FILTER_VALIDATE_EMAIL) && $to !== $this->admin_email) {
                $mail->addCC($to);
            }

            $mail->Subject = $subject;
            if ($isHTML) {
                $mail->isHTML(true);
                $mail->Body = $message;
                $mail->AltBody = strip_tags($message);
            } else {
                $mail->isHTML(false);
                $mail->Body = $message;
            }

            return $mail->send();
        } catch (Exception $e) {
            error_log('EmailService send error: ' . $e->getMessage());
            if (isset($mail)) {
                error_log('PHPMailer error info: ' . $mail->ErrorInfo);
            }
            return false;
        }
    }

    /**
     * Contact form notification + auto-reply
     */
    public function sendContactNotification(array $data): bool
    {
        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        $phone = $data['phone'] ?? '';
        $subject = $data['subject'] ?? '';
        $message = $data['message'] ?? '';

        $adminSubject = 'New Contact Inquiry: ' . $subject;

        $adminBody = '
        <html><head><style>
            body{font-family:Arial,sans-serif;line-height:1.6;color:#333;}
            .container{max-width:600px;margin:0 auto;padding:20px;}
            .header{background:#4B2C5E;color:white;padding:20px;text-align:center;}
            .content{background:#f9f9f9;padding:20px;border:1px solid #ddd;}
            .field{margin-bottom:15px;}
            .label{font-weight:bold;color:#4B2C5E;}
            .message-box{background:white;padding:15px;border-left:4px solid #D4AF37;margin-top:15px;}
        </style></head><body>
            <div class="container">
                <div class="header"><h2>New Contact Inquiry</h2></div>
                <div class="content">
                    <div class="field"><span class="label">Name:</span> ' . htmlspecialchars($name) . '</div>
                    <div class="field"><span class="label">Email:</span> ' . htmlspecialchars($email) . '</div>' .
                    (!empty($phone) ? '<div class="field"><span class="label">Phone:</span> ' . htmlspecialchars($phone) . '</div>' : '') . '
                    <div class="field"><span class="label">Subject:</span> ' . htmlspecialchars($subject) . '</div>
                    <div class="message-box">
                        <div class="label">Message:</div>
                        <p>' . nl2br(htmlspecialchars($message)) . '</p>
                    </div>
                </div>
            </div>
        </body></html>';

        // Admin notification (admin always primary, user CC)
        $this->send($email, $adminSubject, $adminBody, true);

        // Auto-reply to user (also CCed to admin)
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $replySubject = 'Thank you for contacting ' . SITE_NAME;
            $replyBody = '
            <html><head><style>
                body{font-family:Arial,sans-serif;line-height:1.6;color:#333;}
                .container{max-width:600px;margin:0 auto;padding:20px;}
                .header{background:#4B2C5E;color:white;padding:20px;text-align:center;}
                .content{background:#f9f9f9;padding:20px;border:1px solid #ddd;}
                .message-box{background:white;padding:15px;border-left:4px solid #D4AF37;margin-top:15px;}
            </style></head><body>
                <div class="container">
                    <div class="header"><h2>Thank You for Reaching Out</h2></div>
                    <div class="content">
                        <p>Dear ' . htmlspecialchars($name ?: 'Beloved in Christ') . ',</p>
                        <p>Thank you for contacting ' . SITE_NAME . '. We have received your message and will get back to you as soon as possible.</p>
                        <div class="message-box">
                            <p><strong>Your message:</strong></p>
                            <p>' . nl2br(htmlspecialchars($message)) . '</p>
                        </div>
                        <p>Grace and peace be multiplied to you.</p>
                    </div>
                </div>
            </body></html>';

            $this->send($email, $replySubject, $replyBody, true);
        }

        return true;
    }

    /**
     * Prayer request notification + auto-reply
     */
    public function sendPrayerNotification(array $data): bool
    {
        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        $request = $data['prayer_request'] ?? '';

        $adminSubject = 'New Prayer Request' . (!empty($name) ? ' from ' . $name : '');

        $adminBody = '
        <html><head><style>
            body{font-family:Arial,sans-serif;line-height:1.6;color:#333;}
            .container{max-width:600px;margin:0 auto;padding:20px;}
            .header{background:#CD7F32;color:white;padding:20px;text-align:center;}
            .content{background:#f9f9f9;padding:20px;border:1px solid #ddd;}
            .field{margin-bottom:15px;}
            .label{font-weight:bold;color:#CD7F32;}
            .prayer-box{background:white;padding:15px;border-left:4px solid #CD7F32;margin-top:15px;font-style:italic;}
        </style></head><body>
            <div class="container">
                <div class="header"><h2>New Prayer Request</h2></div>
                <div class="content">
                    <div class="field"><span class="label">Name:</span> ' . htmlspecialchars($name ?: 'Anonymous') . '</div>' .
                    (!empty($email) ? '<div class="field"><span class="label">Email:</span> ' . htmlspecialchars($email) . '</div>' : '') . '
                    <div class="prayer-box">
                        <p><strong>Prayer request:</strong></p>
                        <p>' . nl2br(htmlspecialchars($request)) . '</p>
                    </div>
                </div>
            </div>
        </body></html>';

        $this->send($email, $adminSubject, $adminBody, true);

        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $replySubject = 'Thank you for your prayer request';
            $replyBody = '
            <html><head><style>
                body{font-family:Arial,sans-serif;line-height:1.6;color:#333;}
                .container{max-width:600px;margin:0 auto;padding:20px;}
                .header{background:#CD7F32;color:white;padding:20px;text-align:center;}
                .content{background:#f9f9f9;padding:20px;border:1px solid #ddd;}
                .prayer-box{background:white;padding:15px;border-left:4px solid #CD7F32;margin-top:15px;font-style:italic;}
            </style></head><body>
                <div class="container">
                    <div class="header"><h2>We Are Praying With You</h2></div>
                    <div class="content">
                        <p>Dear ' . htmlspecialchars($name ?: 'Beloved in Christ') . ',</p>
                        <p>Thank you for sharing your prayer request. Our team is standing with you in prayer.</p>
                        <div class="prayer-box">
                            <p><strong>Your prayer request:</strong></p>
                            <p>' . nl2br(htmlspecialchars($request)) . '</p>
                        </div>
                        <p>Grace and peace be multiplied to you.</p>
                    </div>
                </div>
            </body></html>';

            $this->send($email, $replySubject, $replyBody, true);
        }

        return true;
    }

    /**
     * Feedback notification + auto-reply
     */
    public function sendFeedbackNotification(array $data): bool
    {
        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        $type = $data['feedback_type'] ?? 'other';
        $message = $data['message'] ?? '';

        $adminSubject = 'New Feedback: ' . ucfirst($type);

        $adminBody = '
        <html><head><style>
            body{font-family:Arial,sans-serif;line-height:1.6;color:#333;}
            .container{max-width:600px;margin:0 auto;padding:20px;}
            .header{background:#D4AF37;color:#333;padding:20px;text-align:center;}
            .content{background:#f9f9f9;padding:20px;border:1px solid #ddd;}
            .field{margin-bottom:15px;}
            .label{font-weight:bold;color:#D4AF37;}
            .feedback-box{background:white;padding:15px;border-left:4px solid #D4AF37;margin-top:15px;}
        </style></head><body>
            <div class="container">
                <div class="header"><h2>New Feedback Submission</h2></div>
                <div class="content">
                    <div class="field"><span class="label">Name:</span> ' . htmlspecialchars($name ?: 'Anonymous') . '</div>' .
                    (!empty($email) ? '<div class="field"><span class="label">Email:</span> ' . htmlspecialchars($email) . '</div>' : '') . '
                    <div class="field"><span class="label">Type:</span> ' . htmlspecialchars(ucfirst($type)) . '</div>
                    <div class="feedback-box">
                        <p><strong>Feedback:</strong></p>
                        <p>' . nl2br(htmlspecialchars($message)) . '</p>
                    </div>
                </div>
            </div>
        </body></html>';

        $this->send($email, $adminSubject, $adminBody, true);

        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $replySubject = 'Thank you for your feedback';
            $replyBody = '
            <html><head><style>
                body{font-family:Arial,sans-serif;line-height:1.6;color:#333;}
                .container{max-width:600px;margin:0 auto;padding:20px;}
                .header{background:#D4AF37;color:#333;padding:20px;text-align:center;}
                .content{background:#f9f9f9;padding:20px;border:1px solid #ddd;}
                .feedback-box{background:white;padding:15px;border-left:4px solid #D4AF37;margin-top:15px;}
            </style></head><body>
                <div class="container">
                    <div class="header"><h2>Thank You for Your Feedback</h2></div>
                    <div class="content">
                        <p>Dear ' . htmlspecialchars($name ?: 'Beloved in Christ') . ',</p>
                        <p>Thank you for taking the time to share your feedback with us.</p>
                        <div class="feedback-box">
                            <p><strong>Your feedback:</strong></p>
                            <p>' . nl2br(htmlspecialchars($message)) . '</p>
                        </div>
                        <p>Grace and peace be multiplied to you.</p>
                    </div>
                </div>
            </body></html>';

            $this->send($email, $replySubject, $replyBody, true);
        }

        return true;
    }

    /**
     * Newsletter welcome + admin notification
     */
    public function sendNewsletterWelcome(string $email, ?string $name = null): bool
    {
        $displayName = $name ?: 'Beloved in Christ';

        // Welcome email to subscriber (admin CC)
        $welcomeSubject = 'Welcome to the ' . SITE_NAME . ' Newsletter';
        $welcomeBody = '
        <html><head><style>
            body{font-family:Arial,sans-serif;line-height:1.6;color:#333;}
            .container{max-width:600px;margin:0 auto;padding:20px;}
            .header{background:#4B2C5E;color:white;padding:20px;text-align:center;}
            .content{background:#f9f9f9;padding:20px;border:1px solid #ddd;}
        </style></head><body>
            <div class="container">
                <div class="header"><h2>Welcome to Our Newsletter</h2></div>
                <div class="content">
                    <p>Dear ' . htmlspecialchars($displayName) . ',</p>
                    <p>Thank you for subscribing to the ' . SITE_NAME . ' newsletter. You will receive updates, teachings, and important announcements.</p>
                    <p>Grace and peace be multiplied to you.</p>
                </div>
            </div>
        </body></html>';

        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->send($email, $welcomeSubject, $welcomeBody, true);
        }

        return true;
    }
}

