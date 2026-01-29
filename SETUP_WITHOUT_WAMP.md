# Running Your Project Without WAMP/XAMPP

## Option 1: Install PHP Only (Lightweight) ⚡

### Step 1: Download PHP
1. Go to: https://windows.php.net/download/
2. Download **PHP 8.x Thread Safe** ZIP file
3. Extract to: `C:\php` (or any folder you prefer)

### Step 2: Add PHP to PATH
1. Press `Win + X` → System → Advanced System Settings
2. Click "Environment Variables"
3. Under "System Variables", find "Path" → Edit
4. Click "New" → Add: `C:\php` (or your PHP folder path)
5. Click OK on all dialogs

### Step 3: Install MySQL Separately
1. Download MySQL: https://dev.mysql.com/downloads/installer/
2. Install MySQL Community Server
3. Remember your root password!

### Step 4: Start MySQL
```bash
# Open Command Prompt as Administrator
net start MySQL80
```

### Step 5: Run PHP Built-in Server
```bash
# Navigate to your project folder
cd "c:\Users\Pretty_Mk\CrossLife Mk code\crosslife"

# Start PHP server
php -S localhost:8000
```

### Step 6: Access Your Site
Open browser: `http://localhost:8000/sermons.php`

---

## Option 2: Use XAMPP (Easiest - All-in-One) 🎯

### Step 1: Download XAMPP
1. Go to: https://www.apachefriends.org/download.html
2. Download XAMPP for Windows
3. Install it (default location: `C:\xampp`)

### Step 2: Start Services
1. Open XAMPP Control Panel
2. Click "Start" for **Apache** and **MySQL**
3. Both should turn green

### Step 3: Copy Your Project
1. Copy your `crosslife` folder to: `C:\xampp\htdocs\`
2. So it becomes: `C:\xampp\htdocs\crosslife\`

### Step 4: Access Your Site
Open browser: `http://localhost/crosslife/sermons.php`

---

## Option 3: Use Online Services (No Installation) 🌐

### Replit (Recommended)
1. Go to: https://replit.com/
2. Sign up (free)
3. Create new Repl → Choose "PHP"
4. Upload your files
5. Set up MySQL database in Replit
6. Run your project online

**Pros:** No installation, works anywhere  
**Cons:** Requires internet, database setup needed

### CodeSandbox
1. Go to: https://codesandbox.io/
2. Create new sandbox → PHP template
3. Upload files
4. Limited database support

---

## Option 4: Use Docker (Advanced) 🐳

If you have Docker Desktop installed:

### Create `docker-compose.yml`:
```yaml
version: '3.8'
services:
  web:
    image: php:8.1-apache
    ports:
      - "8000:80"
    volumes:
      - .:/var/www/html
    depends_on:
      - db
  
  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: crosslife
    ports:
      - "3306:3306"
    volumes:
      - db_data:/var/lib/mysql

volumes:
  db_data:
```

### Run:
```bash
docker-compose up
```

Access: `http://localhost:8000/sermons.php`

---

## Quick Comparison

| Method | Setup Time | Database | Best For |
|--------|-----------|----------|----------|
| **PHP Only** | 15 min | Manual MySQL | Lightweight setup |
| **XAMPP** | 5 min | Included | Easiest, all-in-one |
| **Online (Replit)** | 10 min | Included | No installation |
| **Docker** | 20 min | Included | Advanced users |

---

## Recommended: XAMPP (Easiest)

**Why XAMPP?**
- ✅ Everything included (PHP, MySQL, Apache)
- ✅ 5-minute setup
- ✅ Works offline
- ✅ Perfect for development
- ✅ Free and reliable

**Download:** https://www.apachefriends.org/download.html

---

## After Installation - Test Your Setup

### 1. Test PHP
```bash
php -v
```
Should show PHP version

### 2. Test MySQL
```bash
mysql -u root -p
```
Enter your password, should connect

### 3. Import Database
```bash
mysql -u root -p crosslife < database/schema.sql
```

### 4. Access Your Site
- XAMPP: `http://localhost/crosslife/sermons.php`
- PHP Server: `http://localhost:8000/sermons.php`

---

## Need Help?

**PHP not found?**
- Make sure PHP is in your PATH
- Restart Command Prompt after adding to PATH

**MySQL connection error?**
- Check MySQL is running
- Verify credentials in `admin/config/database.php`
- Test connection: `mysql -u root -p`

**Port already in use?**
- Change port: `php -S localhost:8080` (instead of 8000)
- Or stop other services using port 80/8000
