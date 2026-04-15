<?php
$pageTitle = 'Gallery Albums';
require_once 'config/config.php';
requireLogin();
requireRole(['super_admin', 'admin', 'editor']);

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['delete'])) {
            if (empty($_POST['id'])) {
                redirect('gallery-albums.php', 'Invalid album ID.', 'danger');
            }

            $stmt = $db->prepare('DELETE FROM gallery_albums WHERE id = ?');
            $stmt->execute([$_POST['id']]);

            if ($stmt->rowCount() > 0) {
                redirect('gallery-albums.php', 'Album deleted successfully.');
            }
            redirect('gallery-albums.php', 'Album not found or already deleted.', 'warning');
        }

        if (empty($_POST['title']) || empty($_POST['google_photos_url'])) {
            redirect('gallery-albums.php?action=' . ($id ? 'edit&id=' . $id : 'add'), 'Title and Google Photos URL are required.', 'danger');
        }

        $title = sanitize($_POST['title'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $googlePhotosUrl = trim($_POST['google_photos_url'] ?? '');
        $coverImage = sanitize($_POST['cover_image'] ?? 'assets/img/melchezed order.jpeg');
        $displayOrder = (int) ($_POST['display_order'] ?? 0);
        $status = in_array($_POST['status'] ?? 'active', ['active', 'inactive'], true) ? $_POST['status'] : 'active';

        if (!filter_var($googlePhotosUrl, FILTER_VALIDATE_URL)) {
            redirect('gallery-albums.php?action=' . ($id ? 'edit&id=' . $id : 'add'), 'Please provide a valid Google Photos URL.', 'danger');
        }

        $albumId = $id ?: ($_POST['id'] ?? null);

        if ($albumId) {
            $stmt = $db->prepare('UPDATE gallery_albums SET title = ?, description = ?, google_photos_url = ?, cover_image = ?, display_order = ?, status = ? WHERE id = ?');
            $stmt->execute([$title, $description, $googlePhotosUrl, $coverImage, $displayOrder, $status, $albumId]);

            if ($stmt->rowCount() > 0) {
                redirect('gallery-albums.php', 'Album updated successfully.');
            }
            redirect('gallery-albums.php', 'No changes were made.', 'info');
        }

        $stmt = $db->prepare('INSERT INTO gallery_albums (title, description, google_photos_url, cover_image, display_order, status) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$title, $description, $googlePhotosUrl, $coverImage, $displayOrder, $status]);

        if ($stmt->rowCount() > 0) {
            redirect('gallery-albums.php', 'Album added successfully.');
        }

        redirect('gallery-albums.php?action=add', 'Failed to add album. Please try again.', 'danger');
    } catch (PDOException $e) {
        redirect('gallery-albums.php', handleDBError($e, 'A database error occurred while saving the album.'), 'danger');
    } catch (Exception $e) {
        error_log('Error in gallery-albums.php: ' . $e->getMessage());
        redirect('gallery-albums.php', 'An error occurred: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES), 'danger');
    }
}

if ($action === 'add' || $action === 'edit') {
    $album = null;
    if ($id) {
        $stmt = $db->prepare('SELECT * FROM gallery_albums WHERE id = ?');
        $stmt->execute([$id]);
        $album = $stmt->fetch();

        if (!$album) {
            redirect('gallery-albums.php', 'Album not found.', 'danger');
        }
    }
}

require_once 'includes/header.php';

if ($action === 'add' || $action === 'edit') {
?>
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><?php echo $id ? 'Edit' : 'Add'; ?> Gallery Album</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <?php if ($id): ?>
                <input type="hidden" name="id" value="<?php echo (int) $id; ?>">
            <?php endif; ?>

            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($album['title'] ?? '', ENT_QUOTES); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="4"><?php echo htmlspecialchars($album['description'] ?? '', ENT_QUOTES); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Google Photos Album URL *</label>
                        <input type="url" class="form-control" name="google_photos_url" value="<?php echo htmlspecialchars($album['google_photos_url'] ?? '', ENT_QUOTES); ?>" placeholder="https://photos.app.goo.gl/..." required>
                        <small class="text-muted">The Download button on the public card opens this link.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cover Image (shared or custom)</label>
                        <input type="text" class="form-control" name="cover_image" value="<?php echo htmlspecialchars($album['cover_image'] ?? 'assets/img/melchezed order.jpeg', ENT_QUOTES); ?>">
                        <small class="text-muted">Keep default to use the same image across all album cards.</small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Display Order</label>
                        <input type="number" class="form-control" name="display_order" value="<?php echo (int) ($album['display_order'] ?? 0); ?>" min="0">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status *</label>
                        <select class="form-control" name="status" required>
                            <option value="active" <?php echo ($album['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($album['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Album</button>
                <a href="gallery-albums.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php
} else {
    try {
        $stmt = $db->query('SELECT * FROM gallery_albums ORDER BY display_order ASC, created_at DESC');
        $albums = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Database error loading gallery albums: ' . $e->getMessage());
        $albums = [];
    }
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gallery Albums</h2>
    <a href="?action=add" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Add Album</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Google Photos URL</th>
                        <th>Status</th>
                        <th>Order</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($albums as $album): ?>
                    <tr>
                        <td><?php echo (int) $album['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($album['title'], ENT_QUOTES); ?></strong><br>
                            <small class="text-muted"><?php echo htmlspecialchars(mb_strimwidth((string) ($album['description'] ?? ''), 0, 80, '...'), ENT_QUOTES); ?></small>
                        </td>
                        <td>
                            <a href="<?php echo htmlspecialchars($album['google_photos_url'], ENT_QUOTES); ?>" target="_blank" rel="noopener noreferrer">
                                Open Album
                            </a>
                        </td>
                        <td>
                            <?php if ($album['status'] === 'active'): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo (int) $album['display_order']; ?></td>
                        <td><?php echo formatDateTime($album['created_at']); ?></td>
                        <td>
                            <a href="?action=edit&id=<?php echo (int) $album['id']; ?>" class="btn btn-sm btn-outline-primary me-1">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this album?');">
                                <input type="hidden" name="id" value="<?php echo (int) $album['id']; ?>">
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
    </div>
</div>
<?php
}

require_once 'includes/footer.php';
