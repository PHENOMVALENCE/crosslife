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
 */
function getYouTubeId($url) {
    if (empty($url)) return null;
    
    preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches);
    return isset($matches[1]) ? $matches[1] : null;
}

/**
 * Format features list (newline-separated to array)
 */
function formatFeatures($features) {
    if (empty($features)) return [];
    return array_filter(array_map('trim', explode("\n", $features)));
}

