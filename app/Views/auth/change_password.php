<?php
declare(strict_types=1);
$flashError = $_SESSION['flash_error'] ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

$userName = $_SESSION['simbita_user']['name'] ?? 'User';
$userRole = Auth::role();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Password - <?= e(APP_NAME) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .card {
            background: white;
            padding: 2.5rem;
            border-radius: 1.25rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 460px;
        }
        .card-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .card-header i {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 0.75rem;
        }
        .card-header h2 {
            font-size: 1.5rem;
            color: #111827;
            margin-bottom: 0.35rem;
        }
        .card-header p {
            color: #6b7280;
            font-size: 0.9rem;
        }
        .alert {
            padding: 0.875rem 1rem;
            border-radius: 0.75rem;
            margin-bottom: 1.25rem;
            font-size: 0.875rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .alert-error { background: #fee2e2; color: #991b1b; border-left: 4px solid #ef4444; }
        .alert-success { background: #d1fae5; color: #065f46; border-left: 4px solid #10b981; }
        .field { margin-bottom: 1.25rem; }
        .field label {
            display: block;
            font-weight: 600;
            font-size: 0.875rem;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        .input-wrap {
            position: relative;
        }
        .input-wrap i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 1.05rem;
        }
        .input-wrap input {
            width: 100%;
            padding: 0.85rem 1rem 0.85rem 2.75rem;
            border: 1.5px solid #e5e7eb;
            border-radius: 0.75rem;
            font-size: 0.95rem;
            font-family: inherit;
            transition: all 0.25s;
        }
        .input-wrap input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.12);
        }
        .toggle-pwd {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            font-size: 1.05rem;
        }
        .toggle-pwd:hover { color: #667eea; }
        .btn {
            width: 100%;
            padding: 0.9rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 0.75rem;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s;
            font-family: inherit;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4); }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.25rem;
            color: #6b7280;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .back-link:hover { color: #667eea; }
        .pwd-requirements {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 0.875rem;
            margin-bottom: 1.25rem;
            font-size: 0.8rem;
            color: #6b7280;
        }
        .pwd-requirements strong { color: #374151; display: block; margin-bottom: 0.35rem; }
        .pwd-requirements ul { list-style: none; padding-left: 0; }
        .pwd-requirements li { padding: 0.15rem 0; display: flex; align-items: center; gap: 0.4rem; }
        .pwd-requirements li i { color: #10b981; font-size: 0.75rem; }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
            <i class="bi bi-key-fill"></i>
            <h2>Ubah Password</h2>
            <p>Halo, <?= e($userName) ?>! Perbarui password akun Anda.</p>
        </div>

        <?php if ($flashError): ?>
            <div class="alert alert-error">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span><?= e($flashError) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($flashSuccess): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill"></i>
                <span><?= $flashSuccess ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/change-password">
            <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">

            <div class="field">
                <label for="current_password">Password Saat Ini</label>
                <div class="input-wrap">
                    <i class="bi bi-lock-fill"></i>
                    <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
                    <button type="button" class="toggle-pwd" onclick="togglePwd('current_password')"><i class="bi bi-eye-fill"></i></button>
                </div>
            </div>

            <div class="field">
                <label for="new_password">Password Baru</label>
                <div class="input-wrap">
                    <i class="bi bi-shield-lock-fill"></i>
                    <input type="password" id="new_password" name="new_password" required autocomplete="new-password">
                    <button type="button" class="toggle-pwd" onclick="togglePwd('new_password')"><i class="bi bi-eye-fill"></i></button>
                </div>
            </div>

            <div class="field">
                <label for="confirm_password">Konfirmasi Password Baru</label>
                <div class="input-wrap">
                    <i class="bi bi-shield-check"></i>
                    <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password">
                    <button type="button" class="toggle-pwd" onclick="togglePwd('confirm_password')"><i class="bi bi-eye-fill"></i></button>
                </div>
            </div>

            <div class="pwd-requirements">
                <strong><i class="bi bi-info-circle"></i> Persyaratan Password:</strong>
                <ul>
                    <li><i class="bi bi-check-circle-fill"></i> Minimal 6 karakter</li>
                    <li><i class="bi bi-check-circle-fill"></i> Kombinasi huruf & angka disarankan</li>
                    <li><i class="bi bi-check-circle-fill"></i> Tidak boleh sama dengan password lama</li>
                </ul>
            </div>

            <button type="submit" class="btn">
                <i class="bi bi-shield-lock-fill"></i>
                <span>Simpan Password Baru</span>
            </button>
        </form>

        <a href="javascript:history.back()" class="back-link">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <script>
    function togglePwd(id) {
        const input = document.getElementById(id);
        input.type = input.type === 'password' ? 'text' : 'password';
    }
    </script>
</body>
</html>