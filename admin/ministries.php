<?php
/**
 * Ministries Management - Admin
 * Process POST (and redirects) before any output to avoid "headers already sent" errors.
 */
require_once __DIR__ . '/config/config.php';
requireLogin();

$pageTitle = 'Ministries Management';
$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Handle bulk operations
        if (isset($_POST['bulk_action']) && !empty($_POST['selected_ministries'])) {
            $selectedIds = $_POST['selected_ministries'];
            $bulkAction = $_POST['bulk_action'];
            
            if (!is_array($selectedIds)) {
                redirect('ministries.php', 'No ministries selected.', 'warning');
            }
            
            $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
            
            if ($bulkAction === 'delete') {
                // Get images to delete
                $stmt = $db->prepare("SELECT image_url FROM ministries WHERE id IN ($placeholders)");
                $stmt->execute($selectedIds);
                $ministries = $stmt->fetchAll();
                
                foreach ($ministries as $ministry) {
                    $imagePath = upload_path_to_disk($ministry['image_url'] ?? '');
                    if ($imagePath && file_exists($imagePath)) {
                        @unlink($imagePath);
                    }
                }
                
                $stmt = $db->prepare("DELETE FROM ministries WHERE id IN ($placeholders)");
                $stmt->execute($selectedIds);
                redirect('ministries.php', count($selectedIds) . ' ministry(ies) deleted successfully.');
            } elseif ($bulkAction === 'activate') {
                $stmt = $db->prepare("UPDATE ministries SET status = 'active' WHERE id IN ($placeholders)");
                $stmt->execute($selectedIds);
                redirect('ministries.php', count($selectedIds) . ' ministry(ies) activated successfully.');
            } elseif ($bulkAction === 'deactivate') {
                $stmt = $db->prepare("UPDATE ministries SET status = 'inactive' WHERE id IN ($placeholders)");
                $stmt->execute($selectedIds);
                redirect('ministries.php', count($selectedIds) . ' ministry(ies) deactivated successfully.');
            }
        }
        
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
                    $imagePath = upload_path_to_disk($ministry['image_url']);
                    if ($imagePath && file_exists($imagePath)) {
                        @unlink($imagePath);
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
                        // Delete old image file if updating (path from DB: relative or full URL)
                        $ministryIdForDelete = $id ?: ($_POST['id'] ?? null);
                        if ($ministryIdForDelete) {
                            try {
                                $stmt = $db->prepare("SELECT image_url FROM ministries WHERE id = ?");
                                $stmt->execute([$ministryIdForDelete]);
                                $oldMinistry = $stmt->fetch();
                                if ($oldMinistry && !empty($oldMinistry['image_url'])) {
                                    $oldImagePath = upload_path_to_disk($oldMinistry['image_url']);
                                    if ($oldImagePath && file_exists($oldImagePath)) {
                                        @unlink($oldImagePath);
                                    }
                                }
                            } catch (PDOException $e) {
                                error_log("Error deleting old image: " . $e->getMessage());
                            }
                        }
                        // Store relative path in DB: assets/img/uploads/filename (uploads folder)
                        $image_url = (defined('UPLOAD_PATH_RELATIVE') ? UPLOAD_PATH_RELATIVE : 'assets/img/uploads/') . $fileName;
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
        
        // When editing: if no new file uploaded and image URL field is empty, keep existing image
        $ministryIdForData = $id ?: ($_POST['id'] ?? null);
        if ($ministryIdForData && (empty($image_url) || trim($image_url) === '') && (!isset($_FILES['image']['error']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE)) {
            try {
                $stmt = $db->prepare("SELECT image_url FROM ministries WHERE id = ?");
                $stmt->execute([$ministryIdForData]);
                $existing = $stmt->fetch();
                if ($existing && !empty($existing['image_url'])) {
                    $image_url = $existing['image_url'];
                }
            } catch (PDOException $e) {
                error_log("Ministries: could not load existing image_url: " . $e->getMessage());
            }
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
            $stmt = $db->prepare("SELECT id, display_order FROM ministries WHERE id = ?");
            $stmt->execute([$ministryId]);
            $current = $stmt->fetch();
            if (!$current) {
                redirect('ministries.php', 'Ministry not found.', 'danger');
            }
            
            try {
                $db->beginTransaction();
                
                // Build current ordered list (1-based positions): ORDER BY display_order, id for stable order
                $stmt = $db->query("SELECT id FROM ministries ORDER BY display_order ASC, id ASC");
                $orderedIds = array_map('intval', array_column($stmt->fetchAll(), 'id'));
                $n = count($orderedIds);
                
                $currentPosition = array_search((int) $ministryId, $orderedIds, true);
                if ($currentPosition === false) {
                    $db->rollBack();
                    redirect('ministries.php', 'Ministry not found in list.', 'danger');
                }
                $currentPosition1Based = $currentPosition + 1;
                
                // Desired position from form (1 = first); clamp to 1..n
                $newPosition1Based = max(1, min($n, (int) $data['display_order']));
                if ($newPosition1Based < 1) {
                    $newPosition1Based = 1;
                }
                
                // Reorder: remove current item, insert at new position
                if ($newPosition1Based !== $currentPosition1Based) {
                    $idToMove = $orderedIds[$currentPosition];
                    array_splice($orderedIds, $currentPosition, 1);
                    array_splice($orderedIds, $newPosition1Based - 1, 0, [$idToMove]);
                }
                
                // Assign dense display_order 1..n to everyone (normalizes gaps/duplicates and applies reorder)
                foreach ($orderedIds as $position => $mid) {
                    $order = $position + 1;
                    $stmt = $db->prepare("UPDATE ministries SET display_order = ? WHERE id = ?");
                    $stmt->execute([$order, $mid]);
                }
                
                $finalOrder = (int) (array_search((int) $ministryId, $orderedIds, true) + 1);
                
                $stmt = $db->prepare("UPDATE ministries SET name = ?, description = ?, image_url = ?, leader_name = ?, contact_email = ?, status = ?, display_order = ? WHERE id = ?");
                $stmt->execute([$data['name'], $data['description'], $data['image_url'], $data['leader_name'], $data['contact_email'], $data['status'], $finalOrder, $ministryId]);
                
                $db->commit();
                
                if ($stmt->rowCount() > 0) {
                    redirect('ministries.php', 'Ministry updated successfully.');
                } else {
                    redirect('ministries.php', 'No changes were made.', 'info');
                }
            } catch (PDOException $e) {
                $db->rollBack();
                throw $e;
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

// Duplicate: process and redirect before any output
if ($action === 'duplicate') {
    if (!$id) {
        redirect('ministries.php', 'Ministry ID is required.', 'danger');
    }
    try {
        $stmt = $db->prepare("SELECT * FROM ministries WHERE id = ?");
        $stmt->execute([$id]);
        $ministry = $stmt->fetch();
        if (!$ministry) {
            redirect('ministries.php', 'Ministry not found.', 'danger');
        }
        $newName = 'Copy of ' . $ministry['name'];
        $stmt = $db->prepare("INSERT INTO ministries (name, description, image_url, leader_name, contact_email, status, display_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$newName, $ministry['description'], $ministry['image_url'], $ministry['leader_name'], $ministry['contact_email'], 'inactive', $ministry['display_order']]);
        redirect('ministries.php', 'Ministry duplicated successfully. You can now edit it.', 'success');
    } catch (PDOException $e) {
        error_log("Database error in ministries.php: " . $e->getMessage());
        redirect('ministries.php', 'Error duplicating ministry. Please try again.', 'danger');
    }
}

// No output before this: include header only after POST and redirect-only actions
require_once __DIR__ . '/includes/header.php';

if ($action === 'view') {
    // View single ministry
    if (!$id) {
        redirect('ministries.php', 'Ministry ID is required.', 'danger');
    }
    
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
    ?>
    <?php
    $previewImgSrc = !empty($ministry['image_url']) ? image_url_for_display($ministry['image_url']) : '';
    ?>
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="ministries.php">Ministries</a></li>
            <li class="breadcrumb-item active" aria-current="page">Preview: <?php echo htmlspecialchars($ministry['name']); ?></li>
        </ol>
    </nav>
    
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="mb-0">Ministry Preview</h2>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?php echo defined('SITE_URL') ? rtrim(SITE_URL, '/') . '/ministries.php' : '../ministries.php'; ?>" target="_blank" rel="noopener" class="btn btn-outline-success"><i class="bi bi-box-arrow-up-right me-2"></i>Preview on Website</a>
            <a href="ministries.php?action=edit&id=<?php echo (int) $id; ?>" class="btn btn-primary"><i class="bi bi-pencil me-2"></i>Edit</a>
            <a href="ministries.php?action=duplicate&id=<?php echo (int) $id; ?>" class="btn btn-outline-secondary"><i class="bi bi-files me-2"></i>Duplicate</a>
            <a href="ministries.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-2"></i>Back to List</a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><?php echo htmlspecialchars($ministry['name']); ?></h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6 class="text-muted text-uppercase small mb-2">Image</h6>
                        <?php if ($previewImgSrc): ?>
                            <div class="text-center">
                                <img src="<?php echo htmlspecialchars($previewImgSrc); ?>" alt="<?php echo htmlspecialchars($ministry['name']); ?>" class="img-fluid rounded shadow-sm" style="max-height: 400px; object-fit: contain; width: auto;">
                                <p class="small text-muted mt-2 mb-0">Image displays as on the public site when set.</p>
                            </div>
                        <?php else: ?>
                            <div class="border rounded d-flex align-items-center justify-content-center bg-light" style="height: 200px;">
                                <div class="text-center text-muted">
                                    <i class="bi bi-image" style="font-size: 3rem;"></i>
                                    <p class="mb-0 mt-2">No image set</p>
                                    <small>Add an image in Edit to display one on the website.</small>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-4">
                        <h6 class="text-muted text-uppercase small mb-2">Description</h6>
                        <div class="p-3 bg-light rounded">
                            <?php echo nl2br(htmlspecialchars($ministry['description'] ?? '')); ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted text-uppercase small mb-2">Leader Name</h6>
                            <p class="mb-0"><?php echo htmlspecialchars(!empty($ministry['leader_name']) ? $ministry['leader_name'] : '—'); ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted text-uppercase small mb-2">Contact Email</h6>
                            <p class="mb-0">
                                <?php if (!empty($ministry['contact_email'])): ?>
                                    <a href="mailto:<?php echo htmlspecialchars($ministry['contact_email']); ?>"><?php echo htmlspecialchars($ministry['contact_email']); ?></a>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Details</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-muted text-uppercase small mb-2">Status</h6>
                        <span class="badge bg-<?php echo $ministry['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($ministry['status']); ?></span>
                        <?php if ($ministry['status'] === 'active'): ?>
                            <span class="small text-muted ms-2">Visible on website</span>
                        <?php else: ?>
                            <span class="small text-muted ms-2">Hidden on website</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted text-uppercase small mb-2">Display Order</h6>
                        <p class="mb-0"><?php echo (int) ($ministry['display_order'] ?? 0); ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted text-uppercase small mb-2">Created</h6>
                        <p class="mb-0"><?php echo formatDateTime($ministry['created_at'] ?? ''); ?></p>
                    </div>
                    
                    <div class="mb-0">
                        <h6 class="text-muted text-uppercase small mb-2">Last Updated</h6>
                        <p class="mb-0"><?php echo formatDateTime($ministry['updated_at'] ?? ''); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
} elseif ($action === 'add' || $action === 'edit') {
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
                            <textarea class="form-control" name="description" id="description" rows="6" required maxlength="2000"><?php echo htmlspecialchars($ministry['description'] ?? ''); ?></textarea>
                            <small class="form-text text-muted">
                                <span id="descriptionCount">0</span> / 2000 characters
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <?php if (!empty($ministry['image_url'])): ?>
                                <div class="mb-2">
                                    <img src="<?php echo htmlspecialchars(image_url_for_display($ministry['image_url'])); ?>" alt="Current Image" id="imagePreview" style="max-width: 300px; max-height: 200px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; cursor: pointer;" onclick="window.open(this.src, '_blank')" title="Click to view full size">
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearImage()">
                                            <i class="bi bi-x-circle me-1"></i>Remove Image
                                        </button>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="mb-2">
                                    <img id="imagePreview" src="" alt="Preview" style="max-width: 300px; max-height: 200px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; display: none;">
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" name="image" id="imageInput" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" onchange="previewImage(this)">
                            <small class="form-text text-muted">Upload an image (JPG, PNG, GIF, WebP, max 5MB) or use URL below</small>
                            <div class="invalid-feedback" id="imageError"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Image URL (Optional)</label>
                            <input type="text" class="form-control" name="image_url" value="<?php echo htmlspecialchars($ministry['image_url'] ?? ''); ?>" placeholder="Or enter full URL or path (e.g. assets/img/uploads/photo.jpg)">
                            <small class="form-text text-muted">Optional. Upload a file above, or enter a URL/path. Leave blank to keep current image when editing.</small>
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
                            <input type="number" class="form-control" name="display_order" value="<?php echo (int) ($ministry['display_order'] ?? 1); ?>" min="1">
                            <small class="form-text text-muted">1 = first, 2 = second, etc. Changing this moves this ministry to that position and shifts the others.</small>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Ministry</button>
                    <a href="ministries.php" class="btn btn-secondary"><i class="bi bi-x-circle me-2"></i>Cancel</a>
                    <?php if ($id): ?>
                        <a href="ministries.php?action=view&id=<?php echo $id; ?>" class="btn btn-outline-info"><i class="bi bi-eye me-2"></i>View</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Image preview functionality
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const errorDiv = document.getElementById('imageError');
            
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const maxSize = 5 * 1024 * 1024; // 5MB
                
                // Validate file size
                if (file.size > maxSize) {
                    errorDiv.textContent = 'File size exceeds 5MB limit.';
                    errorDiv.style.display = 'block';
                    input.classList.add('is-invalid');
                    preview.style.display = 'none';
                    return;
                }
                
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    errorDiv.textContent = 'Invalid file type. Only JPG, PNG, GIF, and WebP images are allowed.';
                    errorDiv.style.display = 'block';
                    input.classList.add('is-invalid');
                    preview.style.display = 'none';
                    return;
                }
                
                // Clear errors
                errorDiv.style.display = 'none';
                input.classList.remove('is-invalid');
                
                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }
        
        function clearImage() {
            const preview = document.getElementById('imagePreview');
            const input = document.getElementById('imageInput');
            const urlInput = document.querySelector('input[name="image_url"]');
            
            if (confirm('Remove the current image?')) {
                preview.style.display = 'none';
                preview.src = '';
                if (input) input.value = '';
                if (urlInput) urlInput.value = '';
            }
        }
        
        // Description character counter
        const descriptionField = document.getElementById('description');
        const descriptionCount = document.getElementById('descriptionCount');
        if (descriptionField && descriptionCount) {
            function updateDescriptionCount() {
                const length = descriptionField.value.length;
                descriptionCount.textContent = length;
                if (length > 1900) {
                    descriptionCount.classList.add('text-warning');
                    descriptionCount.classList.remove('text-danger');
                } else if (length >= 2000) {
                    descriptionCount.classList.add('text-danger');
                    descriptionCount.classList.remove('text-warning');
                } else {
                    descriptionCount.classList.remove('text-warning', 'text-danger');
                }
            }
            descriptionField.addEventListener('input', updateDescriptionCount);
            updateDescriptionCount(); // Initial count
        }
        
        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form[method="POST"]');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const name = form.querySelector('input[name="name"]');
                    const description = form.querySelector('textarea[name="description"]');
                    let isValid = true;
                    
                    if (!name.value.trim()) {
                        name.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        name.classList.remove('is-invalid');
                    }
                    
                    if (!description.value.trim()) {
                        description.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        description.classList.remove('is-invalid');
                    }
                    
                    const email = form.querySelector('input[name="contact_email"]');
                    if (email && email.value && !email.value.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                        email.classList.add('is-invalid');
                        isValid = false;
                    } else if (email) {
                        email.classList.remove('is-invalid');
                    }
                    
                    if (!isValid) {
                        e.preventDefault();
                        alert('Please fill in all required fields correctly.');
                    }
                });
            }
        });
    </script>
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
                <!-- Bulk Actions -->
                <form method="POST" id="bulkForm" class="mb-3">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <select name="bulk_action" class="form-select form-select-sm" id="bulkActionSelect">
                                <option value="">Bulk Actions</option>
                                <option value="activate">Activate</option>
                                <option value="deactivate">Deactivate</option>
                                <option value="delete">Delete</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-sm btn-outline-primary" id="bulkActionBtn" disabled>
                                <i class="bi bi-check2 me-1"></i>Apply
                            </button>
                        </div>
                        <div class="col-md-6 text-end">
                            <small class="text-muted">
                                <span id="selectedCount">0</span> selected
                            </small>
                        </div>
                    </div>
                    <!-- Hidden container for selected ministries -->
                    <div id="selectedMinistriesContainer"></div>
                </form>
                
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" id="selectAll" title="Select All">
                                </th>
                                <th style="width: 80px;">Image</th>
                                <th>Name</th>
                                <th>Leader</th>
                                <th>Contact</th>
                                <th style="width: 80px;">Order</th>
                                <th style="width: 100px;">Status</th>
                                <th style="width: 180px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ministries as $ministry): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="selected_ministries[]" value="<?php echo $ministry['id']; ?>" class="row-checkbox">
                                    </td>
                                    <td>
                                        <?php if (!empty($ministry['image_url'])): ?>
                                            <?php $imgSrc = image_url_for_display($ministry['image_url']); ?>
                                            <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="<?php echo htmlspecialchars($ministry['name']); ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px; cursor: pointer;" onclick="window.open('<?php echo htmlspecialchars($imgSrc); ?>', '_blank')" title="Click to view full size">
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
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="ministries.php?action=view&id=<?php echo $ministry['id']; ?>" class="btn btn-outline-info" title="View"><i class="bi bi-eye"></i></a>
                                            <a href="ministries.php?action=edit&id=<?php echo $ministry['id']; ?>" class="btn btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                                            <a href="ministries.php?action=duplicate&id=<?php echo $ministry['id']; ?>" class="btn btn-outline-secondary" title="Duplicate" onclick="return confirm('Duplicate this ministry?');"><i class="bi bi-files"></i></a>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this ministry? This action cannot be undone.');">
                                                <input type="hidden" name="id" value="<?php echo $ministry['id']; ?>">
                                                <button type="submit" name="delete" class="btn btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Bulk selection functionality
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.row-checkbox');
            const bulkActionBtn = document.getElementById('bulkActionBtn');
            const bulkActionSelect = document.getElementById('bulkActionSelect');
            const selectedCount = document.getElementById('selectedCount');
            const bulkForm = document.getElementById('bulkForm');
            
            function updateSelectedCount() {
                const checked = document.querySelectorAll('.row-checkbox:checked').length;
                selectedCount.textContent = checked;
                bulkActionBtn.disabled = !bulkActionSelect.value || checked === 0;
            }
            
            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    checkboxes.forEach(cb => cb.checked = this.checked);
                    updateSelectedCount();
                });
            }
            
            checkboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    if (selectAll) {
                        selectAll.checked = checkboxes.length === document.querySelectorAll('.row-checkbox:checked').length;
                    }
                    updateSelectedCount();
                });
            });
            
            bulkActionSelect.addEventListener('change', updateSelectedCount);
            
            bulkForm.addEventListener('submit', function(e) {
                const checked = document.querySelectorAll('.row-checkbox:checked');
                if (checked.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one ministry.');
                    return false;
                }
                
                if (!bulkActionSelect.value) {
                    e.preventDefault();
                    alert('Please select a bulk action.');
                    return false;
                }
                
                // Add selected IDs to form
                const container = document.getElementById('selectedMinistriesContainer');
                container.innerHTML = '';
                checked.forEach(cb => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'selected_ministries[]';
                    input.value = cb.value;
                    container.appendChild(input);
                });
                
                if (bulkActionSelect.value === 'delete') {
                    if (!confirm('Are you sure you want to delete ' + checked.length + ' selected ministry(ies)? This action cannot be undone.')) {
                        e.preventDefault();
                        return false;
                    }
                }
            });
        });
    </script>
    <?php
}

require_once 'includes/footer.php';
?>

