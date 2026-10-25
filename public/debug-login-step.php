<?php
declare(strict_types=1);

// Muat semua dependency
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../app/Core/Helpers.php';
require __DIR__ . '/../app/Core/Csrf.php';
require __DIR__ . '/../app/Core/Auth.php';
require __DIR__ . '/../app/Models/User.php';

// Ganti dengan kredensial Anda
$testEmail = 'admin@unimof.ac.id';
$testPassword = 'Admin@12345';

echo "<h2>🔍 Debug Login Step-by-Step</h2><pre style='background:#f4f4f4; padding:1rem; border-radius:8px;'>";

echo "1. Mencari user: {$testEmail}\n";
$user = User::findByEmail($testEmail);

if (!$user) {
    echo "   ❌ GAGAL: User tidak ditemukan di database.\n";
    echo "   Tips: Pastikan email tepat dan tabel 'users' sudah di-seed.\n";
    exit;
}
echo "   ✅ User ditemukan!\n";
echo "   - ID: {$user['id']}\n";
echo "   - Role: {$user['role']}\n";
echo "   - Is Active: " . ($user['is_active'] ? 'Ya' : 'Tidak') . "\n";

echo "\n2. Memverifikasi password...\n";
if (!User::verifyPassword($testPassword, $user['password_hash'])) {
    echo "   ❌ GAGAL: Password salah.\n";
    echo "   Hash di DB: {$user['password_hash']}\n";
    echo "   Tips: Jalankan ulang script reset password jika perlu.\n";
    exit;
}
echo "   ✅ Password BENAR!\n";

echo "\n3. Memulai Session...\n";
Auth::start();
echo "   ✅ Session dimulai (Session ID: " . session_id() . ")\n";

echo "\n4. Melakukan Auth::login()...\n";
Auth::login((int)$user['id'], $user['role']);
echo "   ✅ Auth::login() berhasil.\n";

echo "\n5. Menyimpan data ke session manual...\n";
$_SESSION['simbita_user'] = [
    'id' => $user['id'],
    'name' => $user['name'],
    'email' => $user['email'],
    'role' => $user['role']
];
echo "   ✅ Session 'simbita_user' tersimpan.\n";

echo "\n6. Mengecek Auth::check()...\n";
if (Auth::check()) {
    echo "   ✅ SUKSES: Auth::check() bernilai TRUE. Login berhasil!\n";
} else {
    echo "   ❌ GAGAL: Auth::check() bernilai FALSE. Session tidak tersimpan!\n";
    echo "   Tips: Cek apakah browser Anda memblokir cookie, atau cek konfigurasi session di php.ini.\n";
}

echo "\n7. Testing Redirect...\n";
$dashboardUrl = BASE_URL . '/admin/dashboard';
echo "   Seharusnya redirect ke: {$dashboardUrl}\n";
echo "   <a href='{$dashboardUrl}' style='display:inline-block; margin-top:10px; padding:10px 20px; background:#667eea; color:white; text-decoration:none; border-radius:5px;'>👉 KLIK DI SINI UNTUK MASUK DASHBOARD</a>\n";

echo "</pre>";