<?php
$pageTitle = 'Contact Inquiries';
require_once 'includes/header.php';

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $stmt = $db->prepare("UPDATE contact_inquiries SET status = ?, admin_notes = ? WHERE id = ?");
        $stmt->execute([$_POST['status'], sanitize($_POST['admin_notes'] ?? ''), $_POST['id']]);
        redirect('contacts.php', 'Inquiry updated successfully.');
    }
    
    if (isset($_POST['delete'])) {
        $stmt = $db->prepare("DELETE FROM contact_inquiries WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        redirect('contacts.php', 'Inquiry deleted successfully.');
    }
}

if ($action === 'view' && $id) {
    $stmt = $db->prepare("SELECT * FROM contact_inquiries WHERE id = ?");
    $stmt->execute([$id]);
    $inquiry = $stmt->fetch();
    if (!$inquiry) redirect('contacts.php', 'Inquiry not found.', 'danger');
    
    // Mark as read if new
    if ($inquiry['status'] === 'new') {
        $updateStmt = $db->prepare("UPDATE contact_inquiries SET status = 'read' WHERE id = ?");
        $updateStmt->execute([$id]);
        $inquiry['status'] = 'read';
    }
    ?>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Contact Inquiry Details</h5>
            <a href="contacts.php" class="btn btn-sm btn-secondary">Back to List</a>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6>Contact Information</h6>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($inquiry['name']); ?></p>
                    <p><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($inquiry['email']); ?>"><?php echo htmlspecialchars($inquiry['email']); ?></a></p>
                    <?php if ($inquiry['phone']): ?>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($inquiry['phone']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <h6>Inquiry Details</h6>
                    <p><strong>Subject:</strong> <?php echo htmlspecialchars($inquiry['subject']); ?></p>
                    <p><strong>Status:</strong> <span class="badge bg-<?php echo $inquiry['status'] === 'new' ? 'primary' : ($inquiry['status'] === 'replied' ? 'success' : 'secondary'); ?>"><?php echo ucfirst($inquiry['status']); ?></span></p>
                    <p><strong>Received:</strong> <?php echo formatDateTime($inquiry['created_at']); ?></p>
                </div>
            </div>
            
            <div class="mb-4">
                <h6>Message</h6>
                <div class="p-3 bg-light rounded">
                    <?php echo nl2br(htmlspecialchars($inquiry['message'])); ?>
                </div>
            </div>
            
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $inquiry['id']; ?>">
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status">
                        <option value="new" <?php echo $inquiry['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                        <option value="read" <?php echo $inquiry['status'] === 'read' ? 'selected' : ''; ?>>Read</option>
                        <option value="replied" <?php echo $inquiry['status'] === 'replied' ? 'selected' : ''; ?>>Replied</option>
                        <option value="archived" <?php echo $inquiry['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Admin Notes</label>
                    <textarea class="form-control" name="admin_notes" rows="3"><?php echo htmlspecialchars($inquiry['admin_notes'] ?? ''); ?></textarea>
                </div>
                
                <button type="submit" name="update_status" class="btn btn-primary"><i class="bi bi-save me-2"></i>Update Status</button>
            </form>
        </div>
    </div>
    <?php
} else {
    $statusFilter = $_GET['status'] ?? 'all';
    $page = max(1, intval($_GET['page'] ?? 1));
    $offset = ($page - 1) * ITEMS_PER_PAGE;
    
    $where = $statusFilter !== 'all' ? "WHERE status = '$statusFilter'" : '';
    $stmt = $db->query("SELECT COUNT(*) as total FROM contact_inquiries $where");
    $total = $stmt->fetch()['total'];
    $totalPages = ceil($total / ITEMS_PER_PAGE);
    
    $stmt = $db->prepare("SELECT * FROM contact_inquiries $where ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->bindValue(1, ITEMS_PER_PAGE, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $inquiries = $stmt->fetchAll();
    ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Contact Inquiries</h2>
        <div>
            <a href="?status=all" class="btn btn-sm btn-outline-<?php echo $statusFilter === 'all' ? 'primary' : 'secondary'; ?>">All</a>
            <a href="?status=new" class="btn btn-sm btn-outline-<?php echo $statusFilter === 'new' ? 'primary' : 'secondary'; ?>">New</a>
            <a href="?status=read" class="btn btn-sm btn-outline-<?php echo $statusFilter === 'read' ? 'primary' : 'secondary'; ?>">Read</a>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <?php if (empty($inquiries)): ?>
                <p class="text-muted">No inquiries found.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inquiries as $inquiry): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($inquiry['name']); ?></td>
                                    <td><?php echo htmlspecialchars($inquiry['email']); ?></td>
                                    <td><?php echo htmlspecialchars($inquiry['subject']); ?></td>
                                    <td><span class="badge bg-<?php echo $inquiry['status'] === 'new' ? 'primary' : ($inquiry['status'] === 'replied' ? 'success' : 'secondary'); ?>"><?php echo ucfirst($inquiry['status']); ?></span></td>
                                    <td><?php echo formatDate($inquiry['created_at']); ?></td>
                                    <td>
                                        <a href="contacts.php?action=view&id=<?php echo $inquiry['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this inquiry?');">
                                            <input type="hidden" name="id" value="<?php echo $inquiry['id']; ?>">
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
                                    <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $statusFilter; ?>"><?php echo $i; ?></a>
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

