<?php
declare(strict_types=1);
$isEdit = isset($work);
$title = $isEdit ? 'Edit Tugas Akhir' : 'Tambah Tugas Akhir';
$data = $isEdit ? $work : [];

$sup1_id = '';
$sup2_id = '';
foreach (($data['supervisors'] ?? []) as $sup) {
    $role = $sup['assignment_role'] ?? '';
    if ($role === 'supervisor_1') $sup1_id = $sup['lecturer_id'] ?? '';
    if ($role === 'supervisor_2') $sup2_id = $sup['lecturer_id'] ?? '';
}

$statusLabels = [
    'draft' => 'Draft', 'proposal' => 'Proposal', 'feasibility_test' => 'Uji Kelayakan',
    'writing' => 'Penulisan', 'progress_exam' => 'Ujian Kemajuan', 'revision' => 'Revisi',
    'closed_exam' => 'Ujian Tertutup', 'ready_for_defense' => 'Siap Sidang',
    'defense' => 'Sidang', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan',
];

$chipsMap = [
    'draft' => 'wf-chip-draft', 'proposal' => 'wf-chip-info', 'feasibility_test' => 'wf-chip-info',
    'writing' => 'wf-chip-info', 'progress_exam' => 'wf-chip-info', 'revision' => 'wf-chip-warn',
    'closed_exam' => 'wf-chip-info', 'ready_for_defense' => 'wf-chip-info', 'defense' => 'wf-chip-info',
    'completed' => 'wf-chip-success', 'cancelled' => 'wf-chip-danger'
];

