<?php
declare(strict_types=1);

/**
 * =====================================================================
 * SIMBITA - GLOBAL CONFIGURATION
 * FINAL ULTRA PREMIUM - ENTERPRISE GRADE
 * =====================================================================
 * File ini berisi semua konstanta global aplikasi.
 * Dipanggil pertama kali oleh index.php.
 */

// =====================================================================
// 1. ERROR REPORTING
// =====================================================================
error_reporting(E_ALL);
ini_set('display_errors', '1');      // Development: tampilkan error
ini_set('log_errors', '1');
ini_set('error_log', dirname(__DIR__) . '/logs/php_error.log');

// Pastikan folder logs ada
if (!is_dir(dirname(__DIR__) . '/logs')) {
    @mkdir(dirname(__DIR__) . '/logs', 0755, true);
}

// =====================================================================
// 2. TIMEZONE
// =====================================================================
// PENTING: Set timezone Indonesia agar date() tidak warning
date_default_timezone_set('Asia/Jakarta');

// =====================================================================
// 3. BASE URL & APP INFO
// =====================================================================
// ⚠️ GANTI sesuai lokasi aplikasi Anda
define('BASE_URL', 'http://localhost/simbita-app/public');
define('APP_NAME', 'SIMBITA');
define('APP_VERSION', '1.0.0');

// =====================================================================
// 4. DEBUG MODE
// =====================================================================
/**
 * true  = Development (tampilkan error detail + akun demo di login)
 * false = Production  (error disembunyikan, hanya log)
 */
define('APP_DEBUG', true);

// =====================================================================
// 5. DATABASE CONFIGURATION
// =====================================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'simbita_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', 3306);

// =====================================================================
// 6. FILE UPLOAD CONFIGURATION
// =====================================================================
define('STORAGE_PATH', dirname(__DIR__) . '/storage/documents');
define('MAX_UPLOAD_BYTES', 25 * 1024 * 1024);  // 25 MB
define('ALLOWED_MIME', [
    'application/pdf',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
    'application/msword', // .doc
]);
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx']);

// Pastikan folder storage ada
if (!is_dir(STORAGE_PATH)) {
    @mkdir(STORAGE_PATH, 0755, true);
}

// =====================================================================
// 7. SESSION CONFIGURATION
// =====================================================================
define('SESSION_LIFETIME', 7200);        // 2 jam (dalam detik)
define('REMEMBER_LIFETIME', 2592000);    // 30 hari (dalam detik)
define('SESSION_NAME', 'simbita_session');

// =====================================================================
// 8. PAGINATION
// =====================================================================
define('DEFAULT_PER_PAGE', 20);
define('MAX_PER_PAGE', 100);

// =====================================================================
// 9. APP URL HELPERS
// =====================================================================
/**
 * Base path aplikasi (untuk handling subfolder deployment)
 * Contoh: jika BASE_URL = 'http://localhost/simbita-app/public'
 * maka APP_PATH = '/simbita-app/public'
 */
define('APP_PATH', parse_url(BASE_URL, PHP_URL_PATH) ?: '/');

// =====================================================================
// 10. SECURITY CONSTANTS
// =====================================================================
define('BCRYPT_COST', 12);                // Cost untuk password_hash
define('CSRF_TOKEN_LIFETIME', 7200);      // 2 jam
define('RATE_LIMIT_LOGIN', 5);            // Max 5 login attempt per menit
define('RATE_LIMIT_WINDOW', 60);          // Window 1 menit

// =====================================================================
// 11. NOTIFICATION CONFIGURATION
// =====================================================================
define('NOTIFICATION_MAX_TITLE', 255);
define('NOTIFICATION_MAX_MESSAGE', 1000);
define('NOTIFICATION_RETENTION_DAYS', 30); // Hapus notifikasi lama setelah 30 hari

// =====================================================================
// 12. ACADEMIC CONFIGURATION (SIMBITA specific)
// =====================================================================
define('MAX_SUPERVISORS', 2);             // Max dosen pembimbing per TA
define('MAX_KEYWORDS', 10);               // Max kata kunci per TA
define('ABSTRACT_MAX_LENGTH', 2000);      // Max panjang abstrak
define('TITLE_MAX_LENGTH', 300);          // Max panjang judul

// =====================================================================
// 13. ENVIRONMENT DETECTION
// =====================================================================
define('IS_CLI', php_sapi_name() === 'cli');
define('IS_AJAX', !empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
define('IS_HTTPS', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
    || ($_SERVER['SERVER_PORT'] ?? 80) == 443);

// =====================================================================
// 14. DEVELOPER CONTACT (untuk error page)
// =====================================================================
define('ADMIN_EMAIL', 'admin@simbita.id');
define('SUPPORT_EMAIL', 'support@simbita.id');