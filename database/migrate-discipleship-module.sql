-- Discipleship Module - School of Christ Academy
-- Run this migration to add tables for programs, modules, resources, assessments, and student tracking.
-- Prerequisite: discipleship_programs table must already exist (e.g. from schema or crosslife schema).
-- Run via: Admin > migrate-discipleship.php (once, while logged in), or import this file in phpMyAdmin.
-- Does not modify existing discipleship_programs table; only adds new tables.

-- ---------------------------------------------------------------------------
-- 1. Students (learners) - separate from admins; used for registration & tracking
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS discipleship_students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_students_email (email),
    INDEX idx_students_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 2. Learning modules (sequential under a program)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS discipleship_modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    display_order INT DEFAULT 0,
    pass_mark_pct TINYINT UNSIGNED DEFAULT 70 COMMENT '0-100; required to unlock next module',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_modules_program (program_id),
    INDEX idx_modules_order (program_id, display_order),
    CONSTRAINT fk_modules_program FOREIGN KEY (program_id) REFERENCES discipleship_programs (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 3. Module resources (text notes, audio, video)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS discipleship_module_resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module_id INT NOT NULL,
    resource_type ENUM('text', 'audio', 'video') NOT NULL,
    title VARCHAR(255) DEFAULT NULL,
    content TEXT NULL COMMENT 'Formatted text/notes for type=text',
    file_path VARCHAR(500) NULL COMMENT 'Relative path for audio/video uploads',
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_resources_module (module_id),
    CONSTRAINT fk_resources_module FOREIGN KEY (module_id) REFERENCES discipleship_modules (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 4. Multiple-choice questions (per module)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS discipleship_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module_id INT NOT NULL,
    question_text TEXT NOT NULL,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_questions_module (module_id),
    CONSTRAINT fk_questions_module FOREIGN KEY (module_id) REFERENCES discipleship_modules (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 5. Question options (one correct; feedback per option for reinforcement)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS discipleship_question_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    option_text VARCHAR(500) NOT NULL,
    is_correct TINYINT(1) NOT NULL DEFAULT 0,
    feedback_text TEXT NULL COMMENT 'Short explanation shown after answer',
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_options_question (question_id),
    CONSTRAINT fk_options_question FOREIGN KEY (question_id) REFERENCES discipleship_questions (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 6. Student enrollments in programs
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS discipleship_enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    program_id INT NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'completed', 'withdrawn') DEFAULT 'active',
    completed_at DATETIME NULL,
    INDEX idx_enrollments_student (student_id),
    INDEX idx_enrollments_program (program_id),
    UNIQUE KEY uk_enrollment_student_program (student_id, program_id),
    CONSTRAINT fk_enrollments_student FOREIGN KEY (student_id) REFERENCES discipleship_students (id) ON DELETE CASCADE,
    CONSTRAINT fk_enrollments_program FOREIGN KEY (program_id) REFERENCES discipleship_programs (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 7. Module progress (which modules the student has passed; unlocks next)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS discipleship_module_progress (
    enrollment_id INT NOT NULL,
    module_id INT NOT NULL,
    passed_at DATETIME NULL COMMENT 'Set when quiz passed; NULL if not yet passed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (enrollment_id, module_id),
    CONSTRAINT fk_progress_enrollment FOREIGN KEY (enrollment_id) REFERENCES discipleship_enrollments (id) ON DELETE CASCADE,
    CONSTRAINT fk_progress_module FOREIGN KEY (module_id) REFERENCES discipleship_modules (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 8. Quiz attempts (one row per attempt at a module quiz)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS discipleship_module_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT NOT NULL,
    module_id INT NOT NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    score_pct DECIMAL(5,2) NOT NULL COMMENT '0-100',
    passed TINYINT(1) NOT NULL DEFAULT 0,
    INDEX idx_attempts_enrollment (enrollment_id),
    INDEX idx_attempts_module (module_id),
    CONSTRAINT fk_attempts_enrollment FOREIGN KEY (enrollment_id) REFERENCES discipleship_enrollments (id) ON DELETE CASCADE,
    CONSTRAINT fk_attempts_module FOREIGN KEY (module_id) REFERENCES discipleship_modules (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 9. Answers given in an attempt (for review/feedback)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS discipleship_attempt_answers (
    attempt_id INT NOT NULL,
    question_id INT NOT NULL,
    option_id INT NOT NULL,
    is_correct TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (attempt_id, question_id),
    CONSTRAINT fk_answers_attempt FOREIGN KEY (attempt_id) REFERENCES discipleship_module_attempts (id) ON DELETE CASCADE,
    CONSTRAINT fk_answers_question FOREIGN KEY (question_id) REFERENCES discipleship_questions (id) ON DELETE CASCADE,
    CONSTRAINT fk_answers_option FOREIGN KEY (option_id) REFERENCES discipleship_question_options (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
