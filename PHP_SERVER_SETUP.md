# PHP Built-in Server Setup Guide

## Step 1: Install PHP

### Download PHP
1. Go to: **https://windows.php.net/download/**
2. Download **PHP 8.2.x Thread Safe** ZIP (or latest version)
3. Choose: **VS16 x64 Non Thread Safe** or **Thread Safe** (either works)

### Install PHP
1. Create folder: `C:\php` (or any location you prefer)
2. Extract the ZIP file to `C:\php`
3. You should see files like `php.exe`, `php.ini` in `C:\php`

### Add PHP to PATH
1. Press `Win + X` → **System** → **Advanced System Settings**
2. Click **"Environment Variables"**
3. Under **"System Variables"**, find **"Path"** → Click **"Edit"**
4. Click **"New"** → Add: `C:\php`
5. Click **OK** on all dialogs
6. **Close and reopen** Command Prompt

### Verify PHP Installation
Open **Command Prompt** (new window) and type:
```bash
php -v
```
You should see PHP version info. If you see "command not found", PHP is not in PATH.

---

## Step 2: Configure PHP

### Enable Required Extensions
1. Go to `C:\php`
2. Find `php.ini-development` file
3. **Copy** it and rename to `php.ini`
4. Open `php.ini` in Notepad
5. Find and uncomment (remove `;` from start) these lines:
   ```ini
   extension=mysqli
   extension=pdo_mysql
   extension=curl
   extension=openssl
   extension=mbstring
   ```
6. Find `allow_url_fopen` and make sure it's:
   ```ini
   allow_url_fopen = On
   ```
7. **Save** the file

---

## Step 3: Install MySQL

### Download MySQL
1. Go to: **https://dev.mysql.com/downloads/installer/**
2. Download **MySQL Installer for Windows**
3. Choose: **mysql-installer-community** (smaller download)

### Install MySQL
1. Run the installer
2. Choose: **Developer Default** or **Server only**
3. Set **root password** (remember this!)
4. Complete installation

### Start MySQL Service
1. Press `Win + R` → Type: `services.msc` → Enter
2. Find **"MySQL80"** (or similar)
3. Right-click → **Start**
4. Or set it to **Automatic** so it starts on boot

### Verify MySQL
Open **Command Prompt** and type:
```bash
mysql -u root -p
```
Enter your root password. If it connects, MySQL is working!

---

## Step 4: Set Up Database

### Create Database
1. Open Command Prompt
2. Type:
```bash
mysql -u root -p
```
3. Enter your MySQL root password
4. Run these commands:
```sql
CREATE DATABASE IF NOT EXISTS crosslife CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE crosslife;
SOURCE "c:/Users/Pretty_Mk/CrossLife Mk code/crosslife/database/schema.sql";
EXIT;
```

**OR** use phpMyAdmin (if you install it separately):
1. Download: https://www.phpmyadmin.net/downloads/
2. Extract to `C:\php\phpMyAdmin`
3. Access: http://localhost:8000/phpMyAdmin

---

## Step 5: Update Database Config

Edit: `admin/config/database.php`

Make sure it has:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'your_mysql_password');  // Your MySQL root password
define('DB_NAME', 'crosslife');
```

---

## Step 6: Start PHP Server

### Method 1: Use the Batch File
1. Navigate to your project folder:
   ```bash
   cd "c:\Users\Pretty_Mk\CrossLife Mk code\crosslife"
   ```
2. Double-click `start-server.bat`
   OR run:
   ```bash
   php -S localhost:8000
   ```

### Method 2: Manual Start
1. Open **Command Prompt**
2. Navigate to project:
   ```bash
   cd "c:\Users\Pretty_Mk\CrossLife Mk code\crosslife"
   ```
3. Start server:
   ```bash
   php -S localhost:8000
   ```
4. You should see:
   ```
   PHP 8.x.x Development Server (http://localhost:8000) started
   ```

---

## Step 7: Access Your Site

Open browser and go to:
- **Sermons Page:** http://localhost:8000/sermons.php
- **Home Page:** http://localhost:8000/index.php
- **Admin Panel:** http://localhost:8000/admin/login.php

---

## Troubleshooting

### "php is not recognized"
- PHP is not in PATH
- Restart Command Prompt after adding to PATH
- Or use full path: `C:\php\php.exe -S localhost:8000`

### "Database connection failed"
- Check MySQL is running: `net start MySQL80`
- Verify credentials in `admin/config/database.php`
- Test connection: `mysql -u root -p`

### "Port 8000 already in use"
- Use different port: `php -S localhost:8080`
- Or stop other service using port 8000

### "allow_url_fopen disabled"
- Edit `php.ini`
- Find `allow_url_fopen` → Set to `On`
- Restart PHP server

### YouTube videos not loading
- Check `allow_url_fopen = On` in php.ini
- Check internet connection
- Check PHP error logs

---

## Quick Commands Reference

```bash
# Check PHP version
php -v

# Start PHP server
php -S localhost:8000

# Start MySQL (if not running)
net start MySQL80

# Stop MySQL
net stop MySQL80

# Connect to MySQL
mysql -u root -p

# Check if port is in use
netstat -ano | findstr :8000
```

---

## Next Steps

1. ✅ Install PHP
2. ✅ Install MySQL
3. ✅ Configure PHP (php.ini)
4. ✅ Set up database
5. ✅ Start PHP server
6. ✅ Test sermons.php page

---

## Need Help?

- **PHP Issues:** Check `php.ini` configuration
- **MySQL Issues:** Check MySQL service is running
- **Database Issues:** Verify credentials in `database.php`
- **Server Issues:** Check port availability

Good luck! 🚀
