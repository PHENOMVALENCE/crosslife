-- Add PDF to discipleship resource types
-- Run once: Admin > migrate-discipleship-pdf.php or import in phpMyAdmin

ALTER TABLE discipleship_module_resources
MODIFY COLUMN resource_type ENUM('text', 'audio', 'video', 'pdf') NOT NULL;
