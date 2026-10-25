<?php
declare(strict_types=1);

class AuthController
{
    /**
     * Tampilkan halaman login
     */
    public static function showLogin(): void
    {
        // Jika sudah login, redirect ke dashboard sesuai role
        if (Auth::check()) {
            self::redirectToDashboard();
        }
        
        require dirname(__DIR__) . '/Views/auth/login.php';
    }

    /**
     * Proses login (POST request)
     */
    public static function login(): void
    {
        // Pastikan request adalah POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/login');
        }

        // Verifikasi CSRF token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($csrfToken)) {
            $_SESSION['flash_error'] = 'Token keamanan tidak valid. Silakan coba lagi.';
            redirect('/login');
        }

        // Validasi input
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['flash_error'] = 'Email dan password harus diisi.';
            redirect('/login');
        }

        // Cari user di database
        $user = User::findByEmail($email);

        if (!$user) {
            $_SESSION['flash_error'] = 'Email atau password salah.';
            redirect('/login');
        }

        // Cek apakah user aktif
        if (!(int)$user['is_active']) {
            $_SESSION['flash_error'] = 'Akun Anda tidak aktif. Hubungi administrator.';
            redirect('/login');
        }

        // Verifikasi password
        if (!User::verifyPassword($password, $user['password_hash'])) {
            $_SESSION['flash_error'] = 'Email atau password salah.';
            redirect('/login');
        }

        // Login berhasil - buat session
        Auth::login((int)$user['id'], $user['role']);

        // Simpan info user di session untuk navbar
        $_SESSION['simbita_user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ];

        // Update last login
        User::updateLastLogin((int)$user['id']);

        // Log aktivitas
        self::logActivity('login', 'users', (int)$user['id'], 'User berhasil login');

        // Redirect ke dashboard sesuai role
        self::redirectToDashboard();
    }

    /**
     * Proses logout
     */
    public static function logout(): void
    {
        $userId = Auth::id();
        
        // Log aktivitas sebelum session dihapus
        if ($userId) {
            self::logActivity('logout', 'users', $userId, 'User logout');
        }

        Auth::logout();
    }

    /**
     * Redirect ke dashboard sesuai role user
     */
    private static function redirectToDashboard(): void
    {
        $role = Auth::role();
        
        match ($role) {
            'admin' => redirect('/admin/dashboard'),
            'lecturer' => redirect('/lecturer/dashboard'),
            'student' => redirect('/student/dashboard'),
            default => redirect('/login'),
        };
    }

    /**
     * Log aktivitas user ke database
     */
    private static function logActivity(string $action, ?string $table = null, ?int $id = null, ?string $description = null): void
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                INSERT INTO activity_logs (user_id, action, related_table, related_id, description, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                Auth::id(),
                $action,
                $table,
                $id,
                $description,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]);
        } catch (Exception $e) {
            // Log error ke error_log jika gagal menyimpan ke database
            error_log('Failed to log activity: ' . $e->getMessage());
        }
    }
}