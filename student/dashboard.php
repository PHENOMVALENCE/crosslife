<?php
/**
 * Student Dashboard – My programs and available programs
 */
require_once __DIR__ . '/../admin/config/config.php';
require_once __DIR__ . '/../includes/discipleship-functions.php';
requireStudentLogin();

$student = getCurrentStudent();
$enrollments = discipleship_get_student_enrollments($student['id']);
$availablePrograms = discipleship_get_active_programs();
$enrolledProgramIds = array_column($enrollments, 'program_id');
$siteName = defined('SITE_NAME') ? SITE_NAME : 'CrossLife';
$pageTitle = 'My Dashboard';
$breadcrumb = [['Dashboard', '']];
require_once __DIR__ . '/includes/header.php';
?>

<h1 class="h3 mb-2" style="color: var(--text-primary);">My Dashboard</h1>
<p class="text-muted mb-4">Welcome back, <?php echo htmlspecialchars($student['full_name']); ?>. Continue your programs or enroll in new ones.</p>

<!-- My Programs -->
<section class="student-card card mb-4">
    <div class="card-header d-flex align-items-center">
        <i class="bi bi-journal-bookmark me-2"></i>
        My Programs
    </div>
    <div class="card-body">
        <?php if (empty($enrollments)): ?>
            <p class="text-muted mb-0">You are not enrolled in any program yet. Enroll in one of the available programs below.</p>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($enrollments as $e):
                    $total = (int) ($e['modules_total'] ?? 0);
                    $passed = (int) ($e['modules_passed'] ?? 0);
                    $pct = $total > 0 ? min(100, round(($passed / $total) * 100)) : 0;
                    $isCompleted = isset($e['status']) && $e['status'] === 'completed';
                ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <a href="<?php echo $isCompleted ? 'certificate.php?enrollment_id=' . (int)$e['id'] : 'program.php?enrollment_id=' . (int)$e['id']; ?>" class="program-card-link card text-decoration-none">
                            <div class="card-body">
                                <h2 class="h6 card-title mb-2"><?php echo htmlspecialchars($e['program_name']); ?></h2>
                                <?php if ($isCompleted): ?>
                                    <span class="badge badge-status-completed mb-2">Completed</span>
                                <?php elseif ($total > 0): ?>
                                    <div class="elms-progress-label mb-1"><?php echo $passed; ?> / <?php echo $total; ?> modules</div>
                                    <div class="elms-progress-bar mb-2">
                                        <div class="progress-fill" style="width: <?php echo $pct; ?>%;"></div>
                                    </div>
                                <?php endif; ?>
                                <p class="card-text small text-muted mb-3"><?php echo htmlspecialchars(mb_substr(strip_tags($e['description'] ?? ''), 0, 100)); ?><?php echo mb_strlen($e['description'] ?? '') > 100 ? '…' : ''; ?></p>
                                <span class="btn btn-elms-accent btn-sm"><?php echo $isCompleted ? 'View certificate' : 'Continue'; ?> <i class="bi bi-arrow-right ms-1"></i></span>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Available Programs -->
<section class="student-card card">
    <div class="card-header d-flex align-items-center">
        <i class="bi bi-plus-circle me-2"></i>
        Available Programs
    </div>
    <div class="card-body">
        <?php
        $toShow = array_filter($availablePrograms, function ($p) use ($enrolledProgramIds) {
            return !in_array($p['id'], $enrolledProgramIds);
        });
        ?>
        <?php if (empty($toShow)): ?>
            <p class="text-muted mb-0">No other programs available to enroll at the moment. Check back later.</p>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($toShow as $p): ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card h-100 program-card-link">
                            <div class="card-body d-flex flex-column">
                                <h3 class="h6 card-title"><?php echo htmlspecialchars($p['program_name']); ?></h3>
                                <p class="card-text small text-muted flex-grow-1"><?php echo htmlspecialchars(mb_substr(strip_tags($p['description']), 0, 120)); ?>…</p>
                                <a href="program.php?program_id=<?php echo (int) $p['id']; ?>" class="btn btn-outline-elms btn-sm align-self-start">Enroll</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
