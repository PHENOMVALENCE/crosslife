<?php
/**
 * Leadership Management - Admin
 * Process POST (and redirects) before any output to avoid "headers already sent" errors.
 */
require_once __DIR__ . '/config/config.php';
requireLogin();

$pageTitle = 'Leadership Management';
$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Handle bulk operations
        if (isset($_POST['bulk_action']) && !empty($_POST['selected_leaders'])) {
            $selectedIds = $_POST['selected_leaders'];
            $bulkAction = $_POST['bulk_action'];

            if (!is_array($selectedIds)) {
                redirect('leadership.php', 'No leaders selected.', 'warning');
            }

            $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));

            if ($bulkAction === 'delete') {
                $stmt = $db->prepare("SELECT image_url FROM leadership WHERE id IN ($placeholders)");
                $stmt->execute($selectedIds);
                $leaders = $stmt->fetchAll();
                foreach ($leaders as $row) {
                    $imagePath = upload_path_to_disk($row['image_url'] ?? '');
                    if ($imagePath && file_exists($imagePath)) {
                        @unlink($imagePath);
                    }
                }
                $stmt = $db->prepare("DELETE FROM leadership WHERE id IN ($placeholders)");
                $stmt->execute($selectedIds);
                redirect('leadership.php', count($selectedIds) . ' leader(s) deleted successfully.');
            } elseif ($bulkAction === 'activate') {
                $stmt = $db->prepare("UPDATE leadership SET status = 'active' WHERE id IN ($placeholders)");
                $stmt->execute($selectedIds);
                redirect('leadership.php', count($selectedIds) . ' leader(s) activated successfully.');
            } elseif ($bulkAction === 'deactivate') {
                $stmt = $db->prepare("UPDATE leadership SET status = 'inactive' WHERE id IN ($placeholders)");
                $stmt->execute($selectedIds);
                redirect('leadership.php', count($selectedIds) . ' leader(s) deactivated successfully.');
            }
        }

        if (isset($_POST['delete'])) {
            if (empty($_POST['id'])) {
                redirect('leadership.php', 'Invalid leader ID.', 'danger');
            }
            try {
                $stmt = $db->prepare("SELECT image_url FROM leadership WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $leader = $stmt->fetch();
                if ($leader && !empty($leader['image_url'])) {
                    $imagePath = upload_path_to_disk($leader['image_url']);
                    if ($imagePath && file_exists($imagePath)) {
                        @unlink($imagePath);
                    }
                }
            } catch (PDOException $e) {
                error_log("Error deleting leader image: " . $e->getMessage());
            }
            $stmt = $db->prepare("DELETE FROM leadership WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            if ($stmt->rowCount() > 0) {
                redirect('leadership.php', 'Leader deleted successfully.');
            } else {
                redirect('leadership.php', 'Leader not found or already deleted.', 'warning');
            }
        }

        // Validate required fields
        if (empty($_POST['name']) || empty($_POST['role'])) {
            redirect('leadership.php?action=' . ($id ? 'edit&id=' . $id : 'add'), 'Name and Role are required fields.', 'danger');
        }
        if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            redirect('leadership.php?action=' . ($id ? 'edit&id=' . $id : 'add'), 'Invalid email address format.', 'danger');
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
                } elseif ($_FILES['image']['size'] > 5242880) {
                    $uploadError = 'File size exceeds 5MB limit.';
                } else {
                    $fileName = 'leader_' . time() . '_' . uniqid() . '.' . $fileExtension;
                    $filePath = $uploadDir . $fileName;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $filePath)) {
                        $leaderIdForDelete = $id ?: ($_POST['id'] ?? null);
                        if ($leaderIdForDelete) {
                            try {
                                $stmt = $db->prepare("SELECT image_url FROM leadership WHERE id = ?");
                                $stmt->execute([$leaderIdForDelete]);
                                $old = $stmt->fetch();
                                if ($old && !empty($old['image_url'])) {
                                    $oldPath = upload_path_to_disk($old['image_url']);
                                    if ($oldPath && file_exists($oldPath)) {
                                        @unlink($oldPath);
                                    }
                                }
                            } catch (PDOException $e) {
                                error_log("Error deleting old leader image: " . $e->getMessage());
                            }
                        }
                        $image_url = (defined('UPLOAD_PATH_RELATIVE') ? UPLOAD_PATH_RELATIVE : 'assets/img/uploads/') . $fileName;
                    } else {
                        $uploadError = 'Failed to upload image. Please try again.';
                    }
                }
            }
        } elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            switch ($_FILES['image']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $uploadError = 'File size exceeds maximum allowed size.';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $uploadError = 'File was only partially uploaded.';
                    break;
                default:
                    $uploadError = 'Unknown upload error occurred.';
            }
        }
        if (!empty($uploadError)) {
            redirect('leadership.php?action=' . ($id ? 'edit&id=' . $id : 'add'), $uploadError, 'danger');
        }

        // When editing: if no new file and image_url empty, keep existing image
        $leaderIdForData = $id ?: ($_POST['id'] ?? null);
        if ($leaderIdForData && (empty($image_url) || trim($image_url) === '') && (!isset($_FILES['image']['error']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE)) {
            try {
                $stmt = $db->prepare("SELECT image_url FROM leadership WHERE id = ?");
                $stmt->execute([$leaderIdForData]);
                $existing = $stmt->fetch();
                if ($existing && !empty($existing['image_url'])) {
                    $image_url = $existing['image_url'];
                }
            } catch (PDOException $e) {
                error_log("Leadership: could not load existing image_url: " . $e->getMessage());
            }
        }

        // Departments: store as plain text so "Media & ICT" is not saved as "Media &amp; ICT"
        $departmentsRaw = trim(strip_tags((string) ($_POST['departments'] ?? '')));
        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'role' => sanitize($_POST['role'] ?? ''),
            'departments' => $departmentsRaw,
            'bio' => sanitize($_POST['bio'] ?? ''),
            'image_url' => $image_url,
            'email' => sanitize($_POST['email'] ?? ''),
            'phone' => sanitize($_POST['phone'] ?? ''),
            'status' => in_array($_POST['status'] ?? 'active', ['active', 'inactive']) ? $_POST['status'] : 'active',
            'display_order' => intval($_POST['display_order'] ?? 0)
        ];

        $leaderId = $id ?: ($_POST['id'] ?? null);

        if ($leaderId) {
            $stmt = $db->prepare("SELECT id, display_order FROM leadership WHERE id = ?");
            $stmt->execute([$leaderId]);
            $current = $stmt->fetch();
            if (!$current) {
                redirect('leadership.php', 'Leader not found.', 'danger');
            }

            try {
                $db->beginTransaction();

                $stmt = $db->query("SELECT id FROM leadership ORDER BY display_order ASC, id ASC");
                $orderedIds = array_map('intval', array_column($stmt->fetchAll(), 'id'));
                $n = count($orderedIds);
                $currentPosition = array_search((int) $leaderId, $orderedIds, true);
                if ($currentPosition === false) {
                    $db->rollBack();
                    redirect('leadership.php', 'Leader not found in list.', 'danger');
                }
                $currentPosition1Based = $currentPosition + 1;
                $newPosition1Based = max(1, min($n, (int) $data['display_order']));
                if ($newPosition1Based < 1) {
                    $newPosition1Based = 1;
                }

                if ($newPosition1Based !== $currentPosition1Based) {
                    $idToMove = $orderedIds[$currentPosition];
                    array_splice($orderedIds, $currentPosition, 1);
                    array_splice($orderedIds, $newPosition1Based - 1, 0, [$idToMove]);
                }
                foreach ($orderedIds as $position => $mid) {
                    $order = $position + 1;
                    $stmt = $db->prepare("UPDATE leadership SET display_order = ? WHERE id = ?");
                    $stmt->execute([$order, $mid]);
                }
                $finalOrder = (int) (array_search((int) $leaderId, $orderedIds, true) + 1);

                $stmt = $db->prepare("UPDATE leadership SET name = ?, role = ?, departments = ?, bio = ?, image_url = ?, email = ?, phone = ?, status = ?, display_order = ? WHERE id = ?");
                $stmt->execute([$data['name'], $data['role'], $data['departments'], $data['bio'], $data['image_url'], $data['email'], $data['phone'], $data['status'], $finalOrder, $leaderId]);

                $db->commit();
                if ($stmt->rowCount() > 0) {
                    redirect('leadership.php', 'Leader updated successfully.');
                } else {
                    redirect('leadership.php', 'No changes were made.', 'info');
                }
            } catch (PDOException $e) {
                $db->rollBack();
                throw $e;
            }
        } else {
            $stmt = $db->prepare("INSERT INTO leadership (name, role, departments, bio, image_url, email, phone, status, display_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['name'], $data['role'], $data['departments'], $data['bio'], $data['image_url'], $data['email'], $data['phone'], $data['status'], $data['display_order']]);
            if ($stmt->rowCount() > 0) {
                redirect('leadership.php', 'Leader added successfully.');
            } else {
                redirect('leadership.php?action=add', 'Failed to add leader. Please try again.', 'danger');
            }
        }
    } catch (PDOException $e) {
        error_log("Database error in leadership.php: " . $e->getMessage());
        redirect('leadership.php', defined('DEBUG') && DEBUG ? 'Error: ' . $e->getMessage() : 'A database error occurred. Please try again.', 'danger');
    } catch (Exception $e) {
        error_log("Error in leadership.php: " . $e->getMessage());
        redirect('leadership.php', 'An error occurred: ' . htmlspecialchars($e->getMessage()), 'danger');
    }
}

