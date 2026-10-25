<?php
declare(strict_types=1);

// WAJIB: Load config.php DULU sebelum database.php
require dirname(__DIR__) . '/config/config.php';
require dirname(__DIR__) . '/config/database.php';

// Password yang ingin di-test
$password = 'Admin@12345';

echo "<h2>🔐 SIMBITA - Password Tester</h2>";
echo "<pre>";

// 1. Generate hash baru
$hashBaru = password_hash($password, PASSWORD_BCRYPT);
echo "Password: " . $password . "\n";
echo "Hash Baru: " . $hashBaru . "\n\n";

// 2. Cek user admin di database
try {
    $db = Database::connection();
    $stmt = $db->query('SELECT id, email, password_hash, role FROM users WHERE role = "admin" LIMIT 1');
    $admin = $stmt->fetch();

    if (!$admin) {
        echo "❌ ERROR: Tidak ada user admin di database!\n";
        echo "Silakan jalankan SQL berikut di phpMyAdmin:\n\n";
        echo "INSERT INTO users (name, email, password_hash, role, is_active)\n";
        echo "VALUES ('Administrator', 'admin@simbita.local', '" . $hashBaru . "', 'admin', 1);\n";
        exit;
    }

    echo "User ditemukan:\n";
    echo "  ID: " . $admin['id'] . "\n";
    echo "  Email: " . $admin['email'] . "\n";
    echo "  Role: " . $admin['role'] . "\n";
    echo "  Hash di DB: " . $admin['password_hash'] . "\n\n";

    // 3. Test verifikasi password
    $verify = password_verify($password, $admin['password_hash']);
    echo "Test password_verify: " . ($verify ? '✅ BERHASIL' : '❌ GAGAL') . "\n\n";

    // 4. Update password jika gagal
    if (!$verify) {
        $stmt = $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $stmt->execute([$hashBaru, $admin['id']]);
        echo "✅ Password berhasil diupdate dengan hash baru!\n";
        echo "✅ Hash baru yang disimpan: " . $hashBaru . "\n\n";
        
        // Verifikasi ulang
        $verify2 = password_verify($password, $hashBaru);
        echo "Verifikasi ulang: " . ($verify2 ? '✅ BERHASIL' : '❌ GAGAL') . "\n";
    } else {
        echo "✅ Password sudah valid, tidak perlu update.\n";
    }

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "</pre>";
echo "<hr>";
echo "<p><a href='" . BASE_URL . "/login'>← Kembali ke Login</a></p>";