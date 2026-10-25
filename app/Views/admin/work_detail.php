<?php
declare(strict_types=1);

$work = $work ?? [];
$supervisors = $work['supervisors'] ?? [];
$students = []; // not used here

// Status meta untuk timeline
$statusTimeline = [
    'draft'            => ['order' => 1, 'label' => 'Draft',           'icon' => 'file-earmark'],
    'proposal'         => ['order' => 2, 'label' => 'Proposal',        'icon' => 'file-earmark-text'],
    'feasibility_test' => ['order' => 3, 'label' => 'Uji Kelayakan',   'icon' => 'clipboard-check'],
    'writing'          => ['order' => 4, 'label' => 'Penulisan',       'icon' => 'pencil'],
    'progress_exam'    => ['order' => 5, 'label' => 'Ujian Kemajuan',  'icon' => 'award'],
    'revision'         => ['order' => 4, 'label' => 'Revisi',          'icon' => 'arrow-repeat'],
    'closed_exam'      => ['order' => 6, 'label' => 'Ujian Tertutup',  'icon' => 'door-closed'],
    'ready_for_defense'=> ['order' => 7, 'label' => 'Siap Sidang',     'icon' => 'check2-circle'],
    'defense'          => ['order' => 8, 'label' => 'Sidang',          'icon' => 'person-video3'],
    'completed'        => ['order' => 9, 'label' => 'Selesai',         'icon' => 'check-circle-fill'],
    'cancelled'        => ['order' => 0, 'label' => 'Dibatalkan',      'icon' => 'x-circle'],
];

$currentStatus = $work['status'] ?? 'draft';
$currentOrder = $statusTimeline[$currentStatus]['order'] ?? 0;

// Progress percentage
$maxOrder = 9;
$progressPercent = $currentStatus === 'cancelled' ? 0 : min(100, round(($currentOrder / $maxOrder) * 100));

// Hitung durasi dalam hari
$startedAt = !empty($work['started_at']) ? strtotime($work['started_at']) : time();
$daysElapsed = max(0, (int) ((time() - $startedAt) / 86400));

// Inisial nama
$initials = function(string $name): string {
    $parts = array_filter(explode(' ', trim($name)));
    if (empty($parts)) return '-';
    return strtoupper(substr($parts[0], 0, 1)) . (count($parts) > 1 ? strtoupper(substr(end($parts), 0, 1)) : '');
};

$statusMeta = [
    'draft'            => ['class' => 'wd-pill-draft',     'label' => 'Draft'],
    'proposal'         => ['class' => 'wd-pill-proposal',  'label' => 'Proposal'],
    'feasibility_test' => ['class' => 'wd-pill-info',      'label' => 'Uji Kelayakan'],
    'writing'          => ['class' => 'wd-pill-writing',   'label' => 'Penulisan'],
    'progress_exam'    => ['class' => 'wd-pill-info',      'label' => 'Ujian Kemajuan'],
    'revision'         => ['class' => 'wd-pill-revision',  'label' => 'Revisi'],
    'closed_exam'      => ['class' => 'wd-pill-info',      'label' => 'Ujian Tertutup'],
    'ready_for_defense'=> ['class' => 'wd-pill-proposal',  'label' => 'Siap Sidang'],
    'defense'          => ['class' => 'wd-pill-proposal',  'label' => 'Sidang'],
    'completed'        => ['class' => 'wd-pill-success',   'label' => 'Selesai'],
    'cancelled'        => ['class' => 'wd-pill-danger',    'label' => 'Dibatalkan'],
];
$currentMeta = $statusMeta[$currentStatus] ?? $statusMeta['draft'];

