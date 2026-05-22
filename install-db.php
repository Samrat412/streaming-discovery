<?php
/**
 * Database Installation Script
 * Creates all required tables for StreamFlix
 * 
 * Run once: visit yoursite.com/install-db.php
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

$tables = [
    // Visitor Tracking
    "CREATE TABLE IF NOT EXISTS `visitor_counter` (
        `id` INT PRIMARY KEY DEFAULT 1 CHECK (`id` = 1),
        `total_count` BIGINT DEFAULT 0 NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    "CREATE TABLE IF NOT EXISTS `daily_visitors` (
        `visit_date` DATE PRIMARY KEY,
        `visit_count` BIGINT DEFAULT 0 NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    "CREATE TABLE IF NOT EXISTS `hourly_stats` (
        `id` INT PRIMARY KEY AUTO_INCREMENT,
        `stat_date` DATE NOT NULL,
        `stat_hour` INT NOT NULL CHECK (`stat_hour` >= 0 AND `stat_hour` <= 23),
        `page_views_count` INT DEFAULT 0,
        `unique_visitors` INT DEFAULT 0,
        UNIQUE KEY `date_hour` (`stat_date`, `stat_hour`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    "CREATE TABLE IF NOT EXISTS `page_views` (
        `id` INT PRIMARY KEY AUTO_INCREMENT,
        `session_id` VARCHAR(64) NOT NULL,
        `page_path` VARCHAR(500) NOT NULL,
        `device_type` VARCHAR(10) DEFAULT 'desktop',
        `referrer_source` VARCHAR(200),
        `country_code` VARCHAR(5),
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY `idx_created` (`created_at` DESC),
        KEY `idx_path` (`page_path`),
        KEY `idx_session` (`session_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    "CREATE TABLE IF NOT EXISTS `active_sessions` (
        `session_id` VARCHAR(64) PRIMARY KEY,
        `last_seen` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `page_path` VARCHAR(500),
        `device_type` VARCHAR(10) DEFAULT 'desktop',
        KEY `idx_last_seen` (`last_seen`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Comments
    "CREATE TABLE IF NOT EXISTS `comments` (
        `id` INT PRIMARY KEY AUTO_INCREMENT,
        `tmdb_id` INT NOT NULL,
        `media_type` VARCHAR(10) DEFAULT 'movie' NOT NULL,
        `username` VARCHAR(100) DEFAULT 'Anonymous' NOT NULL,
        `comment_text` TEXT NOT NULL,
        `rating` INT CHECK (`rating` >= 1 AND `rating` <= 5),
        `status` VARCHAR(20) DEFAULT 'pending',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY `idx_tmdb` (`tmdb_id`, `media_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Content Requests
    "CREATE TABLE IF NOT EXISTS `content_requests` (
        `id` INT PRIMARY KEY AUTO_INCREMENT,
        `title` VARCHAR(500) NOT NULL,
        `request_type` VARCHAR(10) DEFAULT 'movie',
        `description` TEXT,
        `votes` INT DEFAULT 1,
        `status` VARCHAR(20) DEFAULT 'pending',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Admin Users
    "CREATE TABLE IF NOT EXISTS `admin_users` (
        `id` INT PRIMARY KEY AUTO_INCREMENT,
        `username` VARCHAR(50) UNIQUE NOT NULL,
        `email` VARCHAR(100) UNIQUE NOT NULL,
        `password_hash` VARCHAR(255) NOT NULL,
        `role` ENUM('admin', 'editor', 'moderator') DEFAULT 'editor',
        `status` ENUM('active', 'inactive', 'banned') DEFAULT 'active',
        `last_login` TIMESTAMP NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Admin Logs
    "CREATE TABLE IF NOT EXISTS `admin_logs` (
        `id` INT PRIMARY KEY AUTO_INCREMENT,
        `admin_id` INT NOT NULL,
        `action` VARCHAR(100),
        `details` TEXT,
        `ip_address` VARCHAR(45),
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`admin_id`) REFERENCES `admin_users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Site Settings
    "CREATE TABLE IF NOT EXISTS `site_settings` (
        `id` INT PRIMARY KEY AUTO_INCREMENT,
        `setting_key` VARCHAR(100) UNIQUE NOT NULL,
        `setting_value` LONGTEXT,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Email Templates
    "CREATE TABLE IF NOT EXISTS `email_templates` (
        `id` INT PRIMARY KEY AUTO_INCREMENT,
        `template_name` VARCHAR(100) UNIQUE NOT NULL,
        `subject` VARCHAR(255),
        `body` LONGTEXT,
        `variables` JSON,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Watchlist
    "CREATE TABLE IF NOT EXISTS `watchlist_items` (
        `id` INT PRIMARY KEY AUTO_INCREMENT,
        `session_id` VARCHAR(64) NOT NULL,
        `tmdb_id` INT NOT NULL,
        `media_type` VARCHAR(10) NOT NULL,
        `title` VARCHAR(500),
        `poster_path` VARCHAR(255),
        `backdrop_path` VARCHAR(255),
        `vote_average` DECIMAL(3,1),
        `release_date` DATE,
        `added_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `unique_watchlist` (`session_id`, `tmdb_id`, `media_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Watch Progress
    "CREATE TABLE IF NOT EXISTS `watch_progress` (
        `id` INT PRIMARY KEY AUTO_INCREMENT,
        `session_id` VARCHAR(64) NOT NULL,
        `tmdb_id` INT NOT NULL,
        `media_type` VARCHAR(10) NOT NULL,
        `current_time` INT DEFAULT 0,
        `duration` INT,
        `season` INT,
        `episode` INT,
        `last_watched` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY `unique_progress` (`session_id`, `tmdb_id`, `media_type`, `season`, `episode`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Featured Content
    "CREATE TABLE IF NOT EXISTS `featured_content` (
        `id` INT PRIMARY KEY AUTO_INCREMENT,
        `tmdb_id` INT NOT NULL,
        `media_type` VARCHAR(10) NOT NULL,
        `position` INT DEFAULT 0,
        `featured_from` TIMESTAMP,
        `featured_until` TIMESTAMP,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `unique_featured` (`tmdb_id`, `media_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Collections
    "CREATE TABLE IF NOT EXISTS `collections` (
        `id` INT PRIMARY KEY AUTO_INCREMENT,
        `name` VARCHAR(255) NOT NULL,
        `slug` VARCHAR(255) UNIQUE NOT NULL,
        `description` TEXT,
        `thumbnail` VARCHAR(255),
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Collection Items
    "CREATE TABLE IF NOT EXISTS `collection_items` (
        `id` INT PRIMARY KEY AUTO_INCREMENT,
        `collection_id` INT NOT NULL,
        `tmdb_id` INT NOT NULL,
        `media_type` VARCHAR(10) NOT NULL,
        `position` INT DEFAULT 0,
        FOREIGN KEY (`collection_id`) REFERENCES `collections`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

$success_count = 0;
$error_count = 0;
$errors = [];

echo "<!DOCTYPE html>\n";
echo "<html><head><title>StreamFlix Database Installation</title>";
echo "<style>body{font-family:Arial;margin:20px;background:#1a1a1a;color:#fff;}";
echo ".success{color:#00ff00;}.error{color:#ff0000;}.info{color:#00d4ff;}.box{background:#2a2a2a;padding:20px;border-radius:8px;margin:10px 0;}";
echo "</style></head><body>";
echo "<div class='box'><h1>StreamFlix Database Installation</h1></div>\n";

foreach ($tables as $sql) {
    $table_name = preg_match('/CREATE TABLE.*?`([^`]+)`/', $sql, $match) ? $match[1] : 'Unknown';
    
    if ($conn->query($sql) === TRUE) {
        echo "<div class='box'><span class='success'>✓</span> Table <strong>$table_name</strong> created successfully</div>\n";
        $success_count++;
    } else {
        echo "<div class='box'><span class='error'>✗</span> Error creating <strong>$table_name</strong>: " . $conn->error . "</div>\n";
        $error_count++;
        $errors[] = $conn->error;
    }
}

// Insert default admin user
$admin_username = 'admin';
$admin_password = 'admin123';
$admin_hash = password_hash($admin_password, PASSWORD_BCRYPT);
$admin_email = 'admin@' . SITE_DOMAIN;

$insert_admin = "INSERT INTO admin_users (username, email, password_hash, role, status) 
                VALUES ('$admin_username', '$admin_email', '$admin_hash', 'admin', 'active')
                ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash)";

if ($conn->query($insert_admin) === TRUE) {
    echo "<div class='box'><span class='success'>✓</span> Default admin user created (Username: <strong>admin</strong>, Password: <strong>admin123</strong>)</div>\n";
    $success_count++;
} else {
    echo "<div class='box'><span class='error'>✗</span> Error creating admin user: " . $conn->error . "</div>\n";
    $error_count++;
}

// Insert default settings
$settings = [
    'site_name' => SITE_NAME,
    'site_domain' => SITE_DOMAIN,
    'site_logo' => DEFAULT_IMAGE,
    'tmdb_api_key' => TMDB_API_KEY,
    'cache_enabled' => '1',
    'cache_ttl' => '3600'
];

foreach ($settings as $key => $value) {
    $insert_setting = "INSERT INTO site_settings (setting_key, setting_value) 
                     VALUES ('$key', '$value')
                     ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)";
    if ($conn->query($insert_setting) === TRUE) {
        $success_count++;
    }
}

echo "<div class='box'><span class='info'>ℹ</span> Default settings inserted</div>\n";

// Summary
echo "<div class='box' style='border-top:2px solid #00d4ff;'>";
echo "<h2>Installation Summary</h2>";
echo "<p><span class='success'>✓ Successful:</span> $success_count</p>";
echo "<p><span class='error'>✗ Errors:</span> $error_count</p>";
echo "</div>\n";

if ($error_count === 0) {
    echo "<div class='box' style='background:#1a3a1a;border:2px solid #00ff00;'>";
    echo "<h2 class='success'>✓ Installation Complete!</h2>";
    echo "<p>All tables created successfully.</p>";
    echo "<p><strong>IMPORTANT:</strong> Delete this file (install-db.php) from your server for security.</p>";
    echo "<p><a href='/' style='color:#00d4ff;text-decoration:none;'><< Back to Homepage</a></p>";
    echo "</div>\n";
} else {
    echo "<div class='box' style='background:#3a1a1a;border:2px solid #ff0000;'>";
    echo "<h2 class='error'>✗ Installation Failed</h2>";
    echo "<p>Please check the errors above and try again.</p>";
    echo "</div>\n";
}

echo "</body></html>";

?>