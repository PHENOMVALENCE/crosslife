<?php
$pageTitle = 'Discipleship Programs';
require_once 'includes/header.php';

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['delete'])) {
            if (empty($_POST['id'])) {
                redirect('discipleship.php', 'Invalid program ID.', 'danger');
            }
            
            $stmt = $db->prepare("DELETE FROM discipleship_programs WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            
            if ($stmt->rowCount() > 0) {
                redirect('discipleship.php', 'Program deleted successfully.');
            } else {
                redirect('discipleship.php', 'Program not found or already deleted.', 'warning');
            }
        }
        
        // Validate required fields
        if (empty($_POST['program_name']) || empty($_POST['description'])) {
            redirect('discipleship.php?action=' . ($id ? 'edit&id=' . $id : 'add'), 'Program Name and Description are required fields.', 'danger');
        }
        
        $features = implode("\n", array_filter(array_map('trim', explode("\n", $_POST['features'] ?? ''))));
        
        $data = [
            'program_name' => sanitize($_POST['program_name'] ?? ''),
            'description' => sanitize($_POST['description'] ?? ''),
            'features' => $features,
            'image_url' => sanitize($_POST['image_url'] ?? ''),
            'duration' => sanitize($_POST['duration'] ?? ''),
            'requirements' => sanitize($_POST['requirements'] ?? ''),
            'status' => in_array($_POST['status'] ?? 'active', ['active', 'inactive']) ? $_POST['status'] : 'active',
            'display_order' => intval($_POST['display_order'] ?? 0)
        ];
        
        $programId = $id ?: ($_POST['id'] ?? null);
        
        if ($programId) {
            // Verify program exists
            $stmt = $db->prepare("SELECT id FROM discipleship_programs WHERE id = ?");
            $stmt->execute([$programId]);
            if (!$stmt->fetch()) {
                redirect('discipleship.php', 'Program not found.', 'danger');
            }
            
            $stmt = $db->prepare("UPDATE discipleship_programs SET program_name = ?, description = ?, features = ?, image_url = ?, duration = ?, requirements = ?, status = ?, display_order = ? WHERE id = ?");
            $stmt->execute([$data['program_name'], $data['description'], $data['features'], $data['image_url'], $data['duration'], $data['requirements'], $data['status'], $data['display_order'], $programId]);
            
            if ($stmt->rowCount() > 0) {
                redirect('discipleship.php', 'Program updated successfully.');
            } else {
                redirect('discipleship.php', 'No changes were made.', 'info');
            }
        } else {
            $stmt = $db->prepare("INSERT INTO discipleship_programs (program_name, description, features, image_url, duration, requirements, status, display_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['program_name'], $data['description'], $data['features'], $data['image_url'], $data['duration'], $data['requirements'], $data['status'], $data['display_order']]);
            
            if ($stmt->rowCount() > 0) {
                redirect('discipleship.php', 'Program added successfully.');
            } else {
                redirect('discipleship.php?action=add', 'Failed to add program. Please try again.', 'danger');
            }
        }
    } catch (PDOException $e) {
        redirect('discipleship.php', handleDBError($e, 'A database error occurred. Please try again.'), 'danger');
    } catch (Exception $e) {
        error_log("Error in discipleship.php: " . $e->getMessage());
        redirect('discipleship.php', 'An error occurred: ' . htmlspecialchars($e->getMessage()), 'danger');
    }
}

if ($action === 'add' || $action === 'edit') {
    $program = null;
    if ($id) {
        try {
            $stmt = $db->prepare("SELECT * FROM discipleship_programs WHERE id = ?");
            $stmt->execute([$id]);
            $program = $stmt->fetch();
            if (!$program) {
                redirect('discipleship.php', 'Program not found.', 'danger');
            }
        } catch (PDOException $e) {
            redirect('discipleship.php', handleDBError($e, 'Error loading program.'), 'danger');
        }
    }
    ?>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><?php echo $id ? 'Edit' : 'Add'; ?> Discipleship Program</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">Program Name *</label>
                            <input type="text" class="form-control" name="program_name" value="<?php echo htmlspecialchars($program['program_name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description *</label>
                            <textarea class="form-control" name="description" rows="5" required><?php echo htmlspecialchars($program['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Features (one per line)</label>
                            <textarea class="form-control" name="features" rows="6"><?php echo htmlspecialchars($program['features'] ?? ''); ?></textarea>
                            <small class="text-muted">Enter each feature on a new line</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Image URL</label>
                            <input type="url" class="form-control" name="image_url" value="<?php echo htmlspecialchars($program['image_url'] ?? ''); ?>">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Duration</label>
                                <input type="text" class="form-control" name="duration" value="<?php echo htmlspecialchars($program['duration'] ?? ''); ?>" placeholder="e.g., 12 weeks">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Requirements</label>
                            <textarea class="form-control" name="requirements" rows="3"><?php echo htmlspecialchars($program['requirements'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Status *</label>
                            <select class="form-control" name="status" required>
                                <option value="active" <?php echo ($program['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($program['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                <option value="upcoming" <?php echo ($program['status'] ?? '') === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" class="form-control" name="display_order" value="<?php echo $program['display_order'] ?? 0; ?>" min="0">
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Program</button>
                    <a href="discipleship.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <?php
} else {
    $stmt = $db->query("SELECT * FROM discipleship_programs ORDER BY display_order ASC, program_name ASC");
    $programs = $stmt->fetchAll();
    ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Discipleship Programs</h2>
        <a href="discipleship.php?action=add" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Add New Program</a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <?php if (empty($programs)): ?>
                <p class="text-muted">No programs found. <a href="discipleship.php?action=add">Add your first program</a>.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Program Name</th>
                                <th>Duration</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($programs as $program): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($program['program_name']); ?></td>
                                    <td><?php echo htmlspecialchars($program['duration']); ?></td>
                                    <td><?php echo $program['display_order']; ?></td>
                                    <td><span class="badge bg-<?php echo $program['status'] === 'active' ? 'success' : ($program['status'] === 'upcoming' ? 'warning' : 'secondary'); ?>"><?php echo ucfirst($program['status']); ?></span></td>
                                    <td>
                                        <a href="discipleship.php?action=edit&id=<?php echo $program['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this program?');">
                                            <input type="hidden" name="id" value="<?php echo $program['id']; ?>">
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

