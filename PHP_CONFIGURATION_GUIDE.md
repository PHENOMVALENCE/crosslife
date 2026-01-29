# PHP Configuration Guide - Step by Step

## What We're Doing
We need to enable some PHP features (extensions) so your website can connect to MySQL database and fetch YouTube videos.

---

## Step 1: Create php.ini File

### What is php.ini?
- It's PHP's configuration file
- It controls what features PHP can use
- By default, PHP comes with `php.ini-development` (a template)
- We need to copy it and rename it to `php.ini`

### How to Do It:

**Option A: Using File Explorer (Easiest)**
1. Open **File Explorer** (Windows key + E)
2. Go to: `C:\php`
3. Find the file: `php.ini-development`
4. **Right-click** on it → **Copy**
5. **Right-click** in empty space → **Paste**
6. You'll see: `php.ini-development - Copy`
7. **Right-click** on `php.ini-development - Copy` → **Rename**
8. Type: `php.ini` (remove everything else)
9. Press Enter
10. ✅ Done! You now have `php.ini` file

**Option B: Using Command Prompt**
1. Open Command Prompt
2. Type:
   ```bash
   cd C:\php
   copy php.ini-development php.ini
   ```
3. ✅ Done!

---

## Step 2: Edit php.ini File

### What We Need to Change
We need to "uncomment" (enable) some extensions. In PHP config files, `;` means "disabled" or "commented out". We need to remove the `;` to enable them.

### Step-by-Step Editing:

1. **Open php.ini**
   - Go to `C:\php` folder
   - Find `php.ini` file
   - **Right-click** → **Open with** → **Notepad**
   - (Or double-click if Notepad is default)

2. **Find the Extensions Section**
   - Press `Ctrl + F` (opens Find dialog)
   - Type: `extension=mysqli`
   - Click "Find Next"
   - You'll see something like:
     ```ini
     ;extension=mysqli
     ```
   - Notice the `;` at the start? That means it's DISABLED

3. **Enable mysqli Extension**
   - Find this line:
     ```ini
     ;extension=mysqli
     ```
   - **Delete the semicolon** (`;`) at the beginning
   - It should become:
     ```ini
     extension=mysqli
     ```
   - ✅ Enabled!

4. **Enable pdo_mysql Extension**
   - Press `Ctrl + F` again
   - Type: `extension=pdo_mysql`
   - Find this line:
     ```ini
     ;extension=pdo_mysql
     ```
   - **Delete the semicolon** (`;`)
   - It becomes:
     ```ini
     extension=pdo_mysql
     ```
   - ✅ Enabled!

5. **Enable curl Extension**
   - Press `Ctrl + F`
   - Type: `extension=curl`
   - Find:
     ```ini
     ;extension=curl
     ```
   - **Delete the semicolon** (`;`)
   - ✅ Enabled!

6. **Enable openssl Extension**
   - Press `Ctrl + F`
   - Type: `extension=openssl`
   - Find:
     ```ini
     ;extension=openssl
     ```
   - **Delete the semicolon** (`;`)
   - ✅ Enabled!

7. **Enable mbstring Extension**
   - Press `Ctrl + F`
   - Type: `extension=mbstring`
   - Find:
     ```ini
     ;extension=mbstring
     ```
   - **Delete the semicolon** (`;`)
   - ✅ Enabled!

8. **Enable allow_url_fopen**
   - Press `Ctrl + F`
   - Type: `allow_url_fopen`
   - Find:
     ```ini
     allow_url_fopen = Off
     ```
   - Change `Off` to `On`:
     ```ini
     allow_url_fopen = On
     ```
   - ✅ Enabled!

9. **Save the File**
   - Press `Ctrl + S` (or File → Save)
   - Close Notepad

---

## Visual Example

### Before (Disabled):
```ini
;extension=mysqli
;extension=pdo_mysql
;extension=curl
allow_url_fopen = Off
```

### After (Enabled):
```ini
extension=mysqli
extension=pdo_mysql
extension=curl
allow_url_fopen = On
```

**See the difference?** No `;` at the start, and `Off` changed to `On`!

---

## Quick Checklist

- [ ] Copied `php.ini-development` → renamed to `php.ini`
- [ ] Opened `php.ini` in Notepad
- [ ] Removed `;` from `extension=mysqli`
- [ ] Removed `;` from `extension=pdo_mysql`
- [ ] Removed `;` from `extension=curl`
- [ ] Removed `;` from `extension=openssl`
- [ ] Removed `;` from `extension=mbstring`
- [ ] Changed `allow_url_fopen = Off` to `On`
- [ ] Saved the file

---

## Troubleshooting

### "I can't find php.ini-development"
- Make sure you're in `C:\php` folder
- Look for files ending in `.ini-development` or `.ini-production`
- If you see `php.ini-production`, use that instead

### "I see many extension lines"
- That's normal! Just find the ones listed above
- You don't need to enable ALL extensions, just the 5 we mentioned

### "I'm not sure if I did it right"
- After saving, you can verify:
  - Open `php.ini` again
  - Search for `extension=mysqli` (without semicolon)
  - If you see `extension=mysqli` (no `;`), you did it right!

---

## What Each Extension Does

- **mysqli**: Connects PHP to MySQL database
- **pdo_mysql**: Another way to connect to MySQL (more secure)
- **curl**: Fetches data from internet (needed for YouTube videos)
- **openssl**: Secure connections (HTTPS)
- **mbstring**: Handles special characters properly
- **allow_url_fopen**: Allows PHP to open URLs (needed for YouTube)

---

## After Configuration

Once you've saved `php.ini`:
1. ✅ PHP is configured!
2. Next step: Install MySQL
3. Then: Set up database
4. Finally: Start your server!

---

**Need help?** If you get stuck on any step, let me know! 😊
