<?php
$pageTitle = 'Dashboard';
require_once 'config/config.php';
requireLogin();
require_once 'includes/header.php';

$db = getDB();

// Get statistics
$stats = [];

// Sermons count
$stmt = $db->query("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published FROM sermons");
$stats['sermons'] = $stmt->fetch();

// Events count
$stmt = $db->query("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'upcoming' THEN 1 ELSE 0 END) as upcoming FROM events");
$stats['events'] = $stmt->fetch();

// Ministries count
$stmt = $db->query("SELECT COUNT(*) as total FROM ministries WHERE status = 'active'");
$stats['ministries'] = $stmt->fetch();

// Contact inquiries
$stmt = $db->query("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new FROM contact_inquiries");
$stats['contacts'] = $stmt->fetch();

// Prayer requests
$stmt = $db->query("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new FROM prayer_requests");
$stats['prayers'] = $stmt->fetch();

// Feedback
$stmt = $db->query("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new FROM feedback");
$stats['feedback'] = $stmt->fetch();

// Recent activities
$recentContacts = $db->query("SELECT * FROM contact_inquiries ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recentPrayers = $db->query("SELECT * FROM prayer_requests ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recentEvents = $db->query("SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT 5")->fetchAll();

// Discipleship summary (if tables exist)
$stats['discipleship'] = ['programs' => 0, 'enrollments' => 0];
try {
    $stats['discipleship']['programs'] = (int) $db->query("SELECT COUNT(*) FROM discipleship_programs WHERE status IN ('active', 'upcoming')")->fetchColumn();
    $stats['discipleship']['enrollments'] = (int) $db->query("SELECT COUNT(*) FROM discipleship_enrollments WHERE status = 'active'")->fetchColumn();
} catch (PDOException $e) { /* ignore */ }

$adminName = isset($currentAdmin['full_name']) ? $currentAdmin['full_name'] : 'Admin';
?>

<div class="dashboard-overview mb-4">
    <h1 class="h4 mb-1 fw-bold">Dashboard</h1>
    <p class="text-muted small mb-0">Welcome back. Here’s an overview of your site.</p>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-4">
        <a href="sermons.php" class="text-decoration-none d-block">
            <div class="card dashboard-stat-card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="dashboard-stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-play-circle-fill"></i>
                    </div>
                    <div class="ms-3 flex-grow-1 min-w-0">
                        <div class="text-muted small text-uppercase fw-semibold">Sermons</div>
                        <div class="h4 mb-0 fw-bold"><?php echo (int) ($stats['sermons']['total'] ?? 0); ?></div>
                        <div class="small text-primary"><?php echo (int) ($stats['sermons']['published'] ?? 0); ?> published</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-lg-4">
        <a href="events.php" class="text-decoration-none d-block">
            <div class="card dashboard-stat-card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="dashboard-stat-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    <div class="ms-3 flex-grow-1 min-w-0">
                        <div class="text-muted small text-uppercase fw-semibold">Events</div>
                        <div class="h4 mb-0 fw-bold"><?php echo (int) ($stats['events']['total'] ?? 0); ?></div>
                        <div class="small text-success"><?php echo (int) ($stats['events']['upcoming'] ?? 0); ?> upcoming</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-lg-4">
        <a href="ministries.php" class="text-decoration-none d-block">
            <div class="card dashboard-stat-card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="dashboard-stat-icon bg-info bg-opacity-10 text-info">
                        <i class="bi bi-building"></i>
                    </div>
                    <div class="ms-3 flex-grow-1 min-w-0">
                        <div class="text-muted small text-uppercase fw-semibold">Ministries</div>
                        <div class="h4 mb-0 fw-bold"><?php echo (int) ($stats['ministries']['total'] ?? 0); ?></div>
                        <div class="small text-info">Active</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-lg-4">
        <a href="contacts.php" class="text-decoration-none d-block">
            <div class="card dashboard-stat-card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="dashboard-stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-envelope"></i>
                    </div>
                    <div class="ms-3 flex-grow-1 min-w-0">
                        <div class="text-muted small text-uppercase fw-semibold">Messages</div>
                        <div class="h4 mb-0 fw-bold"><?php echo (int) ($stats['contacts']['total'] ?? 0); ?></div>
                        <div class="small text-warning"><?php echo (int) ($stats['contacts']['new'] ?? 0); ?> new</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-lg-4">
        <a href="prayer-requests.php" class="text-decoration-none d-block">
            <div class="card dashboard-stat-card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="dashboard-stat-icon bg-secondary bg-opacity-10 text-secondary">
                        <i class="bi bi-heart"></i>
                    </div>
                    <div class="ms-3 flex-grow-1 min-w-0">
                        <div class="text-muted small text-uppercase fw-semibold">Prayer requests</div>
                        <div class="h4 mb-0 fw-bold"><?php echo (int) ($stats['prayers']['total'] ?? 0); ?></div>
                        <div class="small text-secondary"><?php echo (int) ($stats['prayers']['new'] ?? 0); ?> new</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-lg-4">
        <a href="feedback.php" class="text-decoration-none d-block">
            <div class="card dashboard-stat-card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="dashboard-stat-icon bg-dark bg-opacity-10 text-dark">
                        <i class="bi bi-chat-left-text"></i>
                    </div>
                    <div class="ms-3 flex-grow-1 min-w-0">
                        <div class="text-muted small text-uppercase fw-semibold">Feedback</div>
                        <div class="h4 mb-0 fw-bold"><?php echo (int) ($stats['feedback']['total'] ?? 0); ?></div>
                        <div class="small text-dark"><?php echo (int) ($stats['feedback']['new'] ?? 0); ?> new</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<?php if (($stats['discipleship']['programs'] ?? 0) > 0): ?>
<div class="row g-3 mb-4">
    <div class="col-12">
        <a href="discipleship.php" class="text-decoration-none d-block">
            <div class="card dashboard-stat-card border-0 shadow-sm border-start border-4 border-primary">
                <div class="card-body d-flex align-items-center flex-wrap gap-3">
                    <div class="dashboard-stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-journal-bookmark"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="mb-0 fw-bold">Discipleship</h5>
                        <p class="text-muted small mb-0"><?php echo (int) $stats['discipleship']['programs']; ?> programs · <?php echo (int) $stats['discipleship']['enrollments']; ?> active enrollments</p>
                    </div>
                    <span class="btn btn-outline-primary btn-sm">Manage <i class="bi bi-arrow-right ms-1"></i></span>
                </div>
            </div>
        </a>
    </div>
</div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between py-3">
                <h5 class="mb-0 fw-semibold"><i class="bi bi-envelope me-2 text-primary"></i>Recent contact inquiries</h5>
                <a href="contacts.php" class="btn btn-sm btn-outline-primary">View all</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentContacts)): ?>
                    <p class="text-muted mb-0 p-3">No inquiries yet.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($recentContacts as $contact): ?>
                            <li class="list-group-item border-0 py-3 d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-semibold"><?php echo htmlspecialchars($contact['name']); ?></div>
                                    <div class="small text-muted"><?php echo htmlspecialchars($contact['subject']); ?></div>
                                    <div class="small text-muted mt-1"><?php echo formatDateTime($contact['created_at']); ?></div>
                                </div>
                                <span class="badge rounded-pill bg-<?php echo $contact['status'] === 'new' ? 'primary' : 'secondary'; ?> ms-2"><?php echo ucfirst($contact['status']); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between py-3">
                <h5 class="mb-0 fw-semibold"><i class="bi bi-calendar-event me-2 text-success"></i>Upcoming events</h5>
                <a href="events.php" class="btn btn-sm btn-outline-success">View all</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentEvents)): ?>
                    <p class="text-muted mb-0 p-3">No upcoming events.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($recentEvents as $event): ?>
                            <li class="list-group-item border-0 py-3 d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-semibold"><?php echo htmlspecialchars($event['title']); ?></div>
                                    <div class="small text-muted">
                                        <i class="bi bi-calendar3 me-1"></i><?php echo formatDate($event['event_date']); ?>
                                        <?php if (!empty($event['event_time'])): ?>
                                            <i class="bi bi-clock ms-2 me-1"></i><?php echo date('g:i A', strtotime($event['event_time'])); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <span class="badge rounded-pill bg-<?php echo $event['status'] === 'upcoming' ? 'success' : 'secondary'; ?> ms-2"><?php echo ucfirst($event['status']); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

