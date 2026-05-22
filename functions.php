<?php
/**
 * Helper Functions
 * Utilities for sanitization, formatting, redirects, etc.
 */

// ==================== SANITIZATION ====================
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function sanitize_url($url) {
    return filter_var($url, FILTER_SANITIZE_URL);
}

function sanitize_email($email) {
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
}

function sanitize_int($value) {
    return intval($value);
}

// ==================== VALIDATION ====================
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function is_valid_url($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

function is_valid_int($value) {
    return filter_var($value, FILTER_VALIDATE_INT) !== false;
}

// ==================== HASHING & SECURITY ====================
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

function generate_token($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

function hash_string($str) {
    return hash('sha256', $str . HASH_SALT);
}

// ==================== REDIRECTS ====================
function redirect($url, $code = 302) {
    header('Location: ' . $url, true, $code);
    exit;
}

function redirect_home() {
    redirect(SITE_URL);
}

function redirect_admin() {
    redirect(ADMIN_URL . '/dashboard');
}

function redirect_404() {
    header('HTTP/1.1 404 Not Found');
    include __DIR__ . '/views/pages/404.php';
    exit;
}

// ==================== JSON RESPONSES ====================
function json_response($data, $status = 200) {
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function success_response($message = 'Success', $data = null) {
    json_response([
        'status' => 'success',
        'message' => $message,
        'data' => $data
    ]);
}

function error_response($message = 'Error', $code = 400) {
    json_response([
        'status' => 'error',
        'message' => $message
    ], $code);
}

// ==================== FORMATTING ====================
function format_number($num) {
    if ($num >= 1000000) {
        return round($num / 1000000, 1) . 'M';
    }
    if ($num >= 1000) {
        return round($num / 1000, 1) . 'K';
    }
    return $num;
}

function format_date($date, $format = 'Y-m-d') {
    return date($format, strtotime($date));
}

function time_ago($date) {
    $timestamp = strtotime($date);
    $diff = time() - $timestamp;
    
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 2592000) return floor($diff / 86400) . ' days ago';
    
    return date('M d, Y', $timestamp);
}

function slugify($text) {
    $text = preg_replace('~[^\\pL\\d]+~u', '-', $text);
    $text = trim($text, '-');
    $text = strtolower($text);
    $text = preg_replace('~[^-\\w]+~', '', $text);
    return preg_replace('~-+~', '-', $text);
}

function unslugify($slug) {
    return ucwords(str_replace('-', ' ', $slug));
}

// ==================== STRING UTILITIES ====================
function truncate($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

function contains($haystack, $needle) {
    return strpos($haystack, $needle) !== false;
}

function starts_with($haystack, $needle) {
    return strpos($haystack, $needle) === 0;
}

function ends_with($haystack, $needle) {
    return substr($haystack, -strlen($needle)) === $needle;
}

// ==================== ARRAY UTILITIES ====================
function array_get($array, $key, $default = null) {
    return isset($array[$key]) ? $array[$key] : $default;
}

function array_first($array) {
    return reset($array);
}

function array_last($array) {
    return end($array);
}

function array_remove($array, $key) {
    unset($array[$key]);
    return $array;
}

// ==================== REQUEST UTILITIES ====================
function get_request_method() {
    return $_SERVER['REQUEST_METHOD'];
}

function is_ajax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    }
    return $_SERVER['REMOTE_ADDR'];
}

function get_user_agent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
}

function get_referrer() {
    return $_SERVER['HTTP_REFERER'] ?? '';
}

function get_query_param($key, $default = null) {
    return $_GET[$key] ?? $default;
}

function get_post_param($key, $default = null) {
    return $_POST[$key] ?? $default;
}

// ==================== SESSION UTILITIES ====================
function session_set($key, $value) {
    $_SESSION[$key] = $value;
}

function session_get($key, $default = null) {
    return $_SESSION[$key] ?? $default;
}

function session_has($key) {
    return isset($_SESSION[$key]);
}

function session_remove($key) {
    unset($_SESSION[$key]);
}

function session_clear() {
    session_destroy();
    $_SESSION = [];
}

// ==================== COOKIE UTILITIES ====================
function cookie_set($key, $value, $expire = null) {
    $expire = $expire ?? time() + (86400 * 30); // 30 days
    setcookie($key, $value, $expire, '/', SITE_DOMAIN, true, true);
}

function cookie_get($key, $default = null) {
    return $_COOKIE[$key] ?? $default;
}

function cookie_remove($key) {
    setcookie($key, '', time() - 3600, '/', SITE_DOMAIN);
}

// ==================== SEO UTILITIES ====================
function generate_slug_id($str) {
    return abs(crc32($str));
}

function is_indexed_page($path) {
    $indexed = ['/movie/', '/tv/', '/browse/', '/search', '/collections', '/editorial'];
    foreach ($indexed as $route) {
        if (strpos($path, $route) === 0) {
            return true;
        }
    }
    return false;
}

function get_canonical_url($path = null) {
    $path = $path ?? $_SERVER['REQUEST_URI'];
    return SITE_URL . $path;
}

// ==================== IMAGE UTILITIES ====================
function get_poster_url($path, $size = 'w342') {
    if (!$path) return DEFAULT_IMAGE;
    return TMDB_IMAGE_URL . '/t/p/' . $size . $path;
}

function get_backdrop_url($path, $size = 'w1280') {
    if (!$path) return DEFAULT_IMAGE;
    return TMDB_IMAGE_URL . '/t/p/' . $size . $path;
}

function get_profile_url($path, $size = 'w185') {
    if (!$path) return DEFAULT_IMAGE;
    return TMDB_IMAGE_URL . '/t/p/' . $size . $path;
}

// ==================== PAGINATION ====================
function paginate($total, $per_page = ITEMS_PER_PAGE) {
    $page = max(1, sanitize_int(get_query_param('page', 1)));
    $total_pages = ceil($total / $per_page);
    $page = min($page, $total_pages);
    
    return [
        'page' => $page,
        'per_page' => $per_page,
        'total' => $total,
        'total_pages' => min($total_pages, MAX_PAGES),
        'offset' => ($page - 1) * $per_page,
        'has_prev' => $page > 1,
        'has_next' => $page < $total_pages
    ];
}

// ==================== LOGGING ====================
function log_error($message, $file = null) {
    $log_file = __DIR__ . '/logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] $message";
    if ($file) $log_entry .= " (File: $file)";
    error_log($log_entry . "\n", 3, $log_file);
}

function log_action($action, $details = '') {
    $log_file = __DIR__ . '/logs/action.log';
    $timestamp = date('Y-m-d H:i:s');
    $user_id = session_get('admin_id', 'guest');
    $log_entry = "[$timestamp] User: $user_id | Action: $action | Details: $details";
    error_log($log_entry . "\n", 3, $log_file);
}

// ==================== RATE LIMITING ====================
function is_rate_limited($key, $limit = 10, $period = 60) {
    $cache_key = "rate_limit_$key";
    $cache = get_cache($cache_key);
    
    if ($cache && $cache >= $limit) {
        return true;
    }
    
    set_cache($cache_key, ($cache ?? 0) + 1, $period);
    return false;
}

?>