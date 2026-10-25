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

        $user = User::findByEmail($email);

        if (!$user || !(int)$user['is_active'] || !password_verify($password, $user['password_hash'])) {
            $_SESSION['flash_error'] = 'Email atau password salah.';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // Pastikan session aktif
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Simpan session
        $_SESSION['simbita_user'] = [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ];

        // Redirect paksa
        header('Location: ' . BASE_URL . '/' . $user['role'] . '/dashboard');
        exit;
    }

    public static function logout(): void
    {
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}