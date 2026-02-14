<?php
// Log immediately when POST hits this page (before login redirect) so we know request arrived
define('SERMON_DEBUG_LOG', __DIR__ . DIRECTORY_SEPARATOR . 'audio_upload_debug.log');
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    file_put_contents(SERMON_DEBUG_LOG, '[' . date('Y-m-d H:i:s') . '] POST hit sermons.php' . "\n", FILE_APPEND | LOCK_EX);
}

require_once 'config/config.php';
requireLogin(); // must be logged in for any action
// If we get here on POST, we're logged in (otherwise requireLogin would have redirected)
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    @file_put_contents(SERMON_DEBUG_LOG, '[' . date('Y-m-d H:i:s') . '] After requireLogin (logged in)' . "\n", FILE_APPEND | LOCK_EX);
}

function sermonDebug($msg, $data = null) {
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg;
    if ($data !== null) {
        $line .= ' ' . (is_string($data) ? $data : json_encode($data, JSON_UNESCAPED_SLASHES));
    }
    $line .= "\n";
    @file_put_contents(SERMON_DEBUG_LOG, $line, FILE_APPEND | LOCK_EX);
    error_log(trim($line));
}

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Handle form submissions BEFORE any output (so redirect works)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // If POST is empty (no title, no delete), PHP may have dropped the body (e.g. post_max_size exceeded)
    $postEmpty = !isset($_POST['delete']) && empty($_POST['title']) && empty($_FILES['audio_file']['name']);
    if ($postEmpty && isset($_SERVER['CONTENT_LENGTH']) && (int)$_SERVER['CONTENT_LENGTH'] > 0) {
        $currentPostMax = ini_get('post_max_size');
        $currentUploadMax = ini_get('upload_max_filesize');
        sermonDebug('POST body dropped by PHP (likely post_max_size exceeded)', 'Content-Length: ' . $_SERVER['CONTENT_LENGTH'] . ' | PHP post_max_size=' . $currentPostMax . ' upload_max_filesize=' . $currentUploadMax);
        redirect('sermons.php?action=add', 'The server did not receive your form (upload ~' . round((int)$_SERVER['CONTENT_LENGTH'] / 1024 / 1024, 1) . ' MB). This PHP process has post_max_size=' . $currentPostMax . ' and upload_max_filesize=' . $currentUploadMax . '. Restart the PHP server after editing php.ini so both are at least 64M.', 'danger');
    }
    sermonDebug('POST received', ['delete' => isset($_POST['delete']), 'title' => isset($_POST['title']) ? substr($_POST['title'], 0, 30) : '']);
    try {
        if (isset($_POST['delete'])) {
            if (empty($_POST['id'])) {
                redirect('sermons.php', 'Invalid sermon ID.', 'danger');
            }
            
            $stmt = $db->prepare("DELETE FROM sermons WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            
            if ($stmt->rowCount() > 0) {
                redirect('sermons.php', 'Sermon deleted successfully.');
            } else {
                redirect('sermons.php', 'Sermon not found or already deleted.', 'warning');
            }
        }
        
        // Validate required fields
        if (empty($_POST['title'])) {
            redirect('sermons.php?action=' . ($id ? 'edit&id=' . $id : 'add'), 'Title is required.', 'danger');
        }
        
        $allowedAudio = ['mp3', 'wav', 'ogg', 'm4a', 'webm'];
        $maxSize = 500 * 1024 * 1024; // 500 MB — long sermons; ensure php.ini: upload_max_filesize & post_max_size ≥ 500M
        
        $audioUrl = '';
        $sermonId = $id ?: ($_POST['id'] ?? null);
        
        // Get existing audio_url when editing
        $existingUrl = '';
        if ($sermonId) {
            $stmt = $db->prepare("SELECT audio_url FROM sermons WHERE id = ?");
            $stmt->execute([$sermonId]);
            $existing = $stmt->fetch();
            $existingUrl = $existing ? ($existing['audio_url'] ?? '') : '';
        }
        
        // Upload error messages for user
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'File is too large for the server (increase upload_max_filesize in php.ini).',
            UPLOAD_ERR_FORM_SIZE => 'File is too large.',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded. Try again.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded. Choose an MP3 file and try again.',
            UPLOAD_ERR_NO_TMP_DIR => 'Server error: missing temp folder.',
            UPLOAD_ERR_CANT_WRITE => 'Server error: cannot save file to disk.',
            UPLOAD_ERR_EXTENSION => 'Server blocked the upload.',
        ];
        $fileError = isset($_FILES['audio_file']['error']) ? (int)$_FILES['audio_file']['error'] : UPLOAD_ERR_NO_FILE;
        $fi = $_FILES['audio_file'] ?? [];
        sermonDebug('FILES state', [
            'name' => $fi['name'] ?? '',
            'error' => $fileError,
            'size' => $fi['size'] ?? 0,
            'tmp_name' => isset($fi['tmp_name']) ? (is_string($fi['tmp_name']) && file_exists($fi['tmp_name']) ? 'exists' : 'missing') : 'unset',
        ]);

        // Favor your hosting space: check upload first (no extra cost)
        if (!empty($_FILES['audio_file']['name']) || $fileError !== UPLOAD_ERR_NO_FILE) {
            if ($fileError !== UPLOAD_ERR_OK) {
                sermonDebug('UPLOAD FAILED: file error', $fileError . ' ' . ($uploadErrors[$fileError] ?? ''));
                $msg = isset($uploadErrors[$fileError]) ? $uploadErrors[$fileError] : 'Upload failed (error ' . $fileError . ').';
                redirect('sermons.php?action=' . ($sermonId ? 'edit&id=' . $sermonId : 'add'), $msg, 'danger');
            }
            sermonDebug('Upload OK, checking format and size');
            $ext = strtolower(pathinfo($_FILES['audio_file']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedAudio)) {
                sermonDebug('Invalid format', $ext);
                redirect('sermons.php?action=' . ($sermonId ? 'edit&id=' . $sermonId : 'add'), 'Invalid audio format. Use: ' . implode(', ', $allowedAudio), 'danger');
            }
            if ($_FILES['audio_file']['size'] > $maxSize) {
                sermonDebug('File too large', $_FILES['audio_file']['size'] . ' > ' . $maxSize);
                redirect('sermons.php?action=' . ($sermonId ? 'edit&id=' . $sermonId : 'add'), 'File too large. Max 500 MB. Or paste a link below if the file is already hosted elsewhere.', 'danger');
            }
            $uploadDir = rtrim(AUDIO_UPLOAD_DIR, DIRECTORY_SEPARATOR . '/') . DIRECTORY_SEPARATOR;
            if (!is_dir($uploadDir)) {
                if (!@mkdir($uploadDir, 0755, true)) {
                    sermonDebug('mkdir FAILED', $uploadDir);
                    redirect('sermons.php?action=' . ($sermonId ? 'edit&id=' . $sermonId : 'add'), 'Server error: could not create upload folder. Check permissions.', 'danger');
                }
                sermonDebug('mkdir OK', $uploadDir);
            }
            $uploadDirReal = realpath($uploadDir) ?: $uploadDir;
            sermonDebug('Upload dir', $uploadDirReal . ' writable=' . (is_writable($uploadDir) ? 'yes' : 'no'));
            if (!is_writable($uploadDir)) {
                redirect('sermons.php?action=' . ($sermonId ? 'edit&id=' . $sermonId : 'add'), 'Upload folder exists but is not writable: ' . $uploadDir, 'danger');
            }
            $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['audio_file']['name']));
            $savedName = date('Ymd-His') . '_' . $safeName;
            $targetPath = $uploadDir . $savedName;
            sermonDebug('move_uploaded_file', ['from' => $_FILES['audio_file']['tmp_name'], 'to' => $targetPath]);
            if (move_uploaded_file($_FILES['audio_file']['tmp_name'], $targetPath)) {
                $audioUrl = AUDIO_UPLOAD_WEB . $savedName;
                sermonDebug('File saved OK', $audioUrl);
            } else {
                sermonDebug('move_uploaded_file FAILED');
                redirect('sermons.php?action=' . ($sermonId ? 'edit&id=' . $sermonId : 'add'), 'Could not save the file. Check that the upload folder is writable.', 'danger');
            }
        } else {
            sermonDebug('No file uploaded, using link or existing');
            // No file: use external link or existing
            $audioUrl = trim(sanitize($_POST['audio_url_external'] ?? ''));
            if (!$audioUrl) {
                $audioUrl = $existingUrl;
            }
            sermonDebug('audioUrl', $audioUrl ?: '(empty)');
            if ($audioUrl && !preg_match('#^(https?://|/)#', $audioUrl)) {
                redirect('sermons.php?action=' . ($sermonId ? 'edit&id=' . $sermonId : 'add'), 'Audio URL must start with http://, https://, or /.', 'danger');
            }
            if (!$audioUrl && !$sermonId) {
                sermonDebug('No audio: redirect to add with error');
                $sizeHint = ' If you did select a file, it may be larger than the server limit (check upload_max_filesize / post_max_size in PHP).';
                redirect('sermons.php?action=add', 'Please choose an MP3 file to upload, or paste a link to the audio.' . $sizeHint, 'danger');
            }
        }
        
        $data = [
            'title' => sanitize($_POST['title'] ?? ''),
            'description' => sanitize($_POST['description'] ?? ''),
            'speaker' => sanitize($_POST['speaker'] ?? ''),
            'sermon_type' => 'audio',
            'youtube_url' => '',
            'audio_url' => $audioUrl,
            'thumbnail_url' => '',
            'sermon_date' => !empty($_POST['sermon_date']) ? $_POST['sermon_date'] : null,
            'category' => sanitize($_POST['category'] ?? ''),
            'status' => 'published'  // always published when added/updated
        ];
        
        if ($sermonId) {
            $stmt = $db->prepare("SELECT id FROM sermons WHERE id = ?");
            $stmt->execute([$sermonId]);
            if (!$stmt->fetch()) {
                redirect('sermons.php', 'Sermon not found.', 'danger');
            }
            
            $stmt = $db->prepare("UPDATE sermons SET title = ?, description = ?, speaker = ?, sermon_type = ?, youtube_url = ?, audio_url = ?, thumbnail_url = ?, sermon_date = ?, category = ?, status = ? WHERE id = ?");
            $stmt->execute([$data['title'], $data['description'], $data['speaker'], $data['sermon_type'], $data['youtube_url'], $data['audio_url'], $data['thumbnail_url'], $data['sermon_date'], $data['category'], $data['status'], $sermonId]);
            
            if ($stmt->rowCount() > 0) {
                redirect('sermons.php', 'Sermon updated successfully.');
            } else {
                redirect('sermons.php', 'No changes were made.', 'info');
            }
        } else {
            sermonDebug('INSERT sermon', ['title' => $data['title'], 'audio_url' => $data['audio_url']]);
            $stmt = $db->prepare("INSERT INTO sermons (title, description, speaker, sermon_type, youtube_url, audio_url, thumbnail_url, sermon_date, category, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['title'], $data['description'], $data['speaker'], $data['sermon_type'], $data['youtube_url'], $data['audio_url'], $data['thumbnail_url'], $data['sermon_date'], $data['category'], $data['status']]);
            $newId = $db->lastInsertId();
            sermonDebug('INSERT result', 'lastInsertId=' . $newId . ' rowCount=' . $stmt->rowCount());
            if ($newId) {
                sermonDebug('SUCCESS: sermon added id=' . $newId);
                redirect('sermons.php', 'Sermon added successfully.');
            } else {
                sermonDebug('INSERT failed: no lastInsertId');
                redirect('sermons.php?action=add', 'Failed to add sermon. Please try again.', 'danger');
            }
        }
    } catch (PDOException $e) {
        sermonDebug('PDOException', $e->getMessage());
        redirect('sermons.php', handleDBError($e, 'A database error occurred. Please try again.'), 'danger');
    } catch (Exception $e) {
        sermonDebug('Exception', $e->getMessage());
        error_log("Error in sermons.php: " . $e->getMessage());
        redirect('sermons.php', 'An error occurred: ' . htmlspecialchars($e->getMessage()), 'danger');
    }
}

