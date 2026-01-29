# Simple PHP Configuration Guide 🎯

## What You Need to Do (Super Simple!)

We need to enable 5 things in PHP so your website works. Think of it like turning on switches.

---

## Part 1: Create the Config File

### Step 1: Open File Explorer
- Press `Windows Key + E`

### Step 2: Go to C:\php
- Type `C:\php` in the address bar
- Press Enter

### Step 3: Copy the Template File
- Find file: `php.ini-development`
- Right-click → Copy
- Right-click empty space → Paste
- You'll see: `php.ini-development - Copy`

### Step 4: Rename It
- Right-click `php.ini-development - Copy`
- Click "Rename"
- Delete everything
- Type: `php.ini`
- Press Enter

✅ **Done!** You now have `php.ini` file.

---

## Part 2: Edit the Config File

### Step 1: Open php.ini
- In `C:\php` folder
- Find `php.ini`
- Double-click it (opens in Notepad)

### Step 2: Enable Extensions

**What to look for:**
You'll see lines that look like this:
```
;extension=mysqli
```

**What to do:**
Delete the `;` at the beginning. So:
```
;extension=mysqli    ← BEFORE (disabled)
extension=mysqli     ← AFTER (enabled)
```

**Do this for these 5 lines:**

1. Find: `;extension=mysqli`
   - Delete the `;`
   - Becomes: `extension=mysqli`

2. Find: `;extension=pdo_mysql`
   - Delete the `;`
   - Becomes: `extension=pdo_mysql`

3. Find: `;extension=curl`
   - Delete the `;`
   - Becomes: `extension=curl`

4. Find: `;extension=openssl`
   - Delete the `;`
   - Becomes: `extension=openssl`

5. Find: `;extension=mbstring`
   - Delete the `;`
   - Becomes: `extension=mbstring`

### Step 3: Enable URL Opening

Find this line:
```
allow_url_fopen = Off
```

Change it to:
```
allow_url_fopen = On
```

### Step 4: Save
- Press `Ctrl + S`
- Close Notepad

✅ **Done!** PHP is configured!

---

## How to Find Lines Quickly

1. Open `php.ini` in Notepad
2. Press `Ctrl + F` (opens search box)
3. Type what you're looking for (e.g., `extension=mysqli`)
4. Click "Find Next"
5. It will jump to that line
6. Make your change
7. Repeat for next item

---

## Visual Guide

### Example 1: Enabling Extension

**BEFORE:**
```
;extension=mysqli
```
↑ See the semicolon? That means OFF.

**AFTER:**
```
extension=mysqli
```
↑ No semicolon = ON!

### Example 2: Enabling URL Opening

**BEFORE:**
```
allow_url_fopen = Off
```

**AFTER:**
```
allow_url_fopen = On
```

---

## Quick Test

After saving, test if it worked:

1. Open Command Prompt
2. Type: `php -m`
3. You should see `mysqli`, `pdo_mysql`, `curl` in the list
4. If you see them, ✅ it worked!

---

## Still Confused?

**Think of it like this:**
- `;` = Commented out = Turned OFF
- No `;` = Active = Turned ON
- `Off` = Disabled
- `On` = Enabled

You're basically turning ON 5 switches and 1 setting!

---

**That's it!** Once you save the file, PHP is configured. Then we move to MySQL! 🚀
