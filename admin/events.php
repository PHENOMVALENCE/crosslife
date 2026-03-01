<?php
$pageTitle = 'Events Management';
require_once 'config/config.php';
requireLogin();

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {
        if (isset($_POST['delete'])) {
            if (empty($_POST['id'])) {
                redirect('events.php', 'Invalid event ID.', 'danger');
            }
            
            $stmt = $db->prepare("DELETE FROM events WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            
            if ($stmt->rowCount() > 0) {
                redirect('events.php', 'Event deleted successfully.');
            } else {
                redirect('events.php', 'Event not found or already deleted.', 'warning');
            }
        }
        
        // Validate required fields
        if (empty($_POST['title']) || empty($_POST['event_date'])) {
            redirect('events.php?action=' . ($id ? 'edit&id=' . $id : 'add'), 'Title and Event Date are required fields.', 'danger');
        }
        
        // Determine image URL: uploaded file takes priority, then URL input, then keep current
        $image_url = sanitize($_POST['image_url'] ?? '');
        $removeImage = ($_POST['remove_image'] ?? '0') === '1';

        if ($removeImage) {
            $image_url = '';
        } elseif (!empty($_FILES['image_file']['name']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $eventsDir = rtrim(UPLOAD_DIR, '/\\') . DIRECTORY_SEPARATOR . 'events';
                if (!is_dir($eventsDir)) {
                    mkdir($eventsDir, 0755, true);
                }
                $newName = 'event_' . time() . '_' . uniqid() . '.' . $ext;
                $dest = $eventsDir . DIRECTORY_SEPARATOR . $newName;
                if (move_uploaded_file($_FILES['image_file']['tmp_name'], $dest)) {
                    $image_url = UPLOAD_PATH_RELATIVE . 'events/' . $newName;
                }
            }
        } elseif (empty($image_url) && !empty($_POST['current_image_url'])) {
            $image_url = sanitize($_POST['current_image_url']);
        }

        $data = [
            'title' => sanitize($_POST['title'] ?? ''),
            'description' => sanitize($_POST['description'] ?? ''),
            'event_date' => $_POST['event_date'] ?? null,
            'event_time' => !empty($_POST['event_time']) ? $_POST['event_time'] : null,
            'end_date' => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
            'end_time' => !empty($_POST['end_time']) ? $_POST['end_time'] : null,
            'location' => sanitize($_POST['location'] ?? ''),
            'event_type' => sanitize($_POST['event_type'] ?? ''),
            'image_url' => $image_url,
            'status' => in_array($_POST['status'] ?? 'upcoming', ['upcoming', 'ongoing', 'completed', 'cancelled']) ? $_POST['status'] : 'upcoming'
        ];
        
        $eventId = $id ?: ($_POST['id'] ?? null);
        
        if ($eventId) {
            // Verify event exists
            $stmt = $db->prepare("SELECT id FROM events WHERE id = ?");
            $stmt->execute([$eventId]);
            if (!$stmt->fetch()) {
                redirect('events.php', 'Event not found.', 'danger');
            }
            
            $stmt = $db->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, event_time = ?, end_date = ?, end_time = ?, location = ?, event_type = ?, image_url = ?, status = ? WHERE id = ?");
            $stmt->execute([$data['title'], $data['description'], $data['event_date'], $data['event_time'], $data['end_date'], $data['end_time'], $data['location'], $data['event_type'], $data['image_url'], $data['status'], $eventId]);
            
            if ($stmt->rowCount() > 0) {
                redirect('events.php', 'Event updated successfully.');
            } else {
                redirect('events.php', 'No changes were made.', 'info');
            }
        } else {
            $stmt = $db->prepare("INSERT INTO events (title, description, event_date, event_time, end_date, end_time, location, event_type, image_url, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['title'], $data['description'], $data['event_date'], $data['event_time'], $data['end_date'], $data['end_time'], $data['location'], $data['event_type'], $data['image_url'], $data['status']]);
            
                if ($stmt->rowCount() > 0) {
                redirect('events.php', 'Event added successfully.');
            } else {
                redirect('events.php?action=add', 'Failed to add event. Please try again.', 'danger');
            }
        }
    } catch (PDOException $e) {
        redirect('events.php', handleDBError($e, 'A database error occurred. Please try again.'), 'danger');
    } catch (Exception $e) {
        error_log("Error in events.php: " . $e->getMessage());
        redirect('events.php', 'An error occurred: ' . htmlspecialchars($e->getMessage()), 'danger');

    }
}

if ($action === 'add' || $action === 'edit') {
    $event = null;
    if ($id) {
        try {
            $stmt = $db->prepare("SELECT * FROM events WHERE id = ?");
            $stmt->execute([$id]);
            $event = $stmt->fetch();
            if (!$event) {
                redirect('events.php', 'Event not found.', 'danger');
            }
        } catch (PDOException $e) {
            redirect('events.php', handleDBError($e, 'Error loading event.'), 'danger');
        }
    }
}

require_once 'includes/header.php';

if ($action === 'add' || $action === 'edit') {
    ?>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><?php echo $id ? 'Edit' : 'Add'; ?> Event</h5>
        </div>
        <div class="card-body">

            <form method="POST" enctype="multipart/form-data">
                <?php if ($id): ?>
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                <?php endif; ?>

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
                                        <img id="imagePreview" src="<?php echo htmlspecialchars(image_url_for_display($event['image_url'] ?? '')); ?>" alt="Preview" class="img-fluid rounded" style="max-height: 300px; width: 100%; object-fit: contain; background: #f8f9fa;">
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
    try {
        // Load all events for DataTables (it handles pagination, filtering, and sorting client-side)
        $stmt = $db->query("SELECT * FROM events ORDER BY event_date DESC");
        $events = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Database error loading events: " . $e->getMessage());
        $events = [];
        $total = 0;
        $totalPages = 0;
        $flash = getFlashMessage();
        if (!$flash) {
            $_SESSION['flash_message'] = 'Error loading events. Please refresh the page.';
            $_SESSION['flash_type'] = 'danger';
        }
    }
    ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Events</h2>
        <div>
            <a href="?status=all" class="btn btn-sm btn-outline-<?php echo ($_GET['status'] ?? 'all') === 'all' ? 'primary' : 'secondary'; ?>">All</a>
            <a href="?status=upcoming" class="btn btn-sm btn-outline-<?php echo ($_GET['status'] ?? '') === 'upcoming' ? 'primary' : 'secondary'; ?>">Upcoming</a>
            <a href="?status=ongoing" class="btn btn-sm btn-outline-<?php echo ($_GET['status'] ?? '') === 'ongoing' ? 'primary' : 'secondary'; ?>">Ongoing</a>
            <a href="?status=completed" class="btn btn-sm btn-outline-<?php echo ($_GET['status'] ?? '') === 'completed' ? 'primary' : 'secondary'; ?>">Completed</a>
            <a href="events.php?action=add" class="btn btn-primary ms-2"><i class="bi bi-plus-circle me-2"></i>Add New Event</a>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <?php if (empty($events)): ?>
                <p class="text-muted">No events found. <a href="events.php?action=add">Add your first event</a>.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover datatable" data-dt-options='{"order":[[1,"desc"]]}'>
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

