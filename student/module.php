<?php
/**
 * Module view: learning content only. Test/quiz is on a separate page (test.php).
 * Sequential unlock enforced. Students mark themselves as "studied" to access the test.
 */
require_once __DIR__ . '/../admin/config/config.php';
require_once __DIR__ . '/../includes/discipleship-functions.php';
requireStudentLogin();

$student = getCurrentStudent();
$enrollmentId = isset($_GET['enrollment_id']) ? (int) $_GET['enrollment_id'] : (int) ($_POST['enrollment_id'] ?? 0);
$moduleId = isset($_GET['module_id']) ? (int) $_GET['module_id'] : (int) ($_POST['module_id'] ?? 0);

if (!$enrollmentId || !$moduleId) {
    $_SESSION['flash_message'] = 'Invalid module.';
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

$module = discipleship_get_module($moduleId);
if (!$module || (int) $module['program_id'] !== (int) $enrollment['program_id']) {
    $_SESSION['flash_message'] = 'Module not found.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: program.php?enrollment_id=' . $enrollmentId);
    exit;
}

$modules = discipleship_get_modules($enrollment['program_id']);
$moduleIds = array_column($modules, 'id');
$unlocked = discipleship_is_module_unlocked($enrollmentId, $moduleId, $moduleIds);
if (!$unlocked) {
    $_SESSION['flash_message'] = 'Complete the previous module to unlock this one.';
    $_SESSION['flash_type'] = 'warning';
    header('Location: program.php?enrollment_id=' . $enrollmentId);
    exit;
}

// Handle "Mark as Studied" POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_studied'])) {
    discipleship_mark_module_studied($enrollmentId, $moduleId);
    $_SESSION['flash_message'] = 'Module marked as studied. You can now take the test.';
    $_SESSION['flash_type'] = 'success';
    header('Location: module.php?enrollment_id=' . $enrollmentId . '&module_id=' . $moduleId);
    exit;
}

$resources = discipleship_get_resources($moduleId);
$questions = discipleship_get_questions_with_options($moduleId);
$passed = discipleship_has_passed_module($enrollmentId, $moduleId);
$studied = discipleship_has_studied_module($enrollmentId, $moduleId);
$program = discipleship_get_program($enrollment['program_id']);
$pageTitle = $module['title'];
$breadcrumb = [
    ['Dashboard', 'dashboard.php'],
    [htmlspecialchars($program['program_name']), 'program.php?enrollment_id=' . $enrollmentId],
    [htmlspecialchars($module['title']), '']
];
$allowedHtml = '<p><br><strong><b><em><i><u><ul><ol><li><h2><h3><h4><a><span><div>';
require_once __DIR__ . '/includes/header.php';
?>

<div class="module-header mb-4">
    <h1 class="h3 mb-2"><?php echo htmlspecialchars($module['title']); ?></h1>
    <a href="program.php?enrollment_id=<?php echo $enrollmentId; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to program</a>
</div>

<?php if (!empty($module['description'])): ?>
<div class="module-content-block mb-4">
    <h2 class="h6 text-muted mb-2">About this module</h2>
    <div class="content-html"><?php echo nl2br(htmlspecialchars($module['description'])); ?></div>
</div>
<?php endif; ?>

