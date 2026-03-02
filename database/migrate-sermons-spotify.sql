-- Migration: Add spotify_url column to sermons table
-- Supports embedding Spotify episodes, tracks, shows, and playlists

ALTER TABLE sermons
    ADD COLUMN spotify_url VARCHAR(500) NULL AFTER audio_url;
