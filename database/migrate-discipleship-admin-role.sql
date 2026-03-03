-- ---------------------------------------------------------------------------
-- Migration: Add discipleship_admin role
-- Adds 'discipleship_admin' to the admins role ENUM
-- ---------------------------------------------------------------------------

ALTER TABLE admins
    MODIFY COLUMN role ENUM('super_admin', 'admin', 'editor', 'discipleship_admin') DEFAULT 'admin';
