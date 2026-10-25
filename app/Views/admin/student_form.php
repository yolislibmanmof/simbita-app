<?php
declare(strict_types=1);
$isEdit = isset($student);
$title = $isEdit ? 'Edit Mahasiswa' : 'Tambah Mahasiswa';
$data = $isEdit ? $student : [];
$jenjangInfo = [
    's1' => ['name' => 'Sarjana', 'icon' => 'mortarboard-fill', 'color' => '#3b82f6'],
    's2' => ['name' => 'Magister', 'icon' => 'mortarboard', 'color' => '#8b5cf6'],
    's3' => ['name' => 'Doktor', 'icon' => 'award-fill', 'color' => '#ec4899'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - <?= e(APP_NAME) ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">
    
    <style>
        /* =========================================================
           STUDENT FORM ULTRA PREMIUM - SCOPED STYLES
           Namespace: .stud-form | Tema: Ocean Blue (#3b82f6)
           ========================================================= */
        .stud-form {
            --stud-primary: #3b82f6;
            --stud-primary-dark: #2563eb;
            --stud-primary-light: #dbeafe;
            --stud-gradient: linear-gradient(135deg, #3b82f6 0%, #2563eb 50%, #1e40af 100%);
            --stud-gradient-soft: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            --stud-glow: 0 10px 30px -10px rgba(59, 130, 246, 0.4);
        }

        /* --- Page Header --- */
        .stud-page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
            animation: studFadeDown 0.5s ease;
        }

        @keyframes studFadeDown {
            from { opacity: 0; transform: translateY(-15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .stud-page-title-wrap { flex: 1; min-width: 280px; }

        .stud-page-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.875rem;
            font-weight: 800;
            color: var(--gray-900);
            margin: 0 0 0.35rem 0;
        }

        .stud-page-title-icon {
            width: 48px; height: 48px;
            background: var(--stud-gradient-soft);
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--stud-primary);
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .stud-page-subtitle {
            color: var(--gray-500);
            font-size: 0.95rem;
            margin: 0 0 0 calc(48px + 0.75rem);
        }

        .stud-back-btn {
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

        .stud-back-btn:hover {
            background: var(--gray-50);
            border-color: var(--gray-400);
            transform: translateX(-3px);
        }

        /* --- Main Card Premium --- */
        .stud-card {
            background: white;
            border-radius: var(--radius-2xl);
            box-shadow: 0 10px 40px -15px rgba(0,0,0,0.1);
            border: 1px solid var(--gray-200);
            overflow: hidden;
            animation: studSlideIn 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes studSlideIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .stud-card-header {
            padding: 2rem;
            background: var(--stud-gradient);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .stud-card-header::before {
            content: '';
            position: absolute;
            top: -60%; right: -5%;
            width: 350px; height: 350px;
            background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, transparent 70%);
            border-radius: 50%;
            animation: studOrb 8s ease-in-out infinite;
        }

        .stud-card-header::after {
            content: '';
            position: absolute;
            bottom: -40%; left: 5%;
            width: 250px; height: 250px;
            background: radial-gradient(circle, rgba(139,92,246,0.2) 0%, transparent 70%);
            border-radius: 50%;
            animation: studOrb 10s ease-in-out infinite reverse;
        }

        @keyframes studOrb {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(-20px, 15px) scale(1.1); }
        }

        .stud-card-header-content {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }

        .stud-card-icon-box {
            width: 64px; height: 64px;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            flex-shrink: 0;
        }

        .stud-card-title {
            font-size: 1.5rem;
            font-weight: 800;
            margin: 0 0 0.25rem 0;
        }

        .stud-card-subtitle {
            font-size: 0.925rem;
            opacity: 0.92;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .stud-card-subtitle .stud-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.2rem 0.65rem;
            background: rgba(255,255,255,0.2);
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 600;
            border: 1px solid rgba(255,255,255,0.25);
        }

        .stud-card-body { padding: 2.5rem; }

        /* --- Form Sections dengan Numbering --- */
        .stud-section {
            margin-bottom: 2.75rem;
            padding-bottom: 2rem;
            border-bottom: 1px dashed var(--gray-200);
            animation: studSectionIn 0.5s ease backwards;
        }

        .stud-section:nth-child(1) { animation-delay: 0.1s; }
        .stud-section:nth-child(2) { animation-delay: 0.2s; }
        .stud-section:nth-child(3) { animation-delay: 0.3s; }
        .stud-section:nth-child(4) { animation-delay: 0.4s; }
        .stud-section:nth-child(5) { animation-delay: 0.5s; }
        .stud-section:last-child { border-bottom: none; padding-bottom: 0; margin-bottom: 0; }

        @keyframes studSectionIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .stud-section-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.75rem;
        }

        .stud-section-num {
            width: 44px; height: 44px;
            border-radius: var(--radius-lg);
            background: var(--stud-gradient);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.15rem;
            flex-shrink: 0;
            box-shadow: var(--stud-glow);
            transition: transform 0.3s;
        }

        .stud-section:hover .stud-section-num {
            transform: rotate(-8deg) scale(1.05);
        }

        .stud-section-info h4 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--gray-900);
            margin: 0 0 0.15rem 0;
        }

        .stud-section-info p {
            font-size: 0.85rem;
            color: var(--gray-500);
            margin: 0;
        }

        /* --- Form Fields Premium --- */
        .stud-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.25rem;
        }

        .stud-grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem;
        }

        @media (max-width: 768px) {
            .stud-grid-2, .stud-grid-3 { grid-template-columns: 1fr; }
        }

        .stud-field { margin-bottom: 1.25rem; }

        .stud-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--gray-700);
            font-size: 0.875rem;
        }

        .stud-label-required { color: var(--accent-red); margin-left: 0.25rem; }

        .stud-label-hint {
            font-size: 0.75rem;
            color: var(--gray-500);
            font-weight: 500;
            font-style: italic;
        }

        .stud-input-wrap {
            position: relative;
            display: block;
        }

        .stud-input-wrap .stud-icon {
            position: absolute;
            left: 1rem; top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            font-size: 1.05rem;
            pointer-events: none;
            transition: color 0.3s;
            z-index: 2;
        }

        .stud-input, .stud-select {
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

        .stud-select {
            padding-right: 3rem;
            appearance: none;
            -webkit-appearance: none;
            cursor: pointer;
        }

        .stud-input:hover, .stud-select:hover { border-color: var(--gray-400); }

        .stud-input:focus, .stud-select:focus {
            outline: none;
            border-color: var(--stud-primary);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
        }

        .stud-input-wrap:focus-within .stud-icon { color: var(--stud-primary); }

        .stud-input::placeholder { color: var(--gray-400); }

        .stud-input-wrap::after {
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

        .stud-input-wrap:focus-within::after {
            transform: translateY(-30%) rotate(225deg);
            border-color: var(--stud-primary);
        }

        .stud-input-wrap:not(:has(.stud-select))::after { display: none; }

        .stud-helper {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            margin-top: 0.5rem;
            font-size: 0.78rem;
            color: var(--gray-500);
            padding-left: 0.25rem;
        }

        .stud-helper i { color: var(--stud-primary); font-size: 0.9rem; }

        /* --- Password Display Box --- */
        .stud-password-box {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 1.5px solid #fcd34d;
            border-radius: var(--radius-xl);
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            margin-bottom: 0.75rem;
            position: relative;
            overflow: hidden;
        }

        .stud-password-box::before {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 100px; height: 100%;
            background: radial-gradient(circle at right, rgba(245,158,11,0.15) 0%, transparent 70%);
        }

        .stud-password-icon {
            width: 48px; height: 48px;
            background: white;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-orange);
            font-size: 1.4rem;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(245, 158, 11, 0.2);
            position: relative;
            z-index: 2;
        }

        .stud-password-content { flex: 1; min-width: 0; position: relative; z-index: 2; }

        .stud-password-label {
            font-size: 0.7rem;
            color: #92400e;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 0.35rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .stud-password-value {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.05rem;
            font-weight: 700;
            color: #78350f;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .stud-password-copy {
            background: white;
            border: 1px solid #fcd34d;
            padding: 0.35rem 0.75rem;
            border-radius: var(--radius-md);
            cursor: pointer;
            color: var(--accent-orange);
            font-size: 0.8rem;
            font-weight: 600;
            transition: all 0.25s;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-family: inherit;
        }

        .stud-password-copy:hover {
            background: #fef3c7;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(245, 158, 11, 0.15);
        }

        .stud-password-copy.is-copied {
            background: var(--stud-primary);
            color: white;
            border-color: var(--stud-primary);
        }

        /* --- Jenjang Selector Cards --- */
        .stud-jenjang-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
        }

        @media (max-width: 640px) {
            .stud-jenjang-grid { grid-template-columns: 1fr; }
        }

        .stud-jenjang-card {
            position: relative;
            padding: 1rem;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-lg);
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: white;
        }

        .stud-jenjang-card:hover {
            border-color: var(--stud-primary);
            background: var(--stud-primary-light);
            transform: translateY(-2px);
        }

        .stud-jenjang-card.is-selected {
            border-color: var(--stud-primary);
            background: var(--stud-primary-light);
            box-shadow: 0 8px 15px -5px rgba(59, 130, 246, 0.25);
        }

        .stud-jenjang-card input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .stud-jenjang-icon {
            width: 42px; height: 42px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
            flex-shrink: 0;
        }

        .stud-jenjang-text { flex: 1; min-width: 0; }

        .stud-jenjang-code {
            font-weight: 800;
            color: var(--gray-900);
            font-size: 0.95rem;
            display: block;
        }

        .stud-jenjang-name {
            font-size: 0.8rem;
            color: var(--gray-500);
        }

        .stud-jenjang-check {
            width: 22px; height: 22px;
            border-radius: 50%;
            border: 2px solid var(--gray-300);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            flex-shrink: 0;
        }

        .stud-jenjang-card.is-selected .stud-jenjang-check {
            background: var(--stud-primary);
            border-color: var(--stud-primary);
            color: white;
        }

        .stud-jenjang-card.is-selected .stud-jenjang-check::after {
            content: '\F26B';
            font-family: 'bootstrap-icons';
            font-size: 0.8rem;
        }

        /* --- Toggle Switch untuk Active --- */
        .stud-toggle-box {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            padding: 1.5rem;
            background: var(--gray-50);
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-xl);
            transition: all 0.3s;
            cursor: pointer;
        }

        .stud-toggle-box:hover {
            border-color: var(--stud-primary);
            background: #eff6ff;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -10px rgba(59, 130, 246, 0.2);
        }

        .stud-toggle-switch {
            position: relative;
            width: 54px; height: 30px;
            flex-shrink: 0;
        }

        .stud-toggle-switch input { opacity: 0; width: 0; height: 0; }

        .stud-toggle-slider {
            position: absolute;
            cursor: pointer;
            inset: 0;
            background: var(--gray-300);
            border-radius: 999px;
            transition: 0.3s;
        }

        .stud-toggle-slider::before {
            content: '';
            position: absolute;
            width: 24px; height: 24px;
            left: 3px; bottom: 3px;
            background: white;
            border-radius: 50%;
            transition: 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .stud-toggle-switch input:checked + .stud-toggle-slider {
            background: var(--stud-primary);
            box-shadow: 0 0 12px rgba(59, 130, 246, 0.4);
        }

        .stud-toggle-switch input:checked + .stud-toggle-slider::before {
            transform: translateX(24px);
        }

        .stud-toggle-info { flex: 1; cursor: pointer; }

        .stud-toggle-title {
            font-weight: 700;
            color: var(--gray-800);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 0.25rem;
        }

        .stud-toggle-status {
            font-size: 0.65rem;
            padding: 0.15rem 0.55rem;
            border-radius: var(--radius-full);
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: all 0.3s;
        }

        .stud-toggle-status.on { background: var(--stud-primary-light); color: #1e40af; }
        .stud-toggle-status.off { background: var(--gray-200); color: var(--gray-600); }

        .stud-toggle-desc {
            font-size: 0.825rem;
            color: var(--gray-500);
            line-height: 1.5;
            margin: 0;
        }

        /* --- Form Footer Premium --- */
        .stud-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-top: 2.5rem;
            padding: 1.5rem;
            background: linear-gradient(to right, var(--gray-50), white);
            border-radius: var(--radius-xl);
            border: 1px solid var(--gray-200);
            flex-wrap: wrap;
        }

        .stud-footer-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: var(--gray-500);
        }

        .stud-footer-info i { color: var(--stud-primary); font-size: 1.1rem; }

        .stud-footer-actions { display: flex; gap: 0.75rem; flex-wrap: wrap; }

        .stud-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
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
            white-space: nowrap;
        }

        .stud-btn-cancel {
            background: white;
            color: var(--gray-700);
            border: 1.5px solid var(--gray-300);
        }

        .stud-btn-cancel:hover {
            background: var(--gray-100);
            border-color: var(--gray-400);
            transform: translateX(-2px);
        }

        .stud-btn-submit {
            background: var(--stud-gradient);
            color: white;
            box-shadow: var(--stud-glow);
            padding: 0.875rem 2rem;
        }

        .stud-btn-submit:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 15px 25px -5px rgba(59,130,246,0.5);
        }

        .stud-btn-submit:disabled { opacity: 0.7; cursor: not-allowed; }

        .stud-spinner {
            display: none;
            width: 16px; height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: studSpin 0.7s linear infinite;
        }

        .stud-btn-submit.is-loading .stud-spinner { display: inline-block; }
        .stud-btn-submit.is-loading .stud-btn-text { opacity: 0.8; }

        @keyframes studSpin { to { transform: rotate(360deg); } }

        /* --- Validation Error --- */
        .stud-input.is-invalid, .stud-select.is-invalid {
            border-color: var(--accent-red);
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
        }

        .stud-jenjang-card.is-invalid {
            border-color: var(--accent-red);
            background: #fee2e2;
        }

        .stud-error-msg {
            color: var(--accent-red);
            font-size: 0.78rem;
            margin-top: 0.35rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            animation: studShake 0.3s;
        }

        @keyframes studShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-4px); }
            75% { transform: translateX(4px); }
        }

        @media (max-width: 768px) {
            .stud-card-body { padding: 1.5rem; }
            .stud-footer { flex-direction: column; }
            .stud-footer-actions { width: 100%; }
            .stud-btn { flex: 1; }
            .stud-page-subtitle { margin-left: 0; margin-top: 0.5rem; }
        }
    </style>
