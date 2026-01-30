<?php
/**
 * Program view: enroll (if program_id) or list modules and progress (if enrollment_id)
 */
require_once __DIR__ . '/../admin/config/config.php';
require_once __DIR__ . '/../includes/discipleship-functions.php';
requireStudentLogin();

$student = getCurrentStudent();
$enrollmentId = isset($_GET['enrollment_id']) ? (int) $_GET['enrollment_id'] : null;
$programId = isset($_GET['program_id']) ? (int) $_GET['program_id'] : null;

// Enroll in program (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $programId) {
    $program = discipleship_get_program($programId);
    if (!$program || !in_array($program['status'], ['active', 'upcoming'])) {
        $_SESSION['flash_message'] = 'Program not available.';
        $_SESSION['flash_type'] = 'danger';
        header('Location: dashboard.php');
        exit;
    }
    $existing = discipleship_get_enrollment_for_student_program($student['id'], $programId);
    if ($existing) {
        header('Location: program.php?enrollment_id=' . $existing['id']);
        exit;
    }
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO discipleship_enrollments (student_id, program_id, status) VALUES (?, ?, 'active')");
    $stmt->execute([$student['id'], $programId]);
    $newId = (int) $db->lastInsertId();
    $_SESSION['flash_message'] = 'You have been enrolled.';
    $_SESSION['flash_type'] = 'success';
    header('Location: program.php?enrollment_id=' . $newId);
    exit;
}

if ($enrollmentId) {
    $enrollment = discipleship_get_enrollment($enrollmentId, $student['id']);
    if (!$enrollment) {
        $_SESSION['flash_message'] = 'Enrollment not found.';
        $_SESSION['flash_type'] = 'danger';
        header('Location: dashboard.php');
        exit;
    }
    $program = discipleship_get_program($enrollment['program_id']);
    $modules = discipleship_get_modules($enrollment['program_id']);
    $moduleIds = array_column($modules, 'id');
    $passedIds = discipleship_get_passed_module_ids($enrollmentId);
} elseif ($programId) {
    $program = discipleship_get_program($programId);
    if (!$program || !in_array($program['status'], ['active', 'upcoming'])) {
        $_SESSION['flash_message'] = 'Program not found or not available.';
        $_SESSION['flash_type'] = 'danger';
        header('Location: dashboard.php');
        exit;
    }
    $enrollment = null;
    $modules = discipleship_get_modules($programId);
    $moduleIds = array_column($modules, 'id');
    $passedIds = [];
} else {
    header('Location: dashboard.php');
    exit;
}

$pageTitle = $program['program_name'];
require_once __DIR__ . '/includes/header.php';
?>

<nav aria-label="breadcrumb" class="student-breadcrumb mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active"><?php echo htmlspecialchars($program['program_name']); ?></li>
    </ol>
</nav>

<?php if (!$enrollment): ?>
    <div class="student-card card mb-4">
        <div class="card-body p-4">
            <h1 class="h3 mb-3"><?php echo htmlspecialchars($program['program_name']); ?></h1>
            <div class="text-muted mb-4" style="max-width: 60ch;"><?php echo nl2br(htmlspecialchars($program['description'])); ?></div>
            <form method="POST" action="program.php?program_id=<?php echo (int) $programId; ?>">
                <button type="submit" class="btn btn-accent"><i class="bi bi-plus-circle me-2"></i>Enroll in this program</button>
            </form>
        </div>
    </div>
<?php else: ?>
    <div class="program-header mb-4">
        <h1 class="h3 mb-2"><?php echo htmlspecialchars($program['program_name']); ?></h1>
        <p class="text-muted mb-0"><?php echo htmlspecialchars(mb_substr(strip_tags($program['description']), 0, 200)); ?>…</p>
        <?php $totalModules = count($modules); if ($totalModules > 0): ?>
            <p class="small text-muted mt-2 mb-0"><?php echo $totalModules; ?> module<?php echo $totalModules !== 1 ? 's' : ''; ?> in this program</p>
        <?php endif; ?>
    </div>

    <h2 class="h5 mb-3">Modules</h2>
    <?php if (empty($modules)): ?>
        <div class="student-card card">
            <div class="card-body">
                <p class="text-muted mb-0">No modules have been added to this program yet. Check back later.</p>
            </div>
        </div>
    <?php else: ?>
        <?php $totalMods = count($modules); foreach ($modules as $i => $mod): ?>
            <?php
            $unlocked = $enrollmentId && discipleship_is_module_unlocked($enrollmentId, $mod['id'], $moduleIds);
            $passed = in_array($mod['id'], $passedIds, true);
            $rowClass = 'module-row' . (!$unlocked ? ' locked' : '');
            ?>
            <div class="<?php echo $rowClass; ?>">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="module-num"><?php echo $i + 1; ?> of <?php echo $totalMods; ?></span>
                    <span class="fw-semibold"><?php echo htmlspecialchars($mod['title']); ?></span>
                    <?php if ($passed): ?>
                        <span class="badge badge-passed">Passed</span>
                    <?php elseif (!$unlocked): ?>
                        <span class="badge badge-locked">Locked</span>
                    <?php endif; ?>
                </div>
                <div class="mt-2 mt-md-0 ms-md-auto">
                    <?php if ($unlocked): ?>
                        <a href="module.php?enrollment_id=<?php echo $enrollmentId; ?>&module_id=<?php echo (int) $mod['id']; ?>" class="btn btn-accent btn-sm">
                            <?php echo $passed ? 'Review' : 'Start'; ?> <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    <?php else: ?>
                        <span class="text-muted small">Complete the previous module to unlock</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
