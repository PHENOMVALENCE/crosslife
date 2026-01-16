<?php
/**
 * Password Hash Generator Utility
 * Simple tool to generate password hashes for admin users
 * 
 * SECURITY: Delete this file after use or restrict access!
 */

require_once 'config/config.php';

$input = $_POST['password'] ?? '';
$hashed = '';
$algorithm = $_POST['algorithm'] ?? 'bcrypt';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($input)) {
    switch ($algorithm) {
        case 'bcrypt':
            $hashed = password_hash($input, PASSWORD_DEFAULT);
            break;
        case 'argon2i':
            $hashed = password_hash($input, PASSWORD_ARGON2I);
            break;
        case 'argon2id':
            $hashed = password_hash($input, PASSWORD_ARGON2ID);
            break;
        case 'md5':
            $hashed = md5($input);
            break;
        case 'sha1':
            $hashed = sha1($input);
            break;
        case 'sha256':
            $hashed = hash('sha256', $input);
            break;
        default:
            $hashed = password_hash($input, PASSWORD_DEFAULT);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Hash Generator - <?php echo SITE_NAME; ?></title>
    
    <!-- Favicons -->
    <link href="../assets/img/logo.jpeg" rel="icon">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
    
    <!-- Vendor CSS Files -->
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Main CSS File -->
    <link href="../assets/css/main.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #000000 0%, #1a1715 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--default-font);
            padding: 2rem;
        }
        
        .hash-container {
            background: var(--surface-color);
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 700px;
            width: 100%;
        }
        
        .hash-header {
            background: linear-gradient(135deg, var(--accent-color) 0%, color-mix(in srgb, var(--accent-color), black 20%) 100%);
            padding: 2rem;
            text-align: center;
            color: var(--contrast-color);
        }
        
        .hash-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
        }
        
        .hash-header p {
            margin: 0.5rem 0 0;
            opacity: 0.9;
        }
        
        .hash-body {
            padding: 2.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--heading-color);
        }
        
        .form-control {
            width: 100%;
            padding: 0.875rem 1.25rem;
            border: 2px solid rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(200, 87, 22, 0.1);
        }
        
        .btn-hash {
            width: 100%;
            padding: 0.875rem;
            background: var(--accent-color);
            color: var(--contrast-color);
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-hash:hover {
            background: color-mix(in srgb, var(--accent-color), black 10%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(200, 87, 22, 0.4);
        }
        
        .result-box {
            margin-top: 1.5rem;
            padding: 1.5rem;
            background: rgba(200, 87, 22, 0.05);
            border: 2px solid rgba(200, 87, 22, 0.2);
            border-radius: 8px;
        }
        
        .result-box label {
            display: block;
            font-weight: 600;
            color: var(--accent-color);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .result-box output {
            display: block;
            padding: 1rem;
            background: var(--surface-color);
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            word-break: break-all;
            color: var(--default-color);
            min-height: 60px;
        }
        
        .copy-btn {
            margin-top: 0.75rem;
            padding: 0.5rem 1rem;
            background: var(--accent-color);
            color: var(--contrast-color);
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .copy-btn:hover {
            background: color-mix(in srgb, var(--accent-color), black 10%);
        }
        
        .copy-btn.copied {
            background: #28a745;
        }
        
        .info-box {
            margin-top: 1.5rem;
            padding: 1rem;
            background: rgba(13, 110, 253, 0.1);
            border-left: 4px solid #0d6efd;
            border-radius: 6px;
            font-size: 0.9rem;
        }
        
        .info-box strong {
            color: #0d6efd;
        }
        
        .back-link {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .back-link a {
            color: var(--accent-color);
            text-decoration: none;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
        
        .algorithm-info {
            font-size: 0.85rem;
            color: var(--default-color);
            opacity: 0.7;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="hash-container">
        <div class="hash-header">
            <h1><i class="bi bi-key me-2"></i>Password Hash Generator</h1>
            <p>Generate secure password hashes</p>
        </div>
        
        <div class="hash-body">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="password">Enter Password/Word to Hash</label>
                    <input type="text" class="form-control" id="password" name="password" value="<?php echo htmlspecialchars($input); ?>" placeholder="Enter your password or word" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="algorithm">Hashing Algorithm</label>
                    <select class="form-control" id="algorithm" name="algorithm">
                        <option value="bcrypt" <?php echo $algorithm === 'bcrypt' ? 'selected' : ''; ?>>BCrypt (Recommended for passwords)</option>
                        <option value="argon2id" <?php echo $algorithm === 'argon2id' ? 'selected' : ''; ?>>Argon2ID (Most secure)</option>
                        <option value="argon2i" <?php echo $algorithm === 'argon2i' ? 'selected' : ''; ?>>Argon2I</option>
                        <option value="sha256" <?php echo $algorithm === 'sha256' ? 'selected' : ''; ?>>SHA-256</option>
                        <option value="sha1" <?php echo $algorithm === 'sha1' ? 'selected' : ''; ?>>SHA-1</option>
                        <option value="md5" <?php echo $algorithm === 'md5' ? 'selected' : ''; ?>>MD5 (Not recommended)</option>
                    </select>
                    <div class="algorithm-info">
                        <strong>BCrypt</strong> is recommended for storing passwords in the database.
                    </div>
                </div>
                
                <button type="submit" class="btn-hash">
                    <i class="bi bi-hash me-2"></i>Generate Hash
                </button>
            </form>
            
            <?php if (!empty($hashed)): ?>
                <div class="result-box">
                    <label>Generated Hash:</label>
                    <output id="hashOutput"><?php echo htmlspecialchars($hashed); ?></output>
                    <button type="button" class="copy-btn" onclick="copyHash()">
                        <i class="bi bi-clipboard me-2"></i>Copy Hash
                    </button>
                </div>
                
                <div class="info-box">
                    <strong><i class="bi bi-info-circle me-2"></i>Usage:</strong><br>
                    Copy the hash above and use it in your database. For admin passwords, use BCrypt hashes.<br>
                    <strong>Example SQL:</strong><br>
                    <code style="font-size: 0.85rem;">UPDATE admins SET password = '<?php echo htmlspecialchars($hashed); ?>' WHERE username = 'admin';</code>
                </div>
            <?php endif; ?>
            
            <div class="back-link">
                <a href="login.php">
                    <i class="bi bi-arrow-left me-2"></i>Back to Login
                </a>
            </div>
        </div>
    </div>
    
    <script>
        function copyHash() {
            const hashOutput = document.getElementById('hashOutput');
            const copyBtn = document.querySelector('.copy-btn');
            
            // Create a temporary textarea
            const textarea = document.createElement('textarea');
            textarea.value = hashOutput.textContent;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            
            // Update button
            const originalText = copyBtn.innerHTML;
            copyBtn.innerHTML = '<i class="bi bi-check me-2"></i>Copied!';
            copyBtn.classList.add('copied');
            
            setTimeout(() => {
                copyBtn.innerHTML = originalText;
                copyBtn.classList.remove('copied');
            }, 2000);
        }
        
        // Auto-focus on input
        document.getElementById('password').focus();
    </script>
</body>
</html>

