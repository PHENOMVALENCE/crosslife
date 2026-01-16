<?php
$pageTitle = 'Feedback Management';
require_once 'includes/header.php';

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['update_status'])) {
            if (empty($_POST['id'])) {
                redirect('feedback.php', 'Invalid feedback ID.', 'danger');
            }
            
            $validStatuses = ['new', 'reviewed', 'addressed', 'archived'];
            $status = in_array($_POST['status'] ?? '', $validStatuses) ? $_POST['status'] : 'new';
            
            $stmt = $db->prepare("UPDATE feedback SET status = ?, admin_notes = ? WHERE id = ?");
            $stmt->execute([$status, sanitize($_POST['admin_notes'] ?? ''), $_POST['id']]);
            
            if ($stmt->rowCount() > 0) {
                redirect('feedback.php', 'Feedback updated successfully.');
            } else {
                redirect('feedback.php', 'No changes were made or feedback not found.', 'info');
            }
        }
        
        if (isset($_POST['delete'])) {
            if (empty($_POST['id'])) {
                redirect('feedback.php', 'Invalid feedback ID.', 'danger');
            }
            
            $stmt = $db->prepare("DELETE FROM feedback WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            
            if ($stmt->rowCount() > 0) {
                redirect('feedback.php', 'Feedback deleted successfully.');
            } else {
                redirect('feedback.php', 'Feedback not found or already deleted.', 'warning');
            }
        }
    } catch (PDOException $e) {
        redirect('feedback.php', handleDBError($e, 'A database error occurred. Please try again.'), 'danger');
    } catch (Exception $e) {
        error_log("Error in feedback.php: " . $e->getMessage());
        redirect('feedback.php', 'An error occurred: ' . htmlspecialchars($e->getMessage()), 'danger');
    }
}

