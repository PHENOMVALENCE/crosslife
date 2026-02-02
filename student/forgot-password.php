<?php
/**
 * Forgot password – request reset link by email.
 */
require_once __DIR__ . '/../admin/config/config.php';
require_once __DIR__ . '/../includes/discipleship-functions.php';

if (isStudentLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = false;
$email = trim($_POST['email'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $email !== '') {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, full_name FROM discipleship_students WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($student) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600);
            try {
                $stmt = $db->prepare("INSERT INTO student_password_resets (student_id, token, expires_at) VALUES (?, ?, ?)");
                $stmt->execute([$student['id'], $token, $expires]);
                $resetUrl = (defined('SITE_URL') ? rtrim(SITE_URL, '/') : '') . '/student/reset-password.php?token=' . urlencode($token);
                $siteName = defined('SITE_NAME') ? SITE_NAME : 'School of Christ Academy';
                $body = "Hello " . ($student['full_name'] ?: '') . ",\n\nYou requested a password reset for your account at {$siteName}.\n\nClick the link below to set a new password (valid for 1 hour):\n\n{$resetUrl}\n\nIf you did not request this, please ignore this email.\n\n— {$siteName}";
                $subject = 'Password reset – ' . $siteName;
                $headers = 'From: ' . (defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost')) . "\r\n" . 'Content-Type: text/plain; charset=UTF-8';
                @mail($email, $subject, $body, $headers);
            } catch (PDOException $e) {
                $error = 'Password reset is not set up yet. Please contact the administrator.';
            }
        }
        if ($error === '') {
            $success = true;
        }
    }
}

$pageTitle = 'Forgot password';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot password – School of Christ Academy</title>
    <link href="../assets/img/logo.png" rel="icon">
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/elms-discipleship.css" rel="stylesheet">
    <style>
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: var(--content-bg, #f8f9fa); }
        .forgot-box { max-width: 420px; width: 100%; padding: 2rem; background: var(--card-bg); border-radius: 8px; border-top: var(--card-border-top); box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
    </style>
</head>
<body>
    <div class="forgot-box">
        <h1 class="h4 mb-3" style="color: var(--text-primary);">Forgot password</h1>
        <?php if ($success): ?>
            <p class="text-success mb-3">If an account exists for that email, we have sent you a link to reset your password. Check your inbox and spam folder.</p>
            <a href="login.php" class="btn btn-elms-accent">Back to login</a>
        <?php else: ?>
            <?php if ($error): ?><div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <p class="text-muted small mb-3">Enter your email and we will send you a link to reset your password.</p>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($email); ?>" required autofocus>
                </div>
                <button type="submit" class="btn btn-elms-accent w-100">Send reset link</button>
            </form>
            <p class="mt-3 mb-0 text-center small"><a href="login.php">Back to login</a></p>
        <?php endif; ?>
    </div>
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