</head>
<body>
    <?php require dirname(__DIR__) . '/partials/navbar.php'; ?>

    <div class="container stud-form">
        <!-- Page Header -->
        <div class="stud-page-header">
            <div class="stud-page-title-wrap">
                <h1 class="stud-page-title">
                    <span class="stud-page-title-icon">
                        <i class="bi bi-person-plus"></i>
                    </span>
                    <?= $title ?>
                </h1>
                <p class="stud-page-subtitle">Kelola data akademik dan informasi kontak mahasiswa.</p>
            </div>
            <a href="<?= BASE_URL ?>/admin/students" class="stud-back-btn">
                <i class="bi bi-arrow-left"></i> Kembali ke Daftar
            </a>
        </div>

        <!-- Form Card -->
        <form method="POST" action="<?= BASE_URL ?>/admin/students/<?= $isEdit ? 'edit' : 'create' ?>" class="stud-form-main" id="studentForm">
            <div class="stud-card">
                <div class="stud-card-header">
                    <div class="stud-card-header-content">
                        <div class="stud-card-icon-box">
                            <i class="bi bi-<?= $isEdit ? 'pencil-square' : 'person-plus-fill' ?>"></i>
                        </div>
                        <div>
                            <h3 class="stud-card-title"><?= $isEdit ? 'Perbarui Data Mahasiswa' : 'Registrasi Mahasiswa Baru' ?></h3>
                            <p class="stud-card-subtitle">
                                Lengkapi formulir dengan data akademik yang valid
                                <span class="stud-badge">
                                    <i class="bi bi-shield-check"></i> Secure
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="stud-card-body">
                    <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="id" value="<?= $data['id'] ?>">
                    <?php endif; ?>

                    <!-- ===== SECTION 1: IDENTITAS DASAR ===== -->
                    <div class="stud-section">
                        <div class="stud-section-header">
                            <div class="stud-section-num">1</div>
                            <div class="stud-section-info">
                                <h4>Identitas Dasar</h4>
                                <p>Informasi nama dan email mahasiswa</p>
                            </div>
                        </div>

                        <div class="stud-grid-2">
                            <div class="stud-field">
                                <label class="stud-label">
                                    <span>Nama Lengkap <span class="stud-label-required">*</span></span>
                                </label>
                                <div class="stud-input-wrap">
                                    <i class="bi bi-person-fill stud-icon"></i>
                                    <input type="text" name="name" class="stud-input" value="<?= e($data['name'] ?? '') ?>" placeholder="Andi Pratama" required>
                                </div>
                            </div>
                            <div class="stud-field">
                                <label class="stud-label">
                                    <span>Email <span class="stud-label-required">*</span></span>
                                    <span class="stud-label-hint">untuk login</span>
                                </label>
                                <div class="stud-input-wrap">
                                    <i class="bi bi-envelope-fill stud-icon"></i>
                                    <input type="email" name="email" class="stud-input" value="<?= e($data['email'] ?? '') ?>" placeholder="mahasiswa@universitas.ac.id" required>
                                </div>
                            </div>
                        </div>

                        <?php if (!$isEdit): ?>
                        <div class="stud-field" style="margin-top: 1rem;">
                            <label class="stud-label">
                                <span>Password Default</span>
                                <span class="stud-label-hint">disarankan dicatat</span>
                            </label>
                            <div class="stud-password-box">
                                <div class="stud-password-icon">
                                    <i class="bi bi-key-fill"></i>
                                </div>
                                <div class="stud-password-content">
                                    <div class="stud-password-label">
                                        <i class="bi bi-shield-lock-fill"></i> Password Sementara
                                    </div>
                                    <div class="stud-password-value">
                                        <span id="passwordValue">password123</span>
                                        <button type="button" class="stud-password-copy" id="copyPasswordBtn" title="Salin ke clipboard">
                                            <i class="bi bi-clipboard"></i> Salin
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="stud-helper">
                                <i class="bi bi-info-circle-fill"></i>
                                <span>Password ini akan digunakan mahasiswa saat login pertama kali. Mahasiswa dapat mengubahnya setelah masuk.</span>
                            </div>
                            <input type="hidden" name="password" value="password123">
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- ===== SECTION 2: DATA AKADEMIK ===== -->
                    <div class="stud-section">
                        <div class="stud-section-header">
                            <div class="stud-section-num">2</div>
                            <div class="stud-section-info">
                                <h4>Data Akademik</h4>
                                <p>Informasi NIM, jenjang, dan tahun angkatan</p>
                            </div>
                        </div>

                        <div class="stud-field">
                            <label class="stud-label">
                                <span>NIM (Nomor Induk Mahasiswa) <span class="stud-label-required">*</span></span>
                                <span class="stud-label-hint">harus unik</span>
                            </label>
                            <div class="stud-input-wrap">
                                <i class="bi bi-hash stud-icon"></i>
                                <input type="text" name="student_number" class="stud-input" value="<?= e($data['student_number'] ?? '') ?>" placeholder="2021001" required>
                            </div>
                            <div class="stud-helper">
                                <i class="bi bi-info-circle-fill"></i>
                                <span>Nomor induk mahasiswa yang terdaftar di sistem akademik kampus</span>
                            </div>
                        </div>

                        <div class="stud-grid-2">
                            <div class="stud-field">
                                <label class="stud-label">
                                    <span>Jenjang Pendidikan <span class="stud-label-required">*</span></span>
                                </label>
                                <div class="stud-jenjang-grid">
                                    <?php foreach ($jenjangInfo as $code => $info): ?>
                                        <label class="stud-jenjang-card <?= ($data['degree_level'] ?? 's1') === $code ? 'is-selected' : '' ?>" data-jenjang="<?= $code ?>">
                                            <input type="radio" name="degree_level" value="<?= $code ?>" <?= ($data['degree_level'] ?? 's1') === $code ? 'checked' : '' ?> required>
                                            <div class="stud-jenjang-icon" style="background: <?= $info['color'] ?>;">
                                                <i class="bi bi-<?= $info['icon'] ?>"></i>
                                            </div>
                                            <div class="stud-jenjang-text">
                                                <span class="stud-jenjang-code"><?= strtoupper($code) ?></span>
                                                <span class="stud-jenjang-name"><?= $info['name'] ?></span>
                                            </div>
                                            <div class="stud-jenjang-check"></div>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="stud-field">
                                <label class="stud-label">
                                    <span>Tahun Angkatan <span class="stud-label-required">*</span></span>
                                </label>
                                <div class="stud-input-wrap">
                                    <i class="bi bi-calendar-event stud-icon"></i>
                                    <input type="number" name="enrollment_year" class="stud-input" value="<?= e((string)($data['enrollment_year'] ?? date('Y'))) ?>" placeholder="2021" min="2000" max="<?= date('Y') + 1 ?>" required>
                                </div>
                                <div class="stud-helper">
                                    <i class="bi bi-info-circle-fill"></i>
                                    <span>Tahun mahasiswa pertama kali mendaftar</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ===== SECTION 3: PROGRAM STUDI ===== -->
                    <div class="stud-section">
                        <div class="stud-section-header">
                            <div class="stud-section-num">3</div>
                            <div class="stud-section-info">
                                <h4>Program Studi & Fakultas</h4>
                                <p>Informasi jurusan dan fakultas mahasiswa</p>
                            </div>
                        </div>

                        <div class="stud-grid-2">
                            <div class="stud-field">
                                <label class="stud-label">
                                    <span>Program Studi <span class="stud-label-required">*</span></span>
                                </label>
                                <div class="stud-input-wrap">
                                    <i class="bi bi-book-fill stud-icon"></i>
                                    <input type="text" name="study_program" class="stud-input" value="<?= e($data['study_program'] ?? '') ?>" placeholder="Teknik Informatika" required>
                                </div>
                            </div>
                            <div class="stud-field">
                                <label class="stud-label">Fakultas</label>
                                <div class="stud-input-wrap">
                                    <i class="bi bi-building-fill stud-icon"></i>
                                    <input type="text" name="faculty" class="stud-input" value="<?= e($data['faculty'] ?? '') ?>" placeholder="Fakultas Teknik">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ===== SECTION 4: KONTAK ===== -->
                    <div class="stud-section">
                        <div class="stud-section-header">
                            <div class="stud-section-num">4</div>
                            <div class="stud-section-info">
                                <h4>Informasi Kontak</h4>
                                <p>Nomor telepon untuk komunikasi penting</p>
                            </div>
                        </div>

                        <div class="stud-field" style="max-width: 50%;">
                            <label class="stud-label">No. Telepon / WhatsApp</label>
                            <div class="stud-input-wrap">
                                <i class="bi bi-telephone-fill stud-icon"></i>
                                <input type="text" name="phone" class="stud-input" value="<?= e($data['phone'] ?? '') ?>" placeholder="081234567890">
                            </div>
                            <div class="stud-helper">
                                <i class="bi bi-info-circle-fill"></i>
                                <span>Untuk menerima notifikasi dan informasi penting</span>
                            </div>
                        </div>
                    </div>

                    <!-- ===== SECTION 5: STATUS AKUN ===== -->
                    <?php if ($isEdit): ?>
                    <div class="stud-section">
                        <div class="stud-section-header">
                            <div class="stud-section-num">5</div>
                            <div class="stud-section-info">
                                <h4>Status Akun</h4>
                                <p>Kontrol akses mahasiswa ke sistem</p>
                            </div>
                        </div>

                        <label class="stud-toggle-box" for="is_active">
                            <div class="stud-toggle-switch">
                                <input type="checkbox" name="is_active" id="is_active" value="1" <?= ($data['is_active'] ?? 1) ? 'checked' : '' ?>>
                                <span class="stud-toggle-slider"></span>
                            </div>
                            <div class="stud-toggle-info">
                                <div class="stud-toggle-title">
                                    Akun Aktif
                                    <span class="stud-toggle-status <?= ($data['is_active'] ?? 1) ? 'on' : 'off' ?>" id="statusBadge">
                                        <?= ($data['is_active'] ?? 1) ? 'AKTIF' : 'NONAKTIF' ?>
                                    </span>
                                </div>
                                <p class="stud-toggle-desc">
                                    Nonaktifkan akun jika mahasiswa telah lulus, cuti, atau DO. Data tetap tersimpan.
                                </p>
                            </div>
                        </label>
                    </div>
                    <?php endif; ?>

                    <!-- ===== FOOTER AKSI ===== -->
                    <div class="stud-footer">
                        <div class="stud-footer-info">
                            <i class="bi bi-shield-check"></i>
                            <span>Data Anda terenkripsi & disimpan aman</span>
                        </div>
                        <div class="stud-footer-actions">
                            <a href="<?= BASE_URL ?>/admin/students" class="stud-btn stud-btn-cancel">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                            <button type="submit" class="stud-btn stud-btn-submit" id="submitBtn">
                                <span class="stud-spinner"></span>
                                <span class="stud-btn-text">
                                    <i class="bi bi-check-circle-fill"></i>
                                    <?= $isEdit ? 'Perbarui Data' : 'Simpan Mahasiswa' ?>
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
    (function() {
        'use strict';

        // ===== COPY PASSWORD =====
        const copyBtn = document.getElementById('copyPasswordBtn');
        if (copyBtn) {
            copyBtn.addEventListener('click', async () => {
                const password = document.getElementById('passwordValue').textContent;
                try {
                    await navigator.clipboard.writeText(password);
                    copyBtn.classList.add('is-copied');
                    copyBtn.innerHTML = '<i class="bi bi-check2"></i> Tersalin!';
                    if (window.showToast) window.showToast('Password berhasil disalin', 'success', 2000);
                    setTimeout(() => {
                        copyBtn.classList.remove('is-copied');
                        copyBtn.innerHTML = '<i class="bi bi-clipboard"></i> Salin';
                    }, 2000);
                } catch (err) {
                    if (window.showToast) window.showToast('Gagal menyalin', 'error');
                }
            });
        }

        // ===== TOGGLE STATUS BADGE =====
        const toggleInput = document.getElementById('is_active');
        const statusBadge = document.getElementById('statusBadge');
        if (toggleInput && statusBadge) {
            toggleInput.addEventListener('change', () => {
                if (toggleInput.checked) {
                    statusBadge.textContent = 'AKTIF';
                    statusBadge.className = 'stud-toggle-status on';
                } else {
                    statusBadge.textContent = 'NONAKTIF';
                    statusBadge.className = 'stud-toggle-status off';
                }
            });
        }

        // ===== JENJANG SELECTOR =====
        document.querySelectorAll('.stud-jenjang-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.stud-jenjang-card').forEach(c => c.classList.remove('is-selected'));
                this.classList.add('is-selected');
                this.querySelector('input').checked = true;
            });
        });

        // ===== FORM VALIDATION =====
        const form = document.getElementById('studentForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.stud-error-msg').forEach(el => el.remove());

                form.querySelectorAll('[required]').forEach(field => {
                    if (field.type === 'radio') {
                        const group = form.querySelectorAll(`input[name="${field.name}"]`);
                        const checked = Array.from(group).some(r => r.checked);
                        if (!checked) {
                            isValid = false;
                            group.forEach(r => r.closest('.stud-jenjang-card')?.classList.add('is-invalid'));
                            const errorMsg = document.createElement('div');
                            errorMsg.className = 'stud-error-msg';
                            errorMsg.innerHTML = '<i class="bi bi-exclamation-circle-fill"></i> Pilih salah satu jenjang';
                            group[0].closest('.stud-field')?.appendChild(errorMsg);
                        }
                    } else if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add('is-invalid');
                        const errorMsg = document.createElement('div');
                        errorMsg.className = 'stud-error-msg';
                        errorMsg.innerHTML = '<i class="bi bi-exclamation-circle-fill"></i> Field ini wajib diisi';
                        field.closest('.stud-field')?.appendChild(errorMsg);
                    }
                });

                const emailField = form.querySelector('input[type="email"]');
                if (emailField && emailField.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailField.value)) {
                    isValid = false;
                    emailField.classList.add('is-invalid');
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'stud-error-msg';
                    errorMsg.innerHTML = '<i class="bi bi-exclamation-circle-fill"></i> Format email tidak valid';
                    emailField.closest('.stud-field')?.appendChild(errorMsg);
                }

                const yearField = form.querySelector('input[name="enrollment_year"]');
                if (yearField && yearField.value) {
                    const year = parseInt(yearField.value);
                    if (year < 2000 || year > new Date().getFullYear() + 1) {
                        isValid = false;
                        yearField.classList.add('is-invalid');
                        const errorMsg = document.createElement('div');
                        errorMsg.className = 'stud-error-msg';
                        errorMsg.innerHTML = '<i class="bi bi-exclamation-circle-fill"></i> Tahun tidak valid (2000 - ' + (new Date().getFullYear() + 1) + ')';
                        yearField.closest('.stud-field')?.appendChild(errorMsg);
                    }
                }

                if (!isValid) {
                    e.preventDefault();
                    if (window.showToast) window.showToast('Mohon lengkapi semua field yang wajib diisi', 'error');
                    const firstError = form.querySelector('.is-invalid');
                    if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return false;
                }

                const submitBtn = document.getElementById('submitBtn');
                if (submitBtn) {
                    submitBtn.classList.add('is-loading');
                    submitBtn.disabled = true;
                }
            });

            form.querySelectorAll('.stud-input, .stud-select').forEach(field => {
                field.addEventListener('input', function() {
                    this.classList.remove('is-invalid');
                    const error = this.closest('.stud-field')?.querySelector('.stud-error-msg');
                    if (error) error.remove();
                });
            });
        }

        // ===== PHONE FORMATTING =====
        const phoneInput = form?.querySelector('input[name="phone"]');
        if (phoneInput) {
            phoneInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9+\-\s]/g, '');
            });
        }

        // ===== AUTO UPPERCASE JENJANG =====
        const degreeInput = form?.querySelector('input[name="student_number"]');
        if (degreeInput) {
            degreeInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^a-zA-Z0-9]/g, '');
            });
        }

        console.log('%c✓ Student Form Loaded', 'color: #3b82f6; font-weight: bold;');
    })();
    </script>
</body>
</html>