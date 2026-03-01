<?php
$pageTitle = 'Sermons Management';
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
        
        $data = [
            'title' => sanitize($_POST['title'] ?? ''),
            'description' => sanitize($_POST['description'] ?? ''),
            'speaker' => sanitize($_POST['speaker'] ?? ''),
            'sermon_type' => in_array($_POST['sermon_type'] ?? 'video', ['video', 'audio']) ? $_POST['sermon_type'] : 'video',
            'youtube_url' => sanitize($_POST['youtube_url'] ?? ''),
            'audio_url' => sanitize($_POST['audio_url'] ?? ''),
            'thumbnail_url' => sanitize($_POST['thumbnail_url'] ?? ''),
            'sermon_date' => (!empty($_POST['sermon_date']) && $_POST['sermon_date'] !== '0000-00-00') ? $_POST['sermon_date'] : null,
            'category' => sanitize($_POST['category'] ?? ''),
            'status' => in_array($_POST['status'] ?? 'draft', ['published', 'draft']) ? $_POST['status'] : 'draft'
        ];
        
        $sermonId = $id ?: ($_POST['id'] ?? null);
        
        if ($sermonId) {
            // Verify sermon exists
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
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><?php echo $id ? 'Edit' : 'Add'; ?> Sermon</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($sermon['title'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="4"><?php echo htmlspecialchars($sermon['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Speaker</label>
                                <input type="text" class="form-control" name="speaker" value="<?php echo htmlspecialchars($sermon['speaker'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sermon Date</label>
                                <input type="date" class="form-control" name="sermon_date" value="<?php echo $sermon['sermon_date'] ?? ''; ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sermon Type *</label>
                                <select class="form-control" name="sermon_type" required>
                                    <option value="video" <?php echo ($sermon['sermon_type'] ?? 'video') === 'video' ? 'selected' : ''; ?>>Video</option>
                                    <option value="audio" <?php echo ($sermon['sermon_type'] ?? '') === 'audio' ? 'selected' : ''; ?>>Audio</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <input type="text" class="form-control" name="category" value="<?php echo htmlspecialchars($sermon['category'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">YouTube URL</label>
                            <input type="url" class="form-control" name="youtube_url" value="<?php echo htmlspecialchars($sermon['youtube_url'] ?? ''); ?>" placeholder="https://www.youtube.com/watch?v=...">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Audio URL</label>
                            <input type="url" class="form-control" name="audio_url" value="<?php echo htmlspecialchars($sermon['audio_url'] ?? ''); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Thumbnail URL</label>
                            <input type="url" class="form-control" name="thumbnail_url" value="<?php echo htmlspecialchars($sermon['thumbnail_url'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Status *</label>
                            <select class="form-control" name="status" required>
                                <option value="draft" <?php echo ($sermon['status'] ?? 'draft') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                <option value="published" <?php echo ($sermon['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Published</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Save Sermon
                    </button>
                    <a href="sermons.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <?php
} else {
    // List view - Load all records for DataTables (it handles pagination client-side)
    try {
        $stmt = $db->query("SELECT * FROM sermons ORDER BY sermon_date DESC, created_at DESC");
        $sermons = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Database error loading sermons: " . $e->getMessage());
        $sermons = [];
    }
    ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Sermons</h2>
        <a href="sermons.php?action=add" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Add New Sermon
        </a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <?php if (empty($sermons)): ?>
                <p class="text-muted">No sermons found. <a href="sermons.php?action=add">Add your first sermon</a>.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover datatable" data-dt-options='{"order":[[3,"desc"]]}'>
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
            <?php endif; ?>
        </div>
    </div>
    <?php
}

require_once 'includes/footer.php';
?>

