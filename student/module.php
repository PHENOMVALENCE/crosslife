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
    header('Location: module.php?enrollment_id=' . $enrollmentId . '&module_id=' . $moduleId . '&results=1');
    exit;
}

$showResults = isset($_GET['results']) && $_GET['results'] === '1';
$showReview = isset($_GET['review']) && $_GET['review'] === '1';
$lastAttempt = null;
$attemptAnswers = [];
$correctOptions = [];

if ($showResults || $showReview) {
    $lastAttempt = discipleship_get_last_attempt($enrollmentId, $moduleId);
    if ($lastAttempt) {
        $attemptAnswers = discipleship_get_attempt_answers_with_text($lastAttempt['id']);
        $correctOptions = discipleship_get_correct_options_by_question($moduleId);
    }
}

$resources = discipleship_get_resources($moduleId);
$questions = discipleship_get_questions_with_options($moduleId);
$passed = discipleship_has_passed_module($enrollmentId, $moduleId);
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

<?php if (!$showResults && !$showReview): ?>
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
            Learning materials
        </h2>
        <p class="text-muted small mb-3">Work through the materials below in order. When you're ready, scroll down to attempt the module test.</p>
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

<?php if (!empty($questions) && !$passed): ?>
    <section class="assessment-section" id="module-assessment" aria-label="Module assessment">
        <h2 class="section-title">
            <i class="bi bi-clipboard-check" aria-hidden="true"></i>
            Ready for the assessment?
        </h2>
        <p class="section-desc">When you're ready, take the module test below. You need at least <?php echo (int) $module['pass_mark_pct']; ?>% to pass and unlock the next module.</p>
    </section>
<?php endif; ?>
<?php endif; ?>

<?php if ($showResults && $lastAttempt): ?>
    <section class="student-card card mt-4">
        <div class="card-header d-flex align-items-center">
            <i class="bi bi-clipboard-check me-2"></i>
            Quiz results
        </div>
        <div class="card-body">
            <?php
            $scorePct = (float) $lastAttempt['score_pct'];
            $passMark = (int) $module['pass_mark_pct'];
            $attemptPassed = (int) $lastAttempt['passed'] === 1;
            $totalQ = count($attemptAnswers);
            $correctQ = count(array_filter($attemptAnswers, function ($a) { return (int) $a['is_correct'] === 1; }));
            ?>
            <div class="elms-score-gauge <?php echo $attemptPassed ? '' : 'fail'; ?> mb-3">
                <?php echo $correctQ; ?> / <?php echo $totalQ; ?>
            </div>
            <p class="text-center mb-2"><strong><?php echo number_format($scorePct, 1); ?>%</strong></p>
            <p class="text-center mb-4">
                <?php if ($attemptPassed): ?>
                    <span class="badge badge-status-active">Passed</span> – You can proceed to the next module.
                <?php else: ?>
                    <span class="badge badge-status-expired">Did not pass</span> – Pass mark is <?php echo $passMark; ?>%. You can try again.
                <?php endif; ?>
            </p>
            <h3 class="h6 mb-3">Per-question feedback</h3>
            <?php foreach ($attemptAnswers as $qi => $a): ?>
                <div class="mb-3 p-3 rounded <?php echo (int)$a['is_correct'] === 1 ? 'bg-success bg-opacity-10' : 'bg-danger bg-opacity-10'; ?>">
                    <p class="fw-semibold mb-1"><?php echo ($qi + 1); ?>. <?php echo htmlspecialchars($a['question_text']); ?></p>
                    <p class="mb-1 small">Your answer: <strong><?php echo htmlspecialchars($a['option_text']); ?></strong> <?php echo (int)$a['is_correct'] === 1 ? '<i class="bi bi-check-circle text-success"></i>' : '<i class="bi bi-x-circle text-danger"></i>'; ?></p>
                    <?php if ((int)$a['is_correct'] === 0 && isset($correctOptions[$a['question_id']])): ?>
                        <p class="mb-1 small text-muted">Correct answer: <?php echo htmlspecialchars($correctOptions[$a['question_id']]['option_text']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty(trim($a['feedback_text'] ?? ''))): ?>
                        <p class="mb-0 small"><?php echo nl2br(htmlspecialchars($a['feedback_text'])); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <div class="mt-4">
                <a href="program.php?enrollment_id=<?php echo $enrollmentId; ?>" class="btn btn-elms-accent"><?php echo $attemptPassed ? 'Next module →' : 'Back to program'; ?></a>
            </div>
        </div>
    </section>
