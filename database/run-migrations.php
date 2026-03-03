<?php
/**
 * Run migrations - execute from project root: php database/run-migrations.php
 */
require_once __DIR__ . '/../admin/config/config.php';
require_once __DIR__ . '/../admin/config/database.php';

$db = getDB();
$errors = [];
$done = [];

// Sermons: PDF support
try {
    $db->exec("ALTER TABLE sermons MODIFY COLUMN sermon_type ENUM('video', 'audio', 'pdf') DEFAULT 'video'");
    $done[] = "sermons: sermon_type updated";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate') === false) $errors[] = "sermons type: " . $e->getMessage();
}
try {
    $db->exec("ALTER TABLE sermons ADD COLUMN pdf_url VARCHAR(500) NULL AFTER spotify_url");
    $done[] = "sermons: pdf_url added";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') === false) $errors[] = "pdf_url: " . $e->getMessage();
}

// Discipleship: pending status, google_id
try {
    $db->exec("ALTER TABLE discipleship_students MODIFY COLUMN status ENUM('pending', 'active', 'inactive') DEFAULT 'pending'");
    $done[] = "discipleship_students: status enum updated";
} catch (PDOException $e) {
    $errors[] = "discipleship status: " . $e->getMessage();
}
try {
    $db->exec("ALTER TABLE discipleship_students ADD COLUMN google_id VARCHAR(100) NULL UNIQUE AFTER password_hash");
    $done[] = "discipleship_students: google_id added";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') === false) $errors[] = "google_id: " . $e->getMessage();
}
try {
    $db->exec("ALTER TABLE discipleship_students MODIFY COLUMN password_hash VARCHAR(255) NULL");
    $done[] = "discipleship_students: password_hash nullable";
} catch (PDOException $e) {
    $errors[] = "password_hash: " . $e->getMessage();
}

echo "Migrations run.\n";
foreach ($done as $d) echo "  [OK] $d\n";
foreach ($errors as $e) echo "  [ERR] $e\n";
