<?php
/**
 * Student Login - School of Christ Academy (Discipleship)
 */
require_once __DIR__ . '/../admin/config/config.php';
require_once __DIR__ . '/../includes/discipleship-functions.php';

if (isStudentLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($email) || empty($password)) {
        $error = 'Please enter email and password.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, email, password_hash, full_name, status FROM discipleship_students WHERE email = ?");
        $stmt->execute([$email]);
        $student = $stmt->fetch();
        if ($student && password_verify($password, $student['password_hash'])) {
            if ($student['status'] === 'active') {
                $_SESSION['student_id'] = (int) $student['id'];
                $_SESSION['student_last_activity'] = time();
                $db->prepare("UPDATE discipleship_students SET last_login = NOW() WHERE id = ?")->execute([$student['id']]);
                header('Location: dashboard.php');
                exit;
            }
            $error = 'Your account has been deactivated.';
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

$flash = getFlashMessage();
$siteName = SITE_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login - School of Christ Academy - <?php echo htmlspecialchars($siteName); ?></title>
    <link href="../assets/img/logo.png" rel="icon">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <style>
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #1a1715 0%, #000 100%); font-family: var(--default-font); }
        .login-box { max-width: 420px; width: 100%; background: var(--surface-color); border-radius: 16px; box-shadow: 0 12px 40px rgba(0,0,0,0.3); overflow: hidden; margin: 20px; }
        .login-head { background: linear-gradient(135deg, var(--accent-color) 0%, color-mix(in srgb, var(--accent-color), black 20%) 100%); padding: 2rem; text-align: center; color: var(--contrast-color); }
        .login-head img { width: 70px; height: 70px; border-radius: 10px; margin-bottom: 0.75rem; }
        .login-head h1 { font-size: 1.5rem; margin: 0; }
        .login-head p { margin: 0.25rem 0 0; opacity: 0.9; font-size: 0.95rem; }
        .login-body { padding: 2rem; }
        .form-control { padding: 0.75rem 1rem; border-radius: 8px; }
        .btn-login { width: 100%; padding: 0.75rem; background: var(--accent-color); color: var(--contrast-color); border: none; border-radius: 8px; font-weight: 600; }
        .btn-login:hover { background: color-mix(in srgb, var(--accent-color), black 10%); color: var(--contrast-color); }
        .back-link { text-align: center; margin-top: 1.25rem; }
        .back-link a { color: var(--accent-color); }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="login-head">
            <img src="../assets/img/logo.png" alt="CrossLife">
            <h1>School of Christ Academy</h1>
            <p>Student Portal</p>
        </div>
        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger py-2"><i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type'] === 'success' ? 'success' : 'danger'; ?> py-2"><?php echo htmlspecialchars($flash['message']); ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required autofocus value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-login"><i class="bi bi-box-arrow-in-right me-2"></i>Login</button>
            </form>
            <p class="text-center text-muted small mt-3 mb-0">Don't have an account? <a href="register.php">Register</a></p>
            <div class="back-link">
                <a href="../index.html"><i class="bi bi-arrow-left me-1"></i>Back to site</a>
            </div>
        </div>
    </div>
</body>
</html>
