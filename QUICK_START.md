# Quick Start Guide - No WAMP/XAMPP Needed

## 🚀 Fastest Way: Install XAMPP (5 minutes)

### Download & Install
1. **Download XAMPP:** https://www.apachefriends.org/download.html
2. **Install** (just click Next, Next, Next)
3. **Open XAMPP Control Panel**

### Start Your Server
1. Click **"Start"** button for **Apache**
2. Click **"Start"** button for **MySQL**
3. Both should turn **green** ✅

### Move Your Project
1. Copy your `crosslife` folder
2. Paste it into: `C:\xampp\htdocs\`
3. Full path: `C:\xampp\htdocs\crosslife\`

### Access Your Site
Open browser: **http://localhost/crosslife/sermons.php**

---

## ⚡ Alternative: PHP Built-in Server

### If you have PHP installed:

1. **Open Command Prompt** in your project folder:
   ```bash
   cd "c:\Users\Pretty_Mk\CrossLife Mk code\crosslife"
   ```

2. **Double-click** `start-server.bat` file
   OR run:
   ```bash
   php -S localhost:8000
   ```

3. **Open browser:** http://localhost:8000/sermons.php

---

## 🌐 Online Option: Replit (No Installation)

1. Go to: https://replit.com/
2. Sign up (free)
3. Create new Repl → **PHP**
4. Upload your files
5. Set up MySQL database
6. Run online!

---

## 📋 What You Need

### Minimum Requirements:
- ✅ **PHP** (for running PHP files)
- ✅ **MySQL** (for database)
- ✅ **Web Browser** (Chrome, Firefox, Edge)

### Easiest Solution:
**XAMPP** includes everything:
- ✅ PHP
- ✅ MySQL  
- ✅ Apache (web server)
- ✅ phpMyAdmin (database manager)

**Download:** https://www.apachefriends.org/download.html

---

## 🎯 Recommended Steps

1. **Download XAMPP** (if you don't have it)
2. **Install XAMPP**
3. **Start Apache & MySQL** in XAMPP Control Panel
4. **Copy project** to `C:\xampp\htdocs\crosslife\`
5. **Import database:**
   - Open: http://localhost/phpmyadmin
   - Click "Import"
   - Select: `database/schema.sql`
   - Click "Go"
6. **Access site:** http://localhost/crosslife/sermons.php

---

## ❓ Troubleshooting

### "PHP not found"
- Install PHP or use XAMPP
- Add PHP to Windows PATH

### "Database connection failed"
- Make sure MySQL is running
- Check `admin/config/database.php` credentials
- Default XAMPP: user=`root`, password=`` (empty)

### "Port already in use"
- Change port in `start-server.bat`: `php -S localhost:8080`
- Or stop other services using port 80

---

## 💡 Pro Tip

**XAMPP is the easiest!** It's one download, one install, and everything works. Perfect for development.

**Download now:** https://www.apachefriends.org/download.html
