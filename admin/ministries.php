<?php
$pageTitle = 'Ministries Management';
require_once 'includes/header.php';

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $stmt = $db->prepare("DELETE FROM ministries WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        redirect('ministries.php', 'Ministry deleted successfully.');
    }
    
    $data = [
        'name' => sanitize($_POST['name'] ?? ''),
        'description' => sanitize($_POST['description'] ?? ''),
        'image_url' => sanitize($_POST['image_url'] ?? ''),
        'leader_name' => sanitize($_POST['leader_name'] ?? ''),
        'contact_email' => sanitize($_POST['contact_email'] ?? ''),
        'status' => $_POST['status'] ?? 'active',
        'display_order' => intval($_POST['display_order'] ?? 0)
    ];
    
    if ($id) {
        $stmt = $db->prepare("UPDATE ministries SET name = ?, description = ?, image_url = ?, leader_name = ?, contact_email = ?, status = ?, display_order = ? WHERE id = ?");
        $stmt->execute([$data['name'], $data['description'], $data['image_url'], $data['leader_name'], $data['contact_email'], $data['status'], $data['display_order'], $id]);
        redirect('ministries.php', 'Ministry updated successfully.');
    } else {
        $stmt = $db->prepare("INSERT INTO ministries (name, description, image_url, leader_name, contact_email, status, display_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$data['name'], $data['description'], $data['image_url'], $data['leader_name'], $data['contact_email'], $data['status'], $data['display_order']]);
        redirect('ministries.php', 'Ministry added successfully.');
    }
}

if ($action === 'add' || $action === 'edit') {
    $ministry = null;
    if ($id) {
        $stmt = $db->prepare("SELECT * FROM ministries WHERE id = ?");
        $stmt->execute([$id]);
        $ministry = $stmt->fetch();
        if (!$ministry) redirect('ministries.php', 'Ministry not found.', 'danger');
    }
    ?>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><?php echo $id ? 'Edit' : 'Add'; ?> Ministry</h5>
        </div>
        <div class="card-body">
            <form method="POST">
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
                            <label class="form-label">Image URL</label>
                            <input type="url" class="form-control" name="image_url" value="<?php echo htmlspecialchars($ministry['image_url'] ?? ''); ?>">
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
    $stmt = $db->query("SELECT * FROM ministries ORDER BY display_order ASC, name ASC");
    $ministries = $stmt->fetchAll();
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
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Leader</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ministries as $ministry): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($ministry['name']); ?></td>
                                    <td><?php echo htmlspecialchars($ministry['leader_name']); ?></td>
                                    <td><?php echo $ministry['display_order']; ?></td>
                                    <td><span class="badge bg-<?php echo $ministry['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($ministry['status']); ?></span></td>
                                    <td>
                                        <a href="ministries.php?action=edit&id=<?php echo $ministry['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this ministry?');">
                                            <input type="hidden" name="id" value="<?php echo $ministry['id']; ?>">
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

