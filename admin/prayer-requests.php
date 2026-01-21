<?php
$pageTitle = 'Prayer Requests';
require_once 'config/config.php';
requireLogin();
require_once 'includes/header.php';

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $stmt = $db->prepare("UPDATE prayer_requests SET status = ?, admin_notes = ? WHERE id = ?");
        $stmt->execute([$_POST['status'], sanitize($_POST['admin_notes'] ?? ''), $_POST['id']]);
        redirect('prayer-requests.php', 'Prayer request updated successfully.');
    }
    
    if (isset($_POST['delete'])) {
        $stmt = $db->prepare("DELETE FROM prayer_requests WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        redirect('prayer-requests.php', 'Prayer request deleted successfully.');
    }
}

if ($action === 'view' && $id) {
    $stmt = $db->prepare("SELECT * FROM prayer_requests WHERE id = ?");
    $stmt->execute([$id]);
    $request = $stmt->fetch();
    if (!$request) redirect('prayer-requests.php', 'Prayer request not found.', 'danger');
    
    if ($request['status'] === 'new') {
        $updateStmt = $db->prepare("UPDATE prayer_requests SET status = 'prayed' WHERE id = ?");
        $updateStmt->execute([$id]);
        $request['status'] = 'prayed';
    }
    ?>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Prayer Request Details</h5>
            <a href="prayer-requests.php" class="btn btn-sm btn-secondary">Back to List</a>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <?php if ($request['name']): ?>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($request['name']); ?></p>
                    <?php endif; ?>
                    <?php if ($request['email']): ?>
                        <p><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($request['email']); ?>"><?php echo htmlspecialchars($request['email']); ?></a></p>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <p><strong>Status:</strong> <span class="badge bg-<?php echo $request['status'] === 'new' ? 'primary' : ($request['status'] === 'prayed' ? 'success' : 'secondary'); ?>"><?php echo ucfirst($request['status']); ?></span></p>
                    <p><strong>Received:</strong> <?php echo formatDateTime($request['created_at']); ?></p>
                </div>
            </div>
            
            <div class="mb-4">
                <h6>Prayer Request</h6>
                <div class="p-3 bg-light rounded">
                    <?php echo nl2br(htmlspecialchars($request['prayer_request'])); ?>
                </div>
            </div>
            
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $request['id']; ?>">
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status">
                        <option value="new" <?php echo $request['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                        <option value="prayed" <?php echo $request['status'] === 'prayed' ? 'selected' : ''; ?>>Prayed</option>
                        <option value="archived" <?php echo $request['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Admin Notes</label>
                    <textarea class="form-control" name="admin_notes" rows="3"><?php echo htmlspecialchars($request['admin_notes'] ?? ''); ?></textarea>
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
    $stmt = $db->query("SELECT COUNT(*) as total FROM prayer_requests $where");
    $total = $stmt->fetch()['total'];
    $totalPages = ceil($total / ITEMS_PER_PAGE);
    
    $stmt = $db->prepare("SELECT * FROM prayer_requests $where ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->bindValue(1, ITEMS_PER_PAGE, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $requests = $stmt->fetchAll();
    ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Prayer Requests</h2>
        <div>
            <a href="?status=all" class="btn btn-sm btn-outline-<?php echo $statusFilter === 'all' ? 'primary' : 'secondary'; ?>">All</a>
            <a href="?status=new" class="btn btn-sm btn-outline-<?php echo $statusFilter === 'new' ? 'primary' : 'secondary'; ?>">New</a>
            <a href="?status=prayed" class="btn btn-sm btn-outline-<?php echo $statusFilter === 'prayed' ? 'primary' : 'secondary'; ?>">Prayed</a>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <?php if (empty($requests)): ?>
                <p class="text-muted">No prayer requests found.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Request Preview</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $request): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($request['name'] ?: 'Anonymous'); ?></td>
                                    <td><?php echo htmlspecialchars($request['email'] ?: '-'); ?></td>
                                    <td><?php echo htmlspecialchars(mb_substr($request['prayer_request'], 0, 50)) . '...'; ?></td>
                                    <td><span class="badge bg-<?php echo $request['status'] === 'new' ? 'primary' : ($request['status'] === 'prayed' ? 'success' : 'secondary'); ?>"><?php echo ucfirst($request['status']); ?></span></td>
                                    <td><?php echo formatDate($request['created_at']); ?></td>
                                    <td>
                                        <a href="prayer-requests.php?action=view&id=<?php echo $request['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this request?');">
                                            <input type="hidden" name="id" value="<?php echo $request['id']; ?>">
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