<?php if (!empty($resources)): ?>
<section class="learning-materials-section" aria-label="Learning materials">
    <h2 class="section-title">
        <i class="bi bi-journal-bookmark-fill" aria-hidden="true"></i>
        Learning Materials
    </h2>
    <p class="text-muted small mb-3">Work through the materials below in order. When you're done, mark the module as studied to unlock the assessment.</p>
    <?php foreach ($resources as $idx => $res):
        $resUrl = !empty($res['file_path']) ? discipleship_resource_url($res['file_path']) : '';
        $resType = $res['resource_type'];
        $stepNum = $idx + 1;
        $typeLabel = ucfirst($resType);
    ?>
    <div class="material-step" id="material-<?php echo $stepNum; ?>">
        <div class="material-step-header">
            <span class="material-step-num" aria-hidden="true"><?php echo $stepNum; ?></span>
            <h3 class="material-step-title"><?php echo !empty($res['title']) ? htmlspecialchars($res['title']) : $typeLabel . ' resource'; ?></h3>
            <span class="material-type-badge <?php echo $resType; ?>"><?php echo $typeLabel; ?></span>
        </div>
        <div class="material-step-body">
            <?php if ($resType === 'text' && !empty($res['content'])): ?>
                <div class="content-html"><?php echo strip_tags($res['content'], $allowedHtml); ?></div>
            <?php elseif ($resType === 'audio' && $resUrl): ?>
                <audio controls class="w-100 rounded" src="<?php echo htmlspecialchars($resUrl); ?>">Your browser does not support audio.</audio>
            <?php elseif ($resType === 'video' && $resUrl): ?>
                <video controls class="w-100 rounded" style="max-height: 400px;" src="<?php echo htmlspecialchars($resUrl); ?>">Your browser does not support video.</video>
            <?php elseif ($resType === 'pdf' && $resUrl): ?>
                <div class="pdf-resource">
                    <div class="pdf-actions mb-2">
                        <a href="<?php echo htmlspecialchars($resUrl); ?>" target="_blank" rel="noopener" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-box-arrow-up-right me-1"></i>Open PDF in new tab
                        </a>
                    </div>
                    <iframe src="<?php echo htmlspecialchars($resUrl); ?>#toolbar=1" class="pdf-embed" title="<?php echo htmlspecialchars($res['title'] ?? 'PDF'); ?>"></iframe>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</section>
<?php endif; ?>

<!-- Assessment / Study Status Section -->
<section class="assessment-section mt-4" id="module-assessment" aria-label="Assessment access">
    <?php if ($passed): ?>
        <!-- Already passed -->
        <div class="assessment-card assessment-card--passed">
            <h2 class="section-title"><i class="bi bi-check-circle-fill text-success" aria-hidden="true"></i> Module Complete</h2>
            <p class="text-success mb-3">You have passed this module's assessment.</p>
            <div class="d-flex flex-wrap gap-2">
                <a href="test.php?enrollment_id=<?php echo $enrollmentId; ?>&module_id=<?php echo $moduleId; ?>&review=1" class="btn btn-outline-elms btn-sm"><i class="bi bi-eye me-1"></i>Review last attempt</a>
                <a href="program.php?enrollment_id=<?php echo $enrollmentId; ?>" class="btn btn-elms-accent btn-sm">Back to program <i class="bi bi-arrow-right ms-1"></i></a>
            </div>
        </div>
    <?php elseif ($studied && !empty($questions)): ?>
        <!-- Studied and test available -->
        <div class="assessment-card assessment-card--ready">
            <h2 class="section-title"><i class="bi bi-clipboard-check" aria-hidden="true"></i> Ready for the Assessment</h2>
            <p class="section-desc">You've completed the learning materials. Take the test when you're ready &mdash; you need at least <strong><?php echo (int) $module['pass_mark_pct']; ?>%</strong> to pass.</p>
            <a href="test.php?enrollment_id=<?php echo $enrollmentId; ?>&module_id=<?php echo $moduleId; ?>" class="btn btn-elms-accent">
                <i class="bi bi-pencil-square me-1"></i>Take the Test
            </a>
        </div>
    <?php elseif (!empty($questions)): ?>
        <!-- Not yet studied -->
        <div class="assessment-card assessment-card--study">
            <h2 class="section-title"><i class="bi bi-book" aria-hidden="true"></i> Finish Studying First</h2>
            <p class="section-desc">Once you've worked through all the learning materials above, mark this module as studied to unlock the assessment.</p>
            <form method="POST" action="module.php?enrollment_id=<?php echo $enrollmentId; ?>&module_id=<?php echo $moduleId; ?>">
                <input type="hidden" name="mark_studied" value="1">
                <button type="submit" class="btn btn-elms-accent">
                    <i class="bi bi-check2-circle me-1"></i>I've Finished Studying
                </button>
            </form>
        </div>
    <?php else: ?>
        <!-- No questions at all -->
        <div class="assessment-card">
            <p class="text-muted mb-2">No assessment for this module yet. You can proceed to the next module when it is added.</p>
            <a href="program.php?enrollment_id=<?php echo $enrollmentId; ?>" class="btn btn-elms-accent">Back to program</a>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
