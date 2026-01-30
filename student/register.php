<?php
/**
 * Student Registration - School of Christ Academy
 */
require_once __DIR__ . '/../admin/config/config.php';
require_once __DIR__ . '/../includes/discipleship-functions.php';

if (isStudentLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $full_name = trim(strip_tags($_POST['full_name'] ?? ''));
    $phone = trim(strip_tags($_POST['phone'] ?? ''));
    if (empty($email) || empty($password) || empty($full_name)) {
        $error = 'Please fill in email, full name, and password.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < (defined('PASSWORD_MIN_LENGTH') ? PASSWORD_MIN_LENGTH : 8)) {
        $error = 'Password must be at least ' . (defined('PASSWORD_MIN_LENGTH') ? PASSWORD_MIN_LENGTH : 8) . ' characters.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM discipleship_students WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'An account with this email already exists. Please log in.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO discipleship_students (email, password_hash, full_name, phone, status) VALUES (?, ?, ?, ?, 'active')");
            $stmt->execute([$email, $hash, $full_name, $phone ?: null]);
            $_SESSION['flash_message'] = 'Registration successful. You can now log in.';
            $_SESSION['flash_type'] = 'success';
            header('Location: login.php');
            exit;
        }
    }
}

$siteName = SITE_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - School of Christ Academy - <?php echo htmlspecialchars($siteName); ?></title>
    <link href="../assets/img/logo.png" rel="icon">
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <style>
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #1a1715 0%, #000 100%); font-family: var(--default-font); }
        .reg-box { max-width: 440px; width: 100%; background: var(--surface-color); border-radius: 16px; box-shadow: 0 12px 40px rgba(0,0,0,0.3); overflow: hidden; margin: 20px; }
        .reg-head { background: linear-gradient(135deg, var(--accent-color) 0%, color-mix(in srgb, var(--accent-color), black 20%) 100%); padding: 1.75rem; text-align: center; color: var(--contrast-color); }
        .reg-head h1 { font-size: 1.35rem; margin: 0; }
        .reg-body { padding: 2rem; }
        .form-control { padding: 0.75rem 1rem; border-radius: 8px; }
        .btn-reg { width: 100%; padding: 0.75rem; background: var(--accent-color); color: var(--contrast-color); border: none; border-radius: 8px; font-weight: 600; }
        .btn-reg:hover { color: var(--contrast-color); background: color-mix(in srgb, var(--accent-color), black 10%); }
        .back-link { text-align: center; margin-top: 1rem; }
        .back-link a { color: var(--accent-color); }
    </style>
</head>
<body>
    <div class="reg-box">
        <div class="reg-head">
            <h1>School of Christ Academy</h1>
            <p class="mb-0 small opacity-90">Create your student account</p>
        </div>
        <div class="reg-body">
            <?php if ($error): ?>
                <div class="alert alert-danger py-2"><i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name *</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone (optional)</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password *</label>
                    <input type="password" class="form-control" id="password" name="password" required minlength="<?php echo defined('PASSWORD_MIN_LENGTH') ? PASSWORD_MIN_LENGTH : 8; ?>">
                    <small class="text-muted">At least <?php echo defined('PASSWORD_MIN_LENGTH') ? PASSWORD_MIN_LENGTH : 8; ?> characters</small>
                </div>
                <button type="submit" class="btn btn-reg"><i class="bi bi-person-plus me-2"></i>Register</button>
            </form>
            <p class="text-center text-muted small mt-3 mb-0">Already have an account? <a href="login.php">Log in</a></p>
            <div class="back-link">
                <a href="../index.html"><i class="bi bi-arrow-left me-1"></i>Back to site</a>
            </div>
        </div>
    </div>
</body>
</html>
