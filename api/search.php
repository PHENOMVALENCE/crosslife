<?php
/**
 * Global Search API
 * Searches across database content: sermons, events, ministries, discipleship programs, leadership
 */
header('Content-Type: application/json');
require_once '../includes/db-functions.php';

$query = trim($_GET['q'] ?? '');

if (strlen($query) < 2) {
    echo json_encode(['success' => false, 'results' => []]);
    exit;
}

$db = getDB();
$searchTerm = '%' . $query . '%';
$results = [];

try {
    // Search Sermons
    $stmt = $db->prepare("
        SELECT id, title, speaker, sermon_date, 'sermon' as type
        FROM sermons 
        WHERE status = 'published' 
        AND (title LIKE ? OR speaker LIKE ? OR description LIKE ? OR category LIKE ?)
        ORDER BY sermon_date DESC
        LIMIT 5
    ");
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $sermons = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($sermons as $sermon) {
        $results[] = [
            'title' => $sermon['title'],
            'description' => 'Sermon by ' . ($sermon['speaker'] ?: 'Unknown') . ' - ' . date('M d, Y', strtotime($sermon['sermon_date'])),
            'date' => $sermon['sermon_date'],
            'url' => 'sermons.php#sermon-' . $sermon['id'],
            'type' => 'Sermon',
            'icon' => 'play-circle'
        ];
    }

    // Search Events
    $stmt = $db->prepare("
        SELECT id, title, event_date, location, 'event' as type
        FROM events 
        WHERE status IN ('upcoming', 'ongoing')
        AND (title LIKE ? OR description LIKE ? OR location LIKE ? OR event_type LIKE ?)
        ORDER BY event_date ASC
        LIMIT 5
    ");
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($events as $event) {
        $results[] = [
            'title' => $event['title'],
            'description' => ($event['location'] ? 'At ' . $event['location'] . ' - ' : '') . date('M d, Y', strtotime($event['event_date'])),
            'date' => $event['event_date'],
            'url' => 'events.php#event-' . $event['id'],
            'type' => 'Event',
            'icon' => 'calendar-event'
        ];
    }

    // Search Ministries
    $stmt = $db->prepare("
        SELECT id, name as title, description, leader_name, 'ministry' as type
        FROM ministries 
        WHERE status = 'active'
        AND (name LIKE ? OR description LIKE ? OR leader_name LIKE ?)
        ORDER BY display_order ASC
        LIMIT 5
    ");
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    $ministries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($ministries as $ministry) {
        $description = strip_tags($ministry['description']);
        $description = strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description;
        $results[] = [
            'title' => $ministry['title'],
            'description' => $description,
            'date' => null,
            'url' => 'ministries.html#ministry-' . $ministry['id'],
            'type' => 'Ministry',
            'icon' => 'people'
        ];
    }

    // Search Discipleship Programs
    $stmt = $db->prepare("
        SELECT id, program_name as title, description, duration, 'program' as type
        FROM discipleship_programs 
        WHERE status = 'active'
        AND (program_name LIKE ? OR description LIKE ? OR features LIKE ?)
        ORDER BY display_order ASC
        LIMIT 5
    ");
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($programs as $program) {
        $description = strip_tags($program['description']);
        $description = strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description;
        $results[] = [
            'title' => $program['title'],
            'description' => ($program['duration'] ? 'Duration: ' . $program['duration'] . ' - ' : '') . $description,
            'date' => null,
            'url' => 'discipleship.html#program-' . $program['id'],
            'type' => 'Discipleship Program',
            'icon' => 'book'
        ];
    }

    // Search Leadership
    $stmt = $db->prepare("
        SELECT id, name as title, role, bio, 'leader' as type
        FROM leadership 
        WHERE status = 'active'
        AND (name LIKE ? OR role LIKE ? OR bio LIKE ?)
        ORDER BY display_order ASC
        LIMIT 5
    ");
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    $leaders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($leaders as $leader) {
        $results[] = [
            'title' => $leader['title'],
            'description' => $leader['role'],
            'date' => null,
            'url' => 'index.php#leadership',
            'type' => 'Leadership',
            'icon' => 'person',
            'scrollTo' => true
        ];
    }

    // Static page sections (keeping original functionality)
    $staticSections = [
        ['title' => 'Home', 'description' => 'Welcome to CrossLife Mission Network', 'url' => 'index.php', 'type' => 'Page', 'icon' => 'house', 'keywords' => 'home welcome introduction vision mission'],
        ['title' => 'About Us', 'description' => 'Learn about our mandate and mission', 'url' => 'index.php#about', 'type' => 'Page', 'icon' => 'info-circle', 'keywords' => 'about mandate vision mission history'],
        ['title' => 'Statement of Faith', 'description' => 'Our foundation of faith', 'url' => 'index.php#statement-of-faith', 'type' => 'Page', 'icon' => 'book', 'keywords' => 'faith beliefs doctrine scripture godhead jesus holy spirit'],
        ['title' => 'Core Beliefs', 'description' => 'What we believe', 'url' => 'index.php#features', 'type' => 'Page', 'icon' => 'heart', 'keywords' => 'beliefs core godhead jesus christ holy spirit identity'],
        ['title' => 'Contact', 'description' => 'Get in touch with us', 'url' => 'contacts.html', 'type' => 'Page', 'icon' => 'envelope', 'keywords' => 'contact inquiry prayer request feedback'],
        ['title' => 'Giving', 'description' => 'Support the ministry', 'url' => 'index.php#giving', 'type' => 'Page', 'icon' => 'heart', 'keywords' => 'giving offering support ministry donation']
    ];

    foreach ($staticSections as $section) {
        $matchesTitle = stripos($section['title'], $query) !== false;
        $matchesDescription = stripos($section['description'], $query) !== false;
        $matchesKeywords = stripos($section['keywords'], $query) !== false;
        
        if ($matchesTitle || $matchesDescription || $matchesKeywords) {
            $results[] = [
                'title' => $section['title'],
                'description' => $section['description'],
                'date' => null,
                'url' => $section['url'],
                'type' => $section['type'],
                'icon' => $section['icon']
            ];
        }
    }

    // Sort results by relevance (title matches first)
    usort($results, function($a, $b) use ($query) {
        $aTitle = stripos($a['title'], $query) !== false ? 1 : 0;
        $bTitle = stripos($b['title'], $query) !== false ? 1 : 0;
        return $bTitle - $aTitle;
    });

    // Limit to top 15 results
    $results = array_slice($results, 0, 15);

    echo json_encode(['success' => true, 'results' => $results]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error', 'results' => []]);
}
