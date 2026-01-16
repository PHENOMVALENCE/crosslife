<?php
/**
 * Migration Script: Import Ministries from ministries.php
 * Run this once to populate the database with default ministries
 */

require_once 'config/config.php';
requireLogin();

$db = getDB();

// Default ministries from ministries.php
$defaultMinistries = [
    [
        'name' => 'Teaching Ministry',
        'description' => 'Dedicated to preaching the Gospel of the Cross, the Message of Sonship, the Gospel of the Kingdom of God, and the Gospel of Immortality through systematic teaching and exposition of God\'s Word.',
        'image_url' => SITE_URL . '/assets/img/_MG_4880.jpg',
        'leader_name' => '',
        'contact_email' => '',
        'status' => 'active',
        'display_order' => 1
    ],
    [
        'name' => 'Discipleship Ministry',
        'description' => 'Through the School of Christ Academy, we provide structured discipleship programs including Foundation Classes, Leadership Training, and Ministry Development to equip believers for the work of ministry.',
        'image_url' => SITE_URL . '/assets/img/_MG_4902.jpg',
        'leader_name' => '',
        'contact_email' => '',
        'status' => 'active',
        'display_order' => 2
    ],
    [
        'name' => 'Prayer Ministry',
        'description' => 'A community of Life, Love, Sonship, and Prayer, committed to intercession for the church, the nation, and the global body of Christ.',
        'image_url' => SITE_URL . '/assets/img/_MG_5021.jpg',
        'leader_name' => '',
        'contact_email' => '',
        'status' => 'active',
        'display_order' => 3
    ],
    [
        'name' => 'Outreach Ministry',
        'description' => 'Reaching the global community by showing the Way, revealing the Truth, and sharing Life through Christ, establishing a global network of manifested Sons of God.',
        'image_url' => SITE_URL . '/assets/img/_MG_5281.jpg',
        'leader_name' => '',
        'contact_email' => '',
        'status' => 'active',
        'display_order' => 4
    ],
    [
        'name' => 'Worship Ministry',
        'description' => 'Leading the church in worship, recognizing that worship is central to the life of CrossLife as we live in Zion, the realm of Christ.',
        'image_url' => SITE_URL . '/assets/img/_MG_5282.jpg',
        'leader_name' => '',
        'contact_email' => '',
        'status' => 'active',
        'display_order' => 5
    ],
    [
        'name' => 'Fellowship Ministry',
        'description' => 'Creating an environment where believers experience the Life of God and grow in their identity in Christ, welcoming people from diverse backgrounds, ages, and walks of life.',
        'image_url' => SITE_URL . '/assets/img/_MG_4859.jpg',
        'leader_name' => '',
        'contact_email' => '',
        'status' => 'active',
        'display_order' => 6
    ]
];

// Check existing ministries by name
$stmt = $db->query("SELECT name FROM ministries");
$existingNames = [];
while ($row = $stmt->fetch()) {
    $existingNames[] = strtolower(trim($row['name']));
}

// Insert default ministries (only if they don't exist)
$stmt = $db->prepare("INSERT INTO ministries (name, description, image_url, leader_name, contact_email, status, display_order) VALUES (?, ?, ?, ?, ?, ?, ?)");

$inserted = 0;
$skipped = 0;
foreach ($defaultMinistries as $ministry) {
    // Check if ministry with this name already exists
    if (in_array(strtolower(trim($ministry['name'])), $existingNames)) {
        $skipped++;
        continue;
    }
    
    try {
        $stmt->execute([
            $ministry['name'],
            $ministry['description'],
            $ministry['image_url'],
            $ministry['leader_name'],
            $ministry['contact_email'],
            $ministry['status'],
            $ministry['display_order']
        ]);
        $inserted++;
    } catch (PDOException $e) {
        // Log error but continue
        error_log("Error inserting {$ministry['name']}: " . $e->getMessage());
    }
}

echo "<!DOCTYPE html><html><head><title>Migration Complete</title><style>body{font-family:Arial,sans-serif;padding:20px;max-width:800px;margin:0 auto;}h2{color:#333;}.success{color:#28a745;}.info{color:#17a2b8;}</style></head><body>";
echo "<h2>Migration Complete</h2>";
if ($inserted > 0) {
    echo "<p class='success'>✓ Successfully inserted $inserted ministries into the database.</p>";
}
if ($skipped > 0) {
    echo "<p class='info'>ℹ Skipped $skipped ministries (already exist in database).</p>";
}
if ($inserted == 0 && $skipped == 0) {
    echo "<p>No ministries were inserted. Please check for errors.</p>";
}
echo "<p><a href='ministries.php' style='display:inline-block;margin-top:20px;padding:10px 20px;background:#007bff;color:white;text-decoration:none;border-radius:4px;'>Go to Ministries Management</a></p>";
echo "</body></html>";
?>