<?php elseif ($showReview && $lastAttempt): ?>
    <section class="student-card card mt-4">
        <div class="card-header d-flex align-items-center">
            <i class="bi bi-clock-history me-2"></i>
            Review last attempt
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">Attempt on <?php echo date('M j, Y g:i A', strtotime($lastAttempt['attempted_at'])); ?> – Score: <?php echo number_format((float)$lastAttempt['score_pct'], 1); ?>% <?php echo (int)$lastAttempt['passed'] === 1 ? '(Passed)' : '(Did not pass)'; ?></p>
            <?php foreach ($attemptAnswers as $qi => $a): ?>
                <div class="mb-3 p-3 rounded <?php echo (int)$a['is_correct'] === 1 ? 'bg-success bg-opacity-10' : 'bg-danger bg-opacity-10'; ?>">
                    <p class="fw-semibold mb-1"><?php echo ($qi + 1); ?>. <?php echo htmlspecialchars($a['question_text']); ?></p>
                    <p class="mb-1 small">Your answer: <?php echo htmlspecialchars($a['option_text']); ?> <?php echo (int)$a['is_correct'] === 1 ? '<i class="bi bi-check-circle text-success"></i>' : '<i class="bi bi-x-circle text-danger"></i>'; ?></p>
                    <?php if (!empty(trim($a['feedback_text'] ?? ''))): ?>
                        <p class="mb-0 small"><?php echo nl2br(htmlspecialchars($a['feedback_text'])); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <a href="module.php?enrollment_id=<?php echo $enrollmentId; ?>&module_id=<?php echo $moduleId; ?>" class="btn btn-elms-accent">Back to module</a>
        </div>
    </section>
<?php elseif (!empty($questions)): ?>
    <?php if ($passed): ?>
        <section class="assessment-section" id="module-assessment" aria-label="Assessment complete">
            <h2 class="section-title"><i class="bi bi-check-circle-fill text-success" aria-hidden="true"></i>Assessment complete</h2>
            <div class="assessment-card">
                <p class="text-success mb-3"><i class="bi bi-check-circle me-2"></i>You have passed this module.</p>
                <a href="program.php?enrollment_id=<?php echo $enrollmentId; ?>" class="btn btn-elms-accent">Back to program</a>
            </div>
        </section>
    <?php else: ?>
        <div class="assessment-card" id="module-assessment">
            <h3 class="h6 mb-2 d-flex align-items-center"><i class="bi bi-clipboard-check me-2"></i>Module test</h3>
            <p class="text-muted small mb-3">Pass mark: <?php echo (int) $module['pass_mark_pct']; ?>%. You must pass to unlock the next module.</p>
            <?php
            $lastAttempt = discipleship_get_last_attempt($enrollmentId, $moduleId);
            if ($lastAttempt): ?>
                <p class="mb-3"><a href="module.php?enrollment_id=<?php echo $enrollmentId; ?>&module_id=<?php echo $moduleId; ?>&review=1" class="small">Review last attempt</a></p>
            <?php endif; ?>
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
                <button type="submit" name="submit_quiz" class="btn btn-elms-accent"><i class="bi bi-send me-2"></i>Submit answers</button>
            </form>
        </div>
    <?php endif; ?>
<?php else: ?>
    <section class="assessment-section" id="module-assessment" aria-label="Module assessment">
        <div class="assessment-card">
            <p class="text-muted mb-2">No assessment for this module yet. You can proceed to the next module when it is added.</p>
            <a href="program.php?enrollment_id=<?php echo $enrollmentId; ?>" class="btn btn-elms-accent">Back to program</a>
        </div>
    </section>
<?php endif; ?>

<?php if ($showResults || $showReview): ?>
    <div class="mt-3"><a href="program.php?enrollment_id=<?php echo $enrollmentId; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to program</a></div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
