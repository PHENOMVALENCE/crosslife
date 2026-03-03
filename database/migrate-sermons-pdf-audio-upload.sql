-- Add PDF type and pdf_url to sermons
-- Supports: video (YouTube), audio (Spotify or uploaded), pdf (document)
ALTER TABLE sermons MODIFY COLUMN sermon_type ENUM('video', 'audio', 'pdf') DEFAULT 'video';
ALTER TABLE sermons ADD COLUMN pdf_url VARCHAR(500) NULL AFTER spotify_url;
