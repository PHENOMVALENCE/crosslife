<?php
/**
 * Discipleship Module - School of Christ Academy
 * DB helpers for programs, modules, resources, questions, enrollments, progress, attempts.
 * Requires: getDB() from admin/config/database.php (via config.php).
 */

if (!function_exists('getDB')) {
    return;
}

/**
 * Get active programs (for public listing and student enrollment)
 */
function discipleship_get_active_programs() {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM discipleship_programs WHERE status IN ('active', 'upcoming') ORDER BY display_order ASC, program_name ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get program by ID
 */
function discipleship_get_program($programId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM discipleship_programs WHERE id = ?");
    $stmt->execute([$programId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get modules for a program (ordered)
 */
function discipleship_get_modules($programId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM discipleship_modules WHERE program_id = ? ORDER BY display_order ASC, id ASC");
    $stmt->execute([$programId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get module by ID
 */
function discipleship_get_module($moduleId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM discipleship_modules WHERE id = ?");
    $stmt->execute([$moduleId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get resources for a module (ordered)
 */
function discipleship_get_resources($moduleId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM discipleship_module_resources WHERE module_id = ? ORDER BY display_order ASC, id ASC");
    $stmt->execute([$moduleId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get questions for a module (ordered) with options
 */
function discipleship_get_questions_with_options($moduleId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM discipleship_questions WHERE module_id = ? ORDER BY display_order ASC, id ASC");
    $stmt->execute([$moduleId]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($questions as &$q) {
        $opt = $db->prepare("SELECT * FROM discipleship_question_options WHERE question_id = ? ORDER BY display_order ASC, id ASC");
        $opt->execute([$q['id']]);
        $q['options'] = $opt->fetchAll(PDO::FETCH_ASSOC);
    }
    return $questions;
}

/**
 * Get enrollment by ID (with program and student)
 */
function discipleship_get_enrollment($enrollmentId, $studentId = null) {
    $db = getDB();
    $sql = "SELECT e.*, p.program_name, p.description as program_description FROM discipleship_enrollments e JOIN discipleship_programs p ON p.id = e.program_id WHERE e.id = ?";
    $params = [$enrollmentId];
    if ($studentId !== null) {
        $sql .= " AND e.student_id = ?";
        $params[] = $studentId;
    }
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get enrollment for student + program (or null)
 */
function discipleship_get_enrollment_for_student_program($studentId, $programId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM discipleship_enrollments WHERE student_id = ? AND program_id = ? AND status = 'active'");
    $stmt->execute([$studentId, $programId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get enrollments for a student (with optional progress: modules_total, modules_passed)
 */
function discipleship_get_student_enrollments($studentId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT e.*, p.program_name, p.description, p.duration, p.image_url FROM discipleship_enrollments e JOIN discipleship_programs p ON p.id = e.program_id WHERE e.student_id = ? AND e.status IN ('active', 'completed') ORDER BY e.enrolled_at DESC");
    $stmt->execute([$studentId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$row) {
        $stmt2 = $db->prepare("SELECT COUNT(*) FROM discipleship_modules WHERE program_id = ?");
        $stmt2->execute([$row['program_id']]);
        $row['modules_total'] = (int) $stmt2->fetchColumn();
        $stmt2 = $db->prepare("SELECT COUNT(*) FROM discipleship_module_progress WHERE enrollment_id = ? AND passed_at IS NOT NULL");
        $stmt2->execute([$row['id']]);
        $row['modules_passed'] = (int) $stmt2->fetchColumn();
    }
    return $rows;
}

/**
 * Check if student has passed a module (unlock next)
 */
function discipleship_has_passed_module($enrollmentId, $moduleId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT 1 FROM discipleship_module_progress WHERE enrollment_id = ? AND module_id = ? AND passed_at IS NOT NULL");
    $stmt->execute([$enrollmentId, $moduleId]);
    return (bool) $stmt->fetch();
}

/**
 * Get passed module IDs for an enrollment (for unlock logic)
 */
function discipleship_get_passed_module_ids($enrollmentId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT module_id FROM discipleship_module_progress WHERE enrollment_id = ? AND passed_at IS NOT NULL ORDER BY module_id ASC");
    $stmt->execute([$enrollmentId]);
    return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'module_id');
}

/**
 * Is module unlocked for this enrollment? (Module 1 always; module N if N-1 passed)
 */
function discipleship_is_module_unlocked($enrollmentId, $moduleId, $orderedModuleIds) {
    $passed = discipleship_get_passed_module_ids($enrollmentId);
    $index = array_search((int) $moduleId, array_map('intval', $orderedModuleIds), true);
    if ($index === false) {
        return false;
    }
    if ($index === 0) {
        return true; // first module always unlocked
    }
    $prevModuleId = (int) $orderedModuleIds[$index - 1];
    return in_array($prevModuleId, $passed, true);
}

/**
 * Record quiz attempt and optionally set progress.passed_at if passed
 */
function discipleship_record_attempt($enrollmentId, $moduleId, $scorePct, $passed, array $answers) {
    $db = getDB();
    $db->beginTransaction();
    try {
        $stmt = $db->prepare("INSERT INTO discipleship_module_attempts (enrollment_id, module_id, score_pct, passed) VALUES (?, ?, ?, ?)");
        $stmt->execute([$enrollmentId, $moduleId, $scorePct, $passed ? 1 : 0]);
        $attemptId = (int) $db->lastInsertId();
        $ins = $db->prepare("INSERT INTO discipleship_attempt_answers (attempt_id, question_id, option_id, is_correct) VALUES (?, ?, ?, ?)");
        foreach ($answers as $a) {
            $ins->execute([$attemptId, $a['question_id'], $a['option_id'], $a['is_correct'] ? 1 : 0]);
        }
        if ($passed) {
            $db->prepare("INSERT INTO discipleship_module_progress (enrollment_id, module_id, passed_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE passed_at = NOW(), updated_at = NOW()")->execute([$enrollmentId, $moduleId]);
            discipleship_check_and_set_program_completed($db, $enrollmentId);
        }
        $db->commit();
        return $attemptId;
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

/**
 * When a module is passed, check if all modules in the program are passed; if so, mark enrollment completed.
 */
function discipleship_check_and_set_program_completed($db, $enrollmentId) {
    $stmt = $db->prepare("SELECT program_id FROM discipleship_enrollments WHERE id = ? AND status = 'active'");
    $stmt->execute([$enrollmentId]);
    $row = $stmt->fetch();
    if (!$row) return;
    $programId = (int) $row['program_id'];
    $stmt = $db->prepare("SELECT COUNT(*) FROM discipleship_modules WHERE program_id = ?");
    $stmt->execute([$programId]);
    $total = (int) $stmt->fetchColumn();
    $stmt = $db->prepare("SELECT COUNT(*) FROM discipleship_module_progress WHERE enrollment_id = ? AND passed_at IS NOT NULL");
    $stmt->execute([$enrollmentId]);
    $passed = (int) $stmt->fetchColumn();
    if ($total > 0 && $passed >= $total) {
        $db->prepare("UPDATE discipleship_enrollments SET status = 'completed', completed_at = NOW() WHERE id = ?")->execute([$enrollmentId]);
    }
}

/**
 * Get last quiz attempt for an enrollment+module (for review)
 */
function discipleship_get_last_attempt($enrollmentId, $moduleId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM discipleship_module_attempts WHERE enrollment_id = ? AND module_id = ? ORDER BY attempted_at DESC LIMIT 1");
    $stmt->execute([$enrollmentId, $moduleId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get answers for an attempt (question_id, option_id, is_correct); join to question/option text for display.
 */
function discipleship_get_attempt_answers_with_text($attemptId) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT a.question_id, a.option_id, a.is_correct,
               q.question_text, o.option_text, o.feedback_text
        FROM discipleship_attempt_answers a
        JOIN discipleship_questions q ON q.id = a.question_id
        JOIN discipleship_question_options o ON o.id = a.option_id
        WHERE a.attempt_id = ?
        ORDER BY q.display_order ASC, q.id ASC
    ");
    $stmt->execute([$attemptId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get correct option_id per question for a module (for showing correct answer in review)
 */
function discipleship_get_correct_options_by_question($moduleId) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT q.id AS question_id, o.id AS option_id, o.option_text
        FROM discipleship_questions q
        JOIN discipleship_question_options o ON o.question_id = q.id AND o.is_correct = 1
        WHERE q.module_id = ?
        ORDER BY q.display_order ASC, q.id ASC
    ");
    $stmt->execute([$moduleId]);
    $out = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $out[$row['question_id']] = $row;
    }
    return $out;
}

/**
 * Count attempts for enrollment+module (for attempt limit)
 */
function discipleship_count_attempts($enrollmentId, $moduleId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) FROM discipleship_module_attempts WHERE enrollment_id = ? AND module_id = ?");
    $stmt->execute([$enrollmentId, $moduleId]);
    return (int) $stmt->fetchColumn();
}

/**
 * Resolve resource file URL for display (audio/video)
 */
function discipleship_resource_url($filePath) {
    if (empty($filePath)) {
        return '';
    }
    if (strpos($filePath, 'http') === 0) {
        return $filePath;
    }
    $base = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
    return $base . '/' . ltrim($filePath, '/');
}