$keywords = array_filter(array_map('trim', explode(',', (string)($work['keywords'] ?? ''))));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Tugas Akhir - <?= e($work['title']) ?> | <?= e(APP_NAME) ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">
    
    <style>
        /* =========================================================
           WORK DETAIL ULTRA PREMIUM - SCOPED STYLES
           Namespace: .wd- | Tema: Violet (#8b5cf6)
           ========================================================= */
        .wd-view { animation: wdFade 0.4s ease; }
        @keyframes wdFade { from { opacity: 0; } to { opacity: 1; } }

        /* --- Page Header --- */
        .wd-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .wd-back-btn {
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

        .wd-back-btn:hover {
            background: var(--gray-50);
            border-color: var(--gray-400);
            transform: translateX(-3px);
        }

        /* --- Hero Card --- */
        .wd-hero {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 50%, #6d28d9 100%);
            border-radius: var(--radius-2xl);
            padding: 2.5rem;
            color: white;
            position: relative;
            overflow: hidden;
            margin-bottom: 1.75rem;
            box-shadow: 0 20px 40px -10px rgba(139,92,246,0.4);
            animation: wdSlideDown 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes wdSlideDown {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .wd-hero::before {
            content: '';
            position: absolute;
            top: -60%; right: -5%;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            border-radius: 50%;
            animation: wdFloat 8s ease-in-out infinite;
        }

        .wd-hero::after {
            content: '';
            position: absolute;
            bottom: -40%; left: 5%;
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(236,72,153,0.2) 0%, transparent 70%);
            border-radius: 50%;
            animation: wdFloat 10s ease-in-out infinite reverse;
        }

        @keyframes wdFloat {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(-20px, 15px) scale(1.1); }
        }

        .wd-hero-content {
            position: relative;
            z-index: 2;
        }

        .wd-hero-badges {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .wd-hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.85rem;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: var(--radius-full);
            font-size: 0.78rem;
            font-weight: 600;
        }

        .wd-hero-title {
            font-size: 1.85rem;
            font-weight: 800;
            line-height: 1.3;
            margin-bottom: 1rem;
            max-width: 800px;
        }

        .wd-hero-meta {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            font-size: 0.9rem;
            opacity: 0.95;
        }

        .wd-hero-meta-item {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        /* --- Progress Bar --- */
        .wd-progress {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            margin-bottom: 1.75rem;
            animation: wdUp 0.5s ease 0.1s backwards;
        }

        @keyframes wdUp {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .wd-progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .wd-progress-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
            color: var(--gray-800);
            font-size: 1rem;
        }

        .wd-progress-percent {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 800;
            font-size: 1.5rem;
            background: linear-gradient(135deg, #8b5cf6, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .wd-progress-bar {
            height: 10px;
            background: var(--gray-100);
            border-radius: var(--radius-full);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .wd-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #8b5cf6 0%, #ec4899 100%);
            border-radius: var(--radius-full);
            transition: width 1s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
        }

        .wd-progress-fill::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            animation: wdShine 2s infinite;
        }

        @keyframes wdShine {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        /* --- Timeline Steps --- */
        .wd-timeline {
            display: flex;
            justify-content: space-between;
            position: relative;
            gap: 0.5rem;
        }

        .wd-timeline::before {
            content: '';
            position: absolute;
            top: 20px; left: 20px; right: 20px;
            height: 2px;
            background: var(--gray-200);
            z-index: 0;
        }

        .wd-step {
            position: relative;
            z-index: 1;
            text-align: center;
            flex: 1;
            min-width: 0;
        }

        .wd-step-dot {
            width: 40px; height: 40px;
            margin: 0 auto 0.5rem;
            border-radius: 50%;
            background: white;
            border: 2px solid var(--gray-300);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-400);
            font-size: 1rem;
            transition: all 0.3s;
        }

        .wd-step.is-done .wd-step-dot {
            background: #10b981;
            border-color: #10b981;
            color: white;
            box-shadow: 0 4px 10px -3px rgba(16,185,129,0.5);
        }

        .wd-step.is-current .wd-step-dot {
            background: linear-gradient(135deg, #8b5cf6, #ec4899);
            border-color: transparent;
            color: white;
            box-shadow: 0 4px 12px -3px rgba(139,92,246,0.5);
            animation: wdPulse 2s infinite;
        }

        @keyframes wdPulse {
            0%, 100% { transform: scale(1); box-shadow: 0 4px 12px -3px rgba(139,92,246,0.5); }
            50% { transform: scale(1.08); box-shadow: 0 6px 20px -3px rgba(139,92,246,0.7); }
        }

        .wd-step-label {
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.03em;
            line-height: 1.3;
        }

        .wd-step.is-done .wd-step-label { color: #10b981; }
        .wd-step.is-current .wd-step-label { color: #7c3aed; font-weight: 700; }

        /* --- Grid Layout --- */
        .wd-grid-2 {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 968px) {
            .wd-grid-2 { grid-template-columns: 1fr; }
        }

        /* --- Cards --- */
        .wd-card {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-xl);
            overflow: hidden;
            animation: wdUp 0.5s ease backwards;
        }

        .wd-card.delay-1 { animation-delay: 0.1s; }
        .wd-card.delay-2 { animation-delay: 0.2s; }
        .wd-card.delay-3 { animation-delay: 0.3s; }

        .wd-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            background: linear-gradient(to right, var(--gray-50), white);
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .wd-card-header i {
            color: #7c3aed;
            font-size: 1.15rem;
        }

        .wd-card-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--gray-800);
            margin: 0;
        }

        .wd-card-body { padding: 1.5rem; }

        /* --- Info List --- */
        .wd-info-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .wd-info-item {
            display: flex;
            align-items: flex-start;
            gap: 0.85rem;
            padding-bottom: 1rem;
            border-bottom: 1px dashed var(--gray-200);
        }

        .wd-info-item:last-child { border-bottom: none; padding-bottom: 0; }

        .wd-info-icon {
            width: 38px; height: 38px;
            border-radius: var(--radius-lg);
            background: #ede9fe;
            color: #7c3aed;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .wd-info-content { flex: 1; min-width: 0; }

        .wd-info-label {
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.2rem;
        }

        .wd-info-value {
            font-weight: 600;
            color: var(--gray-800);
            font-size: 0.95rem;
            line-height: 1.4;
        }

        .wd-info-sub {
            color: var(--gray-500);
            font-size: 0.8rem;
            margin-top: 0.15rem;
        }

        .wd-mono {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 600;
            color: #7c3aed;
        }

        /* --- Status pill --- */
        .wd-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.85rem;
            border-radius: var(--radius-full);
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .wd-pill::before {
            content: '';
            width: 7px; height: 7px;
            border-radius: 50%;
            background: currentColor;
        }

        .wd-pill-draft { background: var(--gray-100); color: var(--gray-600); }
        .wd-pill-proposal { background: #dbeafe; color: #1e40af; }
        .wd-pill-writing { background: #cffafe; color: #155e75; }
        .wd-pill-revision { background: #fef3c7; color: #92400e; }
        .wd-pill-success { background: #d1fae5; color: #065f46; }
        .wd-pill-info { background: #ede9fe; color: #6d28d9; }
        .wd-pill-danger { background: #fee2e2; color: #991b1b; }

        /* --- Abstract --- */
        .wd-abstract {
            background: linear-gradient(135deg, #faf5ff 0%, #ede9fe 100%);
            border-left: 4px solid #8b5cf6;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            color: var(--gray-700);
            line-height: 1.75;
            font-size: 0.925rem;
            position: relative;
        }

        .wd-abstract::before {
            content: '"';
            position: absolute;
            top: -10px; left: 15px;
            font-size: 4rem;
            color: #c4b5fd;
            font-family: Georgia, serif;
            line-height: 1;
        }

        /* --- Keywords --- */
        .wd-keywords {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .wd-keyword {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.4rem 0.85rem;
            background: linear-gradient(135deg, #ede9fe, #ddd6fe);
            color: #6d28d9;
            border-radius: var(--radius-full);
            font-size: 0.8rem;
            font-weight: 600;
            transition: all 0.25s;
            cursor: default;
        }

        .wd-keyword:hover {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: white;
            transform: translateY(-2px);
        }

        /* --- Supervisors --- */
        .wd-sup-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1rem;
        }

        .wd-sup-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.25rem;
            background: white;
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-xl);
            transition: all 0.3s;
        }

        .wd-sup-card:hover {
            border-color: #8b5cf6;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px -8px rgba(139,92,246,0.25);
        }

        .wd-sup-avatar {
            width: 56px; height: 56px;
            border-radius: var(--radius-lg);
            background: linear-gradient(135deg, #8b5cf6, #ec4899);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            font-size: 1.1rem;
            flex-shrink: 0;
            box-shadow: var(--shadow-md);
        }

        .wd-sup-content { flex: 1; min-width: 0; }

        .wd-sup-role {
            font-size: 0.7rem;
            font-weight: 700;
            color: #7c3aed;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.25rem;
        }

        .wd-sup-name {
            font-weight: 700;
            color: var(--gray-900);
            font-size: 0.95rem;
            line-height: 1.3;
        }

        .wd-sup-degree {
            color: var(--gray-500);
            font-size: 0.8rem;
            margin-top: 0.15rem;
        }

        /* --- Stats Grid --- */
        .wd-stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 0.85rem;
        }

        .wd-mini-stat {
            padding: 1rem;
            background: var(--gray-50);
            border-radius: var(--radius-lg);
            text-align: center;
            transition: all 0.3s;
        }

        .wd-mini-stat:hover {
            background: white;
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .wd-mini-icon {
            width: 40px; height: 40px;
            margin: 0 auto 0.6rem;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.1rem;
        }

        .wd-mini-icon.c1 { background: linear-gradient(135deg,#8b5cf6,#7c3aed); }
        .wd-mini-icon.c2 { background: linear-gradient(135deg,#ec4899,#be185d); }
        .wd-mini-icon.c3 { background: linear-gradient(135deg,#f59e0b,#d97706); }
        .wd-mini-icon.c4 { background: linear-gradient(135deg,#10b981,#059669); }

        .wd-mini-num {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--gray-900);
            line-height: 1.1;
            font-variant-numeric: tabular-nums;
        }

        .wd-mini-label {
            font-size: 0.72rem;
            color: var(--gray-500);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-top: 0.2rem;
        }

        /* --- Action Buttons --- */
        .wd-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .wd-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border-radius: var(--radius-lg);
            font-weight: 600;
            font-size: 0.875rem;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            border: 1.5px solid transparent;
            cursor: pointer;
            font-family: inherit;
        }

        .wd-action-primary {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: white;
            box-shadow: 0 8px 16px -6px rgba(139,92,246,0.5);
        }

        .wd-action-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px -6px rgba(139,92,246,0.6);
        }

        .wd-action-secondary {
            background: white;
            color: var(--gray-700);
            border-color: var(--gray-200);
        }

        .wd-action-secondary:hover {
            background: var(--gray-50);
            border-color: var(--gray-400);
        }

        /* --- Empty Supervisor --- */
        .wd-empty-sup {
            text-align: center;
            padding: 2rem 1rem;
            color: var(--gray-500);
        }

        .wd-empty-sup i {
            font-size: 2.5rem;
            opacity: 0.3;
            display: block;
            margin-bottom: 0.5rem;
        }

        @media (max-width: 768px) {
            .wd-hero { padding: 1.5rem; }
            .wd-hero-title { font-size: 1.35rem; }
            .wd-step-label { font-size: 0.6rem; }
            .wd-timeline { gap: 0.25rem; }
            .wd-step-dot { width: 32px; height: 32px; font-size: 0.8rem; }
        }
    </style>
</head>
<body>
    <?php require dirname(__DIR__) . '/partials/navbar.php'; ?>

    <div class="container wd-view">
        <!-- Page Header -->
        <div class="wd-header">
            <a href="<?= BASE_URL ?>/admin/works" class="wd-back-btn">
                <i class="bi bi-arrow-left"></i> Kembali ke Daftar
            </a>
            <div class="wd-actions">
                <a href="<?= BASE_URL ?>/admin/works/edit?id=<?= $work['id'] ?>" class="wd-action-btn wd-action-primary">
                    <i class="bi bi-pencil-fill"></i> Edit Tugas Akhir
                </a>
            </div>
        </div>

        <!-- Hero Card -->
        <div class="wd-hero">
            <div class="wd-hero-content">
                <div class="wd-hero-badges">
                    <span class="wd-hero-badge">
                        <i class="bi bi-bookmark-fill"></i>
                        <?= e($work['work_type_name'] ?? 'Tugas Akhir') ?>
                    </span>
                    <span class="wd-hero-badge">
                        <i class="bi bi-calendar-event"></i>
                        Dimulai <?= date('d M Y', strtotime($work['started_at'])) ?>
                    </span>
                    <span class="wd-hero-badge">
                        <i class="bi bi-clock-fill"></i>
                        <?= $daysElapsed ?> hari berjalan
                    </span>
                </div>
                <h1 class="wd-hero-title"><?= e($work['title']) ?></h1>
                <div class="wd-hero-meta">
                    <div class="wd-hero-meta-item">
                        <i class="bi bi-mortarboard-fill"></i>
                        <strong><?= e($work['student_name']) ?></strong>
                    </div>
                    <div class="wd-hero-meta-item">
                        <i class="bi bi-hash"></i>
                        <?= e($work['student_number'] ?? '—') ?>
                    </div>
                    <div class="wd-hero-meta-item">
                        <i class="bi bi-building"></i>
                        <?= e($work['study_program']) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Card -->
        <div class="wd-progress">
            <div class="wd-progress-header">
                <div class="wd-progress-title">
                    <i class="bi bi-activity"></i> Progress Tugas Akhir
                </div>
                <div class="wd-progress-percent" id="wdPercent">0%</div>
            </div>
            <div class="wd-progress-bar">
                <div class="wd-progress-fill" id="wdProgressFill" style="width: 0%;"></div>
            </div>

            <!-- Timeline Steps -->
            <div class="wd-timeline">
                <?php
                $steps = [
                    ['key' => 'draft', 'icon' => 'file-earmark', 'label' => 'Draft'],
                    ['key' => 'proposal', 'icon' => 'file-text', 'label' => 'Proposal'],
                    ['key' => 'writing', 'icon' => 'pencil', 'label' => 'Penulisan'],
                    ['key' => 'revision', 'icon' => 'arrow-repeat', 'label' => 'Revisi'],
                    ['key' => 'completed', 'icon' => 'check-circle-fill', 'label' => 'Selesai'],
                ];
                foreach ($steps as $idx => $step):
                    $stepOrder = $statusTimeline[$step['key']]['order'] ?? 0;
                    $isDone = $currentOrder > $stepOrder && $currentStatus !== 'cancelled';
                    $isCurrent = $currentStatus === $step['key'];
                    $cls = $isDone ? 'is-done' : ($isCurrent ? 'is-current' : '');
                ?>
                <div class="wd-step <?= $cls ?>">
                    <div class="wd-step-dot">
                        <i class="bi bi-<?= $isDone ? 'check-lg' : $step['icon'] ?>"></i>
                    </div>
                    <div class="wd-step-label"><?= $step['label'] ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Grid 2 Column -->
        <div class="wd-grid-2">
            <!-- Left: Info + Abstract -->
            <div>
                <!-- Info Card -->
                <div class="wd-card delay-1">
                    <div class="wd-card-header">
                        <i class="bi bi-info-circle-fill"></i>
                        <h3 class="wd-card-title">Informasi Umum</h3>
                    </div>
                    <div class="wd-card-body">
                        <div class="wd-info-list">
                            <div class="wd-info-item">
                                <div class="wd-info-icon"><i class="bi bi-person-fill"></i></div>
                                <div class="wd-info-content">
                                    <div class="wd-info-label">Mahasiswa</div>
                                    <div class="wd-info-value"><?= e($work['student_name']) ?></div>
                                    <div class="wd-info-sub wd-mono"><?= e($work['student_number'] ?? '—') ?></div>
                                </div>
                            </div>
                            <div class="wd-info-item">
                                <div class="wd-info-icon"><i class="bi bi-building"></i></div>
                                <div class="wd-info-content">
                                    <div class="wd-info-label">Program Studi & Fakultas</div>
                                    <div class="wd-info-value"><?= e($work['study_program']) ?></div>
                                    <div class="wd-info-sub"><?= e($work['faculty'] ?? 'Fakultas tidak disebutkan') ?></div>
                                </div>
                            </div>
                            <div class="wd-info-item">
                                <div class="wd-info-icon"><i class="bi bi-flag-fill"></i></div>
                                <div class="wd-info-content">
                                    <div class="wd-info-label">Status Saat Ini</div>
                                    <div class="wd-info-value">
                                        <span class="wd-pill <?= $currentMeta['class'] ?>">
                                            <?= $currentMeta['label'] ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="wd-info-item">
                                <div class="wd-info-icon"><i class="bi bi-calendar-event"></i></div>
                                <div class="wd-info-content">
                                    <div class="wd-info-label">Tanggal Mulai</div>
                                    <div class="wd-info-value"><?= date('d F Y', strtotime($work['started_at'])) ?></div>
                                    <div class="wd-info-sub"><?= $daysElapsed ?> hari yang lalu</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Abstract Card -->
                <?php if (!empty($work['abstract'])): ?>
                <div class="wd-card delay-2" style="margin-top: 1.5rem;">
                    <div class="wd-card-header">
                        <i class="bi bi-quote"></i>
                        <h3 class="wd-card-title">Abstrak</h3>
                    </div>
                    <div class="wd-card-body">
                        <div class="wd-abstract">
                            <?= nl2br(e($work['abstract'])) ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Keywords Card -->
                <?php if (!empty($keywords)): ?>
                <div class="wd-card delay-3" style="margin-top: 1.5rem;">
                    <div class="wd-card-header">
                        <i class="bi bi-tags-fill"></i>
                        <h3 class="wd-card-title">Kata Kunci</h3>
                    </div>
                    <div class="wd-card-body">
                        <div class="wd-keywords">
                            <?php foreach ($keywords as $kw): ?>
                                <span class="wd-keyword">
                                    <i class="bi bi-hash"></i>
                                    <?= e($kw) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right: Supervisors + Stats -->
            <div>
                <!-- Supervisors Card -->
                <div class="wd-card delay-1">
                    <div class="wd-card-header">
                        <i class="bi bi-people-fill"></i>
                        <h3 class="wd-card-title">Dosen Pembimbing</h3>
                    </div>
                    <div class="wd-card-body">
                        <?php if (empty($supervisors)): ?>
                            <div class="wd-empty-sup">
                                <i class="bi bi-person-x-fill"></i>
                                <div>Belum ada pembimbing</div>
                                <small>Dosen belum ditetapkan</small>
                            </div>
                        <?php else: ?>
                            <div class="wd-sup-grid">
                                <?php foreach ($supervisors as $sup): ?>
                                    <div class="wd-sup-card">
                                        <div class="wd-sup-avatar"><?= e($initials($sup['lecturer_name'])) ?></div>
                                        <div class="wd-sup-content">
                                            <div class="wd-sup-role">
                                                <?= ucwords(str_replace('_', ' ', $sup['assignment_role'])) ?>
                                            </div>
                                            <div class="wd-sup-name"><?= e($sup['lecturer_name']) ?></div>
                                            <div class="wd-sup-degree"><?= e($sup['academic_degree'] ?? '') ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Stats Card -->
                <div class="wd-card delay-2" style="margin-top: 1.5rem;">
                    <div class="wd-card-header">
                        <i class="bi bi-graph-up-arrow"></i>
                        <h3 class="wd-card-title">Statistik Cepat</h3>
                    </div>
                    <div class="wd-card-body">
                        <div class="wd-stat-grid">
                            <div class="wd-mini-stat">
                                <div class="wd-mini-icon c1"><i class="bi bi-calendar3"></i></div>
                                <div class="wd-mini-num"><?= $daysElapsed ?></div>
                                <div class="wd-mini-label">Hari</div>
                            </div>
                            <div class="wd-mini-stat">
                                <div class="wd-mini-icon c2"><i class="bi bi-people"></i></div>
                                <div class="wd-mini-num"><?= count($supervisors) ?></div>
                                <div class="wd-mini-label">Pembimbing</div>
                            </div>
                            <div class="wd-mini-stat">
                                <div class="wd-mini-icon c3"><i class="bi bi-file-earmark-text"></i></div>
                                <div class="wd-mini-num">0</div>
                                <div class="wd-mini-label">Dokumen</div>
                            </div>
                            <div class="wd-mini-stat">
                                <div class="wd-mini-icon c4"><i class="bi bi-check2-circle"></i></div>
                                <div class="wd-mini-num"><?= $progressPercent ?>%</div>
                                <div class="wd-mini-label">Progress</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/admin.js"></script>
    <script>
    (function() {
        'use strict';

        // ===== PROGRESS BAR ANIMATION =====
        const targetPercent = <?= $progressPercent ?>;
        const fill = document.getElementById('wdProgressFill');
        const percentEl = document.getElementById('wdPercent');

        const animateProgress = () => {
            if (!fill || !percentEl) return;
            fill.style.width = targetPercent + '%';

            const duration = 1500;
            const startTime = performance.now();
            const animate = (now) => {
                const progress = Math.min((now - startTime) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                const current = Math.round(eased * targetPercent);
                percentEl.textContent = current + '%';
                if (progress < 1) requestAnimationFrame(animate);
            };
            requestAnimationFrame(animate);
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', animateProgress);
        } else {
            animateProgress();
        }

        console.log('%c✓ Work Detail Loaded', 'color: #8b5cf6; font-weight: bold;');
    })();
    </script>
</body>
</html>