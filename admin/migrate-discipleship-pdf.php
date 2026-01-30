<?php
/**
 * One-time migration: Add 'pdf' to discipleship_module_resources.resource_type
 */
require_once __DIR__ . '/config/config.php';
requireLogin();

$db = getDB();
try {
    $db->exec("ALTER TABLE discipleship_module_resources MODIFY COLUMN resource_type ENUM('text', 'audio', 'video', 'pdf') NOT NULL");
    $msg = 'PDF resource type added successfully.';
    $type = 'success';
} catch (PDOException $e) {
    $msg = 'Migration failed: ' . $e->getMessage();
    $type = 'danger';
}

require_once __DIR__ . '/includes/header.php';
echo '<div class="alert alert-' . $type . '">' . htmlspecialchars($msg) . '</div>';
echo '<p><a href="discipleship.php" class="btn btn-primary">Discipleship</a></p>';
require_once __DIR__ . '/includes/footer.php';
