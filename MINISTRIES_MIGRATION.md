# Ministries Migration Guide

## Overview
This document explains how to migrate the default ministries from `ministries.php` to the database and ensure they appear on both the admin and frontend.

## Default Ministries

The following 6 ministries are available in `ministries.php`:

1. **Teaching Ministry** - Preaching the Gospel through systematic teaching
2. **Discipleship Ministry** - School of Christ Academy programs
3. **Prayer Ministry** - Intercession and prayer community
4. **Outreach Ministry** - Global community outreach
5. **Worship Ministry** - Leading church worship
6. **Fellowship Ministry** - Creating environment for believers to grow

## Migration Steps

### Step 1: Run the Migration Script

1. Log into the admin panel: `http://localhost/crosslife_2/admin/login.php`
2. Navigate to: `http://localhost/crosslife_2/admin/migrate-ministries.php`
3. The script will:
   - Check for existing ministries
   - Insert only ministries that don't already exist
   - Show a summary of inserted/skipped ministries

### Step 2: Verify in Admin Panel

1. Go to: `http://localhost/crosslife_2/admin/ministries.php`
2. You should see all 6 ministries listed
3. You can edit, delete, or add new ministries from here

### Step 3: Verify on Frontend

1. Visit: `http://localhost/crosslife_2/ministries.php`
2. All active ministries from the database will be displayed
3. If no ministries exist in the database, default static ministries will be shown

## Admin Panel Features

### Adding a Ministry
- Click "Add New Ministry"
- Fill in:
  - Name (required)
  - Description (required)
  - Image (upload file or enter URL)
  - Leader Name (optional)
  - Contact Email (optional)
  - Status (Active/Inactive)
  - Display Order (for sorting)

### Editing a Ministry
- Click the edit icon (pencil) next to any ministry
- Update any fields
- Save changes

### Deleting a Ministry
- Click the delete icon (trash) next to any ministry
- Confirm deletion
- Note: Uploaded images will be automatically deleted

## Frontend Display

The frontend (`ministries.php`) will:
- Display all active ministries from the database
- Show ministry name, description, leader, and contact email
- Display ministry images
- Fall back to default static ministries if database is empty

## Troubleshooting

### Ministries Not Showing
1. Check database connection in `admin/config/database.php`
2. Verify ministries table exists: Run `database/schema.sql`
3. Check ministry status is set to "active"
4. Clear browser cache

### Image Upload Issues
1. Verify `assets/img/uploads/` directory exists and is writable
2. Check file permissions (should be 755 for directory, 644 for files)
3. Verify upload_max_filesize in php.ini
4. Check that image URLs are correct (full URLs or relative paths)

### Admin Page Errors
1. Check PHP error logs
2. Verify all required files exist:
   - `admin/config/config.php`
   - `admin/config/database.php`
   - `admin/includes/header.php`
   - `admin/includes/footer.php`
3. Ensure session is working properly

## Database Schema

The ministries table structure:
```sql
CREATE TABLE ministries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    image_url VARCHAR(500),
    leader_name VARCHAR(100),
    contact_email VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## Notes

- The migration script is safe to run multiple times (won't create duplicates)
- Ministries are ordered by `display_order` then alphabetically by name
- Only active ministries appear on the frontend
- Image uploads are stored in `assets/img/uploads/` with unique filenames

