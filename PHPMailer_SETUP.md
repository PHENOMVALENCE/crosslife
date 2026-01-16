# PHPMailer Setup Guide

## Installation

PHPMailer can be installed in two ways:

### Option 1: Using Composer (Recommended)

1. Install Composer if you don't have it: https://getcomposer.org/
2. Navigate to your project directory
3. Run: `composer require phpmailer/phpmailer`

### Option 2: Manual Installation

1. Download PHPMailer from: https://github.com/PHPMailer/PHPMailer
2. Extract the files to: `vendor/phpmailer/phpmailer/`
3. The structure should be:
   ```
   vendor/
     phpmailer/
       phpmailer/
         src/
           PHPMailer.php
           SMTP.php
           Exception.php
   ```

## Configuration

1. Open `admin/config/email.php`
2. Update the following constants with your SMTP credentials:

```php
define('SMTP_HOST', 'smtp.gmail.com'); // Your SMTP server
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com'); // Your email
define('SMTP_PASSWORD', 'your-app-password'); // Your email password
define('SMTP_ENCRYPTION', 'tls'); // 'tls' or 'ssl'
define('SMTP_FROM_EMAIL', 'karibu@crosslife.org');
define('SMTP_FROM_NAME', 'CrossLife Mission Network');
```

## Gmail Setup (if using Gmail)

1. Enable 2-Step Verification on your Google account
2. Generate an App Password:
   - Go to Google Account settings
   - Security → 2-Step Verification → App passwords
   - Generate a new app password for "Mail"
   - Use this app password in `SMTP_PASSWORD`

## Other Email Providers

### Outlook/Hotmail
```php
define('SMTP_HOST', 'smtp-mail.outlook.com');
define('SMTP_PORT', 587);
define('SMTP_ENCRYPTION', 'tls');
```

### Yahoo
```php
define('SMTP_HOST', 'smtp.mail.yahoo.com');
define('SMTP_PORT', 587);
define('SMTP_ENCRYPTION', 'tls');
```

### Custom SMTP
Use your email provider's SMTP settings.

## Testing

After configuration, test the email functionality by submitting a contact form. Check the PHP error log if emails aren't sending.

## Troubleshooting

1. **Emails not sending**: Check PHP error log for details
2. **Authentication failed**: Verify SMTP credentials
3. **Connection timeout**: Check firewall/port settings
4. **PHPMailer not found**: Ensure files are in correct location

## Notes

- Email sending is optional - forms will still save to database even if email fails
- Email errors are logged but don't prevent form submission
- For production, disable SMTP debug mode

