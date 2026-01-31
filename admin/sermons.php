<?php
$pageTitle = 'Audio Sermons Management';
require_once 'includes/header.php';

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        
        // Favor your hosting space: check upload first (no extra cost)
        if (!empty($_FILES['audio_file']['name']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['audio_file']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedAudio)) {
                redirect('sermons.php?action=' . ($sermonId ? 'edit&id=' . $sermonId : 'add'), 'Invalid audio format. Use: ' . implode(', ', $allowedAudio), 'danger');
            }
            if ($_FILES['audio_file']['size'] > $maxSize) {
                redirect('sermons.php?action=' . ($sermonId ? 'edit&id=' . $sermonId : 'add'), 'File too large. Max 500 MB. Or paste a link below if the file is already hosted elsewhere.', 'danger');
            }
            if (!is_dir(AUDIO_UPLOAD_DIR)) {
                mkdir(AUDIO_UPLOAD_DIR, 0755, true);
            }
            $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['audio_file']['name']));
            $savedName = date('Ymd-His') . '_' . $safeName;
            if (move_uploaded_file($_FILES['audio_file']['tmp_name'], AUDIO_UPLOAD_DIR . $savedName)) {
                $audioUrl = AUDIO_UPLOAD_WEB . $savedName;
            } else {
                redirect('sermons.php?action=' . ($sermonId ? 'edit&id=' . $sermonId : 'add'), 'Failed to save audio file.', 'danger');
            }
        } else {
            // Fallback: external link (only when no file uploaded)
            $audioUrl = trim(sanitize($_POST['audio_url_external'] ?? ''));
            if (!$audioUrl) {
                $audioUrl = $existingUrl;
            }
            if ($audioUrl && !preg_match('#^(https?://|/)#', $audioUrl)) {
                redirect('sermons.php?action=' . ($sermonId ? 'edit&id=' . $sermonId : 'add'), 'Audio URL must start with http://, https://, or /.', 'danger');
            }
            if (!$audioUrl && !$sermonId) {
                redirect('sermons.php?action=add', 'Upload an audio file or paste a link to the audio.', 'danger');
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
            'status' => in_array($_POST['status'] ?? 'draft', ['published', 'draft']) ? $_POST['status'] : 'draft'
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
            $stmt = $db->prepare("INSERT INTO sermons (title, description, speaker, sermon_type, youtube_url, audio_url, thumbnail_url, sermon_date, category, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['title'], $data['description'], $data['speaker'], $data['sermon_type'], $data['youtube_url'], $data['audio_url'], $data['thumbnail_url'], $data['sermon_date'], $data['category'], $data['status']]);
            
            if ($stmt->rowCount() > 0) {
                redirect('sermons.php', 'Sermon added successfully.');
            } else {
                redirect('sermons.php?action=add', 'Failed to add sermon. Please try again.', 'danger');
            }
        }
    } catch (PDOException $e) {
        redirect('sermons.php', handleDBError($e, 'A database error occurred. Please try again.'), 'danger');
    } catch (Exception $e) {
        error_log("Error in sermons.php: " . $e->getMessage());
        redirect('sermons.php', 'An error occurred: ' . htmlspecialchars($e->getMessage()), 'danger');
    }
}

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
    <div class="audio-sermon-form">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><?php echo $id ? 'Edit' : 'Add'; ?> Audio Sermon</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
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
                        <div class="col-12 col-lg-4">
                            <div class="mb-3">
                                <label class="form-label">Status *</label>
                                <select class="form-select" name="status" required>
                                    <option value="draft" <?php echo ($sermon['status'] ?? 'draft') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                    <option value="published" <?php echo ($sermon['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Published</option>
                                </select>
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

