<?php
/**
 * Google OAuth callback - School of Christ Academy
 * Handles the return from Google sign-in. Creates or logs in student account.
 */
require_once __DIR__ . '/../admin/config/config.php';
require_once __DIR__ . '/../includes/discipleship-functions.php';

$clientId = defined('GOOGLE_CLIENT_ID') ? GOOGLE_CLIENT_ID : '';
$clientSecret = defined('GOOGLE_CLIENT_SECRET') ? GOOGLE_CLIENT_SECRET : '';
$redirectUri = (defined('SITE_URL') ? rtrim(SITE_URL, '/') : '') . '/student/google-callback.php';

if (empty($clientId) || empty($clientSecret)) {
    $_SESSION['flash_message'] = 'Google Sign-In is not configured. Please use email and password.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: login.php');
    exit;
}

$error = '';
if (isset($_GET['error'])) {
    $error = $_GET['error'] === 'access_denied' ? 'You cancelled the sign-in.' : 'Google sign-in failed. Please try again.';
    $_SESSION['flash_message'] = $error;
    $_SESSION['flash_type'] = 'danger';
    header('Location: login.php');
    exit;
}

if (empty($_GET['code'])) {
    $_SESSION['flash_message'] = 'Invalid Google response. Please try again.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: login.php');
    exit;
}

// Exchange code for tokens
$tokenUrl = 'https://oauth2.googleapis.com/token';
$postData = [
    'code' => $_GET['code'],
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'redirect_uri' => $redirectUri,
    'grant_type' => 'authorization_code'
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $tokenUrl,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($postData),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$tokenInfo = $response ? json_decode($response, true) : null;
if (!$tokenInfo || empty($tokenInfo['access_token'])) {
    $_SESSION['flash_message'] = 'Could not get access token from Google. Please try again.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: login.php');
    exit;
}

// Fetch user info
$userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . urlencode($tokenInfo['access_token']);
$userJson = @file_get_contents($userInfoUrl);
$user = $userJson ? json_decode($userJson, true) : null;
if (!$user || empty($user['email'])) {
    $_SESSION['flash_message'] = 'Could not retrieve your profile from Google. Please try again.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: login.php');
    exit;
}

$email = trim($user['email']);
$fullName = trim($user['name'] ?? $email);
$googleId = $user['id'] ?? '';

$db = getDB();
$stmt = $db->prepare("SELECT id, email, full_name, status, google_id FROM discipleship_students WHERE (google_id IS NOT NULL AND google_id = ?) OR email = ?");
$stmt->execute([$googleId, $email]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if ($student) {
    if ($student['status'] === 'pending') {
        $_SESSION['flash_message'] = 'Your account is pending admin approval. You will be notified when you can log in.';
        $_SESSION['flash_type'] = 'warning';
        header('Location: login.php');
        exit;
    }
    if ($student['status'] !== 'active') {
        $_SESSION['flash_message'] = 'Your account has been deactivated.';
        $_SESSION['flash_type'] = 'danger';
        header('Location: login.php');
        exit;
    }
    // Update google_id if not set (for existing email-based accounts linking Google)
    if ($googleId && empty($student['google_id'])) {
        $upd = $db->prepare("UPDATE discipleship_students SET google_id = ?, last_login = NOW() WHERE id = ?");
        $upd->execute([$googleId, $student['id']]);
    } else {
        $db->prepare("UPDATE discipleship_students SET last_login = NOW() WHERE id = ?")->execute([$student['id']]);
    }
    $_SESSION['student_id'] = (int) $student['id'];
    $_SESSION['student_last_activity'] = time();
    header('Location: dashboard.php');
    exit;
}

// New user - register with status pending (requires admin approval)
$stmt = $db->prepare("INSERT INTO discipleship_students (email, password_hash, full_name, google_id, status) VALUES (?, NULL, ?, ?, 'pending')");
$stmt->execute([$email, $fullName, $googleId ?: null]);
$_SESSION['flash_message'] = 'Account created with Google. Your registration is pending admin approval. You will receive an email when approved.';
$_SESSION['flash_type'] = 'success';
header('Location: login.php');
exit;
