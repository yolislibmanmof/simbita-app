<?php
declare(strict_types=1);

class Auth
{
    private const SESSION_KEY = 'simbita_user';

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
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
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}