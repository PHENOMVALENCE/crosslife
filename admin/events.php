<?php
$pageTitle = 'Events Management';
require_once 'includes/header.php';

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $stmt = $db->prepare("DELETE FROM events WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        redirect('events.php', 'Event deleted successfully.');
    }
    
    // Handle image upload
    // Start with existing image (if editing) or from hidden field
    $image_url = sanitize($_POST['current_image_url'] ?? '');
    
    // Check if user explicitly removed the image
    if (isset($_POST['remove_image']) && $_POST['remove_image'] === '1') {
        $image_url = '';
    }
    // Check if a new file was uploaded
    elseif (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/img/uploads/events/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_ext, $allowed_exts)) {
            $new_filename = uniqid('event_') . '.' . $file_ext;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $upload_path)) {
                $image_url = 'assets/img/uploads/events/' . $new_filename;
            }
        }
    } 
    // If no file uploaded, check if a new URL was provided (different from current)
    elseif (!empty($_POST['image_url']) && $_POST['image_url'] !== $_POST['current_image_url']) {
        $image_url = sanitize($_POST['image_url']);
    }
    
    $data = [
        'title' => sanitize($_POST['title'] ?? ''),
        'description' => sanitize($_POST['description'] ?? ''),
        'event_date' => $_POST['event_date'] ?? null,
        'event_time' => $_POST['event_time'] ?? null,
        'end_date' => $_POST['end_date'] ?? null,
        'end_time' => $_POST['end_time'] ?? null,
        'location' => sanitize($_POST['location'] ?? ''),
        'event_type' => sanitize($_POST['event_type'] ?? ''),
        'image_url' => $image_url,
        'status' => $_POST['status'] ?? 'upcoming'
    ];
    
    if ($id) {
        $stmt = $db->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, event_time = ?, end_date = ?, end_time = ?, location = ?, event_type = ?, image_url = ?, status = ? WHERE id = ?");
        $stmt->execute([$data['title'], $data['description'], $data['event_date'], $data['event_time'], $data['end_date'], $data['end_time'], $data['location'], $data['event_type'], $data['image_url'], $data['status'], $id]);
        redirect('events.php', 'Event updated successfully.');
    } else {
        $stmt = $db->prepare("INSERT INTO events (title, description, event_date, event_time, end_date, end_time, location, event_type, image_url, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$data['title'], $data['description'], $data['event_date'], $data['event_time'], $data['end_date'], $data['end_time'], $data['location'], $data['event_type'], $data['image_url'], $data['status']]);
        redirect('events.php', 'Event added successfully.');
    }
}