// Duplicate: process and redirect before any output
if ($action === 'duplicate') {
    if (!$id) {
        redirect('leadership.php', 'Leader ID is required.', 'danger');
    }
    try {
        $stmt = $db->prepare("SELECT * FROM leadership WHERE id = ?");
        $stmt->execute([$id]);
        $leader = $stmt->fetch();
        if (!$leader) {
            redirect('leadership.php', 'Leader not found.', 'danger');
        }
        $newName = 'Copy of ' . $leader['name'];
        $stmt = $db->prepare("INSERT INTO leadership (name, role, departments, bio, image_url, email, phone, status, display_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $newName,
            $leader['role'],
            $leader['departments'] ?? '',
            $leader['bio'],
            $leader['image_url'],
            $leader['email'],
            $leader['phone'],
            'inactive',
            $leader['display_order']
        ]);
        redirect('leadership.php', 'Leader duplicated successfully. You can now edit it.', 'success');
    } catch (PDOException $e) {
        error_log("Database error in leadership.php: " . $e->getMessage());
        redirect('leadership.php', 'Error duplicating leader. Please try again.', 'danger');
    }
}

require_once __DIR__ . '/includes/header.php';

if ($action === 'view') {
    if (!$id) {
        redirect('leadership.php', 'Leader ID is required.', 'danger');
    }
    $stmt = $db->prepare("SELECT * FROM leadership WHERE id = ?");
    $stmt->execute([$id]);
    $leader = $stmt->fetch();
    if (!$leader) {
        redirect('leadership.php', 'Leader not found.', 'danger');
    }
    ?>
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo htmlspecialchars($leader['name']); ?></h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <?php if (!empty($leader['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars(image_url_for_display($leader['image_url'])); ?>" alt="<?php echo htmlspecialchars($leader['name']); ?>" class="img-fluid rounded shadow-sm">
                            <?php else: ?>
                                <div class="border rounded d-flex align-items-center justify-content-center bg-light" style="height: 200px;">
                                    <i class="bi bi-person" style="font-size: 4rem; color: #ccc;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <p class="mb-1"><strong>Role:</strong> <?php echo htmlspecialchars($leader['role']); ?></p>
                            <?php if (trim((string)($leader['departments'] ?? '')) !== ''): ?>
                                <p class="mb-1"><strong>Departments:</strong> <?php echo htmlspecialchars(html_entity_decode($leader['departments'], ENT_QUOTES, 'UTF-8')); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($leader['email'])): ?>
                                <p class="mb-1"><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($leader['email']); ?>"><?php echo htmlspecialchars($leader['email']); ?></a></p>
                            <?php endif; ?>
                            <?php if (!empty($leader['phone'])): ?>
                                <p class="mb-0"><strong>Phone:</strong> <?php echo htmlspecialchars($leader['phone']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (!empty($leader['bio'])): ?>
                        <h6 class="text-muted text-uppercase small mb-2">Bio</h6>
                        <div class="p-3 bg-light rounded"><?php echo nl2br(htmlspecialchars($leader['bio'])); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-light"><h6 class="mb-0">Details</h6></div>
                <div class="card-body">
                    <p class="mb-2"><strong>Status:</strong> <span class="badge bg-<?php echo $leader['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($leader['status']); ?></span></p>
                    <p class="mb-2"><strong>Display Order:</strong> <?php echo (int) ($leader['display_order'] ?? 0); ?></p>
                    <p class="mb-0"><strong>Updated:</strong> <?php echo formatDateTime($leader['updated_at'] ?? ''); ?></p>
                </div>
            </div>
            <a href="leadership.php?action=edit&id=<?php echo $id; ?>" class="btn btn-primary"><i class="bi bi-pencil me-2"></i>Edit</a>
            <a href="leadership.php" class="btn btn-secondary">Back to list</a>
        </div>
    </div>
    <?php
} elseif ($action === 'add' || $action === 'edit') {
    $leader = null;
    if ($id) {
        $stmt = $db->prepare("SELECT * FROM leadership WHERE id = ?");
        $stmt->execute([$id]);
        $leader = $stmt->fetch();
        if (!$leader) {
            redirect('leadership.php', 'Leader not found.', 'danger');
        }
    }
    $leaders = [];
    try {
        $stmt = $db->query("SELECT id FROM leadership ORDER BY display_order ASC, id ASC");
        $leaders = $stmt->fetchAll();
    } catch (PDOException $e) {
        // ignore
    }
    ?>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><?php echo $id ? 'Edit' : 'Add'; ?> Leader</h5>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <?php if ($id): ?>
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                <?php endif; ?>
                <div class="row">
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name *</label>
                                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($leader['name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Role *</label>
                                <input type="text" class="form-control" name="role" value="<?php echo htmlspecialchars($leader['role'] ?? ''); ?>" required placeholder="e.g. Senior Pastor">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Departments / Areas</label>
                            <input type="text" class="form-control" name="departments" value="<?php echo htmlspecialchars(html_entity_decode($leader['departments'] ?? '', ENT_QUOTES, 'UTF-8')); ?>" placeholder="e.g. Worship, Youth, Media & ICT (comma-separated)">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bio</label>
                            <textarea class="form-control" name="bio" rows="5"><?php echo htmlspecialchars($leader['bio'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <?php if (!empty($leader['image_url'])): ?>
                                <div class="mb-2">
                                    <img src="<?php echo htmlspecialchars(image_url_for_display($leader['image_url'])); ?>" alt="Current" id="imagePreview" style="max-width: 200px; max-height: 200px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;">
                                </div>
                            <?php else: ?>
                                <div class="mb-2"><img id="imagePreview" src="" alt="" style="max-width: 200px; max-height: 200px; object-fit: cover; border-radius: 4px; display: none;"></div>
                            <?php endif; ?>
                            <input type="file" class="form-control" name="image" id="imageInput" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                            <small class="form-text text-muted">JPG, PNG, GIF, WebP, max 5MB</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Image URL (Optional)</label>
                            <input type="text" class="form-control" name="image_url" value="<?php echo htmlspecialchars($leader['image_url'] ?? ''); ?>" placeholder="Or enter URL/path">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($leader['email'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($leader['phone'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Status *</label>
                            <select class="form-control" name="status" required>
                                <option value="active" <?php echo ($leader['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($leader['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" class="form-control" name="display_order" value="<?php echo (int) ($leader['display_order'] ?? 1); ?>" min="1">
                            <small class="form-text text-muted">1 = first. Changing this moves this leader and shifts others.</small>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Leader</button>
                    <a href="leadership.php" class="btn btn-secondary">Cancel</a>
                    <?php if ($id): ?>
                        <a href="leadership.php?action=view&id=<?php echo $id; ?>" class="btn btn-outline-info">View</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    <script>
    document.getElementById('imageInput') && document.getElementById('imageInput').addEventListener('change', function(e) {
        var p = document.getElementById('imagePreview');
        if (e.target.files && e.target.files[0]) {
            var r = new FileReader();
            r.onload = function() { p.src = r.result; p.style.display = 'block'; };
            r.readAsDataURL(e.target.files[0]);
        } else { p.style.display = 'none'; }
    });
    </script>
    <?php
} else {
    try {
        $stmt = $db->query("SELECT * FROM leadership ORDER BY display_order ASC, name ASC");
        $leaders = $stmt->fetchAll();
    } catch (PDOException $e) {
        $leaders = [];
    }
    $flash = getFlashMessage();
    if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type'] === 'danger' ? 'danger' : ($flash['type'] === 'warning' ? 'warning' : 'success'); ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($flash['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm leadership-list-card">
        <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="mb-0 fw-bold">Leadership</h5>
                <p class="text-muted small mb-0 mt-1">Manage leaders, roles, and display order</p>
            </div>
            <a href="leadership.php?action=add" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Add New Leader</a>
        </div>
        <div class="card-body p-0">
            <?php if (empty($leaders)): ?>
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-people display-4 d-block mb-3 opacity-50"></i>
                    <p class="mb-0">No leaders found.</p>
                    <a href="leadership.php?action=add" class="btn btn-outline-primary btn-sm mt-3">Add your first leader</a>
                </div>
            <?php else: ?>
                <form method="POST" id="bulkForm" class="leadership-bulk-bar px-4 py-3 bg-light border-bottom">
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <select name="bulk_action" class="form-select form-select-sm" style="width: auto; min-width: 140px;">
                            <option value="">Bulk Actions</option>
                            <option value="activate">Activate</option>
                            <option value="deactivate">Deactivate</option>
                            <option value="delete">Delete</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary" id="bulkActionBtn" disabled><i class="bi bi-check2 me-1"></i>Apply</button>
                        <span class="text-muted small ms-2"><span id="selectedCount">0</span> selected</span>
                    </div>
                    <div id="selectedLeadersContainer"></div>
                </form>
                <div class="table-responsive">
                    <table class="table table-hover datatable mb-0 leadership-table">
                        <thead class="table-light">
                            <tr>
                                <th class="align-middle" style="width: 44px;"><input type="checkbox" id="selectAll" title="Select All" class="form-check-input"></th>
                                <th class="align-middle" style="width: 64px;">Image</th>
                                <th class="align-middle">Name</th>
                                <th class="align-middle">Role</th>
                                <th class="align-middle">Departments</th>
                                <th class="align-middle">Contact</th>
                                <th class="align-middle text-center" style="width: 64px;">Order</th>
                                <th class="align-middle text-center" style="width: 90px;">Status</th>
                                <th class="align-middle text-end" style="width: 160px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leaders as $leader): ?>
                                <tr>
                                    <td class="align-middle"><input type="checkbox" name="selected_leaders[]" value="<?php echo (int) $leader['id']; ?>" class="row-checkbox form-check-input"></td>
                                    <td class="align-middle">
                                        <?php if (!empty($leader['image_url'])): ?>
                                            <img src="<?php echo htmlspecialchars(image_url_for_display($leader['image_url'])); ?>" alt="" class="leadership-thumb" loading="lazy">
                                        <?php else: ?>
                                            <div class="leadership-thumb leadership-thumb--placeholder"><i class="bi bi-person"></i></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle"><strong><?php echo htmlspecialchars($leader['name']); ?></strong></td>
                                    <td class="align-middle"><?php echo htmlspecialchars($leader['role']); ?></td>
                                    <td class="align-middle"><?php echo htmlspecialchars(html_entity_decode($leader['departments'] ?? '-', ENT_QUOTES, 'UTF-8')); ?></td>
                                    <td class="align-middle">
                                        <?php if (!empty($leader['email'])): ?>
                                            <a href="mailto:<?php echo htmlspecialchars($leader['email']); ?>" class="text-decoration-none"><?php echo htmlspecialchars($leader['email']); ?></a>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle text-center"><?php echo (int) ($leader['display_order'] ?? 0); ?></td>
                                    <td class="align-middle text-center"><span class="badge bg-<?php echo $leader['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($leader['status']); ?></span></td>
                                    <td class="align-middle text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="leadership.php?action=view&id=<?php echo (int) $leader['id']; ?>" class="btn btn-outline-info" title="View"><i class="bi bi-eye"></i></a>
                                            <a href="leadership.php?action=edit&id=<?php echo (int) $leader['id']; ?>" class="btn btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                                            <a href="leadership.php?action=duplicate&id=<?php echo (int) $leader['id']; ?>" class="btn btn-outline-secondary" title="Duplicate" onclick="return confirm('Duplicate this leader?');"><i class="bi bi-files"></i></a>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this leader?');">
                                                <input type="hidden" name="id" value="<?php echo (int) $leader['id']; ?>">
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

    <style>
    .leadership-list-card { border-radius: 10px; overflow: hidden; }
    .leadership-list-card .card-header { border-bottom: 1px solid rgba(0,0,0,.06); }
    .leadership-bulk-bar { }
    .leadership-table tbody tr { vertical-align: middle; }
    .leadership-table tbody tr:hover { background-color: rgba(0,0,0,.03); }
    .leadership-thumb { width: 48px; height: 48px; object-fit: cover; border-radius: 8px; display: block; }
    .leadership-thumb--placeholder { background: #eee; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; color: #999; }
    </style>
    <script>
    (function() {
        var form = document.getElementById('bulkForm');
        if (!form) return;
        var checkboxes = form.querySelectorAll('.row-checkbox');
        var selectAll = form.querySelector('#selectAll');
        var bulkBtn = document.getElementById('bulkActionBtn');
        var countEl = document.getElementById('selectedCount');
        var container = document.getElementById('selectedLeadersContainer');
        function updateBulk() {
            var checked = form.querySelectorAll('.row-checkbox:checked');
            var n = checked.length;
            if (countEl) countEl.textContent = n;
            if (bulkBtn) bulkBtn.disabled = n === 0;
            if (container) {
                container.innerHTML = '';
                checked.forEach(function(cb) {
                    var i = document.createElement('input');
                    i.type = 'hidden'; i.name = 'selected_leaders[]'; i.value = cb.value;
                    container.appendChild(i);
                });
            }
        }
        checkboxes.forEach(function(cb) { cb.addEventListener('change', updateBulk); });
        if (selectAll) selectAll.addEventListener('change', function() {
            checkboxes.forEach(function(cb) { cb.checked = selectAll.checked; });
            updateBulk();
        });
        updateBulk();
    })();
    </script>
    <?php
}

require_once __DIR__ . '/includes/footer.php';
