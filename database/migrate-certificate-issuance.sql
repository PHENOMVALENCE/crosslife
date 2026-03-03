-- ---------------------------------------------------------------------------
-- Migration: Certificate issuance by admin
-- Adds certificate approval fields to discipleship_enrollments
-- ---------------------------------------------------------------------------

ALTER TABLE discipleship_enrollments
    ADD COLUMN certificate_issued TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Whether certificate has been issued by admin' AFTER completed_at,
    ADD COLUMN certificate_issued_at DATETIME NULL COMMENT 'When admin issued the certificate' AFTER certificate_issued,
    ADD COLUMN certificate_issued_by INT NULL COMMENT 'Admin user who issued the certificate' AFTER certificate_issued_at,
    ADD COLUMN certificate_number VARCHAR(50) NULL COMMENT 'Unique certificate reference number' AFTER certificate_issued_by,
    ADD COLUMN certificate_remarks TEXT NULL COMMENT 'Admin remarks or notes on the certificate' AFTER certificate_number;

-- Add index for certificate number uniqueness
ALTER TABLE discipleship_enrollments
    ADD UNIQUE KEY uk_certificate_number (certificate_number);

-- Add foreign key to admins table
ALTER TABLE discipleship_enrollments
    ADD CONSTRAINT fk_cert_issued_by FOREIGN KEY (certificate_issued_by) REFERENCES admins(id) ON DELETE SET NULL;
