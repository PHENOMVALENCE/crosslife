-- Add departments column to leadership (for existing installations)
-- Run this once if your leadership table was created before departments was added.
-- If you get "Duplicate column name 'departments'", the column already exists.

ALTER TABLE leadership ADD COLUMN departments VARCHAR(500) NULL AFTER role;
