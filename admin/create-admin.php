<?php
/**
 * Create Admin User Utility
 * Run this script once to create additional admin users
 * 
 * Usage: Access via browser or command line
 * Example: http://localhost/crosslife/admin/create-admin.php
 * 
 * SECURITY: Delete this file after creating admin users!
 */

require_once 'config/config.php';

// Only allow if not in production or add IP restriction
// For security, you should delete this file after use

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $full_name = sanitize($_POST['full_name'] ?? '');
    $role = $_POST['role'] ?? 'admin';
    
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $error = 'All fields are required.';
    } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
        $error = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';
    } else {
        try {
            $db = getDB();
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $db->prepare("INSERT INTO admins (username, email, password, full_name, role, status) VALUES (?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$username, $email, $hashedPassword, $full_name, $role]);
            
            $success = 'Admin user created successfully!';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = 'Username or email already exists.';
            } else {
                $error = 'Error creating user: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin User</title>
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f5f5;
            padding: 2rem;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4">Create Admin User</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username *</label>
                <input type="text" class="form-control" name="username" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Email *</label>
                <input type="email" class="form-control" name="email" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Full Name *</label>
                <input type="text" class="form-control" name="full_name" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Password * (min <?php echo PASSWORD_MIN_LENGTH; ?> characters)</label>
                <input type="password" class="form-control" name="password" required minlength="<?php echo PASSWORD_MIN_LENGTH; ?>">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Role *</label>
                <select class="form-control" name="role" required>
                    <option value="admin">Admin</option>
                    <option value="editor">Editor</option>
                    <option value="super_admin">Super Admin</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Create Admin User</button>
            <a href="login.php" class="btn btn-secondary">Back to Login</a>
        </form>
        
        <div class="alert alert-warning mt-4">
            <strong>Security Note:</strong> Delete this file after creating admin users!
        </div>
    </div>
</body>
</html>

