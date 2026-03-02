<?php
/**
 * Test / Assessment page – separated from the learning materials.
 * Students must study (mark_studied) the module before they can access this page.
 */
require_once __DIR__ . '/../admin/config/config.php';
require_once __DIR__ . '/../includes/discipleship-functions.php';
requireStudentLogin();

$student = getCurrentStudent();
$enrollmentId = isset($_GET['enrollment_id']) ? (int) $_GET['enrollment_id'] : (int) ($_POST['enrollment_id'] ?? 0);
$moduleId = isset($_GET['module_id']) ? (int) $_GET['module_id'] : (int) ($_POST['module_id'] ?? 0);

if (!$enrollmentId || !$moduleId) {
    $_SESSION['flash_message'] = 'Invalid test link.';
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

// Gate: must have studied before taking the test
$studied = discipleship_has_studied_module($enrollmentId, $moduleId);
if (!$studied) {
    $_SESSION['flash_message'] = 'Please study the learning materials before taking the test.';
    $_SESSION['flash_type'] = 'warning';
    header('Location: module.php?enrollment_id=' . $enrollmentId . '&module_id=' . $moduleId);
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
    header('Location: test.php?enrollment_id=' . $enrollmentId . '&module_id=' . $moduleId . '&results=1');
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

$questions = discipleship_get_questions_with_options($moduleId);
$passedModule = discipleship_has_passed_module($enrollmentId, $moduleId);
$program = discipleship_get_program($enrollment['program_id']);
$pageTitle = 'Test – ' . $module['title'];
$breadcrumb = [
    ['Dashboard', 'dashboard.php'],
    [htmlspecialchars($program['program_name']), 'program.php?enrollment_id=' . $enrollmentId],
    [htmlspecialchars($module['title']), 'module.php?enrollment_id=' . $enrollmentId . '&module_id=' . $moduleId],
    ['Test', '']
];
require_once __DIR__ . '/includes/header.php';
?>

<div class="module-header mb-4">
    <h1 class="h3 mb-2">
        <i class="bi bi-clipboard-check me-2 text-accent"></i>
        <?php echo htmlspecialchars($module['title']); ?> &ndash; Test
    </h1>
    <div class="d-flex flex-wrap gap-2">
        <a href="module.php?enrollment_id=<?php echo $enrollmentId; ?>&module_id=<?php echo $moduleId; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-book me-1"></i>Back to study materials</a>
        <a href="program.php?enrollment_id=<?php echo $enrollmentId; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to program</a>
    </div>
</div>

<?php if ($showResults && $lastAttempt): ?>
    <!-- Quiz Results -->
    <section class="test-results-section">
        <div class="student-card card">
            <div class="card-header d-flex align-items-center">
                <i class="bi bi-clipboard-check me-2"></i>
                Test Results
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
                        <span class="badge badge-status-active">Passed</span> &mdash; You can proceed to the next module.
                    <?php else: ?>
                        <span class="badge badge-status-expired">Did not pass</span> &mdash; Pass mark is <?php echo $passMark; ?>%. You can try again.
                    <?php endif; ?>
                </p>

                <h3 class="h6 mb-3">Per-question feedback</h3>
                <?php foreach ($attemptAnswers as $qi => $a): ?>
                    <div class="question-feedback mb-3 p-3 rounded <?php echo (int)$a['is_correct'] === 1 ? 'bg-success bg-opacity-10' : 'bg-danger bg-opacity-10'; ?>">
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

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <?php if ($attemptPassed): ?>
                        <a href="program.php?enrollment_id=<?php echo $enrollmentId; ?>" class="btn btn-elms-accent">Next module <i class="bi bi-arrow-right ms-1"></i></a>
                    <?php else: ?>
                        <a href="test.php?enrollment_id=<?php echo $enrollmentId; ?>&module_id=<?php echo $moduleId; ?>" class="btn btn-elms-accent"><i class="bi bi-arrow-repeat me-1"></i>Try again</a>
                        <a href="module.php?enrollment_id=<?php echo $enrollmentId; ?>&module_id=<?php echo $moduleId; ?>" class="btn btn-outline-secondary">Review materials</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

<?php elseif ($showReview && $lastAttempt): ?>
    <!-- Review Last Attempt -->
    <section class="test-review-section">
        <div class="student-card card">
            <div class="card-header d-flex align-items-center">
                <i class="bi bi-clock-history me-2"></i>
                Review Last Attempt
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Attempt on <?php echo date('M j, Y g:i A', strtotime($lastAttempt['attempted_at'])); ?> &mdash; Score: <?php echo number_format((float)$lastAttempt['score_pct'], 1); ?>% <?php echo (int)$lastAttempt['passed'] === 1 ? '(Passed)' : '(Did not pass)'; ?></p>
                <?php foreach ($attemptAnswers as $qi => $a): ?>
                    <div class="question-feedback mb-3 p-3 rounded <?php echo (int)$a['is_correct'] === 1 ? 'bg-success bg-opacity-10' : 'bg-danger bg-opacity-10'; ?>">
                        <p class="fw-semibold mb-1"><?php echo ($qi + 1); ?>. <?php echo htmlspecialchars($a['question_text']); ?></p>
                        <p class="mb-1 small">Your answer: <?php echo htmlspecialchars($a['option_text']); ?> <?php echo (int)$a['is_correct'] === 1 ? '<i class="bi bi-check-circle text-success"></i>' : '<i class="bi bi-x-circle text-danger"></i>'; ?></p>
                        <?php if ((int)$a['is_correct'] === 0 && isset($correctOptions[$a['question_id']])): ?>
                            <p class="mb-1 small text-muted">Correct answer: <?php echo htmlspecialchars($correctOptions[$a['question_id']]['option_text']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty(trim($a['feedback_text'] ?? ''))): ?>
                            <p class="mb-0 small"><?php echo nl2br(htmlspecialchars($a['feedback_text'])); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <div class="d-flex flex-wrap gap-2">
                    <a href="module.php?enrollment_id=<?php echo $enrollmentId; ?>&module_id=<?php echo $moduleId; ?>" class="btn btn-elms-accent"><i class="bi bi-book me-1"></i>Back to module</a>
                </div>
            </div>
        </div>
    </section>

<?php elseif (!empty($questions)): ?>
    <?php if ($passedModule): ?>
        <!-- Already passed – show review option -->
        <section class="assessment-section">
            <div class="assessment-card assessment-card--passed">
                <h2 class="section-title"><i class="bi bi-check-circle-fill text-success" aria-hidden="true"></i> Assessment Complete</h2>
                <p class="text-success mb-3">You have passed this module.</p>
                <?php $lastAttempt = discipleship_get_last_attempt($enrollmentId, $moduleId); ?>
                <?php if ($lastAttempt): ?>
                    <a href="test.php?enrollment_id=<?php echo $enrollmentId; ?>&module_id=<?php echo $moduleId; ?>&review=1" class="btn btn-outline-elms btn-sm me-2"><i class="bi bi-eye me-1"></i>Review last attempt</a>
                <?php endif; ?>
                <a href="program.php?enrollment_id=<?php echo $enrollmentId; ?>" class="btn btn-elms-accent">Back to program</a>
            </div>
        </section>
    <?php else: ?>
        <!-- Quiz Form -->
        <section class="test-form-section">
            <div class="test-info-banner mb-4">
                <div class="d-flex align-items-start gap-3">
                    <i class="bi bi-info-circle fs-4 text-accent flex-shrink-0"></i>
                    <div>
                        <p class="mb-1 fw-semibold">Module Assessment</p>
                        <p class="mb-0 small text-muted">Answer all questions below. You need at least <strong><?php echo (int) $module['pass_mark_pct']; ?>%</strong> to pass and unlock the next module.</p>
                    </div>
                </div>
            </div>

            <?php
            $lastAttempt = discipleship_get_last_attempt($enrollmentId, $moduleId);
            if ($lastAttempt): ?>
                <div class="mb-3 p-3 rounded bg-warning bg-opacity-10">
                    <p class="mb-1 small"><strong>Previous attempt:</strong> <?php echo number_format((float)$lastAttempt['score_pct'], 1); ?>% &mdash; <?php echo (int)$lastAttempt['passed'] === 1 ? 'Passed' : 'Did not pass'; ?></p>
                    <a href="test.php?enrollment_id=<?php echo $enrollmentId; ?>&module_id=<?php echo $moduleId; ?>&review=1" class="small"><i class="bi bi-eye me-1"></i>Review last attempt</a>
                </div>
            <?php endif; ?>

            <form method="POST" action="test.php?enrollment_id=<?php echo $enrollmentId; ?>&module_id=<?php echo $moduleId; ?>" class="test-form">
                <?php foreach ($questions as $qi => $q): ?>
                    <div class="question-block mb-4">
                        <p class="question-text fw-semibold mb-2"><?php echo ($qi + 1); ?>. <?php echo htmlspecialchars($q['question_text']); ?></p>
                        <div class="question-options ms-0 ms-md-3">
                            <?php foreach ($q['options'] as $o): ?>
                                <div class="form-check py-1">
                                    <input class="form-check-input" type="radio" name="q_<?php echo $q['id']; ?>" id="q<?php echo $q['id']; ?>_o<?php echo $o['id']; ?>" value="<?php echo $o['id']; ?>" required>
                                    <label class="form-check-label" for="q<?php echo $q['id']; ?>_o<?php echo $o['id']; ?>"><?php echo htmlspecialchars($o['option_text']); ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" name="submit_quiz" class="btn btn-elms-accent"><i class="bi bi-send me-2"></i>Submit Answers</button>
                    <a href="module.php?enrollment_id=<?php echo $enrollmentId; ?>&module_id=<?php echo $moduleId; ?>" class="btn btn-outline-secondary"><i class="bi bi-book me-1"></i>Review materials</a>
                </div>
            </form>
        </section>
    <?php endif; ?>

<?php else: ?>
    <div class="assessment-card">
        <p class="text-muted mb-2">No questions have been added to this module's test yet.</p>
        <a href="module.php?enrollment_id=<?php echo $enrollmentId; ?>&module_id=<?php echo $moduleId; ?>" class="btn btn-elms-accent">Back to module</a>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
