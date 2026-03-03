# Implementation Notes – Recent Updates

## 1. Sermons: PDF, Video (YouTube), Audio (Spotify + Upload)

### Database Migration
Run these SQL migrations in order:

```sql
-- database/migrate-sermons-pdf-audio-upload.sql
ALTER TABLE sermons MODIFY COLUMN sermon_type ENUM('video', 'audio', 'pdf') DEFAULT 'video';
ALTER TABLE sermons ADD COLUMN pdf_url VARCHAR(500) NULL AFTER spotify_url;
```
(If `pdf_url` already exists, skip the second line.)

### Admin
- **Sermons → Add/Edit**: Sermon type can be Video (YouTube), Audio (Spotify or file upload), or PDF.
- Video: Paste any YouTube URL.
- Audio: Spotify URL, or upload MP3/WAV/OGG/M4A, or enter a direct audio URL.
- PDF: Upload a PDF file or enter a PDF URL.
- Fields are shown/hidden based on the selected type.

### Public Sermons Page
- Filter tabs: All, Video, Audio, PDF.
- Video: embedded YouTube player.
- Audio: Spotify embed or native audio player.
- PDF: View / Download button.

---

## 2. Admin Interface Improvements

- Pending students badge in sidebar (Students menu).
- “View site” link in sidebar header.
- Improved navigation with clear sections.

---

## 3. Discipleship: Admin Approval Before Learning

### Database Migration
```sql
-- database/migrate-discipleship-approval.sql
ALTER TABLE discipleship_students MODIFY COLUMN status ENUM('pending', 'active', 'inactive') DEFAULT 'pending';
ALTER TABLE discipleship_students ADD COLUMN google_id VARCHAR(100) NULL UNIQUE AFTER password_hash;
ALTER TABLE discipleship_students MODIFY COLUMN password_hash VARCHAR(255) NULL;
```

### Flow
1. Student registers → `status = 'pending'`.
2. Admin approves from **Users & Students → Students** (Approve button).
3. Student can then log in and start learning.

---

## 4. Google Sign-In (School of Christ Academy)

### Setup
1. Go to [Google Cloud Console](https://console.cloud.google.com/apis/credentials).
2. Create OAuth 2.0 credentials (Web application).
3. Add redirect URI: `https://yourdomain.com/student/google-callback.php`
   - Local: `http://localhost/crosslife/student/google-callback.php`
4. Set credentials: copy `admin/config/config-google.php.example` to `admin/config/config-google.php` and add your Client ID and Secret. That file is gitignored. Or use environment variables `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET`.

### Behavior
- Login and register pages show a “Continue with Google” button when credentials are set.
- First-time Google users are created with `status = 'pending'` (must be approved).
- Existing email accounts can be linked to Google on first Google sign-in.

---

## 5. Sign-In / Sign-Up UI

- Dark theme with brand colors.
- Google sign-in button when configured.
- Clear hierarchy and spacing.
- Pending approval message on register page.
