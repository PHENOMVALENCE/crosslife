<?php
$pageTitle = 'Feedback Management';
require_once 'includes/header.php';

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $stmt = $db->prepare("UPDATE feedback SET status = ?, admin_notes = ? WHERE id = ?");
        $stmt->execute([$_POST['status'], sanitize($_POST['admin_notes'] ?? ''), $_POST['id']]);
        redirect('feedback.php', 'Feedback updated successfully.');
    }
    
    if (isset($_POST['delete'])) {
        $stmt = $db->prepare("DELETE FROM feedback WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        redirect('feedback.php', 'Feedback deleted successfully.');
    }
}

if ($action === 'view' && $id) {
    $stmt = $db->prepare("SELECT * FROM feedback WHERE id = ?");
    $stmt->execute([$id]);
    $feedback = $stmt->fetch();
    if (!$feedback) redirect('feedback.php', 'Feedback not found.', 'danger');
    
    if ($feedback['status'] === 'new') {
        $updateStmt = $db->prepare("UPDATE feedback SET status = 'reviewed' WHERE id = ?");
        $updateStmt->execute([$id]);
        $feedback['status'] = 'reviewed';
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
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status">
                        <option value="new" <?php echo $feedback['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                        <option value="reviewed" <?php echo $feedback['status'] === 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                        <option value="addressed" <?php echo $feedback['status'] === 'addressed' ? 'selected' : ''; ?>>Addressed</option>
                        <option value="archived" <?php echo $feedback['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Admin Notes</label>
                    <textarea class="form-control" name="admin_notes" rows="3"><?php echo htmlspecialchars($feedback['admin_notes'] ?? ''); ?></textarea>
                </div>
                
                <button type="submit" name="update_status" class="btn btn-primary"><i class="bi bi-save me-2"></i>Update Status</button>
            </form>
        </div>
    </div>
    <?php
} else {
    $statusFilter = $_GET['status'] ?? 'all';
    $typeFilter = $_GET['type'] ?? 'all';
    $page = max(1, intval($_GET['page'] ?? 1));
    $offset = ($page - 1) * ITEMS_PER_PAGE;
    
    $where = [];
    if ($statusFilter !== 'all') $where[] = "status = '$statusFilter'";
    if ($typeFilter !== 'all') $where[] = "feedback_type = '$typeFilter'";
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM feedback $whereClause");
    $total = $stmt->fetch()['total'];
    $totalPages = ceil($total / ITEMS_PER_PAGE);
    
    $stmt = $db->prepare("SELECT * FROM feedback $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->bindValue(1, ITEMS_PER_PAGE, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $feedbacks = $stmt->fetchAll();
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

