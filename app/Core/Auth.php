<?php
declare(strict_types=1);

class Auth
{
    private const SESSION_KEY = 'simbita_user';

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_start();
        }

        // Cek cookie "Remember Me" jika session belum ada
        if (!self::check() && isset($_COOKIE['simbita_remember'])) {
            $token = $_COOKIE['simbita_remember'];
            $db = Database::connection();
            $stmt = $db->prepare('SELECT id, name, email, role FROM users WHERE remember_token = ? AND is_active = 1');
            $stmt->execute([$token]);
            $user = $stmt->fetch();
            
            if ($user) {
                $_SESSION['simbita_user'] = [
                    'id' => (int)$user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ];
            } else {
                // Token tidak valid, hapus cookie
                setcookie('simbita_remember', '', time() - 3600, '/');
            }
        }
    }

    public static function check(): bool
    {
        return isset($_SESSION[self::SESSION_KEY]);
    }

    public static function id(): ?int
    {
        return $_SESSION[self::SESSION_KEY]['id'] ?? null;
    }

    public static function role(): ?string
    {
        return $_SESSION[self::SESSION_KEY]['role'] ?? null;
    }

    public static function user(): ?array
    {
        return $_SESSION[self::SESSION_KEY] ?? null;
    }

    public static function requireRole(string $role): void
    {
        if (!self::check() || self::role() !== $role) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    public static function logout(): void
    {
        // Hapus cookie Remember Me jika ada
        if (isset($_COOKIE['simbita_remember'])) {
            if (self::check()) {
                $db = Database::connection();
                $stmt = $db->prepare('UPDATE users SET remember_token = NULL WHERE id = ?');
                $stmt->execute([self::id()]);
            }
            setcookie('simbita_remember', '', time() - 3600, '/');
        }
        
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}