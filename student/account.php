<?php
/**
 * Student account – profile and change password
 */
require_once __DIR__ . '/../admin/config/config.php';
require_once __DIR__ . '/../includes/discipleship-functions.php';
requireStudentLogin();

$student = getCurrentStudent();
$db = getDB();
$pageTitle = 'My account';
$profileSaved = false;
$passwordSaved = false;
$profileError = '';
$passwordError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $full_name = trim(strip_tags($_POST['full_name'] ?? ''));
        $email = trim($_POST['email'] ?? '');
        $phone = trim(strip_tags($_POST['phone'] ?? ''));
        if (empty($full_name)) {
            $profileError = 'Full name is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $profileError = 'Please enter a valid email address.';
        } else {
            $stmt = $db->prepare("SELECT id FROM discipleship_students WHERE email = ? AND id != ?");
            $stmt->execute([$email, $student['id']]);
            if ($stmt->fetch()) {
                $profileError = 'That email is already in use.';
            } else {
                $stmt = $db->prepare("UPDATE discipleship_students SET full_name = ?, email = ?, phone = ? WHERE id = ?");
                $stmt->execute([$full_name, $email, $phone ?: null, $student['id']]);
                $student = array_merge($student, ['full_name' => $full_name, 'email' => $email, 'phone' => $phone ?: null]);
                $profileSaved = true;
            }
        }
    }
    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $stmt = $db->prepare("SELECT password_hash FROM discipleship_students WHERE id = ?");
        $stmt->execute([$student['id']]);
        $row = $stmt->fetch();
        if (!$row || !password_verify($current, $row['password_hash'])) {
            $passwordError = 'Current password is incorrect.';
        } elseif (strlen($new) < (defined('PASSWORD_MIN_LENGTH') ? PASSWORD_MIN_LENGTH : 8)) {
            $passwordError = 'New password must be at least ' . (defined('PASSWORD_MIN_LENGTH') ? PASSWORD_MIN_LENGTH : 8) . ' characters.';
        } elseif ($new !== $confirm) {
            $passwordError = 'New password and confirmation do not match.';
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE discipleship_students SET password_hash = ? WHERE id = ?");
            $stmt->execute([$hash, $student['id']]);
            $passwordSaved = true;
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">My account</h1>
    <a href="dashboard.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Dashboard</a>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="account-section">
            <h2 class="h5 mb-3"><i class="bi bi-person me-2"></i>Profile</h2>
            <?php if ($profileSaved): ?>
                <div class="alert alert-success py-2">Profile updated successfully.</div>
            <?php endif; ?>
            <?php if ($profileError): ?>
                <div class="alert alert-danger py-2"><?php echo htmlspecialchars($profileError); ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="update_profile" value="1">
                <div class="mb-3">
                    <label class="form-label">Full name *</label>
                    <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($student['full_name'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email *</label>
                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($student['email'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone (optional)</label>
                    <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($student['phone'] ?? ''); ?>">
                </div>
                <button type="submit" class="btn btn-accent">Save profile</button>
            </form>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="account-section">
            <h2 class="h5 mb-3"><i class="bi bi-lock me-2"></i>Change password</h2>
            <?php if ($passwordSaved): ?>
                <div class="alert alert-success py-2">Password updated successfully.</div>
            <?php endif; ?>
            <?php if ($passwordError): ?>
                <div class="alert alert-danger py-2"><?php echo htmlspecialchars($passwordError); ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="change_password" value="1">
                <div class="mb-3">
                    <label class="form-label">Current password *</label>
                    <input type="password" class="form-control" name="current_password" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">New password *</label>
                    <input type="password" class="form-control" name="new_password" required minlength="<?php echo defined('PASSWORD_MIN_LENGTH') ? PASSWORD_MIN_LENGTH : 8; ?>">
                    <div class="form-text">At least <?php echo defined('PASSWORD_MIN_LENGTH') ? PASSWORD_MIN_LENGTH : 8; ?> characters</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm new password *</label>
                    <input type="password" class="form-control" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-accent">Change password</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
