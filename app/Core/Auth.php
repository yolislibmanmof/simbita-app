<?php
declare(strict_types=1);

class Auth
{
    private const SESSION_KEY = 'simbita_user';
    private const REMEMBER_COOKIE = 'simbita_remember';

    private static function rememberCookieOptions(int $expires): array
    {
        return [
            'expires' => $expires,
            'path' => '/',
            'secure' => defined('IS_HTTPS') && IS_HTTPS,
            'httponly' => true,
            'samesite' => 'Lax',
        ];
    }

    private static function clearRememberCookie(): void
    {
        setcookie(
            self::REMEMBER_COOKIE,
            '',
            self::rememberCookieOptions(time() - 3600)
        );

        unset($_COOKIE[self::REMEMBER_COOKIE]);
    }

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');

            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'secure' => defined('IS_HTTPS') && IS_HTTPS,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);

            session_start();
        }

        if (self::check() || !isset($_COOKIE[self::REMEMBER_COOKIE])) {
            return;
        }

        $token = $_COOKIE[self::REMEMBER_COOKIE];

        if (!is_string($token) || !preg_match('/\A[a-f0-9]{64}\z/i', $token)) {
            self::clearRememberCookie();
            return;
        }

        $db = Database::connection();
        $stmt = $db->prepare(
            'SELECT id, name, email, role
             FROM users
             WHERE remember_token = ? AND is_active = 1
             LIMIT 1'
        );
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if (!$user) {
            self::clearRememberCookie();
            return;
        }

        session_regenerate_id(true);

        $_SESSION[self::SESSION_KEY] = [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];
    }

    public static function check(): bool
    {
        return isset($_SESSION[self::SESSION_KEY]);
    }

    public static function id(): ?int
    {
        return isset($_SESSION[self::SESSION_KEY]['id'])
            ? (int)$_SESSION[self::SESSION_KEY]['id']
            : null;
    }

    public static function role(): ?string
    {
        return $_SESSION[self::SESSION_KEY]['role'] ?? null;
    }

    public static function user(): ?array
    {
        return $_SESSION[self::SESSION_KEY] ?? null;
    }

    public static function requireRole(string ...$roles): void
    {
        if (!self::check() || !in_array(self::role(), $roles, true)) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    public static function logout(): void
    {
        $userId = self::id();

        if ($userId !== null) {
            $db = Database::connection();
            $stmt = $db->prepare(
                'UPDATE users SET remember_token = NULL WHERE id = ?'
            );
            $stmt->execute([$userId]);
        }

        self::clearRememberCookie();

        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            session_destroy();

            setcookie(
                session_name(),
                '',
                session_get_cookie_params()
                    + ['expires' => time() - 42000]
            );
        }

        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}