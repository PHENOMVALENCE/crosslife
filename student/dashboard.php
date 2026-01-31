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
require_once __DIR__ . '/includes/header.php';
?>

<h1 class="h3 mb-4">My Dashboard</h1>
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
                <?php foreach ($enrollments as $e): ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <a href="program.php?enrollment_id=<?php echo (int) $e['id']; ?>" class="program-card-link card text-decoration-none">
                            <div class="card-body">
                                <h2 class="h6 card-title mb-2"><?php echo htmlspecialchars($e['program_name']); ?></h2>
                                <p class="card-text small text-muted mb-3"><?php echo htmlspecialchars(mb_substr(strip_tags($e['description'] ?? ''), 0, 100)); ?><?php echo mb_strlen($e['description'] ?? '') > 100 ? '…' : ''; ?></p>
                                <span class="btn btn-accent btn-sm">Continue <i class="bi bi-arrow-right ms-1"></i></span>
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
                                <a href="program.php?program_id=<?php echo (int) $p['id']; ?>" class="btn btn-outline-primary btn-sm align-self-start">Enroll</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
