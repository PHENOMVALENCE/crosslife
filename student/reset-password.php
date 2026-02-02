<?php
/**
 * Reset password – set new password using token from email.
 */
require_once __DIR__ . '/../admin/config/config.php';
require_once __DIR__ . '/../includes/discipleship-functions.php';

if (isStudentLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$token = trim($_GET['token'] ?? '');
$error = '';
$success = false;
$minLen = defined('PASSWORD_MIN_LENGTH') ? PASSWORD_MIN_LENGTH : 8;

if ($token === '') {
    $error = 'Invalid or missing reset link.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if (strlen($newPassword) < $minLen) {
        $error = 'Password must be at least ' . $minLen . ' characters.';
    } elseif ($newPassword !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, student_id FROM student_password_resets WHERE token = ? AND expires_at > NOW() AND used_at IS NULL");
        $stmt->execute([$token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            $error = 'This reset link has expired or already been used. Request a new one from the login page.';
        } else {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $db->prepare("UPDATE discipleship_students SET password_hash = ? WHERE id = ?")->execute([$hash, $row['student_id']]);
            $db->prepare("UPDATE student_password_resets SET used_at = NOW() WHERE id = ?")->execute([$row['id']]);
            $success = true;
        }
    }
}

$pageTitle = 'Reset password';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset password – School of Christ Academy</title>
    <link href="../assets/img/logo.png" rel="icon">
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/elms-discipleship.css" rel="stylesheet">
    <style>
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: var(--content-bg, #f8f9fa); }
        .reset-box { max-width: 420px; width: 100%; padding: 2rem; background: var(--card-bg); border-radius: 8px; border-top: var(--card-border-top); box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
    </style>
</head>
<body>
    <div class="reset-box">
        <h1 class="h4 mb-3" style="color: var(--text-primary);">Set new password</h1>
        <?php if ($success): ?>
            <p class="text-success mb-3">Your password has been updated. You can now log in.</p>
            <a href="login.php" class="btn btn-elms-accent">Log in</a>
        <?php elseif ($token === '' || $error): ?>
            <?php if ($error): ?><div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <a href="login.php" class="btn btn-elms-accent">Back to login</a>
        <?php else: ?>
            <?php if ($error): ?><div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">New password *</label>
                    <input type="password" class="form-control" name="new_password" required minlength="<?php echo $minLen; ?>">
                    <div class="form-text">At least <?php echo $minLen; ?> characters</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm new password *</label>
                    <input type="password" class="form-control" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-elms-accent w-100">Update password</button>
            </form>
            <p class="mt-3 mb-0 text-center small"><a href="login.php">Back to login</a></p>
        <?php endif; ?>
    </div>
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
