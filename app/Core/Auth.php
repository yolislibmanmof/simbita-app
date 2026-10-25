<?php
declare(strict_types=1);

/**
 * =====================================================================
 * SIMBITA - AUTHENTICATION SYSTEM
 * FINAL ULTRA PREMIUM - ENTERPRISE GRADE
 * =====================================================================
 * Fitur:
 * - Session timeout otomatis
 * - Remember me (persistent login)
 * - Role-based access control
 * - User data caching
 * - Flash message integration
 * - Separate handlers untuk web vs API
 */
class Auth
{
    private const SESSION_KEY      = 'simbita_user';
    private const REMEMBER_COOKIE  = 'simbita_remember';
    private const SESSION_LIFETIME = 7200; // 2 jam
    private const REMEMBER_LIFETIME = 2592000; // 30 hari

    /**
     * Start session dengan konfigurasi aman
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Hardening session
            ini_set('session.cookie_httponly', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.cookie_samesite', 'Lax');
            ini_set('session.gc_maxlifetime', (string) self::SESSION_LIFETIME);

            if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
                ini_set('session.cookie_secure', '1');
            }

            session_start();
        }

        // Check session timeout
        self::checkTimeout();

        // Auto-login dari remember cookie jika session mati
        if (!self::check()) {
            self::tryRememberLogin();
        }
    }

    /**
     * Check session timeout
     */
    private static function checkTimeout(): void
    {
        if (!isset($_SESSION[self::SESSION_KEY])) return;

        $lastActivity = $_SESSION[self::SESSION_KEY]['last_activity'] ?? 0;
        if (time() - $lastActivity > self::SESSION_LIFETIME) {
            self::logout(false); // Logout tanpa redirect
            flash_set('warning', 'Sesi Anda telah berakhir karena tidak aktif. Silakan login kembali.');
        } else {
            // Update last activity (sliding expiration)
            $_SESSION[self::SESSION_KEY]['last_activity'] = time();
        }
    }

    /**
     * Login user
     */
    public static function login(int $userId, string $role, array $extraData = []): void
    {
        // Regenerate session ID untuk mencegah session fixation
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        $_SESSION[self::SESSION_KEY] = array_merge([
            'id'            => $userId,
            'role'          => $role,
            'login_time'    => time(),
            'last_activity' => time(),
            'ip'            => client_ip(),
            'user_agent'    => user_agent(),
        ], $extraData);

        // Regenerate CSRF
        Csrf::regenerate();
    }

