<?php
/**
 * StreamFlix Configuration
 * Database, API, Cache, and Site Settings
 */

// ==================== SITE SETTINGS ====================
define('SITE_NAME', 'StreamFlix');
define('SITE_DOMAIN', 'www.streamflix.com');
define('SITE_URL', 'https://' . SITE_DOMAIN);
define('ADMIN_URL', SITE_URL . '/admin');

// ==================== DATABASE ====================
define('DB_HOST', 'localhost');
define('DB_USER', 'streamflix_user');
define('DB_PASS', 'your_secure_password_here');
define('DB_NAME', 'streamflix_db');
define('DB_PORT', 3306);
define('DB_CHARSET', 'utf8mb4');

// ==================== TMDB API ====================
define('TMDB_API_KEY', 'your_tmdb_api_key_here');
define('TMDB_BASE_URL', 'https://api.themoviedb.org/3');
define('TMDB_IMAGE_URL', 'https://image.tmdb.org/t/p');
define('TMDB_CACHE_TTL', 3600); // 1 hour

// ==================== CACHE ====================
define('CACHE_DIR', __DIR__ . '/cache');
define('CACHE_ENABLED', true);
define('CACHE_TTL', 3600); // 1 hour default

// ==================== SECURITY ====================
define('ADMIN_PASSWORD_HASH', password_hash('admin123', PASSWORD_BCRYPT)); // Change this!
define('SESSION_TIMEOUT', 3600); // 1 hour
define('HASH_SALT', 'your_unique_hash_salt_here');

// ==================== EMAIL ====================
define('MAIL_FROM', 'noreply@' . SITE_DOMAIN);
define('MAIL_FROM_NAME', SITE_NAME);
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 25);
define('SMTP_USER', '');
define('SMTP_PASS', '');

// ==================== PAGINATION ====================
define('ITEMS_PER_PAGE', 20);
define('MAX_PAGES', 500);

// ==================== FILE UPLOAD ====================
define('UPLOAD_DIR', __DIR__ . '/uploads');
define('MAX_UPLOAD_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// ==================== SEO ====================
define('SEO_TITLE_TEMPLATE', '{title} - Watch Free {type} Online | ' . SITE_NAME);
define('SEO_DESCRIPTION_TEMPLATE', 'Watch {title} ({year}) {quality} online for free. {genre} {type} streaming in {lang}.');
define('DEFAULT_IMAGE', SITE_URL . '/public/images/placeholder.svg');
define('TITLE_MAX_LENGTH', 60);
define('DESCRIPTION_MAX_LENGTH', 160);

// ==================== GEO TARGETING ====================
define('GEO_TARGET', ['US', 'GB', 'DE', 'FR', 'IT', 'ES', 'NL', 'PL', 'SE', 'DK', 'NO', 'FI']);
define('DEFAULT_LANGUAGE', 'en');
define('DEFAULT_REGION', 'US');

// ==================== ENVIRONMENT ====================
define('ENVIRONMENT', getenv('ENVIRONMENT') ?: 'production');
define('DEBUG_MODE', ENVIRONMENT === 'development');
define('ERROR_REPORTING', DEBUG_MODE ? E_ALL : E_ERROR | E_WARNING);

// ==================== TMDB GENRE MAPS ====================
$GENRE_MAP_MOVIE = [
    28 => 'Action', 12 => 'Adventure', 16 => 'Animation',
    35 => 'Comedy', 80 => 'Crime', 99 => 'Documentary',
    18 => 'Drama', 10751 => 'Family', 14 => 'Fantasy',
    36 => 'History', 27 => 'Horror', 10402 => 'Music',
    9648 => 'Mystery', 10749 => 'Romance', 878 => 'Sci-Fi',
    10770 => 'TV Movie', 53 => 'Thriller', 10752 => 'War',
    37 => 'Western'
];

$GENRE_MAP_TV = [
    10759 => 'Action & Adventure', 16 => 'Animation',
    35 => 'Comedy', 80 => 'Crime', 99 => 'Documentary',
    18 => 'Drama', 10751 => 'Family', 10762 => 'Kids',
    9648 => 'Mystery', 10763 => 'News', 10764 => 'Reality',
    10765 => 'Sci-Fi & Fantasy', 10766 => 'Soap', 10767 => 'Talk',
    10768 => 'War & Politics', 37 => 'Western'
];

// ==================== LANGUAGE MAP ====================
$LANGUAGE_MAP = [
    'en' => 'English', 'es' => 'Spanish', 'fr' => 'French',
    'de' => 'German', 'it' => 'Italian', 'ja' => 'Japanese',
    'ko' => 'Korean', 'zh' => 'Chinese', 'pt' => 'Portuguese',
    'ru' => 'Russian', 'hi' => 'Hindi', 'ar' => 'Arabic'
];

// ==================== FEATURED CONTENT ====================
// TMDB IDs for featured/curated content
define('FEATURED_MOVIES', [550, 278, 238, 240, 155, 19404, 680, 157336]);
define('FEATURED_TV', [1399, 1396, 1407, 60573, 42009, 3952, 39351, 4629]);
define('FEATURED_ANIME', [16498, 37988, 21, 5081, 37521, 41457, 43141, 40748]);

// ==================== STREAMING SERVERS ====================
$STREAMING_SERVERS = [
    ['name' => 'VidSrc', 'url' => 'https://vidsrc.cc/embed/movie/{id}'],
    ['name' => 'VideoAsy', 'url' => 'https://videasy.net/embed/movie/{id}'],
    ['name' => 'Embed2', 'url' => 'https://embed2.net/embed/movie/{id}']
];

// ==================== LOAD ENVIRONMENT OVERRIDES ====================
if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
}

// Set error reporting
error_reporting(ERROR_REPORTING);
if (DEBUG_MODE) {
    ini_set('display_errors', 1);
} else {
    ini_set('display_errors', 0);
}

// Set default timezone
date_default_timezone_set('UTC');

// Set headers
header('X-UA-Compatible: ie=edge');
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

?>