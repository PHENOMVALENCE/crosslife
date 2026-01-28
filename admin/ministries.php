<?php
$pageTitle = 'Ministries Management';
require_once 'includes/header.php';

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['delete'])) {
            if (empty($_POST['id'])) {
                redirect('ministries.php', 'Invalid ministry ID.', 'danger');
            }
            
            // Delete image if exists
            try {
                $stmt = $db->prepare("SELECT image_url FROM ministries WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $ministry = $stmt->fetch();
                if ($ministry && !empty($ministry['image_url'])) {
                    // Only delete if it's an uploaded file (contains uploads/)
                    if (strpos($ministry['image_url'], 'uploads/') !== false) {
                        $imagePath = '../assets/img/' . str_replace(UPLOAD_URL, '', $ministry['image_url']);
                        // Also try direct path replacement
                        if (!file_exists($imagePath)) {
                            $imagePath = str_replace(SITE_URL . '/assets/img/', '../assets/img/', $ministry['image_url']);
                        }
                        if (file_exists($imagePath)) {
                            @unlink($imagePath);
                        }
                    }
                }
            } catch (PDOException $e) {
                // Log error but continue with deletion
                error_log("Error deleting ministry image: " . $e->getMessage());
            }
            
            $stmt = $db->prepare("DELETE FROM ministries WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            
            if ($stmt->rowCount() > 0) {
                redirect('ministries.php', 'Ministry deleted successfully.');
            } else {
                redirect('ministries.php', 'Ministry not found or already deleted.', 'warning');
            }
        }
    
        // Validate required fields
        if (empty($_POST['name']) || empty($_POST['description'])) {
            redirect('ministries.php?action=' . ($id ? 'edit&id=' . $id : 'add'), 'Name and Description are required fields.', 'danger');
        }
        
        // Handle image upload
        $image_url = sanitize($_POST['image_url'] ?? '');
        $uploadError = '';
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = UPLOAD_DIR;
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    $uploadError = 'Failed to create upload directory.';
                }
            }
            
            if (empty($uploadError)) {
                $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (!in_array($fileExtension, $allowedExtensions)) {
                    $uploadError = 'Invalid file type. Only JPG, PNG, GIF, and WebP images are allowed.';
                } elseif ($_FILES['image']['size'] > 5242880) { // 5MB limit
                    $uploadError = 'File size exceeds 5MB limit.';
                } else {
                    $fileName = 'ministry_' . time() . '_' . uniqid() . '.' . $fileExtension;
                    $filePath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $filePath)) {
                        // Delete old image if updating
                        $ministryIdForDelete = $id ?: ($_POST['id'] ?? null);
                        if ($ministryIdForDelete) {
                            try {
                                $stmt = $db->prepare("SELECT image_url FROM ministries WHERE id = ?");
                                $stmt->execute([$ministryIdForDelete]);
                                $oldMinistry = $stmt->fetch();
                                if ($oldMinistry && !empty($oldMinistry['image_url']) && strpos($oldMinistry['image_url'], 'uploads/') !== false) {
                                    // Try multiple path formats
                                    $oldImagePath = '../assets/img/' . str_replace(UPLOAD_URL, '', $oldMinistry['image_url']);
                                    if (!file_exists($oldImagePath)) {
                                        $oldImagePath = str_replace(SITE_URL . '/assets/img/', '../assets/img/', $oldMinistry['image_url']);
                                    }
                                    if (!file_exists($oldImagePath) && strpos($oldMinistry['image_url'], '/assets/img/') !== false) {
                                        $oldImagePath = '..' . substr($oldMinistry['image_url'], strpos($oldMinistry['image_url'], '/assets/img/'));
                                    }
                                    if (file_exists($oldImagePath)) {
                                        @unlink($oldImagePath);
                                    }
                                }
                            } catch (PDOException $e) {
                                error_log("Error deleting old image: " . $e->getMessage());
                            }
                        }
                        $image_url = UPLOAD_URL . $fileName;
                    } else {
                        $uploadError = 'Failed to upload image. Please try again.';
                    }
                }
            }
        } elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            // Handle upload errors
            switch ($_FILES['image']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $uploadError = 'File size exceeds maximum allowed size.';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $uploadError = 'File was only partially uploaded.';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $uploadError = 'Missing temporary folder.';
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $uploadError = 'Failed to write file to disk.';
                    break;
                default:
                    $uploadError = 'Unknown upload error occurred.';
            }
        }
        
        if (!empty($uploadError)) {
            redirect('ministries.php?action=' . ($id ? 'edit&id=' . $id : 'add'), $uploadError, 'danger');
        }
        
        // Validate email if provided
        if (!empty($_POST['contact_email']) && !filter_var($_POST['contact_email'], FILTER_VALIDATE_EMAIL)) {
            redirect('ministries.php?action=' . ($id ? 'edit&id=' . $id : 'add'), 'Invalid email address format.', 'danger');
        }
        
        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'description' => sanitize($_POST['description'] ?? ''),
            'image_url' => $image_url,
            'leader_name' => sanitize($_POST['leader_name'] ?? ''),
            'contact_email' => sanitize($_POST['contact_email'] ?? ''),
            'status' => in_array($_POST['status'] ?? 'active', ['active', 'inactive']) ? $_POST['status'] : 'active',
            'display_order' => intval($_POST['display_order'] ?? 0)
        ];
        
        // Get ID from POST if not in URL (for edit form)
        $ministryId = $id ?: ($_POST['id'] ?? null);
        
        if ($ministryId) {
            // Verify ministry exists
            $stmt = $db->prepare("SELECT id FROM ministries WHERE id = ?");
            $stmt->execute([$ministryId]);
            if (!$stmt->fetch()) {
                redirect('ministries.php', 'Ministry not found.', 'danger');
            }
            
            $stmt = $db->prepare("UPDATE ministries SET name = ?, description = ?, image_url = ?, leader_name = ?, contact_email = ?, status = ?, display_order = ? WHERE id = ?");
            $stmt->execute([$data['name'], $data['description'], $data['image_url'], $data['leader_name'], $data['contact_email'], $data['status'], $data['display_order'], $ministryId]);
            
            if ($stmt->rowCount() > 0) {
                redirect('ministries.php', 'Ministry updated successfully.');
            } else {
                redirect('ministries.php', 'No changes were made.', 'info');
            }
        } else {
            $stmt = $db->prepare("INSERT INTO ministries (name, description, image_url, leader_name, contact_email, status, display_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['name'], $data['description'], $data['image_url'], $data['leader_name'], $data['contact_email'], $data['status'], $data['display_order']]);
            
            if ($stmt->rowCount() > 0) {
                redirect('ministries.php', 'Ministry added successfully.');
            } else {
                redirect('ministries.php?action=add', 'Failed to add ministry. Please try again.', 'danger');
            }
        }
    } catch (PDOException $e) {
        error_log("Database error in ministries.php: " . $e->getMessage());
        $errorMsg = 'A database error occurred. Please try again.';
        if (defined('DEBUG') && DEBUG) {
            $errorMsg .= ' Error: ' . $e->getMessage();
        }
        redirect('ministries.php', $errorMsg, 'danger');
    } catch (Exception $e) {
        error_log("Error in ministries.php: " . $e->getMessage());
        redirect('ministries.php', 'An error occurred: ' . htmlspecialchars($e->getMessage()), 'danger');
    }
}

