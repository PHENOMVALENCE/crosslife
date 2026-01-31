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
 * Get enrollments for a student
 */
function discipleship_get_student_enrollments($studentId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT e.*, p.program_name, p.description, p.duration FROM discipleship_enrollments e JOIN discipleship_programs p ON p.id = e.program_id WHERE e.student_id = ? AND e.status = 'active' ORDER BY e.enrolled_at DESC");
    $stmt->execute([$studentId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        }
        $db->commit();
        return $attemptId;
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
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
