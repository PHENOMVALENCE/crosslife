# MySQL Workbench Setup Guide for CrossLife

Complete guide to set up MySQL Workbench and integrate it with your CrossLife project.

## Step 1: Make Sure MySQL Server is Running

### Check if MySQL is Running
1. Press `Win + R`
2. Type `services.msc` and press Enter
3. Look for **MySQL80** (or MySQL)
4. If it says "Stopped", right-click → **Start**

**OR** use Command Prompt:
```bash
net start MySQL80
```

### Test MySQL Connection
Open Command Prompt and type:
```bash
mysql -u root -p
```
- Enter your MySQL root password (or press Enter if no password)
- If it connects, you're good! Type `EXIT;` to leave

---

## Step 2: Open MySQL Workbench

1. Open **MySQL Workbench** from Start Menu
2. You should see a connection (usually called "Local instance MySQL80" or similar)
3. Click on it to connect
4. Enter your root password if prompted

---

## Step 3: Create/Import the Database

### Method 1: Import Schema File (Easiest)

1. In MySQL Workbench, click **File** → **Open SQL Script**
2. Navigate to: `c:\Users\Pretty_Mk\CrossLife Mk code\crosslife\database\schema.sql`
3. Click **Open**
4. Click the **Execute** button (⚡ lightning bolt icon) or press `Ctrl + Shift + Enter`
5. Wait for "Script executed successfully" message
6. Refresh the left sidebar (click refresh icon) to see the `crosslife` database

### Method 2: Manual Import

1. In MySQL Workbench, click **Server** → **Data Import**
2. Select **Import from Self-Contained File**
3. Browse to: `c:\Users\Pretty_Mk\CrossLife Mk code\crosslife\database\schema.sql`
4. Under **Default Target Schema**, select **New** → Name it `crosslife`
5. Click **Start Import**
6. Wait for success message

### Method 3: Create Database Manually

1. In MySQL Workbench, click the **SQL** tab (or press `Ctrl + T`)
2. Copy and paste this:

```sql
CREATE DATABASE IF NOT EXISTS crosslife CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE crosslife;
```

3. Click **Execute** (⚡ icon)
4. Now open the schema.sql file and copy ALL its contents
5. Paste into the SQL tab
6. Click **Execute**

---

## Step 4: Verify Database Setup

1. In the left sidebar, expand **Schemas**
2. You should see **crosslife** database
3. Expand **crosslife** → **Tables**
4. You should see these tables:
   - ✅ admins
   - ✅ sermons
   - ✅ events
   - ✅ ministries
   - ✅ discipleship_programs
   - ✅ leadership
   - ✅ contact_inquiries
   - ✅ prayer_requests
   - ✅ feedback
   - ✅ newsletter_subscriptions
   - ✅ site_settings

---

## Step 5: Update Database Configuration

1. Open: `c:\Users\Pretty_Mk\CrossLife Mk code\crosslife\admin\config\database.php`

2. Check/Update these settings:

```php
define('DB_HOST', 'localhost');      // Usually 'localhost'
define('DB_USER', 'root');            // Your MySQL username (usually 'root')
define('DB_PASS', '');                // Your MySQL password (leave empty if no password)
define('DB_NAME', 'crosslife');       // Database name
```

**Important:** 
- If your MySQL has a password, update `DB_PASS` with your password
- If you're using a different MySQL username, update `DB_USER`

---

## Step 6: Test the Connection

### Test from PHP

1. Start your PHP server:
   ```bash
   cd "c:\Users\Pretty_Mk\CrossLife Mk code\crosslife"
   php -S localhost:8000
   ```

2. Open browser: `http://localhost:8000/audio-sermons.php`
   - If it loads without errors, database is connected! ✅
   - If you see "Database connection failed", check Step 5

### Test from MySQL Workbench

1. In MySQL Workbench, click **Database** → **Query Database**
2. Select `crosslife` database
3. Type:
   ```sql
   SELECT * FROM site_settings;
   ```
4. Click **Execute**
5. You should see the default site settings

---

## Step 7: Default Admin Account

After importing the schema, you can login to the admin panel:

- **URL:** `http://localhost:8000/admin/login.php`
- **Username:** `admin`
- **Password:** `admin123`

⚠️ **IMPORTANT:** Change this password immediately after first login!

---

## Troubleshooting

### "Access Denied" Error

**Problem:** MySQL Workbench can't connect

**Solution:**
1. Check MySQL service is running (`services.msc`)
2. Try connecting with:
   - Username: `root`
   - Password: (your MySQL root password)
3. If you forgot password, reset it:
   ```bash
   mysql -u root -p
   ALTER USER 'root'@'localhost' IDENTIFIED BY 'newpassword';
   ```

### "Database connection failed" in PHP

**Problem:** PHP can't connect to MySQL

**Solutions:**
1. Check MySQL is running: `net start MySQL80`
2. Verify credentials in `admin/config/database.php`
3. Test connection manually:
   ```bash
   mysql -u root -p
   ```
4. Make sure `extension=pdo_mysql` is enabled in `php.ini`

### "Table doesn't exist" Error

**Problem:** Database exists but tables are missing

**Solution:**
1. Make sure you imported the FULL `schema.sql` file
2. Check in MySQL Workbench: Expand `crosslife` → `Tables`
3. If tables are missing, re-import `schema.sql`

### Port Already in Use

**Problem:** MySQL port 3306 is already in use

**Solution:**
1. Check what's using port 3306:
   ```bash
   netstat -ano | findstr :3306
   ```
2. Stop other MySQL instances
3. Or change MySQL port in MySQL Workbench → Server → Options

---

## Quick Reference Commands

```bash
# Start MySQL Service
net start MySQL80

# Stop MySQL Service
net stop MySQL80

# Connect to MySQL
mysql -u root -p

# Check MySQL Version
mysql --version

# Start PHP Server
cd "c:\Users\Pretty_Mk\CrossLife Mk code\crosslife"
php -S localhost:8000
```

---

## Next Steps

Once database is set up:

1. ✅ Add audio sermons via Admin Panel
2. ✅ Add video sermons via Admin Panel
3. ✅ Manage events, ministries, etc.
4. ✅ Test audio-sermons.php page
5. ✅ Test sermons.php page

---

## Need Help?

- Check MySQL Workbench logs: **View** → **Logs**
- Check PHP error logs (if enabled)
- Verify MySQL service is running
- Test connection manually with `mysql -u root -p`
