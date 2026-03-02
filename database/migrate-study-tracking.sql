-- Migration: Add studied_at column to discipleship_module_progress
-- This tracks when a student finishes studying the learning materials,
-- gating access to the test/assessment page.

ALTER TABLE discipleship_module_progress
    ADD COLUMN studied_at DATETIME NULL AFTER module_id;

-- For existing rows where the student has already passed, backfill studied_at
UPDATE discipleship_module_progress
    SET studied_at = COALESCE(passed_at, created_at)
    WHERE studied_at IS NULL AND passed_at IS NOT NULL;
