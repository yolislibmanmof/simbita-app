<?php
declare(strict_types=1);
$isEdit = isset($lecturer);
$title = $isEdit ? 'Edit Dosen' : 'Tambah Dosen';
$data = $isEdit ? $lecturer : [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - <?= e(APP_NAME) ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">
    
    <style>
        /* =========================================================
           LECTURER FORM ULTRA PREMIUM - SCOPED STYLES
           Namespace: .lect-form
           ========================================================= */
        .lect-form { max-width: 100%; }

        /* --- Main Card Premium --- */
        .lect-card {
            background: white;
            border-radius: var(--radius-2xl);
            box-shadow: 0 10px 40px -15px rgba(0,0,0,0.1);
            border: 1px solid var(--gray-200);
            overflow: hidden;
            animation: lectSlideIn 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes lectSlideIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .lect-card-header {
            padding: 1.75rem 2rem;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .lect-card-header::before {
            content: '';
            position: absolute;
            top: -50%; right: -10%;
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }

        .lect-card-header-content {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .lect-card-icon {
            width: 54px; height: 54px;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .lect-card-title {
            font-size: 1.35rem;
            font-weight: 800;
            margin: 0 0 0.25rem 0;
        }

        .lect-card-subtitle {
            font-size: 0.9rem;
            opacity: 0.9;
            margin: 0;
        }

        .lect-card-body { padding: 2rem; }

        /* --- Form Sections dengan Numbering --- */
        .lect-section {
            margin-bottom: 2.5rem;
            padding-bottom: 2rem;
            border-bottom: 1px dashed var(--gray-200);
            animation: lectSectionIn 0.5s ease backwards;
        }

        .lect-section:nth-child(1) { animation-delay: 0.1s; }
        .lect-section:nth-child(2) { animation-delay: 0.2s; }
        .lect-section:nth-child(3) { animation-delay: 0.3s; }
        .lect-section:nth-child(4) { animation-delay: 0.4s; }
        .lect-section:last-child { border-bottom: none; padding-bottom: 0; }

        @keyframes lectSectionIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .lect-section-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.75rem;
        }

        .lect-section-num {
            width: 42px; height: 42px;
            border-radius: var(--radius-lg);
            background: var(--gradient-success);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.1rem;
            flex-shrink: 0;
            box-shadow: 0 8px 15px -5px rgba(16,185,129,0.4);
        }

        .lect-section-info h4 {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--gray-900);
            margin: 0 0 0.15rem 0;
        }

        .lect-section-info p {
            font-size: 0.825rem;
            color: var(--gray-500);
            margin: 0;
        }

        /* --- Form Fields Premium --- */
        .lect-field { margin-bottom: 1.25rem; }

        .lect-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--gray-700);
            font-size: 0.875rem;
        }

        .lect-label-required { color: var(--accent-red); margin-left: 0.25rem; }

        .lect-label-hint {
            font-size: 0.75rem;
            color: var(--gray-500);
            font-weight: 500;
            font-style: italic;
        }

        .lect-input-wrap {
            position: relative;
            display: block;
        }

        .lect-input-wrap .lect-icon {
            position: absolute;
            left: 1rem; top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            font-size: 1.05rem;
            pointer-events: none;
            transition: color 0.3s;
            z-index: 2;
        }

        .lect-input {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 2.75rem;
            border: 1.5px solid var(--gray-300);
            border-radius: var(--radius-lg);
            font-size: 0.95rem;
            font-family: inherit;
            background: white;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            color: var(--gray-800);
        }

        .lect-input:hover { border-color: var(--gray-400); }

        .lect-input:focus {
            outline: none;
            border-color: var(--accent-green);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.12);
        }

        .lect-input:focus + .lect-icon,
        .lect-input-wrap:focus-within .lect-icon {
            color: var(--accent-green);
        }

        .lect-input::placeholder { color: var(--gray-400); }

        .lect-input.is-readonly {
            background: var(--gray-50);
            color: var(--gray-500);
            cursor: not-allowed;
            border-style: dashed;
        }

        .lect-input.is-readonly + .lect-icon { color: var(--gray-400); }

        /* Select Premium */
        .lect-select {
            width: 100%;
            padding: 0.8rem 3rem 0.8rem 2.75rem;
            border: 1.5px solid var(--gray-300);
            border-radius: var(--radius-lg);
            font-size: 0.95rem;
            font-family: inherit;
            background: white;
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            transition: all 0.3s;
            color: var(--gray-800);
        }

        .lect-select:hover { border-color: var(--gray-400); }
        .lect-select:focus {
            outline: none;
            border-color: var(--accent-green);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.12);
        }

        .lect-input-wrap::after {
            content: '';
            position: absolute;
            right: 1.25rem; top: 50%;
            width: 8px; height: 8px;
            border-right: 2px solid var(--gray-500);
            border-bottom: 2px solid var(--gray-500);
            transform: translateY(-70%) rotate(45deg);
            transition: all 0.3s;
            pointer-events: none;
        }

        .lect-input-wrap:focus-within::after {
            transform: translateY(-30%) rotate(225deg);
            border-color: var(--accent-green);
        }

        /* --- Field Helper Text --- */
        .lect-helper {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            margin-top: 0.5rem;
            font-size: 0.78rem;
            color: var(--gray-500);
            padding-left: 0.25rem;
        }

        .lect-helper i { color: var(--primary-500); font-size: 0.9rem; }

        /* --- Grid Layout --- */
        .lect-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.25rem;
        }

        .lect-grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem;
        }

        @media (max-width: 768px) {
            .lect-grid-2, .lect-grid-3 { grid-template-columns: 1fr; }
        }

        /* --- Password Display Box --- */
        .lect-password-box {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 1px solid #fcd34d;
            border-radius: var(--radius-lg);
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.75rem;
        }

        .lect-password-icon {
            width: 40px; height: 40px;
            background: white;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-orange);
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .lect-password-content { flex: 1; min-width: 0; }

        .lect-password-label {
            font-size: 0.75rem;
            color: #92400e;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.25rem;
        }

        .lect-password-value {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1rem;
            font-weight: 700;
            color: #78350f;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .lect-password-copy {
            background: white;
            border: none;
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius-md);
            cursor: pointer;
            color: var(--accent-orange);
            font-size: 0.85rem;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .lect-password-copy:hover {
            background: #fef3c7;
            transform: scale(1.05);
        }

        /* --- Toggle Switch untuk Active --- */
        .lect-toggle-box {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.25rem 1.5rem;
            background: var(--gray-50);
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-xl);
            transition: all 0.3s;
        }

        .lect-toggle-box:hover {
            border-color: var(--accent-green);
            background: #f0fdf4;
        }

        .lect-toggle-switch {
            position: relative;
            width: 52px; height: 28px;
            flex-shrink: 0;
        }

        .lect-toggle-switch input {
            opacity: 0;
            width: 0; height: 0;
        }

        .lect-toggle-slider {
            position: absolute;
            cursor: pointer;
            inset: 0;
            background: var(--gray-300);
            border-radius: 999px;
            transition: 0.3s;
        }

        .lect-toggle-slider::before {
            content: '';
            position: absolute;
            width: 22px; height: 22px;
            left: 3px; bottom: 3px;
            background: white;
            border-radius: 50%;
            transition: 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .lect-toggle-switch input:checked + .lect-toggle-slider {
            background: var(--accent-green);
        }

        .lect-toggle-switch input:checked + .lect-toggle-slider::before {
            transform: translateX(24px);
        }

        .lect-toggle-info { flex: 1; cursor: pointer; }

        .lect-toggle-title {
            font-weight: 700;
            color: var(--gray-800);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.25rem;
        }

        .lect-toggle-status {
            font-size: 0.7rem;
            padding: 0.15rem 0.5rem;
            border-radius: var(--radius-full);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .lect-toggle-status.on { background: #d1fae5; color: #065f46; }
        .lect-toggle-status.off { background: var(--gray-200); color: var(--gray-600); }

        .lect-toggle-desc {
            font-size: 0.8rem;
            color: var(--gray-500);
            line-height: 1.5;
        }

        /* --- Form Footer Premium --- */
        .lect-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-top: 2.5rem;
            padding: 1.25rem 1.5rem;
            background: linear-gradient(to right, var(--gray-50), white);
            border-radius: var(--radius-xl);
            border: 1px solid var(--gray-200);
            flex-wrap: wrap;
        }

        .lect-footer-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: var(--gray-500);
        }

        .lect-footer-info i { color: var(--accent-yellow); }

        .lect-footer-actions {
            display: flex;
            gap: 0.75rem;
        }

        .lect-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-lg);
            font-weight: 600;
            font-size: 0.9rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            text-decoration: none;
            font-family: inherit;
            position: relative;
            overflow: hidden;
        }

        .lect-btn-cancel {
            background: white;
            color: var(--gray-700);
            border: 1.5px solid var(--gray-300);
        }

        .lect-btn-cancel:hover {
            background: var(--gray-100);
            border-color: var(--gray-400);
            transform: translateX(-2px);
        }

        .lect-btn-submit {
            background: var(--gradient-success);
            color: white;
            box-shadow: 0 8px 15px -5px rgba(16,185,129,0.4);
        }

        .lect-btn-submit:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 12px 20px -5px rgba(16,185,129,0.5);
        }

        .lect-btn-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .lect-btn-submit .lect-spinner {
            display: none;
            width: 16px; height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: lectSpin 0.7s linear infinite;
        }

        .lect-btn-submit.is-loading .lect-spinner { display: inline-block; }
        .lect-btn-submit.is-loading .lect-btn-text { opacity: 0.7; }

        @keyframes lectSpin { to { transform: rotate(360deg); } }

        /* --- Page Header --- */
        .lect-page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .lect-page-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--gray-900);
            margin: 0;
        }

        .lect-page-title i {
            color: var(--accent-green);
            font-size: 1.5rem;
        }

        .lect-page-subtitle {
            color: var(--gray-500);
            font-size: 0.95rem;
            margin: 0.25rem 0 0 2.25rem;
        }

        .lect-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            background: white;
            color: var(--gray-700);
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-lg);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.3s;
        }

        .lect-back-btn:hover {
            background: var(--gray-50);
            border-color: var(--gray-400);
            transform: translateX(-3px);
        }

        /* --- Form Validation Error --- */
        .lect-input.is-invalid,
        .lect-select.is-invalid {
            border-color: var(--accent-red);
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
        }

        .lect-error-msg {
            color: var(--accent-red);
            font-size: 0.78rem;
            margin-top: 0.35rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        @media (max-width: 768px) {
            .lect-card-body { padding: 1.25rem; }
            .lect-footer { flex-direction: column; }
            .lect-footer-actions { width: 100%; }
            .lect-btn { flex: 1; justify-content: center; }
        }
    </style>
</head>
<body>
    <?php require dirname(__DIR__) . '/partials/navbar.php'; ?>

    <div class="container lect-form">
        <!-- Page Header -->
        <div class="lect-page-header">
            <div>
                <h1 class="lect-page-title">
                    <i class="bi bi-person-workspace"></i>
                    <?= $title ?>
                </h1>
                <p class="lect-page-subtitle">Kelola data dosen pembimbing dengan lengkap dan akurat.</p>
            </div>
            <a href="<?= BASE_URL ?>/admin/lecturers" class="lect-back-btn">
                <i class="bi bi-arrow-left"></i> Kembali ke Daftar
            </a>
        </div>

        <!-- Form Card -->
        <form method="POST" action="<?= BASE_URL ?>/admin/lecturers/<?= $isEdit ? 'edit' : 'create' ?>" class="lect-form-main" id="lecturerForm">
            <div class="lect-card">
                <div class="lect-card-header">
                    <div class="lect-card-header-content">
                        <div class="lect-card-icon">
                            <i class="bi bi-<?= $isEdit ? 'pencil-square' : 'person-plus-fill' ?>"></i>
                        </div>
                        <div>
                            <h3 class="lect-card-title"><?= $isEdit ? 'Perbarui Data Dosen' : 'Registrasi Dosen Baru' ?></h3>
                            <p class="lect-card-subtitle">Lengkapi formulir di bawah dengan informasi yang valid</p>
                        </div>
                    </div>
                </div>

                <div class="lect-card-body">
                    <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="id" value="<?= $data['id'] ?>">
                    <?php endif; ?>

                    <!-- ===== SECTION 1: IDENTITAS DASAR ===== -->
                    <div class="lect-section">
                        <div class="lect-section-header">
                            <div class="lect-section-num">1</div>
                            <div class="lect-section-info">
                                <h4>Identitas Dasar</h4>
                                <p>Informasi nama dan kontak utama dosen</p>
                            </div>
                        </div>

                        <div class="lect-grid-2">
                            <div class="lect-field">
                                <label class="lect-label">
                                    <span>Nama Lengkap <span class="lect-label-required">*</span></span>
                                </label>
                                <div class="lect-input-wrap">
                                    <input type="text" name="name" class="lect-input" value="<?= e($data['name'] ?? '') ?>" placeholder="Dr. Ahmad Fauzi, M.Kom." required>
                                    <i class="bi bi-person-fill lect-icon"></i>
                                </div>
                            </div>
                            <div class="lect-field">
                                <label class="lect-label">
                                    <span>Email <span class="lect-label-required">*</span></span>
                                    <span class="lect-label-hint">untuk login</span>
                                </label>
                                <div class="lect-input-wrap">
                                    <input type="email" name="email" class="lect-input" value="<?= e($data['email'] ?? '') ?>" placeholder="dosen@universitas.ac.id" required>
                                    <i class="bi bi-envelope-fill lect-icon"></i>
                                </div>
                            </div>
                        </div>

                        <?php if (!$isEdit): ?>
                        <div class="lect-field" style="margin-top: 1rem;">
                            <label class="lect-label">
                                <span>Password Default</span>
                                <span class="lect-label-hint">disarankan dicatat</span>
                            </label>
                            <div class="lect-password-box">
                                <div class="lect-password-icon">
                                    <i class="bi bi-key-fill"></i>
                                </div>
                                <div class="lect-password-content">
                                    <div class="lect-password-label">Password Sementara</div>
                                    <div class="lect-password-value">
                                        <span id="passwordValue">password123</span>
                                        <button type="button" class="lect-password-copy" id="copyPasswordBtn" title="Salin ke clipboard">
                                            <i class="bi bi-clipboard"></i> Salin
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="lect-helper">
                                <i class="bi bi-info-circle-fill"></i>
                                <span>Password ini akan digunakan dosen saat login pertama kali. Dosen dapat mengubahnya setelah masuk.</span>
                            </div>
                            <input type="hidden" name="password" value="password123">
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- ===== SECTION 2: DATA AKADEMIK ===== -->
                    <div class="lect-section">
                        <div class="lect-section-header">
                            <div class="lect-section-num">2</div>
                            <div class="lect-section-info">
                                <h4>Data Akademik</h4>
                                <p>Informasi identitas dosen & kepangkatan</p>
                            </div>
                        </div>

                        <div class="lect-grid-3">
                            <div class="lect-field">
                                <label class="lect-label">NIDN / NIP</label>
                                <div class="lect-input-wrap">
                                    <input type="text" name="lecturer_number" class="lect-input" value="<?= e($data['lecturer_number'] ?? '') ?>" placeholder="0012345678">
                                    <i class="bi bi-card-text lect-icon"></i>
                                </div>
                                <div class="lect-helper">
                                    <i class="bi bi-info-circle-fill"></i>
                                    <span>Nomor Induk Dosen atau Pegawai</span>
                                </div>
                            </div>
                            <div class="lect-field">
                                <label class="lect-label">Gelar Akademik</label>
                                <div class="lect-input-wrap">
                                    <input type="text" name="academic_degree" class="lect-input" value="<?= e($data['academic_degree'] ?? '') ?>" placeholder="Dr., M.Kom.">
                                    <i class="bi bi-award-fill lect-icon"></i>
                                </div>
                            </div>
                            <div class="lect-field">
                                <label class="lect-label">Departemen / Prodi</label>
                                <div class="lect-input-wrap">
                                    <input type="text" name="department" class="lect-input" value="<?= e($data['department'] ?? '') ?>" placeholder="Teknik Informatika">
                                    <i class="bi bi-building-fill lect-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ===== SECTION 3: KEAHLIAN & KONTAK ===== -->
                    <div class="lect-section">
                        <div class="lect-section-header">
                            <div class="lect-section-num">3</div>
                            <div class="lect-section-info">
                                <h4>Keahlian & Kontak</h4>
                                <p>Bidang spesialisasi dan cara menghubungi</p>
                            </div>
                        </div>

                        <div class="lect-grid-2">
                            <div class="lect-field">
                                <label class="lect-label">Bidang Keahlian</label>
                                <div class="lect-input-wrap">
                                    <input type="text" name="expertise" class="lect-input" value="<?= e($data['expertise'] ?? '') ?>" placeholder="Machine Learning, Web Development">
                                    <i class="bi bi-lightbulb-fill lect-icon"></i>
                                </div>
                                <div class="lect-helper">
                                    <i class="bi bi-info-circle-fill"></i>
                                    <span>Pisahkan dengan koma jika lebih dari satu</span>
                                </div>
                            </div>
                            <div class="lect-field">
                                <label class="lect-label">No. Telepon / WhatsApp</label>
                                <div class="lect-input-wrap">
                                    <input type="text" name="phone" class="lect-input" value="<?= e($data['phone'] ?? '') ?>" placeholder="081234567890">
                                    <i class="bi bi-telephone-fill lect-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ===== SECTION 4: STATUS AKUN ===== -->
                    <?php if ($isEdit): ?>
                    <div class="lect-section">
                        <div class="lect-section-header">
                            <div class="lect-section-num">4</div>
                            <div class="lect-section-info">
                                <h4>Status Akun</h4>
                                <p>Kontrol akses dosen ke sistem</p>
                            </div>
                        </div>

                        <label class="lect-toggle-box" for="is_active">
                            <div class="lect-toggle-switch">
                                <input type="checkbox" name="is_active" id="is_active" value="1" <?= ($data['is_active'] ?? 1) ? 'checked' : '' ?>>
                                <span class="lect-toggle-slider"></span>
                            </div>
                            <div class="lect-toggle-info">
                                <div class="lect-toggle-title">
                                    Akun Aktif
                                    <span class="lect-toggle-status <?= ($data['is_active'] ?? 1) ? 'on' : 'off' ?>" id="statusBadge">
                                        <?= ($data['is_active'] ?? 1) ? 'AKTIF' : 'NONAKTIF' ?>
                                    </span>
                                </div>
                                <div class="lect-toggle-desc">
                                    Nonaktifkan akun jika dosen sedang cuti, pensiun, atau tidak lagi mengajar. Data tetap tersimpan.
                                </div>
                            </div>
                        </label>
                    </div>
                    <?php endif; ?>

                    <!-- ===== FOOTER AKSI ===== -->
                    <div class="lect-footer">
                        <div class="lect-footer-info">
                            <i class="bi bi-shield-check"></i>
                            <span>Data Anda terenkripsi & disimpan aman</span>
                        </div>
                        <div class="lect-footer-actions">
                            <a href="<?= BASE_URL ?>/admin/lecturers" class="lect-btn lect-btn-cancel">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                            <button type="submit" class="lect-btn lect-btn-submit" id="submitBtn">
                                <span class="lect-spinner"></span>
                                <span class="lect-btn-text">
                                    <i class="bi bi-check-circle-fill"></i>
                                    <?= $isEdit ? 'Perbarui Data' : 'Simpan Dosen' ?>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/admin.js"></script>
    <script>
    /**
     * Lecturer Form - Scoped JavaScript
     * Namespace aman, tidak bentrok dengan admin.js global
     */
    (function() {
        'use strict';

        // ===== COPY PASSWORD =====
        const copyBtn = document.getElementById('copyPasswordBtn');
        if (copyBtn) {
            copyBtn.addEventListener('click', async () => {
                const password = document.getElementById('passwordValue').textContent;
                try {
                    await navigator.clipboard.writeText(password);
                    const originalHTML = copyBtn.innerHTML;
                    copyBtn.innerHTML = '<i class="bi bi-check2"></i> Tersalin!';
                    copyBtn.style.color = '#10b981';
                    setTimeout(() => {
                        copyBtn.innerHTML = originalHTML;
                        copyBtn.style.color = '';
                    }, 2000);
                    if (window.showToast) window.showToast('Password berhasil disalin', 'success', 2000);
                } catch (err) {
                    if (window.showToast) window.showToast('Gagal menyalin', 'error');
                }
            });
        }

        // ===== TOGGLE STATUS BADGE =====
        const toggleInput = document.getElementById('is_active');
        const statusBadge = document.getElementById('statusBadge');
        if (toggleInput && statusBadge) {
            const updateBadge = () => {
                if (toggleInput.checked) {
                    statusBadge.textContent = 'AKTIF';
                    statusBadge.className = 'lect-toggle-status on';
                } else {
                    statusBadge.textContent = 'NONAKTIF';
                    statusBadge.className = 'lect-toggle-status off';
                }
            };
            toggleInput.addEventListener('change', updateBadge);
        }

        // ===== FORM VALIDATION =====
        const form = document.getElementById('lecturerForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Clear previous errors
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.lect-error-msg').forEach(el => el.remove());

                // Validate required fields
                form.querySelectorAll('[required]').forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add('is-invalid');
                        const errorMsg = document.createElement('div');
                        errorMsg.className = 'lect-error-msg';
                        errorMsg.innerHTML = '<i class="bi bi-exclamation-circle-fill"></i> Field ini wajib diisi';
                        field.closest('.lect-field')?.appendChild(errorMsg);
                    }
                });

                // Validate email format
                const emailField = form.querySelector('input[type="email"]');
                if (emailField && emailField.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailField.value)) {
                    isValid = false;
                    emailField.classList.add('is-invalid');
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'lect-error-msg';
                    errorMsg.innerHTML = '<i class="bi bi-exclamation-circle-fill"></i> Format email tidak valid';
                    emailField.closest('.lect-field')?.appendChild(errorMsg);
                }

                if (!isValid) {
                    e.preventDefault();
                    if (window.showToast) window.showToast('Mohon lengkapi semua field yang wajib diisi', 'error');
                    // Scroll to first error
                    const firstError = form.querySelector('.is-invalid');
                    if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return false;
                }

                // Show loading state
                const submitBtn = document.getElementById('submitBtn');
                if (submitBtn) {
                    submitBtn.classList.add('is-loading');
                    submitBtn.disabled = true;
                }
            });

            // Auto-remove error on input
            form.querySelectorAll('.lect-input, .lect-select').forEach(field => {
                field.addEventListener('input', function() {
                    this.classList.remove('is-invalid');
                    const error = this.closest('.lect-field')?.querySelector('.lect-error-msg');
                    if (error) error.remove();
                });
            });
        }

        // ===== PHONE NUMBER FORMATTING =====
        const phoneInput = form?.querySelector('input[name="phone"]');
        if (phoneInput) {
            phoneInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9+\-\s]/g, '');
            });
        }

        // ===== STAGGER ANIMATION =====
        document.querySelectorAll('.lect-form .lect-section').forEach((section, i) => {
            section.style.animationDelay = (i * 0.1) + 's';
        });

        console.log('%c✓ Lecturer Form Loaded', 'color: #10b981; font-weight: bold;');
    })();
    </script>
</body>
</html>