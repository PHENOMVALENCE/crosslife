<?php
/**
 * One-time migration: Create discipleship module tables (students, modules, resources, questions, enrollments, progress, attempts).
 * Requires: discipleship_programs table must already exist.
 * Run once while logged in as admin, then delete or restrict access.
 */
require_once __DIR__ . '/config/config.php';
requireLogin();

$pageTitle = 'Discipleship migration';
$db = getDB();

// Ensure discipleship_programs exists (required by FK in migration)
$programsExist = false;
try {
    $stmt = $db->query("SHOW TABLES LIKE 'discipleship_programs'");
    $programsExist = $stmt->rowCount() > 0;
} catch (PDOException $e) {
    // ignore
}

if (!$programsExist) {
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="alert alert-danger">Table <code>discipleship_programs</code> not found. Create it first (e.g. from database/schema.sql), then run this migration again.</div>';
    echo '<p><a href="discipleship.php" class="btn btn-secondary">Back to Discipleship</a></p>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$sqlFile = dirname(__DIR__) . '/database/migrate-discipleship-module.sql';
if (!is_readable($sqlFile)) {
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="alert alert-danger">Migration file not found: database/migrate-discipleship-module.sql</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$sql = file_get_contents($sqlFile);
// Remove single-line comments (-- ...)
$sql = preg_replace('/--[^\n]*\n/', "\n", $sql);
// Remove multi-line comments
$sql = preg_replace('/\/\*[\s\S]*?\*\//', '', $sql);
$statements = array_filter(array_map('trim', explode(';', $sql)));
$executed = 0;
$errors = [];

foreach ($statements as $stmt) {
    if ($stmt === '') continue;
    if (stripos($stmt, 'CREATE TABLE') === false && stripos($stmt, 'CREATE DATABASE') === false && stripos($stmt, 'USE ') === false) {
        continue; // run only CREATE TABLE / USE / CREATE DATABASE
    }
    try {
        $db->exec($stmt . ';');
        $executed++;
    } catch (PDOException $e) {
        $errors[] = $e->getMessage();
    }
}

require_once __DIR__ . '/includes/header.php';

if (!empty($errors)) {
    echo '<div class="alert alert-warning">Some statements failed (tables may already exist):</div><ul>';
    foreach ($errors as $err) {
        echo '<li>' . htmlspecialchars($err) . '</li>';
    }
    echo '</ul>';
}
echo '<div class="alert alert-success">Migration run complete. Executed ' . (int) $executed . ' statement(s).</div>';
echo '<p>The discipleship tables (discipleship_students, discipleship_modules, etc.) should now exist. Try <a href="../student/register.php">Student Registration</a> to confirm.</p>';
echo '<p><a href="discipleship.php" class="btn btn-primary">Discipleship Programs</a> <a href="../student/register.php" class="btn btn-outline-secondary">Student Register</a></p>';

require_once __DIR__ . '/includes/footer.php';