if ($action === 'add' || $action === 'edit') {
    $event = null;
    if ($id) {
        $stmt = $db->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([$id]);
        $event = $stmt->fetch();
        if (!$event) redirect('events.php', 'Event not found.', 'danger');
    }
    ?>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><?php echo $id ? 'Edit' : 'Add'; ?> Event</h5>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($event['title'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="4"><?php echo htmlspecialchars($event['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Event Date *</label>
                                <input type="date" class="form-control" name="event_date" value="<?php echo $event['event_date'] ?? ''; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Event Time</label>
                                <input type="time" class="form-control" name="event_time" value="<?php echo $event['event_time'] ?? ''; ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" class="form-control" name="end_date" value="<?php echo $event['end_date'] ?? ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Time</label>
                                <input type="time" class="form-control" name="end_time" value="<?php echo $event['end_time'] ?? ''; ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Location</label>
                                <input type="text" class="form-control" name="location" value="<?php echo htmlspecialchars($event['location'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Event Type</label>
                                <input type="text" class="form-control" name="event_type" value="<?php echo htmlspecialchars($event['event_type'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Event Image</label>
                            <!-- Hidden field to preserve current image -->
                            <input type="hidden" name="current_image_url" id="currentImageUrl" value="<?php echo htmlspecialchars($event['image_url'] ?? ''); ?>">
                            <input type="hidden" name="remove_image" id="removeImageFlag" value="0">
                            
                            <input type="file" class="form-control" name="image_file" id="imageFileInput" accept="image/*">
                            <small class="text-muted">Upload an image file (JPG, PNG, GIF, WebP) or use URL below</small>
                            
                            <!-- Image Preview -->
                            <div class="mt-3" id="imagePreviewContainer" <?php echo empty($event['image_url']) ? 'style="display:none;"' : ''; ?>>
                                <div class="card" style="max-width: 400px;">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label mb-0"><strong>Image Preview</strong></label>
                                            <button type="button" class="btn btn-sm btn-outline-danger" id="removeImageBtn" onclick="removeImage()">
                                                <i class="bi bi-trash"></i> Remove
                                            </button>
                                        </div>
                                        <img id="imagePreview" src="<?php echo htmlspecialchars($event['image_url'] ?? ''); ?>" alt="Preview" class="img-fluid rounded" style="max-height: 300px; width: 100%; object-fit: contain; background: #f8f9fa;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Or Image URL</label>
                            <input type="url" class="form-control" name="image_url" id="imageUrlInput" value="<?php echo htmlspecialchars($event['image_url'] ?? ''); ?>" placeholder="https://example.com/image.jpg">
                            <small class="text-muted">Leave empty if uploading a file above</small>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Status *</label>
                            <select class="form-control" name="status" required>
                                <option value="upcoming" <?php echo ($event['status'] ?? 'upcoming') === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                                <option value="ongoing" <?php echo ($event['status'] ?? '') === 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                                <option value="completed" <?php echo ($event['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo ($event['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Event</button>
                    <a href="events.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <?php
} else {
    $page = max(1, intval($_GET['page'] ?? 1));
    $offset = ($page - 1) * ITEMS_PER_PAGE;
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM events");
    $total = $stmt->fetch()['total'];
    $totalPages = ceil($total / ITEMS_PER_PAGE);
    
    $stmt = $db->prepare("SELECT * FROM events ORDER BY event_date DESC LIMIT ? OFFSET ?");
    $stmt->bindValue(1, ITEMS_PER_PAGE, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $events = $stmt->fetchAll();
    ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Events</h2>
        <a href="events.php?action=add" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Add New Event</a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <?php if (empty($events)): ?>
                <p class="text-muted">No events found. <a href="events.php?action=add">Add your first event</a>.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Date</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $event): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($event['title']); ?></td>
                                    <td><?php echo formatDate($event['event_date']); ?></td>
                                    <td><?php echo htmlspecialchars($event['location']); ?></td>
                                    <td><span class="badge bg-<?php echo $event['status'] === 'upcoming' ? 'success' : ($event['status'] === 'ongoing' ? 'warning' : 'secondary'); ?>"><?php echo ucfirst($event['status']); ?></span></td>
                                    <td>
                                        <a href="events.php?action=edit&id=<?php echo $event['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this event?');">
                                            <input type="hidden" name="id" value="<?php echo $event['id']; ?>">
                                            <button type="submit" name="delete" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
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

<script>
// Image preview functionality
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('imageFileInput');
    const urlInput = document.getElementById('imageUrlInput');
    const preview = document.getElementById('imagePreview');
    const previewContainer = document.getElementById('imagePreviewContainer');
    
    // Preview when file is selected
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    previewContainer.style.display = 'block';
                    // Clear URL input when file is selected
                    urlInput.value = '';
                }
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Preview when URL is entered
    if (urlInput) {
        urlInput.addEventListener('blur', function(e) {
            const url = e.target.value.trim();
            if (url) {
                preview.src = url;
                previewContainer.style.display = 'block';
                // Clear file input when URL is entered
                if (fileInput) fileInput.value = '';
            }
        });
    }
});

// Remove image function
function removeImage() {
    const fileInput = document.getElementById('imageFileInput');
    const urlInput = document.getElementById('imageUrlInput');
    const currentImageUrl = document.getElementById('currentImageUrl');
    const removeFlag = document.getElementById('removeImageFlag');
    const preview = document.getElementById('imagePreview');
    const previewContainer = document.getElementById('imagePreviewContainer');
    
    // Clear inputs
    if (fileInput) fileInput.value = '';
    if (urlInput) urlInput.value = '';
    if (currentImageUrl) currentImageUrl.value = '';
    
    // Set remove flag
    if (removeFlag) removeFlag.value = '1';
    
    // Hide preview
    preview.src = '';
    previewContainer.style.display = 'none';
}
</script>

