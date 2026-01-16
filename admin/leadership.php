<?php
$pageTitle = 'Leadership Management';
require_once 'includes/header.php';

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['delete'])) {
            if (empty($_POST['id'])) {
                redirect('leadership.php', 'Invalid leader ID.', 'danger');
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
        
        // Validate email if provided
        if (!empty($_POST['email']) && !validateEmail($_POST['email'])) {
            redirect('leadership.php?action=' . ($id ? 'edit&id=' . $id : 'add'), 'Invalid email address format.', 'danger');
        }
        
        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'role' => sanitize($_POST['role'] ?? ''),
            'bio' => sanitize($_POST['bio'] ?? ''),
            'image_url' => sanitize($_POST['image_url'] ?? ''),
            'email' => sanitize($_POST['email'] ?? ''),
            'phone' => sanitize($_POST['phone'] ?? ''),
            'status' => in_array($_POST['status'] ?? 'active', ['active', 'inactive']) ? $_POST['status'] : 'active',
            'display_order' => intval($_POST['display_order'] ?? 0)
        ];
        
        $leaderId = $id ?: ($_POST['id'] ?? null);
        
        if ($leaderId) {
            // Verify leader exists
            $stmt = $db->prepare("SELECT id FROM leadership WHERE id = ?");
            $stmt->execute([$leaderId]);
            if (!$stmt->fetch()) {
                redirect('leadership.php', 'Leader not found.', 'danger');
            }
            
            $stmt = $db->prepare("UPDATE leadership SET name = ?, role = ?, bio = ?, image_url = ?, email = ?, phone = ?, status = ?, display_order = ? WHERE id = ?");
            $stmt->execute([$data['name'], $data['role'], $data['bio'], $data['image_url'], $data['email'], $data['phone'], $data['status'], $data['display_order'], $leaderId]);
            
            if ($stmt->rowCount() > 0) {
                redirect('leadership.php', 'Leader updated successfully.');
            } else {
                redirect('leadership.php', 'No changes were made.', 'info');
            }
        } else {
            $stmt = $db->prepare("INSERT INTO leadership (name, role, bio, image_url, email, phone, status, display_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['name'], $data['role'], $data['bio'], $data['image_url'], $data['email'], $data['phone'], $data['status'], $data['display_order']]);
            
            if ($stmt->rowCount() > 0) {
                redirect('leadership.php', 'Leader added successfully.');
            } else {
                redirect('leadership.php?action=add', 'Failed to add leader. Please try again.', 'danger');
            }
        }
    } catch (PDOException $e) {
        redirect('leadership.php', handleDBError($e, 'A database error occurred. Please try again.'), 'danger');
    } catch (Exception $e) {
        error_log("Error in leadership.php: " . $e->getMessage());
        redirect('leadership.php', 'An error occurred: ' . htmlspecialchars($e->getMessage()), 'danger');
    }
}

if ($action === 'add' || $action === 'edit') {
    $leader = null;
    if ($id) {
        try {
            $stmt = $db->prepare("SELECT * FROM leadership WHERE id = ?");
            $stmt->execute([$id]);
            $leader = $stmt->fetch();
            if (!$leader) {
                redirect('leadership.php', 'Leader not found.', 'danger');
            }
        } catch (PDOException $e) {
            redirect('leadership.php', handleDBError($e, 'Error loading leader.'), 'danger');
        }
    }
    ?>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><?php echo $id ? 'Edit' : 'Add'; ?> Leader</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name *</label>
                                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($leader['name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Role *</label>
                                <input type="text" class="form-control" name="role" value="<?php echo htmlspecialchars($leader['role'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Bio</label>
                            <textarea class="form-control" name="bio" rows="5"><?php echo htmlspecialchars($leader['bio'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Image URL</label>
                            <input type="url" class="form-control" name="image_url" value="<?php echo htmlspecialchars($leader['image_url'] ?? ''); ?>">
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
                            <input type="number" class="form-control" name="display_order" value="<?php echo $leader['display_order'] ?? 0; ?>" min="0">
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Leader</button>
                    <a href="leadership.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <?php
} else {
    $stmt = $db->query("SELECT * FROM leadership ORDER BY display_order ASC, name ASC");
    $leaders = $stmt->fetchAll();
    ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Leadership</h2>
        <a href="leadership.php?action=add" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Add New Leader</a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <?php if (empty($leaders)): ?>
                <p class="text-muted">No leaders found. <a href="leadership.php?action=add">Add your first leader</a>.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Email</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leaders as $leader): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($leader['name']); ?></td>
                                    <td><?php echo htmlspecialchars($leader['role']); ?></td>
                                    <td><?php echo htmlspecialchars($leader['email']); ?></td>
                                    <td><?php echo $leader['display_order']; ?></td>
                                    <td><span class="badge bg-<?php echo $leader['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($leader['status']); ?></span></td>
                                    <td>
                                        <a href="leadership.php?action=edit&id=<?php echo $leader['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this leader?');">
                                            <input type="hidden" name="id" value="<?php echo $leader['id']; ?>">
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

