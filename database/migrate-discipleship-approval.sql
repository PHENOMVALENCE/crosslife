-- Student registration approval: new students register as 'pending', admins approve to 'active'
-- Only 'active' students can log in
ALTER TABLE discipleship_students
    MODIFY COLUMN status ENUM('pending', 'active', 'inactive') DEFAULT 'pending';

-- Google OAuth: store Google ID for sign-in
ALTER TABLE discipleship_students
    ADD COLUMN google_id VARCHAR(100) NULL UNIQUE AFTER password_hash;

-- Allow password_hash to be NULL for Google-only accounts
ALTER TABLE discipleship_students
    MODIFY COLUMN password_hash VARCHAR(255) NULL;
