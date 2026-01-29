# YouTube Video Integration Guide

## Step-by-Step Instructions for Adding Pastor Lenhard Kyamba's YouTube Videos

### Method 1: Quick Bulk Import (Recommended)

#### Step 1: Access the Import Tool
1. Log into the Admin Panel: `http://localhost/crosslife/admin/login.php`
2. Go to **Sermons** in the sidebar
3. Click the green **"Import YouTube Videos"** button at the top

#### Step 2: Get Video URLs from YouTube Channel
1. Open a new browser tab
2. Go to Pastor Lenhard Kyamba's YouTube channel:
   - **Channel URL:** https://www.youtube.com/@PastorLenhardKyamba/videos
   - Or: https://www.youtube.com/@PASTORLENARD/videos
3. Browse through the videos
4. For each video you want to import:
   - Click on the video to open it
   - Copy the URL from the browser address bar
   - Example URLs that work:
     - `https://www.youtube.com/watch?v=VIDEO_ID`
     - `https://youtu.be/VIDEO_ID`
     - `https://www.youtube.com/embed/VIDEO_ID`

#### Step 3: Import Videos
1. In the Import Tool, paste all video URLs (one per line) in the text area:
   ```
   https://www.youtube.com/watch?v=abc123xyz
   https://www.youtube.com/watch?v=def456uvw
   https://youtu.be/ghi789rst
   ```
2. Set **Default Speaker:** "Pastor Lenhard Kyamba" (or leave as is)
3. Set **Default Category:** (Optional) e.g., "Gospel of the Cross", "Sonship", etc.
4. Click **"Import Videos"**

#### Step 4: Review and Publish
1. Videos are imported as **Drafts** by default
2. Go back to **Sermons** list
3. Click **Edit** on each imported video
4. Review and update:
   - **Title** (auto-filled from YouTube, but you can improve it)
   - **Description** (add a meaningful description)
   - **Sermon Date** (set the actual date of the sermon)
   - **Category** (organize by series/topic)
   - **Status:** Change from "Draft" to **"Published"** to make it visible on the website
5. Click **"Save Sermon"**

#### Step 5: Verify on Frontend
1. Visit: `http://localhost/crosslife/sermons.php`
2. Your imported videos should now appear
3. Click the play button to watch videos in the modal

---

### Method 2: Manual One-by-One Import

#### Step 1: Get Video URL
1. Go to the YouTube video
2. Copy the URL from the address bar

#### Step 2: Add via Admin Panel
1. Go to **Sermons** → **Add New Sermon**
2. Fill in the form:
   - **Title:** Video title
   - **Description:** Brief description
   - **Speaker:** "Pastor Lenhard Kyamba"
   - **Sermon Type:** Select "Video"
   - **YouTube URL:** Paste the full YouTube URL
   - **Sermon Date:** Date of the sermon
   - **Category:** e.g., "Gospel Series", "Sunday Service", etc.
   - **Status:** Select "Published"
3. Click **"Save Sermon"**

---

## Tips & Best Practices

### Organizing Videos
- **Use Categories** to group related sermons:
  - "Gospel of the Cross"
  - "Sonship"
  - "Kingdom of God"
  - "Sunday Services"
  - "Bible Study"

### Video Titles
- Make titles descriptive and search-friendly
- Include series name and part number if applicable
- Example: "The Gospel of the Cross - Part 1: Understanding the Cross"

### Descriptions
- Add key points or scripture references
- Include timestamps for important sections (optional)
- Add relevant tags or topics

### Dates
- Use the actual sermon date for proper chronological ordering
- Older sermons will appear in the archive

### Thumbnails
- Thumbnails are automatically fetched from YouTube
- You can override by providing a custom thumbnail URL

---

## Troubleshooting

### Video Not Showing on Frontend
- Check that **Status** is set to "Published" (not "Draft")
- Verify the YouTube URL is correct
- Clear browser cache

### Import Tool Not Working
- Make sure you're pasting valid YouTube URLs
- Check that each URL is on a new line
- Verify the video is public (not private/unlisted)

### Video Thumbnail Not Loading
- Thumbnails are fetched automatically from YouTube
- If missing, you can manually add a thumbnail URL in the edit form

### Duplicate Videos
- The import tool automatically skips videos that already exist
- Check the "Recent Videos" section to see what was imported

---

## YouTube Channel Links

- **Pastor Lenhard Kyamba:** https://www.youtube.com/@PastorLenhardKyamba
- **CrossLife TV:** https://www.youtube.com/@CrossLifeTV

---

## Next Steps

1. ✅ Import videos using the bulk import tool
2. ✅ Review and edit imported videos
3. ✅ Organize by categories
4. ✅ Publish videos
5. ✅ Test on frontend (`sermons.php`)
6. ✅ Share the sermons page with your congregation!

---

## Need Help?

If you encounter any issues:
1. Check the browser console for errors
2. Verify database connection
3. Ensure YouTube URLs are valid and public
4. Check that videos are set to "Published" status
