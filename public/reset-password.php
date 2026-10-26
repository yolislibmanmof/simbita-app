<?php
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../config/database.php';

$db = Database::connection();

// Generate hash yang BENAR untuk password Admin@12345
$password = 'Admin@12345';
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

echo "<h2>Reset Password SIMBITA</h2>";
echo "<p>Password: <strong>$password</strong></p>";
echo "<p>Hash yang dihasilkan: <code>$hash</code></p>";
echo "<hr>";

// Update SEMUA user dengan password yang benar
$stmt = $db->prepare('UPDATE users SET password_hash = ? WHERE is_active = 1');
$stmt->execute([$hash]);

echo "<p>✅ <strong>" . $stmt->rowCount() . " user</strong> berhasil direset passwordnya.</p>";
echo "<hr>";

// Tampilkan daftar user
$stmt = $db->query('SELECT id, name, email, role FROM users ORDER BY role, name');
echo "<h3>Daftar User:</h3><ul>";
while ($user = $stmt->fetch()) {
    echo "<li><strong>{$user['role']}</strong>: {$user['email']} ({$user['name']})</li>";
}
echo "</ul>";

echo "<p><a href='/simbita-app/public/login'>← Kembali ke Login</a></p>";
echo "<p style='color:red;'><strong>⚠️ PENTING: HAPUS file ini setelah selesai!</strong></p>";