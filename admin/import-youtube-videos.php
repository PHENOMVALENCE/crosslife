<?php
/**
 * YouTube Video Import Tool
 * Import videos from Pastor Lenhard Kyamba's YouTube channel
 * 
 * Note: This requires YouTube Data API v3 key for full automation
 * For now, this provides a manual import interface
 */

$pageTitle = 'Import YouTube Videos';
require_once 'includes/header.php';

// Include db-functions to get getYouTubeId function
require_once __DIR__ . '/../includes/db-functions.php';

$db = getDB();
$message = '';
$messageType = '';

// Handle manual import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_videos'])) {
    $videoUrls = $_POST['video_urls'] ?? '';
    $defaultSpeaker = sanitize($_POST['default_speaker'] ?? 'Pastor Lenhard Kyamba');
    $defaultCategory = sanitize($_POST['default_category'] ?? '');
    
    if (empty($videoUrls)) {
        $message = 'Please enter at least one YouTube URL.';
        $messageType = 'danger';
    } else {
        $urls = array_filter(array_map('trim', explode("\n", $videoUrls)));
        $imported = 0;
        $skipped = 0;
        $errors = [];
        
        foreach ($urls as $url) {
            $url = trim($url);
            if (empty($url)) continue;
            
            // Extract YouTube video ID
            $videoId = getYouTubeId($url);
            
            if (!$videoId) {
                $errors[] = "Invalid YouTube URL: $url";
                $skipped++;
                continue;
            }
            
            // Check if video already exists
            $stmt = $db->prepare("SELECT id FROM sermons WHERE youtube_url LIKE ?");
            $stmt->execute(["%$videoId%"]);
            if ($stmt->fetch()) {
                $skipped++;
                continue;
            }
            
            // Fetch video details from YouTube (using oEmbed API - no key required)
            $oembedUrl = "https://www.youtube.com/oembed?url=" . urlencode($url) . "&format=json";
            $videoData = @file_get_contents($oembedUrl);
            
            if ($videoData) {
                $videoInfo = json_decode($videoData, true);
                $title = $videoInfo['title'] ?? 'Untitled Video';
                $thumbnail = $videoInfo['thumbnail_url'] ?? '';
                
                // Insert into database
                try {
                    $stmt = $db->prepare("INSERT INTO sermons (title, description, speaker, sermon_type, youtube_url, thumbnail_url, category, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $title,
                        "Imported from YouTube",
                        $defaultSpeaker,
                        'video',
                        $url,
                        $thumbnail,
                        $defaultCategory,
                        'draft' // Import as draft so you can review before publishing
                    ]);
                    $imported++;
                } catch (PDOException $e) {
                    $errors[] = "Error importing $url: " . $e->getMessage();
                    $skipped++;
                }
            } else {
                $errors[] = "Could not fetch video details for: $url";
                $skipped++;
            }
        }
        
        $message = "Import complete! $imported video(s) imported, $skipped skipped.";
        if (!empty($errors)) {
            $message .= "<br><small>Errors: " . implode("<br>", array_slice($errors, 0, 5)) . "</small>";
        }
        $messageType = $imported > 0 ? 'success' : 'warning';
    }
}

// Get recent imports
$stmt = $db->query("SELECT * FROM sermons WHERE sermon_type = 'video' ORDER BY created_at DESC LIMIT 10");
$recentVideos = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Import YouTube Videos</h5>
    </div>
    <div class="card-body">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-8">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">YouTube Video URLs</label>
                        <textarea class="form-control" name="video_urls" rows="10" placeholder="Paste YouTube URLs here, one per line:
https://www.youtube.com/watch?v=VIDEO_ID_1
https://www.youtube.com/watch?v=VIDEO_ID_2
https://youtu.be/VIDEO_ID_3"></textarea>
                        <small class="form-text text-muted">Enter one YouTube URL per line. Supports any YouTube URL format.</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Default Speaker</label>
                            <input type="text" class="form-control" name="default_speaker" value="Pastor Lenhard Kyamba">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Default Category (Optional)</label>
                            <input type="text" class="form-control" name="default_category" placeholder="e.g., Gospel of the Cross">
                        </div>
                    </div>
                    
                    <button type="submit" name="import_videos" class="btn btn-primary">
                        <i class="bi bi-download me-2"></i>Import Videos
                    </button>
                    <a href="sermons.php" class="btn btn-secondary">Back to Sermons</a>
                </form>
                
                <hr class="my-4">
                
                <div class="alert alert-info">
                    <h6><i class="bi bi-info-circle me-2"></i>How to Get Video URLs:</h6>
                    <ol class="mb-0">
                        <li>Go to <a href="https://www.youtube.com/@PastorLenhardKyamba/videos" target="_blank">Pastor Lenhard Kyamba's YouTube Channel</a></li>
                        <li>Browse the videos section</li>
                        <li>Click on a video to open it</li>
                        <li>Copy the URL from your browser's address bar</li>
                        <li>Paste it here (one per line)</li>
                    </ol>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-header">
                        <h6 class="mb-0">Quick Tips</h6>
                    </div>
                    <div class="card-body">
                        <ul class="small mb-0">
                            <li>Videos are imported as <strong>drafts</strong> by default</li>
                            <li>Review and edit imported videos before publishing</li>
                            <li>You can update titles, descriptions, and dates after import</li>
                            <li>Duplicate videos (same YouTube ID) are automatically skipped</li>
                            <li>Thumbnails are automatically fetched from YouTube</li>
                        </ul>
                    </div>
                </div>
                
                <?php if (!empty($recentVideos)): ?>
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">Recent Videos</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($recentVideos as $video): ?>
                                <li class="mb-2">
                                    <a href="sermons.php?action=edit&id=<?php echo $video['id']; ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($video['title']); ?>
                                    </a>
                                    <br>
                                    <small class="text-muted">
                                        <span class="badge bg-<?php echo $video['status'] === 'published' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($video['status']); ?>
                                        </span>
                                    </small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
