<?php
/**
 * Certificate of completion – shown when enrollment is completed (all modules passed).
 */
require_once __DIR__ . '/../admin/config/config.php';
require_once __DIR__ . '/../includes/discipleship-functions.php';
requireStudentLogin();

$student = getCurrentStudent();
$enrollmentId = isset($_GET['enrollment_id']) ? (int) $_GET['enrollment_id'] : 0;

if (!$enrollmentId) {
    $_SESSION['flash_message'] = 'Invalid certificate.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: dashboard.php');
    exit;
}

$enrollment = discipleship_get_enrollment($enrollmentId, $student['id']);
if (!$enrollment) {
    $_SESSION['flash_message'] = 'Enrollment not found.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: dashboard.php');
    exit;
}

if ((isset($enrollment['status']) ? $enrollment['status'] : '') !== 'completed') {
    $_SESSION['flash_message'] = 'Certificate is available when you complete all modules.';
    $_SESSION['flash_type'] = 'info';
    header('Location: program.php?enrollment_id=' . $enrollmentId);
    exit;
}

$program = discipleship_get_program($enrollment['program_id']);
$completedAt = null;
$db = getDB();
$stmt = $db->prepare("SELECT completed_at FROM discipleship_enrollments WHERE id = ?");
$stmt->execute([$enrollmentId]);
$row = $stmt->fetch();
if ($row && !empty($row['completed_at'])) {
    $completedAt = $row['completed_at'];
}

$pageTitle = 'Certificate – ' . $program['program_name'];
$breadcrumb = [
    ['Dashboard', 'dashboard.php'],
    ['Certificate', '']
];
require_once __DIR__ . '/includes/header.php';
?>

<div class="text-center mb-4">
    <h1 class="h3 mb-2" style="color: var(--text-primary);">Congratulations!</h1>
    <p class="text-muted">You have completed <strong><?php echo htmlspecialchars($program['program_name']); ?></strong>.</p>
</div>

<div class="elms-certificate-card mb-4">
    <div class="text-center">
        <p class="text-uppercase small text-muted mb-1">Certificate of Completion</p>
        <h2 class="h4 mb-3" style="color: var(--text-primary);"><?php echo htmlspecialchars($program['program_name']); ?></h2>
        <p class="mb-2">This is to certify that</p>
        <p class="h5 mb-3 fw-bold"><?php echo htmlspecialchars($student['full_name']); ?></p>
        <p class="mb-2">has successfully completed all requirements of this program.</p>
        <?php if ($completedAt): ?>
            <p class="text-muted small mb-0">Completed on <?php echo date('F j, Y', strtotime($completedAt)); ?></p>
        <?php endif; ?>
        <div class="mt-4 pt-3 border-top">
            <img src="../assets/img/logo.png" alt="" width="48" height="48" class="rounded">
            <p class="small text-muted mt-2 mb-0"><?php echo htmlspecialchars(defined('SITE_NAME') ? SITE_NAME : 'CrossLife'); ?> · School of Christ Academy</p>
        </div>
    </div>
</div>

<div class="d-flex flex-wrap justify-content-center gap-2 mb-4">
    <button type="button" class="btn btn-elms-accent" onclick="window.print();">
        <i class="bi bi-download me-2"></i>Download / Print certificate
    </button>
    <a href="dashboard.php" class="btn btn-outline-elms">Back to Dashboard</a>
</div>

<style media="print">
    .elms-sidebar, .elms-topbar, .elms-breadcrumb-wrap, .breadcrumb, .btn, .student-portal footer, .alert { display: none !important; }
    .elms-main-wrap { margin-left: 0 !important; }
    .elms-content { padding: 0 !important; }
    .elms-certificate-card { box-shadow: none; border: 2px solid #2c3e50 !important; }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
