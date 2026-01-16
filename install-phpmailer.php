<?php
/**
 * PHPMailer Installation Helper
 * This script helps download and set up PHPMailer
 */

echo "PHPMailer Installation Helper\n";
echo "============================\n\n";

$vendorPath = __DIR__ . '/vendor/phpmailer/phpmailer';
$targetPath = $vendorPath . '/src';

// Check if already installed
if (file_exists($targetPath . '/PHPMailer.php')) {
    echo "✓ PHPMailer appears to be already installed at: $targetPath\n";
    exit(0);
}

echo "PHPMailer is not installed.\n\n";
echo "Installation Options:\n\n";
echo "Option 1: Using Composer (Recommended)\n";
echo "  Run: composer require phpmailer/phpmailer\n\n";

echo "Option 2: Manual Download\n";
echo "  1. Download from: https://github.com/PHPMailer/PHPMailer/releases\n";
echo "  2. Extract the 'src' folder to: $targetPath\n";
echo "  3. Ensure these files exist:\n";
echo "     - $targetPath/PHPMailer.php\n";
echo "     - $targetPath/SMTP.php\n";
echo "     - $targetPath/Exception.php\n\n";

echo "After installation, configure SMTP settings in:\n";
echo "  admin/config/email.php\n\n";

echo "See PHPMailer_SETUP.md for detailed instructions.\n";

