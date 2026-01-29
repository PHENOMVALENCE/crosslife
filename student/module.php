<?php
/**
 * Module view: learning content + quiz. Sequential unlock enforced.
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

// Submit quiz
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    $questions = discipleship_get_questions_with_options($moduleId);
    $correctCount = 0;
    $answers = [];
    foreach ($questions as $q) {
        $selected = (int) ($_POST['q_' . $q['id']] ?? 0);
        if (!$selected) continue;
        $option = null;
        foreach ($q['options'] as $o) {
            if ((int) $o['id'] === $selected) {
                $option = $o;
                break;
            }
        }
        if ($option) {
            $isCorrect = (int) $option['is_correct'] === 1;
            if ($isCorrect) $correctCount++;
            $answers[] = ['question_id' => $q['id'], 'option_id' => $option['id'], 'is_correct' => $isCorrect];
        }
    }
    $total = count($questions);
    $scorePct = $total > 0 ? round(($correctCount / $total) * 100, 2) : 0;
    $passMark = (int) $module['pass_mark_pct'];
    $passed = $scorePct >= $passMark;
    discipleship_record_attempt($enrollmentId, $moduleId, $scorePct, $passed, $answers);
    $_SESSION['flash_message'] = $passed
        ? "You passed with {$scorePct}%. You can now proceed to the next module."
        : "You scored {$scorePct}%. Pass mark is {$passMark}%. You can try again.";
    $_SESSION['flash_type'] = $passed ? 'success' : 'warning';
    header('Location: program.php?enrollment_id=' . $enrollmentId);
    exit;
}

$resources = discipleship_get_resources($moduleId);
$questions = discipleship_get_questions_with_options($moduleId);
$passed = discipleship_has_passed_module($enrollmentId, $moduleId);
$program = discipleship_get_program($enrollment['program_id']);
$pageTitle = $module['title'];
$allowedHtml = '<p><br><strong><b><em><i><u><ul><ol><li><h2><h3><h4><a><span><div>';
require_once __DIR__ . '/includes/header.php';
?>

<nav aria-label="breadcrumb" class="student-breadcrumb mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="program.php?enrollment_id=<?php echo $enrollmentId; ?>"><?php echo htmlspecialchars($program['program_name']); ?></a></li>
        <li class="breadcrumb-item active"><?php echo htmlspecialchars($module['title']); ?></li>
    </ol>
</nav>

<h1 class="h3 mb-4"><?php echo htmlspecialchars($module['title']); ?></h1>
<?php if (!empty($module['description'])): ?>
    <div class="module-content-block mb-4">
        <div class="content-html"><?php echo nl2br(htmlspecialchars($module['description'])); ?></div>
    </div>
<?php endif; ?>

<?php if (!empty($resources)): ?>
    <h2 class="h5 mb-3">Learning materials</h2>
    <?php foreach ($resources as $res): ?>
        <div class="module-content-block mb-4">
            <?php if (!empty($res['title'])): ?>
                <h3 class="h6 mb-3"><?php echo htmlspecialchars($res['title']); ?></h3>
            <?php endif; ?>
            <?php if ($res['resource_type'] === 'text' && !empty($res['content'])): ?>
                <div class="content-html"><?php echo strip_tags($res['content'], $allowedHtml); ?></div>
            <?php elseif ($res['resource_type'] === 'audio' && !empty($res['file_path'])): ?>
                <audio controls class="w-100" src="<?php echo htmlspecialchars(discipleship_resource_url($res['file_path'])); ?>">Your browser does not support audio.</audio>
            <?php elseif ($res['resource_type'] === 'video' && !empty($res['file_path'])): ?>
                <video controls class="w-100 rounded" style="max-height: 400px;" src="<?php echo htmlspecialchars(discipleship_resource_url($res['file_path'])); ?>">Your browser does not support video.</video>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php if (!empty($questions)): ?>
    <section class="student-card card mt-4">
        <div class="card-header d-flex align-items-center">
            <i class="bi bi-clipboard-check me-2"></i>
            Module assessment
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">Pass mark: <?php echo (int) $module['pass_mark_pct']; ?>%. You must pass to unlock the next module.</p>
            <?php if ($passed): ?>
                <p class="text-success mb-3"><i class="bi bi-check-circle me-2"></i>You have passed this module.</p>
                <a href="program.php?enrollment_id=<?php echo $enrollmentId; ?>" class="btn btn-accent">Back to program</a>
            <?php else: ?>
                <form method="POST" action="module.php">
                    <input type="hidden" name="enrollment_id" value="<?php echo $enrollmentId; ?>">
                    <input type="hidden" name="module_id" value="<?php echo $moduleId; ?>">
                    <?php foreach ($questions as $qi => $q): ?>
                        <div class="mb-4">
                            <p class="fw-semibold mb-2"><?php echo ($qi + 1); ?>. <?php echo htmlspecialchars($q['question_text']); ?></p>
                            <div class="ms-0 ms-md-3">
                                <?php foreach ($q['options'] as $o): ?>
                                    <div class="form-check py-1">
                                        <input class="form-check-input" type="radio" name="q_<?php echo $q['id']; ?>" id="q<?php echo $q['id']; ?>_o<?php echo $o['id']; ?>" value="<?php echo $o['id']; ?>" required>
                                        <label class="form-check-label" for="q<?php echo $q['id']; ?>_o<?php echo $o['id']; ?>"><?php echo htmlspecialchars($o['option_text']); ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <button type="submit" name="submit_quiz" class="btn btn-accent">Submit answers</button>
                </form>
            <?php endif; ?>
        </div>
    </section>
<?php else: ?>
    <div class="student-card card mt-4">
        <div class="card-body">
            <p class="text-muted mb-2">No assessment for this module yet. You can proceed to the next module when it is added.</p>
            <a href="program.php?enrollment_id=<?php echo $enrollmentId; ?>" class="btn btn-accent">Back to program</a>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
