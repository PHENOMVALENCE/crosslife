<?php
/**
 * Student Registration - School of Christ Academy
 */
require_once __DIR__ . '/../admin/config/config.php';
require_once __DIR__ . '/../includes/discipleship-functions.php';

$googleEnabled = !empty(GOOGLE_CLIENT_ID) && !empty(GOOGLE_CLIENT_SECRET);
$googleAuthUrl = '';
if ($googleEnabled) {
    $redirectUri = rtrim(SITE_URL, '/') . '/student/google-callback.php';
    $googleAuthUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
        'scope' => 'email profile',
        'redirect_uri' => $redirectUri,
        'response_type' => 'code',
        'client_id' => GOOGLE_CLIENT_ID,
        'access_type' => 'online',
        'prompt' => 'select_account'
    ]);
}

if (isStudentLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
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
            $stmt = $db->prepare("INSERT INTO discipleship_students (email, password_hash, full_name, phone, status) VALUES (?, ?, ?, ?, 'pending')");
            $stmt->execute([$email, $hash, $full_name, $phone ?: null]);
            $_SESSION['flash_message'] = 'Registration submitted! Your account is pending admin approval. You will receive an email when approved and can then log in to start your learning journey.';
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
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <style>
        :root { --brand: #c85716; --brand-dark: #a34612; --bg-dark: #0f0f0f; }
        * { box-sizing: border-box; }
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: var(--bg-dark); font-family: 'DM Sans', 'Inter', sans-serif; padding: 20px; }
        .auth-wrapper { max-width: 460px; width: 100%; }
        .auth-card { background: #1a1a1a; border-radius: 20px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.06); }
        .auth-header { padding: 2rem 2rem; text-align: center; background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%); }
        .auth-header img { width: 64px; height: 64px; border-radius: 14px; margin-bottom: 0.75rem; box-shadow: 0 4px 14px rgba(0,0,0,0.2); }
        .auth-header h1 { font-size: 1.4rem; font-weight: 700; color: #fff; margin: 0; letter-spacing: -0.02em; }
        .auth-header p { margin: 0.25rem 0 0; color: rgba(255,255,255,0.9); font-size: 0.9rem; }
        .auth-body { padding: 2rem; }
        .form-label { font-weight: 500; color: #e5e5e5; margin-bottom: 0.4rem; }
        .form-control { background: #262626; border: 1px solid #404040; color: #fff; padding: 0.75rem 1rem; border-radius: 10px; }
        .form-control:focus { background: #2d2d2d; border-color: var(--brand); color: #fff; box-shadow: 0 0 0 3px rgba(200,87,22,0.2); }
        .form-control::placeholder { color: #737373; }
        .btn-register { width: 100%; padding: 0.85rem; background: var(--brand); color: #fff; border: none; border-radius: 10px; font-weight: 600; font-size: 1rem; transition: all 0.2s; }
        .btn-register:hover { background: var(--brand-dark); color: #fff; transform: translateY(-1px); }
        .divider { display: flex; align-items: center; margin: 1.5rem 0; color: #737373; font-size: 0.85rem; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #404040; }
        .divider span { padding: 0 1rem; }
        .btn-google { width: 100%; padding: 0.75rem 1rem; background: #262626; color: #e5e5e5; border: 1px solid #404040; border-radius: 10px; font-weight: 500; display: flex; align-items: center; justify-content: center; gap: 0.75rem; text-decoration: none; transition: all 0.2s; }
        .btn-google:hover { background: #333; color: #fff; border-color: #525252; }
        .auth-footer { text-align: center; margin-top: 1.5rem; }
        .auth-footer a { color: var(--brand); text-decoration: none; font-weight: 500; }
        .auth-footer a:hover { color: var(--brand-dark); text-decoration: underline; }
        .back-link { margin-top: 1rem; }
        .back-link a { color: #737373; font-size: 0.9rem; text-decoration: none; }
        .back-link a:hover { color: #a3a3a3; }
        .pending-note { font-size: 0.85rem; color: #a3a3a3; margin-top: 1rem; padding: 0.75rem; background: #262626; border-radius: 8px; }
        .alert { border-radius: 10px; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <img src="../assets/img/logo.png" alt="CrossLife">
                <h1>School of Christ Academy</h1>
                <p>Create your student account</p>
            </div>
            <div class="auth-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger py-3"><i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($googleEnabled): ?>
                <a href="<?php echo htmlspecialchars($googleAuthUrl); ?>" class="btn btn-google mb-3">
                    <svg width="20" height="20" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                    Continue with Google
                </a>
                <div class="divider"><span>or register with email</span></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name *</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" placeholder="John Doe">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder="you@example.com">
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone (optional)</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" placeholder="+255 700 000 000">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password *</label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="<?php echo defined('PASSWORD_MIN_LENGTH') ? PASSWORD_MIN_LENGTH : 8; ?>" placeholder="••••••••">
                        <small class="text-muted">At least <?php echo defined('PASSWORD_MIN_LENGTH') ? PASSWORD_MIN_LENGTH : 8; ?> characters</small>
                    </div>
                    <button type="submit" class="btn btn-register"><i class="bi bi-person-plus me-2"></i>Create account</button>
                </form>

                <div class="pending-note">
                    <i class="bi bi-info-circle me-2"></i>After registering, an admin will review and approve your account. You'll be notified when you can log in and start learning.
                </div>

                <p class="text-center text-muted small mt-4 mb-0">Already have an account? <a href="login.php">Sign in</a></p>
                <div class="back-link text-center">
                    <a href="../index.html"><i class="bi bi-arrow-left me-1"></i>Back to site</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
