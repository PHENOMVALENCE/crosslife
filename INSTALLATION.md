# CrossLife Mission Network - Installation Guide

## Quick Start

### Step 1: Database Setup

1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Click on "Import" tab
3. Select the file: `database/schema.sql`
4. Click "Go" to import

Alternatively, using MySQL command line:
```bash
mysql -u root -p < database/schema.sql
```

### Step 2: Configure Database

Edit `admin/config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Your MySQL username
define('DB_PASS', '');            // Your MySQL password (leave empty for XAMPP default)
define('DB_NAME', 'crosslife');
```

### Step 3: Access Admin Panel

1. Navigate to: `http://localhost/crosslife_2/admin/login.php`
2. Login with:
   - **Username:** `admin`
   - **Password:** `admin123`

### Step 4: Change Default Password

After logging in, you should change the default password for security.

## Default Admin Account

- **Username:** admin
- **Email:** admin@crosslife.org (default, can be changed)
- **Password:** admin123

## Database Structure

The database `crosslife` includes the following tables:
- `admins` - Admin users
- `sermons` - Sermons and teachings
- `events` - Church events
- `ministries` - Ministry information
- `discipleship_programs` - Discipleship programs
- `leadership` - Leadership team
- `contact_inquiries` - Contact form submissions
- `prayer_requests` - Prayer requests
- `feedback` - User feedback
- `site_settings` - Site configuration

## Form Integration

All forms on the website are now connected to the database:
- Contact form (`contacts.html`) → Saves to `contact_inquiries` table
- Prayer request form (`contacts.html`) → Saves to `prayer_requests` table
- Feedback form (`index.html` & `contacts.html`) → Saves to `feedback` table

## Admin Panel Features

### Dashboard
- Overview statistics
- Recent activities
- Quick access to all modules

### Content Management
- **Sermons:** Add/edit/delete sermons with YouTube links
- **Events:** Manage church events and calendar
- **Ministries:** Manage ministry details
- **Discipleship:** Manage School of Christ Academy programs
- **Leadership:** Manage leadership profiles

### Communication
- **Contact Inquiries:** View and manage contact submissions
- **Prayer Requests:** Manage prayer requests
- **Feedback:** Review and respond to feedback

### Settings
- Configure site information
- Update contact details

## Security Notes

1. **Change default password immediately**
2. The admin panel uses session-based authentication
3. All passwords are hashed using PHP's `password_hash()`
4. SQL injection protection via prepared statements
5. XSS protection through output escaping

## Troubleshooting

### Can't connect to database
- Check MySQL service is running (XAMPP Control Panel)
- Verify database credentials in `admin/config/database.php`
- Ensure database `crosslife` exists

### Login not working
- Verify admin user exists: `SELECT * FROM crosslife.admins;`
- Check password hash is correct
- Clear browser cookies

### Forms not saving
- Check database connection
- Verify form action URLs point to correct files
- Check PHP error logs in XAMPP

## File Permissions

Ensure these directories exist and are writable:
- `assets/img/uploads/` (for future file uploads)

## Next Steps

1. Import the database schema
2. Configure database credentials
3. Login to admin panel
4. Change default password
5. Start adding content!

## Support

For issues:
1. Check PHP error logs: `C:\xampp\php\logs\php_error_log`
2. Check MySQL error logs
3. Verify all files are in correct locations
4. Ensure XAMPP services (Apache & MySQL) are running