if ($action === 'view' && $id) {
    try {
        $stmt = $db->prepare("SELECT * FROM feedback WHERE id = ?");
        $stmt->execute([$id]);
        $feedback = $stmt->fetch();
        if (!$feedback) {
            redirect('feedback.php', 'Feedback not found.', 'danger');
        }
        
        if ($feedback['status'] === 'new') {
            try {
                $updateStmt = $db->prepare("UPDATE feedback SET status = 'reviewed' WHERE id = ?");
                $updateStmt->execute([$id]);
                $feedback['status'] = 'reviewed';
            } catch (PDOException $e) {
                error_log("Error updating feedback status: " . $e->getMessage());
                // Continue even if status update fails
            }
        }
    } catch (PDOException $e) {
        redirect('feedback.php', handleDBError($e, 'Error loading feedback.'), 'danger');
    }
    ?>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Feedback Details</h5>
            <a href="feedback.php" class="btn btn-sm btn-secondary">Back to List</a>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <?php if ($feedback['name']): ?>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($feedback['name']); ?></p>
                    <?php else: ?>
                        <p><strong>Name:</strong> <em>Anonymous</em></p>
                    <?php endif; ?>
                    <?php if ($feedback['email']): ?>
                        <p><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($feedback['email']); ?>"><?php echo htmlspecialchars($feedback['email']); ?></a></p>
                    <?php endif; ?>
                    <p><strong>Type:</strong> <span class="badge bg-info"><?php echo ucfirst($feedback['feedback_type']); ?></span></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Status:</strong> <span class="badge bg-<?php echo $feedback['status'] === 'new' ? 'primary' : ($feedback['status'] === 'addressed' ? 'success' : 'secondary'); ?>"><?php echo ucfirst($feedback['status']); ?></span></p>
                    <p><strong>Received:</strong> <?php echo formatDateTime($feedback['created_at']); ?></p>
                </div>
            </div>
            
            <div class="mb-4">
                <h6>Feedback Message</h6>
                <div class="p-3 bg-light rounded">
                    <?php echo nl2br(htmlspecialchars($feedback['message'])); ?>
                </div>
            </div>
            
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $feedback['id']; ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status *</label>
                        <select class="form-control" name="status" required>
                            <option value="new" <?php echo $feedback['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                            <option value="reviewed" <?php echo $feedback['status'] === 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                            <option value="addressed" <?php echo $feedback['status'] === 'addressed' ? 'selected' : ''; ?>>Addressed</option>
                            <option value="archived" <?php echo $feedback['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Last Updated</label>
                        <input type="text" class="form-control" value="<?php echo formatDateTime($feedback['updated_at'] ?? $feedback['created_at']); ?>" readonly>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Admin Notes</label>
                    <textarea class="form-control" name="admin_notes" rows="3" placeholder="Add notes about this feedback..."><?php echo htmlspecialchars($feedback['admin_notes'] ?? ''); ?></textarea>
                    <small class="form-text text-muted">These notes are only visible to admins.</small>
                </div>
                
                <button type="submit" name="update_status" class="btn btn-primary"><i class="bi bi-save me-2"></i>Update Status</button>
            </form>
        </div>
    </div>
    <?php
} else {
    try {
        $statusFilter = $_GET['status'] ?? 'all';
        $typeFilter = $_GET['type'] ?? 'all';
        $validStatuses = ['new', 'reviewed', 'addressed', 'archived'];
        $validTypes = ['praise', 'suggestion', 'concern', 'testimony', 'other'];
        
        if ($statusFilter !== 'all' && !in_array($statusFilter, $validStatuses)) {
            $statusFilter = 'all';
        }
        if ($typeFilter !== 'all' && !in_array($typeFilter, $validTypes)) {
            $typeFilter = 'all';
        }
        
        $page = max(1, intval($_GET['page'] ?? 1));
        $offset = ($page - 1) * ITEMS_PER_PAGE;
        
        // Use prepared statements to prevent SQL injection
        $where = [];
        $params = [];
        if ($statusFilter !== 'all') {
            $where[] = "status = ?";
            $params[] = $statusFilter;
        }
        if ($typeFilter !== 'all') {
            $where[] = "feedback_type = ?";
            $params[] = $typeFilter;
        }
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $countStmt = $db->prepare("SELECT COUNT(*) as total FROM feedback $whereClause");
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];
        $totalPages = ceil($total / ITEMS_PER_PAGE);
        
        $params[] = ITEMS_PER_PAGE;
        $params[] = $offset;
        $stmt = $db->prepare("SELECT * FROM feedback $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?");
        foreach ($params as $i => $param) {
            $stmt->bindValue($i + 1, $param, is_int($param) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        $feedbacks = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Database error loading feedback: " . $e->getMessage());
        $feedbacks = [];
        $total = 0;
        $totalPages = 0;
        $flash = getFlashMessage();
        if (!$flash) {
            $_SESSION['flash_message'] = 'Error loading feedback. Please refresh the page.';
            $_SESSION['flash_type'] = 'danger';
        }
    }
    ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Feedback</h2>
        <div>
            <a href="?status=all&type=all" class="btn btn-sm btn-outline-<?php echo $statusFilter === 'all' && $typeFilter === 'all' ? 'primary' : 'secondary'; ?>">All</a>
            <a href="?status=new&type=all" class="btn btn-sm btn-outline-<?php echo $statusFilter === 'new' ? 'primary' : 'secondary'; ?>">New</a>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <?php if (empty($feedbacks)): ?>
                <p class="text-muted">No feedback found.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Message Preview</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($feedbacks as $feedback): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($feedback['name'] ?: 'Anonymous'); ?></td>
                                    <td><span class="badge bg-info"><?php echo ucfirst($feedback['feedback_type']); ?></span></td>
                                    <td><?php echo htmlspecialchars(mb_substr($feedback['message'], 0, 50)) . '...'; ?></td>
                                    <td><span class="badge bg-<?php echo $feedback['status'] === 'new' ? 'primary' : ($feedback['status'] === 'addressed' ? 'success' : 'secondary'); ?>"><?php echo ucfirst($feedback['status']); ?></span></td>
                                    <td><?php echo formatDate($feedback['created_at']); ?></td>
                                    <td>
                                        <a href="feedback.php?action=view&id=<?php echo $feedback['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this feedback?');">
                                            <input type="hidden" name="id" value="<?php echo $feedback['id']; ?>">
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
                                    <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $statusFilter; ?>&type=<?php echo $typeFilter; ?>"><?php echo $i; ?></a>
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

