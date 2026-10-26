<?php
declare(strict_types=1);
$flashError   = $_SESSION['flash_error']   ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashWarning = $_SESSION['flash_warning'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success'], $_SESSION['flash_warning']);

$oldEmail = $_POST['email'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= e(APP_NAME) ?></title>
    <meta name="description" content="Masuk ke Sistem Informasi Bimbingan Tugas Akhir SIMBITA">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        /* =========================================================
           LOGIN PAGE ULTRA PREMIUM - SELF-CONTAINED STYLES
           Namespace: .lg- | Tema: Purple-Blue Gradient
           ========================================================= */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --lg-primary: #667eea;
            --lg-secondary: #764ba2;
            --lg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --lg-gradient-hover: linear-gradient(135deg, #5568d3 0%, #654a91 100%);
            --lg-glow: 0 20px 60px -10px rgba(102, 126, 234, 0.5);
            --lg-radius: 1.25rem;
            --lg-radius-sm: 0.75rem;
        }

        html, body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        body {
            background: var(--lg-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative;
            overflow-x: hidden;
        }

        /* --- Animated Background Shapes --- */
        body::before,
        body::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            filter: blur(80px);
            opacity: 0.4;
            animation: lgFloat 12s ease-in-out infinite;
        }

        body::before {
            width: 500px; height: 500px;
            background: radial-gradient(circle, #ec4899 0%, transparent 70%);
            top: -10%; right: -10%;
        }

        body::after {
            width: 600px; height: 600px;
            background: radial-gradient(circle, #06b6d4 0%, transparent 70%);
            bottom: -15%; left: -15%;
            animation-delay: -6s;
            animation-duration: 15s;
        }

        @keyframes lgFloat {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(40px, -40px) scale(1.1); }
            66% { transform: translate(-30px, 30px) scale(0.95); }
        }

        /* --- Main Container --- */
        .lg-wrapper {
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 2;
            animation: lgFadeUp 0.7s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes lgFadeUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* --- Logo / Brand --- */
        .lg-brand {
            text-align: center;
            margin-bottom: 2rem;
            color: white;
        }

        .lg-brand-icon {
            width: 64px; height: 64px;
            margin: 0 auto 1rem;
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            animation: lgPulseIcon 3s ease-in-out infinite;
        }

        @keyframes lgPulseIcon {
            0%, 100% { transform: scale(1); box-shadow: 0 10px 30px rgba(0,0,0,0.15); }
            50% { transform: scale(1.05); box-shadow: 0 15px 40px rgba(0,0,0,0.25); }
        }

        .lg-brand-title {
            font-size: 1.75rem;
            font-weight: 800;
            margin-bottom: 0.35rem;
            letter-spacing: -0.02em;
            text-shadow: 0 2px 20px rgba(0,0,0,0.2);
        }

        .lg-brand-subtitle {
            font-size: 0.925rem;
            opacity: 0.9;
            font-weight: 500;
        }

        /* --- Card (Glassmorphism) --- */
        .lg-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: var(--lg-radius);
            padding: 2.5rem;
            box-shadow: 
                0 30px 60px -15px rgba(0, 0, 0, 0.3),
                0 0 0 1px rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .lg-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: var(--lg-gradient);
            z-index: 1;
        }

        .lg-card-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-top: 0.5rem;
        }

        .lg-card-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: #111827;
            margin-bottom: 0.35rem;
            letter-spacing: -0.01em;
        }

        .lg-card-subtitle {
            font-size: 0.9rem;
            color: #6b7280;
            font-weight: 500;
        }

        /* --- Alerts --- */
        .lg-alert {
            padding: 0.875rem 1rem;
            border-radius: var(--lg-radius-sm);
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 0.875rem;
            font-weight: 500;
            animation: lgShake 0.4s ease;
            border-left: 4px solid;
        }

        .lg-alert i { font-size: 1.1rem; flex-shrink: 0; }

        .lg-alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left-color: #ef4444;
        }

        .lg-alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left-color: #10b981;
        }

        .lg-alert-warning {
            background: #fef3c7;
            color: #92400e;
            border-left-color: #f59e0b;
        }

        @keyframes lgShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-6px); }
            75% { transform: translateX(6px); }
        }

        /* --- Form --- */
        .lg-form { position: relative; z-index: 2; }

        .lg-field { margin-bottom: 1.25rem; }

        .lg-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-weight: 600;
            font-size: 0.85rem;
            color: #374151;
        }

        .lg-label-text {
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .lg-label-text i { color: #6b7280; font-size: 0.95rem; }

        .lg-input-wrap {
            position: relative;
        }

        .lg-input-wrap .lg-input-icon {
            position: absolute;
            left: 1rem; top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 1.05rem;
            pointer-events: none;
            transition: color 0.25s;
            z-index: 2;
        }

        .lg-input {
            width: 100%;
            padding: 0.85rem 1rem 0.85rem 2.75rem;
            border: 1.5px solid #e5e7eb;
            border-radius: var(--lg-radius-sm);
            font-size: 0.95rem;
            font-family: inherit;
            font-weight: 500;
            background: white;
            transition: all 0.25s;
            color: #111827;
        }

        .lg-input:hover { border-color: #d1d5db; }

        .lg-input:focus {
            outline: none;
            border-color: var(--lg-primary);
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.12);
        }

        .lg-input:focus + .lg-input-icon { color: var(--lg-primary); }

        .lg-input::placeholder { color: #9ca3af; font-weight: 400; }

        /* Password toggle */
        .lg-password-toggle {
            position: absolute;
            right: 1rem; top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            padding: 0.25rem;
            font-size: 1.05rem;
            transition: color 0.25s;
            z-index: 2;
        }

        .lg-password-toggle:hover { color: var(--lg-primary); }

        .lg-input-password { padding-right: 2.75rem; }

        /* --- Remember Me + Forgot --- */
        .lg-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
        }

        .lg-checkbox {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            color: #4b5563;
            font-weight: 500;
            user-select: none;
        }

        .lg-checkbox input {
            width: 18px; height: 18px;
            cursor: pointer;
            accent-color: var(--lg-primary);
            flex-shrink: 0;
        }

        .lg-forgot {
            color: var(--lg-primary);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .lg-forgot:hover { color: var(--lg-secondary); text-decoration: underline; }

        /* --- Submit Button --- */
        .lg-submit {
            width: 100%;
            padding: 0.9rem;
            background: var(--lg-gradient);
            color: white;
            border: none;
            border-radius: var(--lg-radius-sm);
            font-size: 0.95rem;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 0 10px 20px -5px rgba(102, 126, 234, 0.5);
            position: relative;
            overflow: hidden;
        }

        .lg-submit:hover:not(:disabled) {
            background: var(--lg-gradient-hover);
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -5px rgba(102, 126, 234, 0.6);
        }

        .lg-submit:active:not(:disabled) { transform: translateY(0); }

        .lg-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .lg-submit i { font-size: 1.1rem; transition: transform 0.3s; }
        .lg-submit:hover:not(:disabled) i { transform: translateX(4px); }

        .lg-spinner {
            display: none;
            width: 18px; height: 18px;
            border: 2.5px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: lgSpin 0.7s linear infinite;
        }

        .lg-submit.is-loading .lg-spinner { display: inline-block; }
        .lg-submit.is-loading .lg-btn-text { opacity: 0.8; }
        .lg-submit.is-loading i.bi-arrow-right { display: none; }

        @keyframes lgSpin { to { transform: rotate(360deg); } }

        /* --- Demo Credentials Box --- */
        .lg-demo-box {
            margin-top: 1.5rem;
            padding: 1rem 1.15rem;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 1px solid #fcd34d;
            border-radius: var(--lg-radius-sm);
            position: relative;
            overflow: hidden;
        }

        .lg-demo-box::before {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 80px; height: 100%;
            background: radial-gradient(circle at right, rgba(245,158,11,0.2) 0%, transparent 70%);
        }

        .lg-demo-title {
            font-size: 0.72rem;
            font-weight: 800;
            color: #92400e;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.35rem;
            position: relative;
            z-index: 2;
        }

        .lg-demo-list {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            position: relative;
            z-index: 2;
        }

        .lg-demo-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.4rem 0.6rem;
            background: rgba(255,255,255,0.55);
            border-radius: 0.4rem;
            font-size: 0.8rem;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid transparent;
        }

        .lg-demo-item:hover {
            background: white;
            border-color: #fcd34d;
            transform: translateX(2px);
        }

        .lg-demo-role {
            font-weight: 700;
            color: #78350f;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            flex-shrink: 0;
        }

        .lg-demo-role i { font-size: 0.9rem; }

        .lg-demo-cred {
            font-family: 'JetBrains Mono', 'Courier New', monospace;
            font-size: 0.72rem;
            color: #92400e;
            font-weight: 500;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* --- Footer --- */
        .lg-footer {
            text-align: center;
            margin-top: 1.75rem;
            padding-top: 1.25rem;
            border-top: 1px solid #f3f4f6;
            font-size: 0.82rem;
            color: #6b7280;
        }

        .lg-footer a {
            color: var(--lg-primary);
            text-decoration: none;
            font-weight: 600;
        }

        .lg-footer a:hover { text-decoration: underline; }

        .lg-copyright {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.75);
            font-weight: 500;
        }

        .lg-copyright i { color: #ec4899; }

        /* --- Responsive --- */
        @media (max-width: 480px) {
            body { padding: 1rem; }
            .lg-card { padding: 1.75rem 1.5rem; }
            .lg-brand-title { font-size: 1.5rem; }
            .lg-options { flex-direction: column; gap: 0.75rem; align-items: flex-start; }
            .lg-demo-item { flex-direction: column; align-items: flex-start; gap: 0.25rem; }
        }

        /* --- Focus Visible (Accessibility) --- */
        .lg-input:focus-visible,
        .lg-submit:focus-visible,
        .lg-forgot:focus-visible {
            outline: 2px solid var(--lg-primary);
            outline-offset: 2px;
        }
    </style>
</head>
<body>
    <div class="lg-wrapper">
        <!-- Brand -->
        <div class="lg-brand">
            <div class="lg-brand-icon">
                <i class="bi bi-book-half"></i>
            </div>
            <h1 class="lg-brand-title"><?= e(APP_NAME) ?></h1>
            <p class="lg-brand-subtitle">Sistem Informasi Bimbingan Tugas Akhir</p>
        </div>

        <!-- Login Card -->
        <div class="lg-card">
            <div class="lg-card-header">
                <h2 class="lg-card-title">Selamat Datang Kembali</h2>
                <p class="lg-card-subtitle">Silakan masuk ke akun Anda untuk melanjutkan</p>
            </div>

            <!-- Flash Messages -->
            <?php if ($flashError): ?>
                <div class="lg-alert lg-alert-error">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span><?= e($flashError) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($flashSuccess): ?>
                <div class="lg-alert lg-alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <span><?= e($flashSuccess) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($flashWarning): ?>
                <div class="lg-alert lg-alert-warning">
                    <i class="bi bi-info-circle-fill"></i>
                    <span><?= e($flashWarning) ?></span>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form method="POST" action="<?= e(BASE_URL) ?>/login" class="lg-form" id="loginForm" autocomplete="on">
                <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">

                <!-- Email -->
                <div class="lg-field">
                    <label class="lg-label" for="email">
                        <span class="lg-label-text">
                            <i class="bi bi-envelope-fill"></i>
                            Email
                        </span>
                    </label>
                    <div class="lg-input-wrap">
                        <input 
                            type="email" 
                            class="lg-input" 
                            id="email" 
                            name="email"
                            placeholder="nama@email.com"
                            value="<?= e($oldEmail) ?>"
                            required 
                            autofocus
                            autocomplete="email"
                        >
                        <i class="bi bi-person-fill lg-input-icon"></i>
                    </div>
                </div>

                <!-- Password -->
                <div class="lg-field">
                    <label class="lg-label" for="password">
                        <span class="lg-label-text">
                            <i class="bi bi-lock-fill"></i>
                            Password
                        </span>
                        <a href="<?= BASE_URL ?>/forgot-password" class="lg-forgot">Lupa password?</a>
                    </label>
                    <div class="lg-input-wrap">
                        <input 
                            type="password" 
                            class="lg-input lg-input-password" 
                            id="password" 
                            name="password"
                            placeholder="Masukkan password Anda"
                            required
                            autocomplete="current-password"
                        >
                        <i class="bi bi-shield-lock-fill lg-input-icon"></i>
                        <button type="button" class="lg-password-toggle" id="togglePassword" title="Tampilkan password" aria-label="Toggle password visibility">
                            <i class="bi bi-eye-fill"></i>
                        </button>
                    </div>
                </div>

                <!-- Options -->
                <div class="lg-options">
                    <label class="lg-checkbox">
                        <input type="checkbox" name="remember" value="1">
                        <span>Ingat saya</span>
                    </label>
                </div>

                <!-- Submit -->
                <button type="submit" class="lg-submit" id="submitBtn">
                    <span class="lg-spinner"></span>
                    <span class="lg-btn-text">Masuk ke Akun</span>
                    <i class="bi bi-arrow-right"></i>
                </button>
            </form>

            <!-- Demo Credentials -->
            <?php if (defined('APP_DEBUG') && APP_DEBUG): ?>
            <div class="lg-demo-box">
                <div class="lg-demo-title">
                    <i class="bi bi-key-fill"></i>
                    Akun Demo (klik untuk isi otomatis)
                </div>
                <div class="lg-demo-list">
                    <div class="lg-demo-item" data-email="admin@simbita.id" data-password="admin123">
                        <span class="lg-demo-role"><i class="bi bi-shield-fill-check"></i> Admin</span>
                        <span class="lg-demo-cred">admin@simbita.id / admin123</span>
                    </div>
                    <div class="lg-demo-item" data-email="dosen@simbita.id" data-password="dosen123">
                        <span class="lg-demo-role"><i class="bi bi-person-workspace"></i> Dosen</span>
                        <span class="lg-demo-cred">dosen@simbita.id / dosen123</span>
                    </div>
                    <div class="lg-demo-item" data-email="mahasiswa@simbita.id" data-password="mhs123">
                        <span class="lg-demo-role"><i class="bi bi-mortarboard-fill"></i> Mahasiswa</span>
                        <span class="lg-demo-cred">mahasiswa@simbita.id / mhs123</span>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Footer -->
            <div class="lg-footer">
                Butuh bantuan? <a href="#">Hubungi Admin</a>
            </div>
        </div>

        <!-- Copyright -->
        <p class="lg-copyright">
            © <?= date('Y') ?> <?= e(APP_NAME) ?> · Dibuat dengan <i class="bi bi-heart-fill"></i> untuk akademik yang lebih baik
        </p>
    </div>

    <script>
    (function() {
        'use strict';

        // ===== PASSWORD TOGGLE =====
        const toggleBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        if (toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', () => {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                toggleBtn.innerHTML = isPassword 
                    ? '<i class="bi bi-eye-slash-fill"></i>' 
                    : '<i class="bi bi-eye-fill"></i>';
                toggleBtn.title = isPassword ? 'Sembunyikan password' : 'Tampilkan password';
            });
        }

        // ===== FORM SUBMISSION WITH LOADING =====
        const form = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');

        if (form && submitBtn) {
            form.addEventListener('submit', function(e) {
                const email = form.querySelector('#email').value.trim();
                const password = form.querySelector('#password').value.trim();

                // Basic validation
                if (!email || !password) {
                    e.preventDefault();
                    return;
                }

                // Show loading state
                submitBtn.classList.add('is-loading');
                submitBtn.disabled = true;
                submitBtn.querySelector('.lg-btn-text').textContent = 'Memproses...';
            });
        }

        // ===== DEMO CREDENTIALS AUTO-FILL =====
        document.querySelectorAll('.lg-demo-item').forEach(item => {
            item.addEventListener('click', () => {
                const email = item.dataset.email;
                const password = item.dataset.password;
                
                const emailInput = document.getElementById('email');
                const passwordInput = document.getElementById('password');
                
                if (emailInput && passwordInput) {
                    emailInput.value = email;
                    passwordInput.value = password;
                    passwordInput.focus();
                    
                    // Visual feedback
                    item.style.background = 'white';
                    item.style.borderColor = '#10b981';
                    setTimeout(() => {
                        item.style.background = '';
                        item.style.borderColor = '';
                    }, 1000);
                }
            });
        });

        // ===== ENTER KEY SUBMIT =====
        document.querySelectorAll('.lg-input').forEach(input => {
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    form?.requestSubmit();
                }
            });
        });

        // ===== AUTO-DISMISS ALERTS =====
        document.querySelectorAll('.lg-alert-success, .lg-alert-warning').forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'all 0.3s ease';
                alert.style.opacity = '0';
                alert.style.transform = 'translateX(20px)';
                setTimeout(() => alert.remove(), 300);
            }, 4000);
        });

        console.log('%c✓ SIMBITA Login Page Loaded', 'color: #667eea; font-weight: bold;');
    })();
    </script>
</body>
</html>