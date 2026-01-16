# CrossLife Mission Network - Admin Dashboard

Complete backend admin dashboard for managing the CrossLife Mission Network website.

## Installation Instructions

### 1. Database Setup

1. Open phpMyAdmin or MySQL command line
2. Import the database schema:
   ```sql
   source database/schema.sql
   ```
   Or manually execute the SQL file located at `database/schema.sql`

3. The database will be created with the name `crosslife` and all necessary tables

### 2. Database Configuration

Edit `admin/config/database.php` and update the database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Your MySQL username
define('DB_PASS', '');            // Your MySQL password
define('DB_NAME', 'crosslife');
```

### 3. Default Admin Credentials

After importing the database, you can login with:
- **Username:** admin
- **Password:** admin123

**⚠️ IMPORTANT:** Change the default password immediately after first login!

### 4. File Permissions

Ensure the following directories are writable (if you plan to add file uploads):
- `assets/img/uploads/` (create this directory if it doesn't exist)

### 5. Access the Admin Panel

Navigate to: `http://localhost/crosslife_2/admin/login.php`

## Features

### Content Management
- **Sermons:** Manage video and audio sermons with YouTube integration
- **Events:** Create and manage church events and calendar
- **Ministries:** Manage ministry information and details
- **Discipleship Programs:** Manage School of Christ Academy programs
- **Leadership:** Manage leadership team profiles

### Communication Management
- **Contact Inquiries:** View and manage contact form submissions
- **Prayer Requests:** Manage prayer requests from visitors
- **Feedback:** Review and respond to user feedback

### System
- **Settings:** Configure site settings
- **Dashboard:** Overview statistics and recent activities

## File Structure

```
admin/
├── config/
│   ├── database.php      # Database connection
│   └── config.php        # Configuration and helper functions
├── includes/
│   ├── header.php        # Admin header and sidebar
│   └── footer.php        # Admin footer
├── index.php             # Dashboard
├── login.php             # Admin login
├── logout.php            # Logout handler
├── sermons.php           # Sermons management
├── events.php            # Events management
├── ministries.php        # Ministries management
├── discipleship.php      # Discipleship programs management
├── leadership.php        # Leadership management
├── contacts.php          # Contact inquiries
├── prayer-requests.php   # Prayer requests
├── feedback.php          # Feedback management
└── settings.php          # Site settings

database/
└── schema.sql            # Database schema

forms/
├── contact.php           # Contact form handler
├── prayer-request.php    # Prayer request handler
└── feedback.php          # Feedback form handler
```

## Security Features

- Password hashing using PHP's `password_hash()`
- Session-based authentication
- Session timeout (1 hour)
- CSRF token support (ready for implementation)
- Input sanitization
- SQL injection prevention using prepared statements
- XSS protection through output escaping

## Customization

### Changing Session Timeout

Edit `admin/config/config.php`:
```php
define('SESSION_TIMEOUT', 3600); // Change to desired seconds
```

### Changing Items Per Page

Edit `admin/config/config.php`:
```php
define('ITEMS_PER_PAGE', 10); // Change to desired number
```

## Troubleshooting

### Database Connection Error
- Check database credentials in `admin/config/database.php`
- Ensure MySQL service is running
- Verify database `crosslife` exists

### Login Not Working
- Verify default admin user exists in database
- Check password hash in database
- Clear browser cookies and try again

### Forms Not Saving
- Check database connection
- Verify form action URLs are correct
- Check PHP error logs

## Support

For issues or questions, check:
1. PHP error logs
2. MySQL error logs
3. Browser console for JavaScript errors

## Notes

- All passwords are hashed using PHP's `password_hash()` function
- The admin panel uses the same theme as the main website
- All forms save data to MySQL database
- The system is ready for production use with proper security measures

