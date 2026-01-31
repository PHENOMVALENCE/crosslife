<?php
$pageTitle = 'Newsletter Subscribers';
require_once 'includes/header.php';

$db = getDB();

try {
    $stmt = $db->query("SELECT * FROM newsletter_subscriptions ORDER BY subscribed_at DESC");
    $subscribers = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Database error loading newsletter subscribers: " . $e->getMessage());
    $subscribers = [];
    $_SESSION['flash_message'] = 'Error loading newsletter subscribers. Please refresh the page.';
    $_SESSION['flash_type'] = 'danger';
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Newsletter Subscribers</h2>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($subscribers)): ?>
            <p class="text-muted">No newsletter subscribers found.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover datatable" data-dt-options='{"order":[[3,"desc"]]}'>
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Subscribed At</th>
                            <th>Unsubscribed At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subscribers as $sub): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($sub['email']); ?></td>
                                <td><?php echo htmlspecialchars($sub['name'] ?? ''); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $sub['status'] === 'active' ? 'success' : ($sub['status'] === 'unsubscribed' ? 'secondary' : 'warning'); 
                                    ?>">
                                        <?php echo ucfirst($sub['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo formatDateTime($sub['subscribed_at']); ?></td>
                                <td><?php echo !empty($sub['unsubscribed_at']) ? formatDateTime($sub['unsubscribed_at']) : '-'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

