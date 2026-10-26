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

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

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
            setcookie('simbita_remember', $token, time() + REMEMBER_LIFETIME, '/');
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
        Auth::logout(); // Menggunakan method Auth::logout() yang sudah di-update
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
            redirect('/login');
        }
        
        $email = trim($_POST['email'] ?? '');
        $user = User::findByEmail($email);
        
        // Best practice keamanan: Selalu tampilkan pesan sukses agar tidak bisa menebak email terdaftar
        if ($user) {
            $token = bin2hex(random_bytes(16));
            $db = Database::connection();
            // Simpan token di kolom remember_token (sebagai simulasi reset token)
            $stmt = $db->prepare('UPDATE users SET remember_token = ? WHERE id = ?');
            $stmt->execute([$token, $user['id']]);
            
            $_SESSION['flash_success'] = "Link reset password telah dikirim ke {$email}.<br><strong>(MODE DEMO: Token reset Anda adalah: <code>{$token}</code>)</strong>";
        } else {
            $_SESSION['flash_success'] = "Jika email terdaftar, link reset password telah dikirim.";
        }
        
        redirect('/login');
    }
}