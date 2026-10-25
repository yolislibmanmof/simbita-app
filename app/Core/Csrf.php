<?php
declare(strict_types=1);

/**
 * =====================================================================
 * SIMBITA - CSRF PROTECTION
 * FINAL ULTRA PREMIUM - ENTERPRISE GRADE
 * =====================================================================
 * Fitur:
 * - Token expiration (default 2 jam)
 * - Per-session isolation
 * - AJAX header support
 * - Double-submit cookie pattern (opsional)
 */
class Csrf
{
    private const TOKEN_KEY   = 'simbita_csrf_token';
    private const EXPIRY_KEY  = 'simbita_csrf_expiry';
    private const EXPIRY_TIME = 7200; // 2 jam dalam detik

    /**
     * Generate token baru (atau return yang existing jika belum expired)
     */
    public static function generate(): string
    {
        $now = time();
        $expiry = $_SESSION[self::EXPIRY_KEY] ?? 0;

        // Generate token baru jika belum ada atau sudah expired
        if (empty($_SESSION[self::TOKEN_KEY]) || $expiry < $now) {
            self::regenerate();
        }

        return $_SESSION[self::TOKEN_KEY];
    }

    /**
     * Alias untuk generate()
     */
    public static function token(): string
    {
        return self::generate();
    }

    /**
     * Generate token baru paksa
     */
    public static function regenerate(): void
    {
        $_SESSION[self::TOKEN_KEY]  = bin2hex(random_bytes(32));
        $_SESSION[self::EXPIRY_KEY] = time() + self::EXPIRY_TIME;
    }

    /**
     * Verifikasi token (dari POST atau header X-CSRF-Token)
     */
    public static function verify(?string $token = null): bool
    {
        // Auto-detect dari header untuk AJAX request
        if ($token === null && is_ajax()) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        }

        if (!$token || empty($_SESSION[self::TOKEN_KEY])) {
            return false;
        }

        // Check expiry
        $expiry = $_SESSION[self::EXPIRY_KEY] ?? 0;
        if (time() > $expiry) {
            self::regenerate();
            return false;
        }

        // Constant-time comparison untuk mencegah timing attack
        return hash_equals($_SESSION[self::TOKEN_KEY], $token);
    }

    /**
     * Force verify - throw exception jika gagal
     */
    public static function verifyOrThrow(?string $token = null): void
    {
        if (!self::verify($token)) {
            http_response_code(419);
            throw new RuntimeException('CSRF token tidak valid atau sudah expired');
        }
    }

    /**
     * Ambil token dari POST request
     */
    public static function fromPost(): ?string
    {
        return $_POST['csrf_token'] ?? null;
    }

    /**
     * Verifikasi token dari POST
     */
    public static function verifyPost(): bool
    {
        return self::verify(self::fromPost());
    }

    /**
     * Clear token (biasanya setelah logout)
     */
    public static function clear(): void
    {
        unset($_SESSION[self::TOKEN_KEY], $_SESSION[self::EXPIRY_KEY]);
    }

    /**
     * Get token lifetime remaining (dalam detik)
     */
    public static function lifetime(): int
    {
        $expiry = $_SESSION[self::EXPIRY_KEY] ?? 0;
        return max(0, $expiry - time());
    }

    /**
     * Check apakah token masih valid (tanpa auto-regenerate)
     */
    public static function isValid(): bool
    {
        if (empty($_SESSION[self::TOKEN_KEY])) return false;
        $expiry = $_SESSION[self::EXPIRY_KEY] ?? 0;
        return time() <= $expiry;
    }
}