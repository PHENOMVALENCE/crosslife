# PHP Server Setup Checklist ✅

Follow these steps in order:

## ✅ Step 1: Install PHP
- [ ] Download PHP from: https://windows.php.net/download/
- [ ] Extract to `C:\php` (or your preferred location)
- [ ] Add PHP to Windows PATH
- [ ] **Test:** Open Command Prompt → Type `php -v` → Should show version

## ✅ Step 2: Configure PHP
- [ ] Copy `php.ini-development` to `php.ini` in PHP folder
- [ ] Edit `php.ini` and uncomment:
  - `extension=mysqli`
  - `extension=pdo_mysql`
  - `extension=curl`
  - `extension=openssl`
- [ ] Set `allow_url_fopen = On`
- [ ] Save file

## ✅ Step 3: Install MySQL
- [ ] Download MySQL from: https://dev.mysql.com/downloads/installer/
- [ ] Install MySQL (remember root password!)
- [ ] Start MySQL service (services.msc or `net start MySQL80`)
- [ ] **Test:** `mysql -u root -p` → Should connect

## ✅ Step 4: Create Database
- [ ] Open Command Prompt
- [ ] Run: `mysql -u root -p`
- [ ] Enter password
- [ ] Run:
  ```sql
  CREATE DATABASE crosslife;
  USE crosslife;
  SOURCE "c:/Users/Pretty_Mk/CrossLife Mk code/crosslife/database/schema.sql";
  EXIT;
  ```

## ✅ Step 5: Update Database Config
- [ ] Edit: `admin/config/database.php`
- [ ] Set your MySQL root password:
  ```php
  define('DB_PASS', 'your_password_here');
  ```

## ✅ Step 6: Start Server
- [ ] Navigate to project folder in Command Prompt
- [ ] Run: `php -S localhost:8000`
- [ ] OR double-click `start-server.bat`
- [ ] Should see: "Server started at http://localhost:8000"

## ✅ Step 7: Test
- [ ] Open browser: http://localhost:8000/sermons.php
- [ ] Page should load!
- [ ] YouTube videos should appear (if internet connected)

---

## Quick Start Commands

```bash
# Check PHP
php -v

# Start MySQL
net start MySQL80

# Start PHP Server
cd "c:\Users\Pretty_Mk\CrossLife Mk code\crosslife"
php -S localhost:8000

# Or use batch file
start-server.bat
```

---

## Need Help?

See `PHP_SERVER_SETUP.md` for detailed instructions!
