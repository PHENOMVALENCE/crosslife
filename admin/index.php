<?php
$pageTitle = 'Dashboard';
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
?>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div style="width: 60px; height: 60px; background: rgba(200, 87, 22, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-play-circle" style="font-size: 2rem; color: var(--accent-color);"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0" style="color: var(--default-color); opacity: 0.7;">Sermons</h6>
                        <h3 class="mb-0" style="color: var(--heading-color);"><?php echo $stats['sermons']['total']; ?></h3>
                        <small style="color: var(--accent-color);"><?php echo $stats['sermons']['published']; ?> published</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div style="width: 60px; height: 60px; background: rgba(200, 87, 22, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-calendar-event" style="font-size: 2rem; color: var(--accent-color);"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0" style="color: var(--default-color); opacity: 0.7;">Events</h6>
                        <h3 class="mb-0" style="color: var(--heading-color);"><?php echo $stats['events']['total']; ?></h3>
                        <small style="color: var(--accent-color);"><?php echo $stats['events']['upcoming']; ?> upcoming</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div style="width: 60px; height: 60px; background: rgba(200, 87, 22, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-envelope" style="font-size: 2rem; color: var(--accent-color);"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0" style="color: var(--default-color); opacity: 0.7;">Inquiries</h6>
                        <h3 class="mb-0" style="color: var(--heading-color);"><?php echo $stats['contacts']['total']; ?></h3>
                        <small style="color: var(--accent-color);"><?php echo $stats['contacts']['new']; ?> new</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div style="width: 60px; height: 60px; background: rgba(200, 87, 22, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-pray" style="font-size: 2rem; color: var(--accent-color);"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0" style="color: var(--default-color); opacity: 0.7;">Prayer Requests</h6>
                        <h3 class="mb-0" style="color: var(--heading-color);"><?php echo $stats['prayers']['total']; ?></h3>
                        <small style="color: var(--accent-color);"><?php echo $stats['prayers']['new']; ?> new</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-envelope me-2"></i>Recent Contact Inquiries</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recentContacts)): ?>
                    <p class="text-muted mb-0">No inquiries yet.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentContacts as $contact): ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($contact['name']); ?></h6>
                                        <p class="mb-1 small text-muted"><?php echo htmlspecialchars($contact['subject']); ?></p>
                                        <small class="text-muted"><?php echo formatDateTime($contact['created_at']); ?></small>
                                    </div>
                                    <span class="badge bg-<?php echo $contact['status'] === 'new' ? 'primary' : 'secondary'; ?>">
                                        <?php echo ucfirst($contact['status']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-3">
                        <a href="contacts.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Upcoming Events</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recentEvents)): ?>
                    <p class="text-muted mb-0">No upcoming events.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentEvents as $event): ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($event['title']); ?></h6>
                                        <p class="mb-1 small text-muted">
                                            <i class="bi bi-calendar me-1"></i><?php echo formatDate($event['event_date']); ?>
                                            <?php if ($event['event_time']): ?>
                                                <i class="bi bi-clock ms-2 me-1"></i><?php echo date('g:i A', strtotime($event['event_time'])); ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <span class="badge bg-<?php echo $event['status'] === 'upcoming' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($event['status']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-3">
                        <a href="events.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

