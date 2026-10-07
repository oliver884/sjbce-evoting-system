<?php
/**
 * Site configuration + secure session bootstrap.
 * Every entry-point PHP file should require this first.
 */

// Force a consistent timezone (Ghana, UTC+0, no DST) so PHP's clock matches
// MySQL's clock — without this, servers often default to different timezones
// and election start/end times silently drift.
date_default_timezone_set('Africa/Accra');

define('SITE_NAME', 'SJBCE Election Portal');
// Auto-detect the base URL from where this project actually lives on the
// server, instead of requiring it to be typed in by hand. This works no
// matter what the project folder is named or how deep it's nested.
$projectRoot  = str_replace('\\', '/', dirname(__DIR__));           // filesystem path to project root
$documentRoot = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'], '/\\'));
$autoBaseUrl  = substr($projectRoot, strlen($documentRoot));
$autoBaseUrl  = trim($autoBaseUrl, '/');
$autoBaseUrl  = $autoBaseUrl === '' ? '/' : '/' . $autoBaseUrl . '/';
define('BASE_URL', $autoBaseUrl);
define('UPLOAD_DIR', __DIR__ . '/../assets/uploads/candidates/');
define('UPLOAD_URL', BASE_URL . 'assets/uploads/candidates/');
define('MAX_PHOTO_BYTES', 2 * 1024 * 1024); // 2MB

// --- Secure session settings (must run before session_start) ---
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Lax');
    if (!empty($_SERVER['HTTPS'])) {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', '0'); // never show raw errors to end users
ini_set('log_errors', '1');

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/audit.php';
require_once __DIR__ . '/../includes/rate_limit.php';