$pageTitle = 'Audio Sermons Management';
require_once 'includes/header.php';

if ($action === 'add' || $action === 'edit') {
    $sermon = null;
    if ($id) {
        try {
            $stmt = $db->prepare("SELECT * FROM sermons WHERE id = ?");
            $stmt->execute([$id]);
            $sermon = $stmt->fetch();
            if (!$sermon) {
                redirect('sermons.php', 'Sermon not found.', 'danger');
            }
        } catch (PDOException $e) {
            redirect('sermons.php', handleDBError($e, 'Error loading sermon.'), 'danger');
        }
    }
    ?>
    <style>
        .audio-sermon-form .card { border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        .audio-sermon-form .card-header { font-weight: 600; }
        .audio-sermon-form .form-control, .audio-sermon-form .form-select { border-radius: 8px; }
        .audio-sermon-form .file-upload-wrap { border: 2px dashed var(--border-color, #dee2e6); border-radius: 12px; padding: 1.5rem; text-align: center; background: #f8f9fa; }
        .audio-sermon-form .file-upload-wrap input[type="file"] { max-width: 100%; }
        .audio-sermon-form .btn-primary { border-radius: 8px; }
        @media (max-width: 768px) { .audio-sermon-form .col-md-4 { margin-top: 0; } }
    </style>
    <?php
    $audioStoragePath = defined('AUDIO_UPLOAD_DIR') ? AUDIO_UPLOAD_DIR : (dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'audio' . DIRECTORY_SEPARATOR);
    if (!is_dir($audioStoragePath)) {
        @mkdir($audioStoragePath, 0755, true);
    }
    ?>
    <div class="audio-sermon-form">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><?php echo $id ? 'Edit' : 'Add'; ?> Audio Sermon</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" action="sermons.php?action=<?php echo $id ? 'edit&id=' . (int)$id : 'add'; ?>">
                    <?php if ($id): ?><input type="hidden" name="id" value="<?php echo (int)$id; ?>"><?php endif; ?>
                    <div class="row g-3">
                        <div class="col-12 col-lg-8">
                            <div class="mb-3">
                                <label class="form-label">Title *</label>
                                <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($sermon['title'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="4"><?php echo htmlspecialchars($sermon['description'] ?? ''); ?></textarea>
                            </div>
                            <div class="row g-3">
                                <div class="col-12 col-sm-6 mb-3">
                                    <label class="form-label">Speaker</label>
                                    <input type="text" class="form-control" name="speaker" value="<?php echo htmlspecialchars($sermon['speaker'] ?? ''); ?>">
                                </div>
                                <div class="col-12 col-sm-6 mb-3">
                                    <label class="form-label">Sermon Date</label>
                                    <input type="date" class="form-control" name="sermon_date" value="<?php echo $sermon['sermon_date'] ?? ''; ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <input type="text" class="form-control" name="category" value="<?php echo htmlspecialchars($sermon['category'] ?? ''); ?>" placeholder="e.g. Teaching, Faith">
                            </div>
                            <div class="mb-3 p-3 rounded" style="background: #e7f5e9; border: 1px solid #a3cfbb;">
                                <label class="form-label fw-semibold">Upload audio file (recommended – uses your Hostinger space, no extra cost)</label>
                                <div class="file-upload-wrap">
                                    <input type="file" class="form-control" name="audio_file" accept=".mp3,.wav,.ogg,.m4a,.webm,audio/*">
                                    <small class="d-block mt-2 text-muted">MP3, WAV, OGG, M4A. Max 500 MB. Stored on your hosting – favors your included space; no third‑party fees.</small>
                                    <?php if ($id && !empty($sermon['audio_url'])): ?>
                                        <p class="mt-2 mb-0 small text-success">Current: <?php echo htmlspecialchars($sermon['audio_url']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Or paste link (only if file is already hosted elsewhere)</label>
                                <input type="url" class="form-control" name="audio_url_external" value="<?php echo htmlspecialchars($sermon['audio_url'] ?? ''); ?>" placeholder="https://… (optional)">
                                <small class="d-block mt-2 text-muted">Use only when the audio is already on another site (e.g. free link). Otherwise upload above to use your Hostinger space and avoid extra cost.</small>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Save Audio Sermon
                        </button>
                        <a href="sermons.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
} else {
    // List view - audio sermons only
    $page = max(1, intval($_GET['page'] ?? 1));
    $offset = ($page - 1) * ITEMS_PER_PAGE;
    
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM sermons WHERE sermon_type = 'audio'");
    $stmt->execute();
    $total = $stmt->fetch()['total'];
    $totalPages = ceil($total / ITEMS_PER_PAGE);
    
    $stmt = $db->prepare("SELECT * FROM sermons WHERE sermon_type = 'audio' ORDER BY sermon_date DESC, created_at DESC LIMIT ? OFFSET ?");
    $stmt->bindValue(1, ITEMS_PER_PAGE, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $sermons = $stmt->fetchAll();
    ?>
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4">
        <h2 class="mb-0">Audio Sermons</h2>
        <a href="sermons.php?action=add" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Add Audio Sermon
        </a>
    </div>
    <div class="card" style="border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.08);">
        <div class="card-body">
            <?php if (empty($sermons)): ?>
                <p class="text-muted mb-0">No audio sermons yet. <a href="sermons.php?action=add">Add your first audio sermon</a>.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Speaker</th>
                                <th>Type</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sermons as $sermon): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($sermon['title']); ?></td>
                                    <td><?php echo htmlspecialchars($sermon['speaker']); ?></td>
                                    <td><span class="badge bg-info"><?php echo ucfirst($sermon['sermon_type']); ?></span></td>
                                    <td><?php echo formatDate($sermon['sermon_date']); ?></td>
                                    <td><span class="badge bg-<?php echo $sermon['status'] === 'published' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($sermon['status']); ?></span></td>
                                    <td>
                                        <a href="sermons.php?action=edit&id=<?php echo $sermon['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this sermon?');">
                                            <input type="hidden" name="id" value="<?php echo $sermon['id']; ?>">
                                            <button type="submit" name="delete" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($totalPages > 1): ?>
                    <nav>
                        <ul class="pagination">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

require_once 'includes/footer.php';
?>

