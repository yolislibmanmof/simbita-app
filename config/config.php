<?php
declare(strict_types=1);

// =========================================================================
// 0. ENVIRONMENT LOADER (Custom .env.php implementation)
// =========================================================================
$envFile = dirname(__DIR__) . '/.env.php';
if (file_exists($envFile)) {
    $envVars = require $envFile;
    if (is_array($envVars)) {
        foreach ($envVars as $key => $value) {
            // Hanya set jika belum ada di environment server (memungkinkan override dari PHP-FPM/Apache)
            if (getenv($key) === false) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}

// Helper function untuk mengambil env dengan fallback
$env = function(string $key, $default = null) {
    $value = getenv($key);
    return $value !== false ? $value : $default;
};

// 1. ERROR REPORTING
// Menggunakan FILTER_NULL_ON_FAILURE agar parsing boolean lebih aman dan presisi
$debugValue = $env('APP_DEBUG', 'false');
define('APP_DEBUG', filter_var($debugValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false);

error_reporting(E_ALL);
ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', dirname(__DIR__) . '/logs/php_error.log');

$logDirectory = dirname(__DIR__) . '/logs';
if (!is_dir($logDirectory) && !mkdir($logDirectory, 0755, true) && !is_dir($logDirectory)) {
    error_log('SIMBITA: gagal membuat direktori log.');
}

// 2. TIMEZONE
date_default_timezone_set('Asia/Jakarta');

// 3. BASE URL & APP INFO
define('BASE_URL', $env('APP_URL', 'http://localhost/simbita-app/public'));
define('APP_NAME', $env('APP_NAME', 'SIMBITA'));
define('APP_VERSION', '1.0.0');

// 4. DATABASE CONFIGURATION
define('DB_HOST', $env('DB_HOST', 'localhost'));
define('DB_NAME', $env('DB_NAME', 'simbita_db'));
define('DB_USER', $env('DB_USER', 'root'));
// PENTING: Gunakan cek !== false agar password kosong ('') tidak dianggap sebagai nilai yang tidak ada
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : ''); 
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', (int)$env('DB_PORT', 3306));

// 5. FILE UPLOAD CONFIGURATION
define('STORAGE_PATH', dirname(__DIR__) . '/storage/documents');
define('MAX_UPLOAD_BYTES', 25 * 1024 * 1024);
define('ALLOWED_MIME', [
    'application/pdf',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/msword',
]);
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx']);

if (!is_dir(STORAGE_PATH)) {
    @mkdir(STORAGE_PATH, 0755, true);
}

// 6. SESSION CONFIGURATION
define('SESSION_LIFETIME', 7200);
define('REMEMBER_LIFETIME', 2592000);
define('SESSION_NAME', 'simbita_session');

// 7. PAGINATION
define('DEFAULT_PER_PAGE', 20);
define('MAX_PER_PAGE', 100);

// 8. APP URL HELPERS
define('APP_PATH', parse_url(BASE_URL, PHP_URL_PATH) ?: '/');

// 9. SECURITY CONSTANTS
define('BCRYPT_COST', 12);
define('CSRF_TOKEN_LIFETIME', 7200);
define('RATE_LIMIT_LOGIN', 5);
define('RATE_LIMIT_WINDOW', 60);

// 10. NOTIFICATION CONFIGURATION
define('NOTIFICATION_MAX_TITLE', 255);
define('NOTIFICATION_MAX_MESSAGE', 1000);
define('NOTIFICATION_RETENTION_DAYS', 30);

// 11. ACADEMIC CONFIGURATION
define('MAX_SUPERVISORS', 2);
define('MAX_KEYWORDS', 10);
define('ABSTRACT_MAX_LENGTH', 2000);
define('TITLE_MAX_LENGTH', 300);

// 12. ENVIRONMENT DETECTION
define('IS_CLI', php_sapi_name() === 'cli');
define('IS_AJAX', !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
define('IS_HTTPS', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443);

// 13. DEVELOPER CONTACT
define('ADMIN_EMAIL', 'admin@simbita.id');
define('SUPPORT_EMAIL', 'support@simbita.id');