if ($action === 'add' || $action === 'edit') {
    $ministry = null;
    if ($id) {
        try {
            $stmt = $db->prepare("SELECT * FROM ministries WHERE id = ?");
            $stmt->execute([$id]);
            $ministry = $stmt->fetch();
            if (!$ministry) {
                redirect('ministries.php', 'Ministry not found.', 'danger');
            }
        } catch (PDOException $e) {
            error_log("Database error in ministries.php: " . $e->getMessage());
            redirect('ministries.php', 'Error loading ministry. Please try again.', 'danger');
        }
    }
    ?>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><?php echo $id ? 'Edit' : 'Add'; ?> Ministry</h5>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <?php if ($id): ?>
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                <?php endif; ?>
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">Name *</label>
                            <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($ministry['name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description *</label>
                            <textarea class="form-control" name="description" rows="6" required><?php echo htmlspecialchars($ministry['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <?php if (!empty($ministry['image_url'])): ?>
                                <div class="mb-2">
                                    <img src="<?php echo htmlspecialchars($ministry['image_url']); ?>" alt="Current Image" style="max-width: 200px; max-height: 150px; object-fit: cover; border-radius: 4px;">
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" name="image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                            <small class="form-text text-muted">Upload an image (JPG, PNG, GIF, WebP) or use URL below</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Image URL (Alternative)</label>
                            <input type="url" class="form-control" name="image_url" value="<?php echo htmlspecialchars($ministry['image_url'] ?? ''); ?>" placeholder="Or enter image URL">
                            <small class="form-text text-muted">If you upload a file above, it will override this URL</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Leader Name</label>
                                <input type="text" class="form-control" name="leader_name" value="<?php echo htmlspecialchars($ministry['leader_name'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Email</label>
                                <input type="email" class="form-control" name="contact_email" value="<?php echo htmlspecialchars($ministry['contact_email'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Status *</label>
                            <select class="form-control" name="status" required>
                                <option value="active" <?php echo ($ministry['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($ministry['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" class="form-control" name="display_order" value="<?php echo $ministry['display_order'] ?? 0; ?>" min="0">
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Ministry</button>
                    <a href="ministries.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <?php
} else {
    try {
        $stmt = $db->query("SELECT * FROM ministries ORDER BY display_order ASC, name ASC");
        $ministries = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Database error loading ministries: " . $e->getMessage());
        $ministries = [];
        $flash = getFlashMessage();
        if (!$flash) {
            $_SESSION['flash_message'] = 'Error loading ministries. Please refresh the page.';
            $_SESSION['flash_type'] = 'danger';
        }
    }
    ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Ministries</h2>
        <a href="ministries.php?action=add" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Add New Ministry</a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <?php if (empty($ministries)): ?>
                <p class="text-muted">No ministries found. <a href="ministries.php?action=add">Add your first ministry</a>.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th style="width: 80px;">Image</th>
                                <th>Name</th>
                                <th>Leader</th>
                                <th>Contact</th>
                                <th style="width: 80px;">Order</th>
                                <th style="width: 100px;">Status</th>
                                <th style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ministries as $ministry): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($ministry['image_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($ministry['image_url']); ?>" alt="<?php echo htmlspecialchars($ministry['name']); ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                                        <?php else: ?>
                                            <div style="width: 60px; height: 60px; background: #f0f0f0; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                                <i class="bi bi-image" style="font-size: 24px; color: #ccc;"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($ministry['name']); ?></strong>
                                        <?php if (!empty($ministry['description'])): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars(substr($ministry['description'], 0, 60)) . (strlen($ministry['description']) > 60 ? '...' : ''); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($ministry['leader_name'] ?: '-'); ?></td>
                                    <td>
                                        <?php if (!empty($ministry['contact_email'])): ?>
                                            <a href="mailto:<?php echo htmlspecialchars($ministry['contact_email']); ?>"><?php echo htmlspecialchars($ministry['contact_email']); ?></a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $ministry['display_order']; ?></td>
                                    <td><span class="badge bg-<?php echo $ministry['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($ministry['status']); ?></span></td>
                                    <td>
                                        <a href="ministries.php?action=edit&id=<?php echo $ministry['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this ministry?');">
                                            <input type="hidden" name="id" value="<?php echo $ministry['id']; ?>">
                                            <button type="submit" name="delete" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
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

