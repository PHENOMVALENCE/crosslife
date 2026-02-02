-- Student password reset tokens (for Forgot password flow)
-- Run once: import in phpMyAdmin or run from admin

CREATE TABLE IF NOT EXISTS student_password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    INDEX idx_student (student_id),
    INDEX idx_expires (expires_at),
    CONSTRAINT fk_reset_student FOREIGN KEY (student_id) REFERENCES discipleship_students (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