    /**
     * Set remember me cookie
     */
    public static function setRemember(int $userId, string $token): void
    {
        $cookieValue = base64_encode($userId . ':' . $token);
        $expiry = time() + self::REMEMBER_LIFETIME;

        setcookie(self::REMEMBER_COOKIE, $cookieValue, [
            'expires'  => $expiry,
            'path'     => '/',
            'secure'   => !empty($_SERVER['HTTPS']),
            'httponly'  => true,
            'samesite' => 'Lax',
        ]);

        // Simpan hash token di database (untuk validasi)
        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                UPDATE users 
                SET remember_token = ?, remember_expiry = ? 
                WHERE id = ?
            ');
            $stmt->execute([
                hash('sha256', $token),
                date('Y-m-d H:i:s', $expiry),
                $userId
            ]);
        } catch (Exception $e) {
            // Silent fail - kolom mungkin belum ada
        }
    }

    /**
     * Clear remember me cookie
     */
    public static function clearRemember(int $userId): void
    {
        setcookie(self::REMEMBER_COOKIE, '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'secure'   => !empty($_SERVER['HTTPS']),
            'httponly'  => true,
            'samesite' => 'Lax',
        ]);

        try {
            $db = Database::connection();
            $stmt = $db->prepare('UPDATE users SET remember_token = NULL, remember_expiry = NULL WHERE id = ?');
            $stmt->execute([$userId]);
        } catch (Exception $e) {
            // Silent fail
        }
    }

    /**
     * Try auto-login dari remember cookie
     */
    private static function tryRememberLogin(): void
    {
        $cookie = $_COOKIE[self::REMEMBER_COOKIE] ?? null;
        if (!$cookie) return;

        $decoded = base64_decode($cookie, true);
        if (!$decoded || !str_contains($decoded, ':')) return;

        [$userId, $token] = explode(':', $decoded, 2);
        $userId = (int) $userId;

        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                SELECT id, role, remember_token, remember_expiry, is_active 
                FROM users 
                WHERE id = ? AND remember_token IS NOT NULL
            ');
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (!$user) {
                self::clearRemember($userId);
                return;
            }

            // Validate expiry
            if (strtotime($user['remember_expiry']) < time()) {
                self::clearRemember($userId);
                return;
            }

            // Validate token hash
            if (!hash_equals($user['remember_token'], hash('sha256', $token))) {
                self::clearRemember($userId);
                return;
            }

            // User tidak aktif
            if (!($user['is_active'] ?? 1)) {
                self::clearRemember($userId);
                return;
            }

            // Auto login
            self::login((int) $user['id'], $user['role']);
        } catch (Exception $e) {
            // Silent fail
        }
    }

    /**
     * Logout
     * @param bool $redirect Redirect ke /login (default: true)
     */
    public static function logout(bool $redirect = true): void
    {
        $userId = self::id();

        // Clear remember cookie jika ada
        if ($userId) {
            self::clearRemember($userId);
        }

        // Clear session
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        if ($redirect) {
            redirect('/login');
        }
    }

    /**
     * Check apakah user sudah login
     */
    public static function check(): bool
    {
        return isset($_SESSION[self::SESSION_KEY]['id']);
    }

    /**
     * Check apakah user adalah guest (belum login)
     */
    public static function guest(): bool
    {
        return !self::check();
    }

    /**
     * Get user ID
     */
    public static function id(): ?int
    {
        return isset($_SESSION[self::SESSION_KEY]['id'])
            ? (int) $_SESSION[self::SESSION_KEY]['id']
            : null;
    }

    /**
     * Get user role
     */
    public static function role(): ?string
    {
        return $_SESSION[self::SESSION_KEY]['role'] ?? null;
    }

    /**
     * Get session data lengkap
     */
    public static function user(): ?array
    {
        return $_SESSION[self::SESSION_KEY] ?? null;
    }

    /**
     * Get specific session data
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[self::SESSION_KEY][$key] ?? $default;
    }

    /**
     * Set extra session data
     */
    public static function set(string $key, mixed $value): void
    {
        if (self::check()) {
            $_SESSION[self::SESSION_KEY][$key] = $value;
        }
    }

    /**
     * Check apakah user memiliki salah satu role yang diizinkan
     */
    public static function hasRole(string ...$allowedRoles): bool
    {
        return self::check() && in_array(self::role(), $allowedRoles, true);
    }

    /**
     * Require login - untuk web pages (redirect jika tidak login)
     */
    public static function requireLogin(string $redirectTo = '/login'): void
    {
        if (!self::check()) {
            // Simpan URL yang dituju untuk redirect setelah login
            $_SESSION['intended_url'] = current_url();
            flash_set('warning', 'Silakan login terlebih dahulu untuk melanjutkan.');
            redirect($redirectTo);
        }
    }

    /**
     * Require guest - untuk halaman login/register (redirect jika sudah login)
     */
    public static function requireGuest(string $redirectTo = '/'): void
    {
        if (self::check()) {
            redirect($redirectTo);
        }
    }

    /**
     * Require specific role - untuk web pages (redirect dengan pesan error)
     */
    public static function requireRole(string ...$allowedRoles): void
    {
        // Jika belum login, redirect ke login
        if (!self::check()) {
            $_SESSION['intended_url'] = current_url();
            flash_set('warning', 'Silakan login terlebih dahulu untuk melanjutkan.');
            redirect('/login');
        }

        // Jika role tidak diizinkan
        if (!in_array(self::role(), $allowedRoles, true)) {
            // Untuk AJAX request, return JSON
            if (is_ajax()) {
                json_response(['error' => 'Forbidden. Anda tidak memiliki akses.'], 403);
            }

            // Untuk web request, redirect dengan pesan error
            flash_set('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
            
            // Redirect ke dashboard sesuai role
            $role = self::role();
            $fallback = match ($role) {
                'admin'    => '/admin/dashboard',
                'lecturer' => '/lecturer/dashboard',
                'student'  => '/student/dashboard',
                default    => '/',
            };
            redirect($fallback);
        }
    }

    /**
     * Require role untuk API endpoint (return JSON)
     */
    public static function requireApiRole(string ...$allowedRoles): void
    {
        if (!self::check()) {
            json_response(['error' => 'Unauthorized. Silakan login.'], 401);
        }

        if (!in_array(self::role(), $allowedRoles, true)) {
            json_response(['error' => 'Forbidden. Anda tidak memiliki akses.'], 403);
        }
    }

    /**
     * Check apakah request ini berasal dari device yang sama saat login
     * (untuk deteksi session hijacking)
     */
    public static function verifyDevice(): bool
    {
        if (!self::check()) return false;

        $loginIp = $_SESSION[self::SESSION_KEY]['ip'] ?? null;
        $loginAgent = $_SESSION[self::SESSION_KEY]['user_agent'] ?? null;

        if ($loginIp !== client_ip()) return false;
        if ($loginAgent !== user_agent()) return false;

        return true;
    }

    /**
     * Get session duration (dalam detik)
     */
    public static function sessionAge(): int
    {
        $loginTime = $_SESSION[self::SESSION_KEY]['login_time'] ?? 0;
        return time() - $loginTime;
    }

    /**
     * Get remaining session time (dalam detik)
     */
    public static function sessionRemaining(): int
    {
        $lastActivity = $_SESSION[self::SESSION_KEY]['last_activity'] ?? 0;
        return max(0, self::SESSION_LIFETIME - (time() - $lastActivity));
    }
}