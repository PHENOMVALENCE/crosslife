<?php
$pageTitle = 'Sermons Management';
require_once 'includes/header.php';

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $stmt = $db->prepare("DELETE FROM sermons WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        redirect('sermons.php', 'Sermon deleted successfully.');
    }
    
    $data = [
        'title' => sanitize($_POST['title'] ?? ''),
        'description' => sanitize($_POST['description'] ?? ''),
        'speaker' => sanitize($_POST['speaker'] ?? ''),
        'sermon_type' => $_POST['sermon_type'] ?? 'video',
        'youtube_url' => sanitize($_POST['youtube_url'] ?? ''),
        'audio_url' => sanitize($_POST['audio_url'] ?? ''),
        'thumbnail_url' => sanitize($_POST['thumbnail_url'] ?? ''),
        'sermon_date' => $_POST['sermon_date'] ?? null,
        'category' => sanitize($_POST['category'] ?? ''),
        'status' => $_POST['status'] ?? 'draft'
    ];
    
    if ($id) {
        // Update
        $stmt = $db->prepare("UPDATE sermons SET title = ?, description = ?, speaker = ?, sermon_type = ?, youtube_url = ?, audio_url = ?, thumbnail_url = ?, sermon_date = ?, category = ?, status = ? WHERE id = ?");
        $stmt->execute([$data['title'], $data['description'], $data['speaker'], $data['sermon_type'], $data['youtube_url'], $data['audio_url'], $data['thumbnail_url'], $data['sermon_date'], $data['category'], $data['status'], $id]);
        redirect('sermons.php', 'Sermon updated successfully.');
    } else {
        // Insert
        $stmt = $db->prepare("INSERT INTO sermons (title, description, speaker, sermon_type, youtube_url, audio_url, thumbnail_url, sermon_date, category, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$data['title'], $data['description'], $data['speaker'], $data['sermon_type'], $data['youtube_url'], $data['audio_url'], $data['thumbnail_url'], $data['sermon_date'], $data['category'], $data['status']]);
        redirect('sermons.php', 'Sermon added successfully.');
    }
}

if ($action === 'add' || $action === 'edit') {
    $sermon = null;
    if ($id) {
        $stmt = $db->prepare("SELECT * FROM sermons WHERE id = ?");
        $stmt->execute([$id]);
        $sermon = $stmt->fetch();
        if (!$sermon) {
            redirect('sermons.php', 'Sermon not found.', 'danger');
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
    // List view
    $page = max(1, intval($_GET['page'] ?? 1));
    $offset = ($page - 1) * ITEMS_PER_PAGE;
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM sermons");
    $total = $stmt->fetch()['total'];
    $totalPages = ceil($total / ITEMS_PER_PAGE);
    
    $stmt = $db->prepare("SELECT * FROM sermons ORDER BY sermon_date DESC, created_at DESC LIMIT ? OFFSET ?");
    $stmt->bindValue(1, ITEMS_PER_PAGE, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $sermons = $stmt->fetchAll();
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
                    <table class="table table-hover">
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

