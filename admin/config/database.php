<?php
/**
 * Database Configuration
 * CrossLife Mission Network Cross Admin
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'crosslife');
define('DB_CHARSET', 'utf8mb4');

// SMTP / Email configuration (centralized)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'mwiganivalence@gmail.com');
define('SMTP_PASS', 'cipt glmj vhfl amjj');
define('SMTP_FROM_EMAIL', 'karibu@crosslife.org');
define('SMTP_FROM_NAME', 'CrossLife Mission Network');

// Recipient / site info
// Guard against re-defining these if config.php (which also defines SITE_NAME) is loaded first.
if (!defined('ADMIN_EMAIL')) {
    define('ADMIN_EMAIL', 'mwiganivalence@gmail.com');
}
if (!defined('SITE_EMAIL')) {
    define('SITE_EMAIL', 'karibu@crosslife.org');
}
if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'CrossLife Mission Network');
}

/**
 * Database Connection Class
 */
class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Get database connection
function getDB() {
    return Database::getInstance()->getConnection();
}

