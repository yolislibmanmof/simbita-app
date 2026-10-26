<?php
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - <?= e(APP_NAME) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f3f4f6; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .card { background: white; padding: 2.5rem; border-radius: 1rem; box-shadow: 0 10px 30px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { margin: 0 0 0.5rem 0; color: #111827; font-size: 1.5rem; }
        p { color: #6b7280; font-size: 0.9rem; margin-bottom: 1.5rem; }
        input { width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.9rem; margin-bottom: 1rem; box-sizing: border-box; }
        input:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }
        button { width: 100%; padding: 0.75rem; background: #6366f1; color: white; border: none; border-radius: 0.5rem; font-weight: 600; cursor: pointer; font-size: 0.9rem; }
        button:hover { background: #4f46e5; }
        .back { display: block; text-align: center; margin-top: 1rem; color: #6b7280; text-decoration: none; font-size: 0.85rem; }
        .back:hover { color: #111827; }
        .alert { padding: 0.75rem; border-radius: 0.5rem; margin-bottom: 1rem; font-size: 0.85rem; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    </style>
</head>
<body>
    <div class="card">
        <h2>🔑 Lupa Password?</h2>
        <p>Masukkan email Anda. Kami akan mengirimkan link untuk mereset password.</p>
        
        <?php if ($flashError): ?>
            <div class="alert alert-danger"><?= e($flashError) ?></div>
        <?php endif; ?>
        <?php if ($flashSuccess): ?>
            <div class="alert alert-success"><?= $flashSuccess ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/forgot-password">
            <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
            <input type="email" name="email" placeholder="Alamat Email" required autofocus>
            <button type="submit">Kirim Link Reset</button>
        </form>
        <a href="<?= BASE_URL ?>/login" class="back">← Kembali ke Login</a>
    </div>
</body>
</html>