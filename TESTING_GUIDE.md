# Testing Guide for Sermons Page

## Quick Test Steps

### 1. Basic Access Test
- ✅ Open: `http://localhost/crosslife/sermons.php`
- ✅ Page should load without errors
- ✅ You should see the page header "Sermons & Teaching"

### 2. Check What's Displayed

**If you have videos in database:**
- ✅ You should see video cards with thumbnails
- ✅ Each card shows: title, speaker, date, type badge

**If you DON'T have videos in database:**
- ✅ YouTube videos should still appear automatically
- ✅ Look for videos from Pastor Lenhard Kyamba's channel
- ✅ Videos will have a YouTube icon badge

### 3. Test Video Playback
- ✅ Click the play button on any video card
- ✅ OR click "Watch Video" button
- ✅ Modal should open with YouTube player
- ✅ Video should start playing

### 4. Test Filters
- ✅ Use "Filter by Type" → Select "Video" or "Audio"
- ✅ Page should filter and show only selected type
- ✅ Click "Clear Filters" to reset

### 5. Test Pagination (if many videos)
- ✅ If more than 12 videos, pagination appears at bottom
- ✅ Click page numbers to navigate
- ✅ Filters should persist across pages

## Troubleshooting

### ❌ Page Shows "No sermons found"
**Possible causes:**
1. Database not connected
2. No videos in database AND YouTube fetch failed
3. YouTube channel handle is incorrect

**Solutions:**
- Check database connection in `admin/config/database.php`
- Verify channel handle: `PastorLenhardKyamba` (no @)
- Check browser console for errors
- Check PHP error logs

### ❌ YouTube Videos Not Appearing
**Possible causes:**
1. Internet connection issue
2. YouTube blocking the request
3. Channel handle incorrect
4. PHP `allow_url_fopen` disabled

**Solutions:**
- Check internet connection
- Verify channel exists: https://www.youtube.com/@PastorLenhardKyamba/videos
- Check PHP settings: `allow_url_fopen` should be `On`
- Check PHP error logs

### ❌ Videos Show But Can't Play
**Possible causes:**
1. JavaScript errors
2. Bootstrap modal not loading
3. YouTube iframe blocked

**Solutions:**
- Open browser console (F12) and check for errors
- Verify Bootstrap JS is loading
- Check if YouTube iframe is being blocked by browser

### ❌ Page Loads But Is Blank/White
**Possible causes:**
1. PHP error
2. Missing files
3. Database connection error

**Solutions:**
- Check PHP error logs
- Verify all files exist:
  - `includes/db-functions.php`
  - `includes/youtube-functions.php`
  - `admin/config/database.php`
- Check database credentials

## Quick Diagnostic Commands

### Check PHP Configuration
Create a file `test.php` in your crosslife folder:
```php
<?php
echo "PHP Version: " . phpversion() . "<br>";
echo "allow_url_fopen: " . (ini_get('allow_url_fopen') ? 'ON' : 'OFF') . "<br>";
echo "Database connection test...<br>";
require_once 'includes/db-functions.php';
$db = getDB();
echo "Database: Connected!<br>";
?>
```

### Test YouTube Fetch Directly
Create `test-youtube.php`:
```php
<?php
require_once 'includes/youtube-functions.php';
$videos = fetchYouTubeChannelVideos('PastorLenhardKyamba', 5);
echo "Found " . count($videos) . " videos<br>";
print_r($videos);
?>
```

## Expected Results

### ✅ Success Indicators:
- Page loads in 2-5 seconds
- Video cards appear with thumbnails
- Play buttons are clickable
- Modal opens and plays videos
- Filters work correctly
- No PHP errors in page source

### ❌ Failure Indicators:
- Blank white page
- PHP error messages
- "No sermons found" when videos should exist
- JavaScript errors in console
- Videos don't play when clicked

## Next Steps After Testing

1. **If everything works:**
   - ✅ Add more videos via admin panel
   - ✅ Test on different browsers
   - ✅ Test on mobile devices

2. **If there are issues:**
   - Check error logs
   - Verify database connection
   - Test YouTube fetch separately
   - Check PHP configuration

## Need Help?

Check these files for configuration:
- Database: `admin/config/database.php`
- YouTube channels: `sermons.php` (line 20)
- Functions: `includes/youtube-functions.php`
