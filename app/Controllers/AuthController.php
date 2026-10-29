<?php
declare(strict_types=1);

class AuthController
{
    public static function showLogin(): void
    {
        if (isset($_SESSION['simbita_user']['role'])) {
            header('Location: ' . BASE_URL . '/' . $_SESSION['simbita_user']['role'] . '/dashboard');
            exit;
        }
        require dirname(__DIR__) . '/Views/auth/login.php';
    }

    public static function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']) && $_POST['remember'] === 'on';
        
        $user = User::findByEmail($email);

        if (!$user || !(int)$user['is_active'] || !password_verify($password, $user['password_hash'])) {
            $_SESSION['flash_error'] = 'Email atau password salah.';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // ✅ REGENERATE SESSION ID (pindahkan ke LUAR array)
        session_regenerate_id(true);

        // ✅ SET SESSION (array sudah benar)
        $_SESSION['simbita_user'] = [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ];

        // Handle Remember Me
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $db = Database::connection();
            $stmt = $db->prepare('UPDATE users SET remember_token = ? WHERE id = ?');
            $stmt->execute([$token, $user['id']]);
            setcookie('simbita_remember', $token, [
                'expires' => time() + REMEMBER_LIFETIME,
                'path' => '/',
                'secure' => defined('IS_HTTPS') && IS_HTTPS,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }

        // Update last login
        $db = Database::connection();
        $stmt = $db->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?');
        $stmt->execute([$user['id']]);

        header('Location: ' . BASE_URL . '/' . $user['role'] . '/dashboard');
        exit;
    }

    public static function logout(): void
    {
        Auth::logout();
    }

    // =================================================================
    // FITUR LUPA PASSWORD
    // =================================================================
    public static function showForgotPassword(): void
    {
        require dirname(__DIR__) . '/Views/auth/forgot_password.php';
    }

    public static function processForgotPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            return;
        }

        $_SESSION['flash_error'] = 'Fitur reset kata sandi sementara dinonaktifkan. Silakan hubungi administrator.';
        redirect('/login');
    }

    // =================================================================
    // FITUR UBAH PASSWORD
    // =================================================================
    public static function showChangePassword(): void
    {
        Auth::requireRole('admin', 'lecturer', 'student');
        require dirname(__DIR__) . '/Views/auth/change_password.php';
    }

    public static function changePassword(): void
    {
        Auth::requireRole('admin', 'lecturer', 'student');
        
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($csrfToken)) {
            $_SESSION['flash_error'] = 'Token keamanan tidak valid.';
            redirect('/change-password');
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['flash_error'] = 'Semua field wajib diisi.';
            redirect('/change-password');
        }

        if (strlen($newPassword) < 6) {
            $_SESSION['flash_error'] = 'Password baru minimal 6 karakter.';
            redirect('/change-password');
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['flash_error'] = 'Konfirmasi password tidak cocok.';
            redirect('/change-password');
        }

        $userId = Auth::id();
        $db = Database::connection();
        $stmt = $db->prepare('SELECT password_hash FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
            $_SESSION['flash_error'] = 'Password saat ini salah.';
            redirect('/change-password');
        }

        $newHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $db->prepare('UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute([$newHash, $userId]);

        $db->prepare('UPDATE users SET remember_token = NULL WHERE id = ?')->execute([$userId]);

        $_SESSION['flash_success'] = '✅ Password berhasil diubah. Silakan login ulang dengan password baru Anda.';
        
        Auth::logout();
    }
}