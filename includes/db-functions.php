<?php
/**
 * Database Functions for Frontend
 * Single source of truth: frontend pages should display only data returned by these
 * functions so the Cross Admin controls what appears on the public site.
 */

require_once __DIR__ . '/../admin/config/database.php';

/**
 * Get published sermons
 */
function getPublishedSermons($limit = null, $type = null) {
    $db = getDB();
    $where = "WHERE status = 'published'";
    if ($type) {
        $where .= " AND sermon_type = " . $db->quote($type);
    }
    $order = "ORDER BY sermon_date DESC, created_at DESC";
    $limitClause = $limit ? "LIMIT " . intval($limit) : "";
    
    $stmt = $db->query("SELECT * FROM sermons $where $order $limitClause");
    return $stmt->fetchAll();
}

/**
 * Get upcoming events
 */
function getUpcomingEvents($limit = null) {
    $db = getDB();
    $where = "WHERE event_date >= CURDATE() AND status IN ('upcoming', 'ongoing')";
    $order = "ORDER BY event_date ASC, event_time ASC";
    $limitClause = $limit ? "LIMIT " . intval($limit) : "";
    
    $stmt = $db->query("SELECT * FROM events $where $order $limitClause");
    return $stmt->fetchAll();
}

/**
 * Get all events
 */
function getAllEvents($limit = null) {
    $db = getDB();
    $order = "ORDER BY event_date DESC";
    $limitClause = $limit ? "LIMIT " . intval($limit) : "";
    
    $stmt = $db->query("SELECT * FROM events $order $limitClause");
    return $stmt->fetchAll();
}

/**
 * Get events for public frontend (excludes cancelled).
 * Order: upcoming/ongoing first by date, then past events.
 */
function getPublicEvents($limit = null) {
    $db = getDB();
    $where = "WHERE status != 'cancelled'";
    $order = "ORDER BY (event_date >= CURDATE() OR status = 'ongoing') DESC, event_date ASC, event_time ASC";
    $limitClause = $limit ? "LIMIT " . intval($limit) : "";
    
    $stmt = $db->query("SELECT * FROM events $where $order $limitClause");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get active ministries (used by public ministries page; data managed in Admin → Ministries)
 */
function getActiveMinistries() {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM ministries WHERE status = 'active' ORDER BY display_order ASC, name ASC");
    return $stmt->fetchAll();
}

/**
 * Get active discipleship programs
 */
function getActiveDiscipleshipPrograms() {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM discipleship_programs WHERE status IN ('active', 'upcoming') ORDER BY display_order ASC, program_name ASC");
    return $stmt->fetchAll();
}

/**
 * Get active leadership for frontend display.
 * Returns only columns needed for the public leadership page; excludes internal fields.
 * Order: display_order, then name for stable sorting.
 */
function getActiveLeadership() {
    $db = getDB();
    $stmt = $db->query(
        "SELECT id, name, role, " .
        "COALESCE(departments, '') AS departments, " .
        "bio, image_url, email, phone " .
        "FROM leadership " .
        "WHERE status = 'active' " .
        "ORDER BY display_order ASC, name ASC"
    );
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get site settings
 */
function getSiteSettings() {
    $db = getDB();
    $stmt = $db->query("SELECT setting_key, setting_value FROM site_settings");
    $settings = [];
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}

/**
 * Format event date for display
 */
function formatEventDate($event) {
    $date = date('M j', strtotime($event['event_date']));
    if ($event['event_time']) {
        $time = date('g:i A', strtotime($event['event_time']));
        return $date . ' at ' . $time;
    }
    return $date;
}

/**
 * Get event date badge
 */
function getEventDateBadge($event) {
    $date = new DateTime($event['event_date']);
    $now = new DateTime();
    
    if ($date->format('Y-m-d') === $now->format('Y-m-d')) {
        return ['day' => 'Today', 'month' => date('M')];
    }
    
    $day = $date->format('j');
    $month = $date->format('M');
    
    return ['day' => $day, 'month' => $month];
}

/**
 * Extract YouTube video ID from URL
 * Supports: watch, embed, youtu.be, shorts, live, v/ paths
 */
function getYouTubeId($url) {
    if (empty($url)) return null;

    $url = trim($url);

    // youtu.be/VIDEO_ID
    if (preg_match('/youtu\.be\/([^"&?\/\s]{11})/', $url, $m)) return $m[1];

    // youtube.com/shorts/VIDEO_ID
    if (preg_match('/youtube\.com\/shorts\/([^"&?\/\s]{11})/', $url, $m)) return $m[1];

    // youtube.com/live/VIDEO_ID
    if (preg_match('/youtube\.com\/live\/([^"&?\/\s]{11})/', $url, $m)) return $m[1];

    // youtube.com/embed/VIDEO_ID or /v/VIDEO_ID
    if (preg_match('/youtube(?:-nocookie)?\.com\/(?:embed|v)\/([^"&?\/\s]{11})/', $url, $m)) return $m[1];

    // youtube.com/watch?v=VIDEO_ID (anywhere in query string)
    if (preg_match('/youtube\.com\/watch.*[?&]v=([^"&?\/\s]{11})/', $url, $m)) return $m[1];

    // General catch-all for other youtube.com patterns
    if (preg_match('/youtube\.com\/(?:[^\/]+\/.+\/|(?:e(?:mbed)?)\/|.*[?&]v=)([^"&?\/\s]{11})/', $url, $m)) return $m[1];

    return null;
}

/**
 * Get Spotify embed URL from a Spotify link.
 * Input:  https://open.spotify.com/episode/ABC123 or /track/... /show/... /playlist/...
 * Output: https://open.spotify.com/embed/episode/ABC123  (ready for iframe)
 * Returns null if not a valid Spotify URL.
 */
function getSpotifyEmbedUrl($url) {
    if (empty($url)) return null;

    $url = trim($url);

    // Already an embed URL
    if (preg_match('#^https?://open\.spotify\.com/embed/(episode|track|show|playlist|album)/([a-zA-Z0-9]+)#', $url, $m)) {
        return 'https://open.spotify.com/embed/' . $m[1] . '/' . $m[2];
    }

    // Standard Spotify URL (with optional query params / si tracking)
    if (preg_match('#^https?://open\.spotify\.com/(episode|track|show|playlist|album)/([a-zA-Z0-9]+)#', $url, $m)) {
        return 'https://open.spotify.com/embed/' . $m[1] . '/' . $m[2];
    }

    return null;
}

/**
 * Detect the embed type for a Spotify URL (episode, track, show, playlist, album)
 */
function getSpotifyType($url) {
    if (empty($url)) return null;
    if (preg_match('#open\.spotify\.com/(?:embed/)?(episode|track|show|playlist|album)/#', $url, $m)) {
        return $m[1];
    }
    return null;
}

/**
 * Format features list (newline-separated to array)
 */
function formatFeatures($features) {
    if (empty($features)) return [];
    return array_filter(array_map('trim', explode("\n", $features)));
}