$initials = function(string $name): string {
    $parts = array_filter(explode(' ', trim($name)));
    if (empty($parts)) return '-';
    return strtoupper(substr($parts[0], 0, 1)) . (count($parts) > 1 ? strtoupper(substr(end($parts), 0, 1)) : '');
};
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
           WORK FORM ULTRA PREMIUM - SCOPED STYLES
           Namespace: .wf- | Tema: Violet (#8b5cf6)
           ========================================================= */
        .wf-view { animation: wfFade 0.4s ease; }
        @keyframes wfFade { from { opacity: 0; } to { opacity: 1; } }

        .wf-view {
            --wf-primary: #8b5cf6;
            --wf-primary-dark: #7c3aed;
            --wf-primary-light: #ede9fe;
            --wf-gradient: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 50%, #6d28d9 100%);
            --wf-gradient-soft: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%);
            --wf-glow: 0 10px 30px -10px rgba(139, 92, 246, 0.4);
        }

        /* --- Page Header --- */
        .wf-page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .wf-page-title-wrap { flex: 1; min-width: 280px; }

        .wf-page-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.875rem;
            font-weight: 800;
            color: var(--gray-900);
            margin: 0 0 0.35rem 0;
        }

        .wf-page-title-icon {
            width: 48px; height: 48px;
            background: var(--wf-gradient-soft);
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--wf-primary);
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .wf-page-subtitle {
            color: var(--gray-500);
            font-size: 0.95rem;
            margin: 0 0 0 calc(48px + 0.75rem);
        }

        .wf-back-btn {
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

        .wf-back-btn:hover {
            background: var(--gray-50);
            border-color: var(--gray-400);
            transform: translateX(-3px);
        }

        /* --- Main Card --- */
        .wf-card {
            background: white;
            border-radius: var(--radius-2xl);
            box-shadow: 0 10px 40px -15px rgba(0,0,0,0.1);
            border: 1px solid var(--gray-200);
            overflow: hidden;
            animation: wfSlideIn 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes wfSlideIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .wf-card-header {
            padding: 2rem;
            background: var(--wf-gradient);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .wf-card-header::before {
            content: '';
            position: absolute;
            top: -60%; right: -5%;
            width: 350px; height: 350px;
            background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, transparent 70%);
            border-radius: 50%;
            animation: wfOrb 8s ease-in-out infinite;
        }

        .wf-card-header::after {
            content: '';
            position: absolute;
            bottom: -40%; left: 5%;
            width: 250px; height: 250px;
            background: radial-gradient(circle, rgba(236,72,153,0.2) 0%, transparent 70%);
            border-radius: 50%;
            animation: wfOrb 10s ease-in-out infinite reverse;
        }

        @keyframes wfOrb {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(-20px, 15px) scale(1.1); }
        }

        .wf-card-header-content {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }

        .wf-card-icon-box {
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

        .wf-card-title { font-size: 1.5rem; font-weight: 800; margin: 0 0 0.25rem 0; }

        .wf-card-subtitle {
            font-size: 0.925rem;
            opacity: 0.92;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .wf-badge {
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

        .wf-card-body { padding: 2.5rem; }

        /* --- Sections --- */
        .wf-section {
            margin-bottom: 2.75rem;
            padding-bottom: 2rem;
            border-bottom: 1px dashed var(--gray-200);
            animation: wfSectionIn 0.5s ease backwards;
        }

        .wf-section:nth-child(1) { animation-delay: 0.1s; }
        .wf-section:nth-child(2) { animation-delay: 0.2s; }
        .wf-section:nth-child(3) { animation-delay: 0.3s; }
        .wf-section:last-child { border-bottom: none; padding-bottom: 0; margin-bottom: 0; }

        @keyframes wfSectionIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .wf-section-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.75rem;
        }

        .wf-section-num {
            width: 44px; height: 44px;
            border-radius: var(--radius-lg);
            background: var(--wf-gradient);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.15rem;
            flex-shrink: 0;
            box-shadow: var(--wf-glow);
            transition: transform 0.3s;
        }

        .wf-section:hover .wf-section-num { transform: rotate(-8deg) scale(1.05); }

        .wf-section-info h4 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--gray-900);
            margin: 0 0 0.15rem 0;
        }

        .wf-section-info p {
            font-size: 0.85rem;
            color: var(--gray-500);
            margin: 0;
        }

        /* --- Fields --- */
        .wf-field { margin-bottom: 1.25rem; }

        .wf-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--gray-700);
            font-size: 0.875rem;
        }

        .wf-required { color: var(--accent-red); margin-left: 0.25rem; }

        .wf-hint {
            font-size: 0.75rem;
            color: var(--gray-500);
            font-weight: 500;
            font-style: italic;
        }

        .wf-helper {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            margin-top: 0.5rem;
            font-size: 0.78rem;
            color: var(--gray-500);
            padding-left: 0.25rem;
        }

        .wf-helper i { color: var(--wf-primary); font-size: 0.9rem; }

        .wf-input-wrap { position: relative; display: block; }

        .wf-icon {
            position: absolute;
            left: 1rem; top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            font-size: 1.05rem;
            pointer-events: none;
            transition: color 0.3s;
            z-index: 2;
        }

        .wf-input, .wf-select, .wf-textarea {
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

        .wf-textarea {
            resize: vertical;
            min-height: 120px;
            padding: 0.9rem 1rem 0.9rem 2.75rem;
            line-height: 1.6;
        }

        .wf-select {
            padding-right: 3rem;
            appearance: none;
            -webkit-appearance: none;
            cursor: pointer;
        }

        .wf-input:hover, .wf-select:hover, .wf-textarea:hover { border-color: var(--gray-400); }

        .wf-input:focus, .wf-select:focus, .wf-textarea:focus {
            outline: none;
            border-color: var(--wf-primary);
            box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.12);
        }

        .wf-input-wrap:focus-within .wf-icon { color: var(--wf-primary); }

        .wf-input::placeholder, .wf-textarea::placeholder { color: var(--gray-400); }

        .wf-input-wrap::after {
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

        .wf-input-wrap:focus-within::after {
            transform: translateY(-30%) rotate(225deg);
            border-color: var(--wf-primary);
        }

        .wf-input-wrap:has(.wf-input)::after,
        .wf-input-wrap:has(.wf-textarea)::after { display: none; }

        /* Grid */
        .wf-grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; }
        @media (max-width: 768px) { .wf-grid-2 { grid-template-columns: 1fr; } }

        /* --- Char Counter --- */
        .wf-counter {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.78rem;
            color: var(--gray-400);
            font-weight: 600;
            transition: color 0.2s;
        }
        .wf-counter.warn { color: var(--accent-orange); }
        .wf-counter.danger { color: var(--accent-red); }

        /* --- Live Status Preview --- */
        .wf-status-preview {
            margin-top: 0.85rem;
            padding: 1rem;
            background: var(--gray-50);
            border-radius: var(--radius-lg);
            border: 1px dashed var(--gray-200);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .wf-status-preview-label {
            font-size: 0.75rem;
            color: var(--gray-500);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .wf-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 0.9rem;
            border-radius: var(--radius-full);
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            transition: all 0.3s;
        }

        .wf-chip::before {
            content: '';
            width: 7px; height: 7px;
            border-radius: 50%;
            background: currentColor;
            animation: wfPulse 1.6s infinite;
        }

        @keyframes wfPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.8); }
        }

        .wf-chip-draft { background: var(--gray-100); color: var(--gray-600); }
        .wf-chip-info { background: #cffafe; color: #155e75; }
        .wf-chip-warn { background: #fef3c7; color: #92400e; }
        .wf-chip-success { background: #d1fae5; color: #065f46; }
        .wf-chip-danger { background: #fee2e2; color: #991b1b; }

        /* --- Live Keywords Tags --- */
        .wf-tags-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
            margin-top: 0.75rem;
            min-height: 36px;
        }

        .wf-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.3rem 0.7rem;
            background: var(--wf-primary-light);
            color: var(--wf-primary);
            border-radius: var(--radius-full);
            font-size: 0.78rem;
            font-weight: 600;
            animation: wfPop 0.25s ease;
        }

        @keyframes wfPop {
            from { opacity: 0; transform: scale(0.8); }
            to { opacity: 1; transform: scale(1); }
        }

        .wf-tag-empty {
            color: var(--gray-400);
            font-size: 0.8rem;
            font-style: italic;
            padding: 0.3rem 0;
        }

        /* --- Supervisor Cards --- */
        .wf-sup-cards {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        @media (max-width: 768px) { .wf-sup-cards { grid-template-columns: 1fr; } }

        .wf-sup-card {
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-xl);
            padding: 1.25rem;
            background: white;
            transition: all 0.3s;
        }

        .wf-sup-card:hover {
            border-color: var(--wf-primary);
            background: #faf5ff;
        }

        .wf-sup-card-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .wf-sup-card-icon {
            width: 40px; height: 40px;
            border-radius: var(--radius-lg);
            background: var(--wf-gradient-soft);
            color: var(--wf-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .wf-sup-card-label {
            font-weight: 700;
            color: var(--gray-800);
            font-size: 0.9rem;
            margin: 0;
        }

        .wf-sup-card-desc {
            font-size: 0.75rem;
            color: var(--gray-500);
            margin: 0;
        }

        /* Selected supervisor preview */
        .wf-sup-preview {
            margin-top: 0.85rem;
            padding: 0.75rem;
            background: var(--wf-primary-light);
            border-radius: var(--radius-md);
            display: none;
            align-items: center;
            gap: 0.6rem;
            animation: wfPop 0.3s ease;
        }

        .wf-sup-preview.is-active { display: flex; }

        .wf-sup-preview-avatar {
            width: 34px; height: 34px;
            border-radius: var(--radius-md);
            background: var(--wf-gradient);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.8rem;
            flex-shrink: 0;
        }

        .wf-sup-preview-name {
            font-weight: 600;
            color: var(--gray-800);
            font-size: 0.85rem;
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* --- Footer --- */
        .wf-footer {
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

        .wf-footer-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: var(--gray-500);
        }

        .wf-footer-info i { color: var(--wf-primary); font-size: 1.1rem; }

        .wf-footer-actions { display: flex; gap: 0.75rem; flex-wrap: wrap; }

        .wf-btn {
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

        .wf-btn-cancel {
            background: white;
            color: var(--gray-700);
            border: 1.5px solid var(--gray-300);
        }

        .wf-btn-cancel:hover {
            background: var(--gray-100);
            border-color: var(--gray-400);
            transform: translateX(-2px);
        }

        .wf-btn-submit {
            background: var(--wf-gradient);
            color: white;
            box-shadow: var(--wf-glow);
            padding: 0.875rem 2rem;
        }

        .wf-btn-submit:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 15px 25px -5px rgba(139,92,246,0.5);
        }

        .wf-btn-submit:disabled { opacity: 0.7; cursor: not-allowed; }

        .wf-spinner {
            display: none;
            width: 16px; height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: wfSpin 0.7s linear infinite;
        }

        .wf-btn-submit.is-loading .wf-spinner { display: inline-block; }
        .wf-btn-submit.is-loading .wf-btn-text { opacity: 0.8; }

        @keyframes wfSpin { to { transform: rotate(360deg); } }

        /* --- Validation --- */
        .wf-input.is-invalid, .wf-select.is-invalid, .wf-textarea.is-invalid {
            border-color: var(--accent-red);
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
        }

        .wf-error-msg {
            color: var(--accent-red);
            font-size: 0.78rem;
            margin-top: 0.35rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            animation: wfShake 0.3s;
        }

        @keyframes wfShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-4px); }
            75% { transform: translateX(4px); }
        }

        @media (max-width: 768px) {
            .wf-card-body { padding: 1.5rem; }
            .wf-footer { flex-direction: column; }
            .wf-footer-actions { width: 100%; }
            .wf-btn { flex: 1; }
            .wf-page-subtitle { margin-left: 0; margin-top: 0.5rem; }
        }
    </style>
</head>
<body>
    <?php require dirname(__DIR__) . '/partials/navbar.php'; ?>

    <div class="container wf-view">
        <div class="wf-page-header">
            <div class="wf-page-title-wrap">
                <h1 class="wf-page-title">
                    <span class="wf-page-title-icon"><i class="bi bi-journal-richtext"></i></span>
                    <?= $title ?>
                </h1>
                <p class="wf-page-subtitle">Kelola data skripsi, tesis, atau disertasi mahasiswa dengan lengkap.</p>
            </div>
            <a href="<?= BASE_URL ?>/admin/works" class="wf-back-btn">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <form method="POST" action="<?= BASE_URL ?>/admin/works/<?= $isEdit ? 'edit' : 'create' ?>" id="workForm">
            <div class="wf-card">
                <div class="wf-card-header">
                    <div class="wf-card-header-content">
                        <div class="wf-card-icon-box">
                            <i class="bi bi-<?= $isEdit ? 'pencil-square' : 'plus-circle-fill' ?>"></i>
                        </div>
                        <div>
                            <h3 class="wf-card-title"><?= $isEdit ? 'Perbarui Tugas Akhir' : 'Buat Tugas Akhir Baru' ?></h3>
                            <p class="wf-card-subtitle">
                                Lengkapi formulir dengan data yang valid
                                <span class="wf-badge"><i class="bi bi-shield-check"></i> Secure</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="wf-card-body">
                    <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="id" value="<?= $data['id'] ?>">
                    <?php endif; ?>

                    <!-- ===== SECTION 1: INFORMASI DASAR ===== -->
                    <div class="wf-section">
                        <div class="wf-section-header">
                            <div class="wf-section-num">1</div>
                            <div class="wf-section-info">
                                <h4>Informasi Dasar</h4>
                                <p>Tentukan pemilik, jenis, dan status tugas akhir.</p>
                            </div>
                        </div>

                        <div class="wf-field">
                            <label class="wf-label">
                                <span>Mahasiswa <span class="wf-required">*</span></span>
                            </label>
                            <div class="wf-input-wrap">
                                <i class="bi bi-mortarboard-fill wf-icon"></i>
                                <select name="student_id" class="wf-select" id="studentSelect" required>
                                    <option value="">-- Pilih Mahasiswa --</option>
                                    <?php foreach ($students as $s): ?>
                                        <option value="<?= $s['id'] ?>" <?= ($data['student_user_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
                                            <?= e($s['name']) ?> (<?= e($s['student_number']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="wf-grid-2">
                            <div class="wf-field">
                                <label class="wf-label">
                                    <span>Jenis Tugas Akhir <span class="wf-required">*</span></span>
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="bi bi-journal-bookmark-fill wf-icon"></i>
                                    <select name="work_type_id" class="wf-select" required>
                                        <option value="">-- Pilih Jenis --</option>
                                        <?php foreach ($workTypes as $wt): ?>
                                            <option value="<?= $wt['id'] ?>" <?= ($data['work_type_id'] ?? '') == $wt['id'] ? 'selected' : '' ?>>
                                                <?= e($wt['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="wf-field">
                                <label class="wf-label">
                                    <span>Status <span class="wf-required">*</span></span>
                                </label>
                                <div class="wf-input-wrap">
                                    <i class="bi bi-flag-fill wf-icon"></i>
                                    <select name="status" class="wf-select" id="statusSelect" required>
                                        <?php foreach ($statusLabels as $val => $label): ?>
                                            <option value="<?= $val ?>" <?= ($data['status'] ?? 'draft') === $val ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="wf-status-preview">
                                    <span class="wf-status-preview-label">Preview:</span>
                                    <span class="wf-chip wf-chip-draft" id="statusChip">Draft</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ===== SECTION 2: DETAIL AKADEMIK ===== -->
                    <div class="wf-section">
                        <div class="wf-section-header">
                            <div class="wf-section-num">2</div>
                            <div class="wf-section-info">
                                <h4>Detail Akademik</h4>
                                <p>Judul, abstrak, dan kata kunci penelitian.</p>
                            </div>
                        </div>

                        <div class="wf-field">
                            <label class="wf-label">
                                <span>Judul Tugas Akhir <span class="wf-required">*</span></span>
                                <span class="wf-counter" id="titleCounter">0/300</span>
                            </label>
                            <div class="wf-input-wrap">
                                <i class="bi bi-card-heading wf-icon"></i>
                                <input type="text" name="title" id="titleInput" class="wf-input" maxlength="300"
                                       value="<?= e($data['title'] ?? '') ?>"
                                       placeholder="Masukkan judul lengkap tugas akhir" required>
                            </div>
                        </div>

                        <div class="wf-field">
                            <label class="wf-label">
                                <span>Abstrak</span>
                                <span class="wf-hint">opsional</span>
                            </label>
                            <div class="wf-input-wrap">
                                <i class="bi bi-quote wf-icon" style="top: 1.3rem;"></i>
                                <textarea name="abstract" class="wf-textarea" rows="5"
                                          placeholder="Ringkasan singkat mengenai latar belakang, metode, dan tujuan penelitian..."><?= e($data['abstract'] ?? '') ?></textarea>
                            </div>
                            <div class="wf-helper">
                                <i class="bi bi-info-circle-fill"></i>
                                <span>Dapat dilengkapi nanti oleh mahasiswa</span>
                            </div>
                        </div>

                        <div class="wf-field">
                            <label class="wf-label">
                                <span>Kata Kunci</span>
                                <span class="wf-hint">pisahkan dengan koma</span>
                            </label>
                            <div class="wf-input-wrap">
                                <i class="bi bi-tags-fill wf-icon"></i>
                                <input type="text" name="keywords" id="keywordsInput" class="wf-input"
                                       value="<?= e($data['keywords'] ?? '') ?>"
                                       placeholder="machine learning, data mining, python">
                            </div>
                            <div class="wf-tags-preview" id="tagsPreview">
                                <span class="wf-tag-empty">Ketik untuk melihat preview tags...</span>
                            </div>
                        </div>
                    </div>

                    <!-- ===== SECTION 3: TIM PEMBIMBING ===== -->
                    <div class="wf-section">
                        <div class="wf-section-header">
                            <div class="wf-section-num">3</div>
                            <div class="wf-section-info">
                                <h4>Tim Pembimbing</h4>
                                <p>Tetapkan dosen pembimbing utama dan pendamping.</p>
                            </div>
                        </div>

                        <div class="wf-sup-cards">
                            <div class="wf-sup-card">
                                <div class="wf-sup-card-header">
                                    <div class="wf-sup-card-icon"><i class="bi bi-person-check-fill"></i></div>
                                    <div>
                                        <p class="wf-sup-card-label">Pembimbing 1</p>
                                        <p class="wf-sup-card-desc">Utama (wajib)</p>
                                    </div>
                                </div>
                                <div class="wf-input-wrap">
                                    <i class="bi bi-person-workspace wf-icon"></i>
                                    <select name="supervisor_1" class="wf-select" data-sup-preview="sup1Preview">
                                        <option value="">-- Pilih Dosen --</option>
                                        <?php foreach ($lecturers as $l): ?>
                                            <option value="<?= $l['id'] ?>" <?= $sup1_id == $l['id'] ? 'selected' : '' ?>
                                                data-name="<?= e($l['name']) ?>" data-degree="<?= e($l['academic_degree'] ?? '') ?>">
                                                <?= e($l['name']) ?> <?= e($l['academic_degree'] ?? '') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="wf-sup-preview" id="sup1Preview"></div>
                            </div>

                            <div class="wf-sup-card">
                                <div class="wf-sup-card-header">
                                    <div class="wf-sup-card-icon"><i class="bi bi-person-plus-fill"></i></div>
                                    <div>
                                        <p class="wf-sup-card-label">Pembimbing 2</p>
                                        <p class="wf-sup-card-desc">Pendamping (opsional)</p>
                                    </div>
                                </div>
                                <div class="wf-input-wrap">
                                    <i class="bi bi-person-workspace wf-icon"></i>
                                    <select name="supervisor_2" class="wf-select" data-sup-preview="sup2Preview">
                                        <option value="">-- Pilih Dosen (Opsional) --</option>
                                        <?php foreach ($lecturers as $l): ?>
                                            <option value="<?= $l['id'] ?>" <?= $sup2_id == $l['id'] ? 'selected' : '' ?>
                                                data-name="<?= e($l['name']) ?>" data-degree="<?= e($l['academic_degree'] ?? '') ?>">
                                                <?= e($l['name']) ?> <?= e($l['academic_degree'] ?? '') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="wf-sup-preview" id="sup2Preview"></div>
                            </div>
                        </div>
                    </div>

                    <!-- ===== FOOTER ===== -->
                    <div class="wf-footer">
                        <div class="wf-footer-info">
                            <i class="bi bi-shield-check"></i>
                            <span>Data Anda terenkripsi & disimpan aman</span>
                        </div>
                        <div class="wf-footer-actions">
                            <a href="<?= BASE_URL ?>/admin/works" class="wf-btn wf-btn-cancel">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                            <button type="submit" class="wf-btn wf-btn-submit" id="submitBtn">
                                <span class="wf-spinner"></span>
                                <span class="wf-btn-text">
                                    <i class="bi bi-check-circle-fill"></i>
                                    <?= $isEdit ? 'Perbarui Data' : 'Simpan Tugas Akhir' ?>
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

        const statusLabels = <?= json_encode($statusLabels) ?>;
        const chipsMap = <?= json_encode($chipsMap) ?>;

        const initials = (name) => {
            const parts = (name || '').trim().split(/\s+/).filter(Boolean);
            if (!parts.length) return '-';
            return (parts[0][0] + (parts.length > 1 ? parts[parts.length - 1][0] : '')).toUpperCase();
        };

        // ===== STATUS LIVE PREVIEW =====
        const statusSelect = document.getElementById('statusSelect');
        const statusChip = document.getElementById('statusChip');
        const updateStatus = () => {
            const val = statusSelect.value;
            statusChip.textContent = statusLabels[val] || val;
            statusChip.className = 'wf-chip ' + (chipsMap[val] || 'wf-chip-draft');
        };
        statusSelect?.addEventListener('change', updateStatus);
        updateStatus();

        // ===== TITLE COUNTER =====
        const titleInput = document.getElementById('titleInput');
        const titleCounter = document.getElementById('titleCounter');
        const updateTitleCount = () => {
            const len = titleInput.value.length;
            titleCounter.textContent = len + '/300';
            titleCounter.classList.toggle('warn', len > 250 && len <= 290);
            titleCounter.classList.toggle('danger', len > 290);
        };
        titleInput?.addEventListener('input', updateTitleCount);
        updateTitleCount();

        // ===== KEYWORDS LIVE TAGS =====
        const kwInput = document.getElementById('keywordsInput');
        const kwPreview = document.getElementById('tagsPreview');
        const updateTags = () => {
            const tags = kwInput.value.split(',').map(t => t.trim()).filter(Boolean);
            if (tags.length === 0) {
                kwPreview.innerHTML = '<span class="wf-tag-empty">Ketik untuk melihat preview tags...</span>';
                return;
            }
            kwPreview.innerHTML = tags.map(t => `<span class="wf-tag"><i class="bi bi-hash"></i>${t}</span>`).join('');
        };
        kwInput?.addEventListener('input', updateTags);
        updateTags();

        // ===== SUPERVISOR LIVE PREVIEW =====
        document.querySelectorAll('select[data-sup-preview]').forEach(sel => {
            const previewId = sel.dataset.supPreview;
            const previewEl = document.getElementById(previewId);
            if (!previewEl) return;

            const update = () => {
                const opt = sel.options[sel.selectedIndex];
                if (!opt || !opt.value) {
                    previewEl.classList.remove('is-active');
                    previewEl.innerHTML = '';
                    return;
                }
                const name = opt.dataset.name || '';
                const degree = opt.dataset.degree || '';
                previewEl.innerHTML = `
                    <div class="wf-sup-preview-avatar">${initials(name)}</div>
                    <div class="wf-sup-preview-name">
                        ${name}${degree ? ' <span style="color:var(--gray-500);font-weight:500;">' + degree + '</span>' : ''}
                    </div>
                `;
                previewEl.classList.add('is-active');
            };
            sel.addEventListener('change', update);
            update();
        });

        // ===== FORM VALIDATION =====
        const form = document.getElementById('workForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                let isValid = true;
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.wf-error-msg').forEach(el => el.remove());

                form.querySelectorAll('[required]').forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add('is-invalid');
                        const errorMsg = document.createElement('div');
                        errorMsg.className = 'wf-error-msg';
                        errorMsg.innerHTML = '<i class="bi bi-exclamation-circle-fill"></i> Field ini wajib diisi';
                        field.closest('.wf-field')?.appendChild(errorMsg);
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    if (window.showToast) window.showToast('Mohon lengkapi field yang wajib diisi', 'error');
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

            form.querySelectorAll('.wf-input, .wf-select, .wf-textarea').forEach(field => {
                field.addEventListener('input', function() {
                    this.classList.remove('is-invalid');
                    const error = this.closest('.wf-field')?.querySelector('.wf-error-msg');
                    if (error) error.remove();
                });
                field.addEventListener('change', function() {
                    this.classList.remove('is-invalid');
                    const error = this.closest('.wf-field')?.querySelector('.wf-error-msg');
                    if (error) error.remove();
                });
            });
        }

        console.log('%c✓ Work Form Loaded', 'color: #8b5cf6; font-weight: bold;');
    })();
    </script>
</body>
</html>