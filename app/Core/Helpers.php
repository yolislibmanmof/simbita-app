<?php
declare(strict_types=1);

/**
 * =====================================================================
 * SIMBITA - GLOBAL HELPERS
 * FINAL ULTRA PREMIUM - ENTERPRISE GRADE
 * =====================================================================
 */

/* ---------------------------------------------------------------
 * 1. SECURITY & OUTPUT
 * --------------------------------------------------------------- */

/**
 * Escape output untuk mencegah XSS
 * Menerima tipe apa pun (string, int, float, bool, null, array, object)
 * sehingga aman di strict_types mode untuk semua view.
 */
function e(mixed $value = null): string
{
    if ($value === null) {
        return '';
    }
    if (is_bool($value)) {
        $value = $value ? '1' : '0';
    } elseif (is_array($value) || is_object($value)) {
        try {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            $value = (string) $value;
        }
    } else {
        $value = (string) $value;
    }
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Escape untuk JavaScript context
 */
function ejs(?string $string): string
{
    return json_encode($string ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
}

/**
 * Escape untuk URL
 */
function eurl(?string $string): string
{
    return urlencode($string ?? '');
}

/**
 * Generate CSRF input field HTML
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(Csrf::token()) . '">';
}

/* ---------------------------------------------------------------
 * 2. REDIRECT & RESPONSE
 * --------------------------------------------------------------- */

/**
 * Redirect ke URL lain (aman dari "headers already sent" error)
 */
function redirect(string $path): void
{
    if (headers_sent($file, $line)) {
        // Fallback ke JavaScript redirect jika header sudah dikirim
        $url = BASE_URL . (str_starts_with($path, '/') ? $path : '/' . $path);
        echo '<script>window.location.href = ' . ejs($url) . ';</script>';
        echo '<noscript><meta http-equiv="refresh" content="0;url=' . e($url) . '"></noscript>';
        exit;
    }

    if (!str_starts_with($path, '/')) {
        $path = '/' . $path;
    }
    header("Location: " . BASE_URL . $path);
    exit;
}

/**
 * Redirect kembali ke halaman sebelumnya (fallback ke dashboard)
 */
function redirect_back(string $fallback = '/'): void
{
    $referer = $_SERVER['HTTP_REFERER'] ?? null;
    if ($referer && filter_var($referer, FILTER_VALIDATE_URL)) {
        header("Location: " . $referer);
        exit;
    }
    redirect($fallback);
}

/**
 * Mengembalikan response JSON (dengan exception handling)
 */
function json_response(array $data, int $statusCode = 200): void
{
    if (headers_sent()) {
        // Tidak bisa set header, output apa adanya
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    try {
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        echo json_encode(['error' => 'JSON encoding failed', 'message' => $e->getMessage()]);
    }
    exit;
}

/**
 * Debug helper
 */
function dd(mixed ...$data): void
{
    if (php_sapi_name() === 'cli') {
        foreach ($data as $item) {
            var_dump($item);
            echo "\n";
        }
        exit;
    }

    echo '<pre style="background:#1f2937;color:#f3f4f6;padding:1rem;border-radius:8px;overflow:auto;max-height:80vh;">';
    foreach ($data as $item) {
        var_dump($item);
    }
    echo '</pre>';
    exit;
}

/**
 * Dump tanpa exit
 */
function dump(mixed ...$data): void
{
    if (php_sapi_name() === 'cli') {
        foreach ($data as $item) var_dump($item);
        return;
    }
    echo '<pre style="background:#fef3c7;color:#78350f;padding:1rem;border-radius:8px;overflow:auto;">';
    foreach ($data as $item) var_dump($item);
    echo '</pre>';
}

/* ---------------------------------------------------------------
 * 3. URL & PATH HELPERS
 * --------------------------------------------------------------- */

/**
 * URL absolut ke base URL
 */
function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

/**
 * URL ke asset (CSS, JS, gambar)
 */
function asset(string $path): string
{
    return BASE_URL . '/assets/' . ltrim($path, '/');
}

/**
 * Current URL lengkap
 */
function current_url(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    return $scheme . '://' . $host . $uri;
}

/**
 * Current path saja (tanpa domain)
 */
function current_path(): string
{
    return parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
}

/* ---------------------------------------------------------------
 * 4. SESSION & FLASH MESSAGES
 * --------------------------------------------------------------- */

/**
 * Ambil nilai "old input" setelah redirect (untuk form validation)
 */
function old(string $key, mixed $default = ''): mixed
{
    $old = $_SESSION['_old_input'] ?? [];
    return $old[$key] ?? $default;
}

/**
 * Simpan old input untuk next request
 */
function old_save(): void
{
    $_SESSION['_old_input'] = $_POST;
}

/**
 * Clear old input
 */
function old_clear(): void
{
    unset($_SESSION['_old_input']);
}

/**
 * Set flash message
 */
function flash_set(string $type, string $message): void
{
    $_SESSION['flash_' . $type] = $message;
}

/**
 * Get & clear flash message (one-time)
 */
function flash_get(string $type): ?string
{
    $key = 'flash_' . $type;
    $value = $_SESSION[$key] ?? null;
    unset($_SESSION[$key]);
    return $value;
}

/**
 * Check apakah flash message ada
 */
function flash_has(string $type): bool
{
    return isset($_SESSION['flash_' . $type]);
}

/* ---------------------------------------------------------------
 * 5. DATE & TIME
 * --------------------------------------------------------------- */

/**
 * Format tanggal bahasa Indonesia (pengganti strftime yang deprecated di PHP 8.1+)
 * 
 * @param int|string|null $timestamp Unix timestamp atau string datetime
 * @param string $format 'full' (dengan hari), 'date', 'datetime', 'time', 'short'
 */
function id_date(int|string|null $timestamp = null, string $format = 'full'): string
{
    if ($timestamp === null) {
        $ts = time();
    } elseif (is_string($timestamp)) {
        $ts = strtotime($timestamp);
        if ($ts === false) return '-';
    } else {
        $ts = $timestamp;
    }

    $days   = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $months = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    $dayName = $days[(int) date('w', $ts)];
    $day     = (int) date('j', $ts);
    $month   = $months[(int) date('n', $ts)];
    $year    = date('Y', $ts);
    $time    = date('H:i', $ts);

    return match ($format) {
        'full'     => "$dayName, $day $month $year",
        'date'     => "$day $month $year",
        'datetime' => "$day $month $year, $time",
        'time'     => $time,
        'short'    => date('d/m/Y', $ts),
        default    => "$dayName, $day $month $year",
    };
}

/**
 * Relative time (waktu relatif: "5 menit lalu")
 */
function time_ago(int|string|null $timestamp): string
{
    if ($timestamp === null) return '-';
    if (is_string($timestamp)) {
        $ts = strtotime($timestamp);
        if ($ts === false) return '-';
    } else {
        $ts = $timestamp;
    }

    $diff = time() - $ts;
    if ($diff < 0) return 'baru saja';

    if ($diff < 60)       return 'baru saja';
    if ($diff < 3600)     return floor($diff / 60) . ' menit lalu';
    if ($diff < 86400)    return floor($diff / 3600) . ' jam lalu';
    if ($diff < 604800)   return floor($diff / 86400) . ' hari lalu';
    if ($diff < 2592000)  return floor($diff / 604800) . ' minggu lalu';
    if ($diff < 31536000) return floor($diff / 2592000) . ' bulan lalu';
    return floor($diff / 31536000) . ' tahun lalu';
}

/* ---------------------------------------------------------------
 * 6. NUMBER & CURRENCY
 * --------------------------------------------------------------- */

/**
 * Format angka dengan pemisah ribuan
 */
function format_number(int|float $number, int $decimals = 0): string
{
    return number_format($number, $decimals, ',', '.');
}

/**
 * Format Rupiah
 */
function format_rupiah(int|float $amount): string
{
    return 'Rp ' . format_number($amount, 0);
}

/**
 * Format ukuran file (bytes -> KB/MB/GB)
 */
function format_filesize(int $bytes): string
{
    if ($bytes <= 0) return '0 B';
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    $size = (float) $bytes;
    while ($size >= 1024 && $i < count($units) - 1) {
        $size /= 1024;
        $i++;
    }
    return round($size, 2) . ' ' . $units[$i];
}

/* ---------------------------------------------------------------
 * 7. STRING HELPERS
 * --------------------------------------------------------------- */

/**
 * Truncate string dengan ellipsis
 * Menerima tipe apa pun (aman untuk strict_types)
 */
function truncate(mixed $text, int $length = 100, string $suffix = '...'): string
{
    if ($text === null) {
        return '';
    }
    $text = (string) $text;
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * Generate URL-friendly slug
 */
function slug(string $text): string
{
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

/**
 * Sanitize nama file (untuk upload)
 */
function sanitize_filename(string $filename): string
{
    $filename = preg_replace('/[^\w\.\-]/', '_', $filename);
    $filename = preg_replace('/_+/', '_', $filename);
    if (strlen($filename) > 200) {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $name = substr($filename, 0, 190 - strlen($ext));
        $filename = $name . '.' . $ext;
    }
    return $filename;
}

/**
 * Check apakah string mengandung kata (case-insensitive)
 */
function str_contains_ci(string $haystack, string $needle): bool
{
    return str_contains(mb_strtolower($haystack), mb_strtolower($needle));
}

/* ---------------------------------------------------------------
 * 8. ARRAY HELPERS
 * --------------------------------------------------------------- */

/**
 * Safe array get dengan default value
 */
function array_get(array $array, string $key, mixed $default = null): mixed
{
    return $array[$key] ?? $default;
}

/**
 * Group array by key
 */
function array_group(array $array, string $key): array
{
    $grouped = [];
    foreach ($array as $item) {
        if (isset($item[$key])) {
            $grouped[$item[$key]][] = $item;
        }
    }
    return $grouped;
}

/**
 * Pluck single column from array
 */
function array_pluck(array $array, string $key): array
{
    return array_column($array, $key);
}

/* ---------------------------------------------------------------
 * 9. REQUEST HELPERS
 * --------------------------------------------------------------- */

/**
 * Check apakah request adalah AJAX
 */
function is_ajax(): bool
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Check apakah request adalah POST
 */
function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/**
 * Get client IP address (dengan proxy support)
 */
function client_ip(): string
{
    $headers = [
        'HTTP_CF_CONNECTING_IP', // Cloudflare
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_REAL_IP',
        'REMOTE_ADDR'
    ];

    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip = explode(',', $_SERVER[$header])[0];
            $ip = trim($ip);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }

    return '0.0.0.0';
}

/**
 * Get user agent
 */
function user_agent(): string
{
    return $_SERVER['HTTP_USER_AGENT'] ?? '';
}

/* ---------------------------------------------------------------
 * 10. FILE & VALIDATION HELPERS
 * --------------------------------------------------------------- */

/**
 * Check apakah file adalah gambar
 */
function is_image(string $path): bool
{
    if (!file_exists($path)) return false;
    $info = @getimagesize($path);
    return $info !== false;
}

/**
 * Validasi email (lebih strict dari filter_var biasa)
 */
function is_valid_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false
        && preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email);
}

/**
 * Generate random string
 */
function random_string(int $length = 32, string $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'): string
{
    $result = '';
    $max = strlen($chars) - 1;
    for ($i = 0; $i < $length; $i++) {
        $result .= $chars[random_int(0, $max)];
    }
    return $result;
}

/**
 * Generate UUID v4
 */
function uuid(): string
{
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

/* ---------------------------------------------------------------
 * 11. PASSWORD HELPERS
 * ---------------------------------------------------------------
 * CATATAN PENTING:
 * Nama fungsi TIDAK BOLEH sama dengan fungsi bawaan PHP
 * (password_hash, password_verify, password_needs_rehash, dll).
 * Semua fungsi dibungkus function_exists() agar anti "Cannot redeclare".
 */

/**
 * Hash password dengan bcrypt (cost 12)
 */
if (!function_exists('password_make')) {
    function password_make(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
}

/**
 * Verify password terhadap hash
 */
if (!function_exists('password_check')) {
    function password_check(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}

/**
 * Check apakah hash perlu di-rehash (misal setelah upgrade cost).
 * 
 * ⚠️ DINAMAI password_should_rehash() KARENA password_needs_rehash()
 *    adalah fungsi BAWAAN PHP dan tidak boleh dideklarasikan ulang.
 *    Fungsi ini hanya wrapper tipis ke bawaan PHP dengan cost 12.
 */
if (!function_exists('password_should_rehash')) {
    function password_should_rehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => 12]);
    }
}