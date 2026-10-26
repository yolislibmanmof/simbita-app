<?php
declare(strict_types=1);
$flashError   = $_SESSION['flash_error']   ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

$myWork       = $myWork ?? null;
$myDocuments  = $myDocuments ?? [];
$myMilestones = $myMilestones ?? [];
$upcomingMilestones = $upcomingMilestones ?? [];

// Stats calculation
$docCount = count($myDocuments);
$completedMilestones = count(array_filter($myMilestones, fn($m) => !empty($m['completed_at'])));
$totalMilestones = count($myMilestones);
$nextMilestone = null;
foreach ($myMilestones as $m) {
    if (empty($m['completed_at']) && strtotime($m['due_date']) >= time()) {
        $nextMilestone = $m;
        break;
    }
}

// Greeting
$hour = (int) date('H');
$greeting = $hour < 11 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 18 ? 'Selamat Sore' : 'Selamat Malam'));
$userName = $_SESSION['simbita_user']['name'] ?? 'Mahasiswa';
$firstName = explode(' ', $userName)[0];

$statusMeta = [
    'draft'            => ['label' => 'Draft',          'class' => 'sd-pill-draft',    'icon' => 'file-earmark'],
    'proposal'         => ['label' => 'Proposal',       'class' => 'sd-pill-proposal', 'icon' => 'file-earmark-text'],
    'feasibility_test' => ['label' => 'Uji Kelayakan',  'class' => 'sd-pill-info',     'icon' => 'clipboard-check'],
    'writing'          => ['label' => 'Penulisan',      'class' => 'sd-pill-writing',  'icon' => 'pencil'],
    'progress_exam'    => ['label' => 'Ujian Kemajuan', 'class' => 'sd-pill-info',     'icon' => 'award'],
    'revision'         => ['label' => 'Revisi',         'class' => 'sd-pill-revision', 'icon' => 'arrow-repeat'],
    'closed_exam'      => ['label' => 'Ujian Tertutup', 'class' => 'sd-pill-info',     'icon' => 'door-closed'],
    'ready_for_defense'=> ['label' => 'Siap Sidang',    'class' => 'sd-pill-proposal', 'icon' => 'check2-circle'],
    'defense'          => ['label' => 'Sidang',         'class' => 'sd-pill-proposal', 'icon' => 'person-video3'],
    'completed'        => ['label' => 'Selesai',        'class' => 'sd-pill-success',  'icon' => 'check-circle-fill'],
    'cancelled'        => ['label' => 'Dibatalkan',     'class' => 'sd-pill-danger',   'icon' => 'x-circle'],
];
$currentMeta = $myWork ? ($statusMeta[$myWork['status']] ?? $statusMeta['draft']) : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa - <?= e(APP_NAME) ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">
    
    <style>
        /* =========================================================
           STUDENT DASHBOARD ULTRA PREMIUM - SCOPED STYLES
           Namespace: .sd- | Tema: Ocean Blue (#3b82f6)
           ========================================================= */
        .sd-view {
            --sd-primary: #3b82f6;
            --sd-primary-dark: #2563eb;
            --sd-primary-light: #dbeafe;
            --sd-gradient: linear-gradient(135deg, #3b82f6 0%, #2563eb 50%, #1e40af 100%);
            --sd-glow: 0 10px 30px -10px rgba(59, 130, 246, 0.4);
            animation: sdFade 0.4s ease;
        }
        @keyframes sdFade { from { opacity: 0; } to { opacity: 1; } }

        /* --- Welcome Banner --- */
        .sd-welcome {
            background: var(--sd-gradient);
            border-radius: var(--radius-2xl);
            padding: 2.5rem;
            color: white;
            position: relative;
            overflow: hidden;
            margin-bottom: 2rem;
            box-shadow: 0 20px 40px -10px rgba(59,130,246,0.4);
            animation: sdSlideIn 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes sdSlideIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .sd-welcome::before {
            content: '';
            position: absolute;
            top: -50%; right: -10%;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            border-radius: 50%;
            animation: sdFloat 6s ease-in-out infinite;
        }

        .sd-welcome::after {
            content: '';
            position: absolute;
            bottom: -30%; left: 10%;
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(139,92,246,0.2) 0%, transparent 70%);
            border-radius: 50%;
            animation: sdFloat 8s ease-in-out infinite reverse;
        }

        @keyframes sdFloat {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(-20px, -20px); }
        }

        .sd-welcome-content {
            position: relative;
            z-index: 2;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .sd-welcome-text h2 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sd-wave {
            display: inline-block;
            animation: sdWave 2s ease-in-out infinite;
            transform-origin: 70% 70%;
        }

        @keyframes sdWave {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(20deg); }
            75% { transform: rotate(-15deg); }
        }

        .sd-welcome-text p {
            font-size: 1.05rem;
            opacity: 0.9;
            max-width: 500px;
        }

        .sd-welcome-badges {
            display: flex;
            gap: 0.6rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .sd-welcome-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 1rem;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: var(--radius-full);
            font-weight: 600;
            font-size: 0.85rem;
        }

        .sd-welcome-date {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            padding: 0.625rem 1.25rem;
            border-radius: var(--radius-full);
            font-weight: 600;
            font-size: 0.9rem;
            border: 1px solid rgba(255,255,255,0.2);
        }

        /* --- Stats Grid --- */
        .sd-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .sd-stat {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
            animation: sdUp 0.5s ease backwards;
        }

        .sd-stat:nth-child(1) { animation-delay: 0.05s; }
        .sd-stat:nth-child(2) { animation-delay: 0.1s; }
        .sd-stat:nth-child(3) { animation-delay: 0.15s; }

        @keyframes sdUp {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .sd-stat::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(59,130,246,0.05), transparent);
            transition: left 0.7s;
        }

        .sd-stat:hover::before { left: 100%; }

        .sd-stat:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 30px -10px rgba(0,0,0,0.12);
            border-color: var(--sd-primary);
        }

        .sd-stat-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .sd-stat-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .sd-stat-icon {
            width: 44px; height: 44px;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
            flex-shrink: 0;
            transition: transform 0.3s;
        }

        .sd-stat:hover .sd-stat-icon { transform: scale(1.1) rotate(-5deg); }

        .sd-stat-icon.i-blue { background: linear-gradient(135deg,#3b82f6,#1e40af); }
        .sd-stat-icon.i-green { background: linear-gradient(135deg,#10b981,#059669); }
        .sd-stat-icon.i-purple { background: linear-gradient(135deg,#8b5cf6,#6d28d9); }

        .sd-stat-value {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--gray-900);
            line-height: 1;
            margin-bottom: 0.5rem;
            font-variant-numeric: tabular-nums;
        }

        .sd-stat-desc {
            font-size: 0.8rem;
            color: var(--gray-500);
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        /* --- Main Grid --- */
        .sd-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
        }

        @media (max-width: 968px) {
            .sd-grid { grid-template-columns: 1fr; }
        }

        /* --- Cards --- */
        .sd-card {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            animation: sdUp 0.5s ease 0.2s backwards;
        }

        .sd-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            background: linear-gradient(to right, var(--gray-50), white);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .sd-card-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
        }

        .sd-card-title i { color: var(--sd-primary); }

        .sd-card-body { padding: 1.5rem; }

        /* --- Work Info --- */
        .sd-work-title {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }

        .sd-work-type {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 0.85rem;
            background: var(--sd-primary-light);
            color: var(--sd-primary-dark);
            border-radius: var(--radius-full);
            font-size: 0.8rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        .sd-abstract-box {
            background: var(--gray-50);
            border-left: 4px solid var(--sd-primary);
            padding: 1.25rem;
            border-radius: 0 var(--radius-md) var(--radius-md) 0;
            color: var(--gray-700);
            font-size: 0.9rem;
            line-height: 1.7;
            margin-top: 1rem;
        }

        /* --- Documents List --- */
        .sd-doc-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .sd-doc-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            background: white;
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-lg);
            transition: all 0.25s;
        }

        .sd-doc-item:hover {
            border-color: var(--sd-primary);
            background: #f8fafc;
            transform: translateX(4px);
        }

        .sd-doc-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            min-width: 0;
            flex: 1;
        }

        .sd-doc-icon {
            width: 44px; height: 44px;
            border-radius: var(--radius-md);
            background: #fee2e2;
            color: #dc2626;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .sd-doc-details { min-width: 0; }

        .sd-doc-name {
            font-weight: 700;
            color: var(--gray-900);
            font-size: 0.9rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 0.25rem;
        }

        .sd-doc-meta {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.75rem;
            color: var(--gray-500);
        }

        .sd-doc-version {
            display: inline-flex;
            align-items: center;
            gap: 0.2rem;
            padding: 0.15rem 0.5rem;
            background: #ede9fe;
            color: #6d28d9;
            border-radius: var(--radius-full);
            font-weight: 700;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.7rem;
        }

        .sd-doc-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 0.85rem;
            background: white;
            color: var(--gray-700);
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 0.8rem;
            text-decoration: none;
            transition: all 0.25s;
            flex-shrink: 0;
        }

        .sd-doc-btn:hover {
            background: var(--sd-primary);
            color: white;
            border-color: var(--sd-primary);
        }

        /* --- Milestones Timeline --- */
        .sd-milestone-list {
            position: relative;
            padding-left: 1.5rem;
        }

        .sd-milestone-list::before {
            content: '';
            position: absolute;
            top: 0.5rem; bottom: 0.5rem; left: 0.5rem;
            width: 2px;
            background: var(--gray-200);
            border-radius: var(--radius-full);
        }

        .sd-milestone-item {
            position: relative;
            padding-bottom: 1.5rem;
        }

        .sd-milestone-item:last-child { padding-bottom: 0; }

        .sd-milestone-dot {
            position: absolute;
            left: -1.5rem; top: 0.25rem;
            width: 24px; height: 24px;
            border-radius: 50%;
            background: white;
            border: 3px solid var(--gray-300);
            z-index: 2;
            transition: all 0.3s;
        }

        .sd-milestone-item.is-done .sd-milestone-dot {
            background: #10b981;
            border-color: #10b981;
        }

        .sd-milestone-item.is-next .sd-milestone-dot {
            background: var(--sd-primary);
            border-color: var(--sd-primary);
            box-shadow: 0 0 0 4px rgba(59,130,246,0.2);
            animation: sdPulse 2s infinite;
        }

        @keyframes sdPulse {
            0%, 100% { box-shadow: 0 0 0 4px rgba(59,130,246,0.2); }
            50% { box-shadow: 0 0 0 8px rgba(59,130,246,0); }
        }

        .sd-milestone-card {
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-lg);
            padding: 1rem;
            transition: all 0.25s;
        }

        .sd-milestone-item:hover .sd-milestone-card {
            background: white;
            border-color: var(--sd-primary);
            transform: translateX(4px);
        }

        .sd-milestone-title {
            font-weight: 700;
            color: var(--gray-900);
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .sd-milestone-desc {
            font-size: 0.8rem;
            color: var(--gray-600);
            margin-bottom: 0.5rem;
            line-height: 1.5;
        }

        .sd-milestone-date {
            font-size: 0.75rem;
            color: var(--gray-500);
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .sd-milestone-date.overdue { color: #dc2626; font-weight: 600; }
        .sd-milestone-date.done { color: #059669; font-weight: 600; }

        /* --- Status Pills --- */
        .sd-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.8rem;
            border-radius: var(--radius-full);
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            white-space: nowrap;
        }

        .sd-pill::before {
            content: '';
            width: 6px; height: 6px;
            border-radius: 50%;
            background: currentColor;
        }

        .sd-pill-draft { background: var(--gray-100); color: var(--gray-600); }
        .sd-pill-proposal { background: #dbeafe; color: #1e40af; }
        .sd-pill-writing { background: #cffafe; color: #155e75; }
        .sd-pill-revision { background: #fef3c7; color: #92400e; }
        .sd-pill-revision::before { animation: sdSpin 2s linear infinite; }
        .sd-pill-success { background: #d1fae5; color: #065f46; }
        .sd-pill-success::before { animation: sdPulse 1.6s infinite; }
        .sd-pill-info { background: #ede9fe; color: #6d28d9; }
        .sd-pill-danger { background: #fee2e2; color: #991b1b; }

        @keyframes sdSpin { to { transform: rotate(360deg); } }

        /* --- Empty State --- */
        .sd-empty {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--gray-500);
        }

        .sd-empty-icon {
            width: 80px; height: 80px;
            margin: 0 auto 1rem;
            border-radius: 50%;
            background: var(--sd-primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.25rem;
            color: var(--sd-primary);
            animation: sdEmptyPulse 2s ease-in-out infinite;
        }

        @keyframes sdEmptyPulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(59,130,246,0.3); }
            50% { transform: scale(1.05); box-shadow: 0 0 0 15px rgba(59,130,246,0); }
        }

        .sd-empty h3 { font-size: 1.1rem; color: var(--gray-800); margin-bottom: 0.35rem; }
        .sd-empty p { font-size: 0.85rem; margin: 0; }

        /* --- Alerts --- */
        .sd-view .alert-dismissible {
            animation: sdSlideRight 0.4s ease;
        }

        @keyframes sdSlideRight {
            from { opacity: 0; transform: translateX(30px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @media (max-width: 768px) {
            .sd-welcome { padding: 1.5rem; }
            .sd-welcome-text h2 { font-size: 1.5rem; }
            .sd-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php require dirname(__DIR__) . '/partials/navbar.php'; ?>

    <div class="container sd-view">
        <!-- Welcome Banner -->
        <div class="sd-welcome">
            <div class="sd-welcome-content">
                <div class="sd-welcome-text">
                    <h2>
                        <span class="sd-wave">👋</span>
                        <?= $greeting ?>, <?= e($firstName) ?>!
                    </h2>
                    <p>Pantau progress tugas akhir, unggah dokumen, dan lihat deadline milestone Anda di sini.</p>
                    <div class="sd-welcome-badges">
                        <?php if ($myWork): ?>
                            <span class="sd-welcome-badge">
                                <i class="bi bi-file-earmark-text"></i>
                                <?= e($myWork['work_type_name']) ?>
                            </span>
                            <span class="sd-welcome-badge">
                                <i class="bi bi-flag-fill"></i>
                                <?= $currentMeta['label'] ?>
                            </span>
                        <?php else: ?>
                            <span class="sd-welcome-badge">
                                <i class="bi bi-info-circle"></i>
                                Belum ada tugas akhir terdaftar
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                    <div class="dash-welcome-date">
                    <i class="bi bi-calendar-event"></i>
                    <?= e(id_date()) ?>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if ($flashError): ?>
            <div class="alert alert-danger alert-dismissible">
                <div class="alert-content"><i class="bi bi-exclamation-triangle-fill"></i> <?= e($flashError) ?></div>
                <button class="btn-close">&times;</button>
            </div>
        <?php endif; ?>
        <?php if ($flashSuccess): ?>
            <div class="alert alert-success alert-dismissible">
                <div class="alert-content"><i class="bi bi-check-circle-fill"></i> <?= e($flashSuccess) ?></div>
                <button class="btn-close">&times;</button>
            </div>
        <?php endif; ?>

        <?php if (!$myWork): ?>
            <!-- Empty State -->
            <div class="sd-card">
                <div class="sd-empty">
                    <div class="sd-empty-icon"><i class="bi bi-inbox-fill"></i></div>
                    <h3>Belum Ada Tugas Akhir</h3>
                    <p>Anda belum memiliki tugas akhir yang terdaftar. Silakan hubungi administrator atau dosen pembimbing Anda untuk memulai proses bimbingan.</p>
                </div>
            </div>
        <?php else: ?>
            <!-- Stats -->
            <div class="sd-stats">
                <div class="sd-stat">
                    <div class="sd-stat-top">
                        <div class="sd-stat-label"><i class="bi bi-flag-fill"></i> Status Saat Ini</div>
                        <div class="sd-stat-icon i-blue"><i class="bi bi-<?= $currentMeta['icon'] ?>"></i></div>
                    </div>
                    <div class="sd-stat-value"><?= $currentMeta['label'] ?></div>
                    <div class="sd-stat-desc">
                        <span style="color: var(--sd-primary); font-weight: 600;">Sejak <?= date('d M Y', strtotime($myWork['started_at'])) ?></span>
                    </div>
                </div>
                <div class="sd-stat">
                    <div class="sd-stat-top">
                        <div class="sd-stat-label"><i class="bi bi-file-earmark"></i> Dokumen</div>
                        <div class="sd-stat-icon i-purple"><i class="bi bi-folder-fill"></i></div>
                    </div>
                    <div class="sd-stat-value"><?= $docCount ?></div>
                    <div class="sd-stat-desc">
                        <span class="sd-trend-up"><i class="bi bi-check-circle-fill"></i> File terunggah</span>
                    </div>
                </div>
                <div class="sd-stat">
                    <div class="sd-stat-top">
                        <div class="sd-stat-label"><i class="bi bi-calendar-check"></i> Milestone</div>
                        <div class="sd-stat-icon i-green"><i class="bi bi-trophy-fill"></i></div>
                    </div>
                    <div class="sd-stat-value"><?= $completedMilestones ?> / <?= $totalMilestones ?></div>
                    <div class="sd-stat-desc">
                        <?php if ($nextMilestone): ?>
                            <span style="color: var(--accent-orange); font-weight: 600;">Next: <?= date('d M', strtotime($nextMilestone['due_date'])) ?></span>
                        <?php else: ?>
                            <span class="sd-trend-up"><i class="bi bi-check2-circle"></i> Semua selesai</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Main Grid -->
            <div class="sd-grid">
                <!-- Left: Work Info & Documents -->
                <div>
                    <!-- Academic Work Info -->
                    <div class="sd-card" style="margin-bottom: 1.5rem;">
                        <div class="sd-card-header">
                            <h3 class="sd-card-title">
                                <i class="bi bi-file-earmark-text"></i>
                                Detail Tugas Akhir
                            </h3>
                            <span class="sd-pill <?= $currentMeta['class'] ?>">
                                <i class="bi bi-<?= $currentMeta['icon'] ?>"></i>
                                <?= $currentMeta['label'] ?>
                            </span>
                        </div>
                        <div class="sd-card-body">
                            <h4 class="sd-work-title"><?= e($myWork['title']) ?></h4>
                            <div class="sd-work-type">
                                <i class="bi bi-bookmark-fill"></i>
                                <?= e($myWork['work_type_name']) ?>
                            </div>

                            <?php if (!empty($myWork['abstract'])): ?>
                                <h6 style="font-size: 0.85rem; font-weight: 700; color: var(--gray-700); margin-bottom: 0.5rem;">Abstrak:</h6>
                                <div class="sd-abstract-box">
                                    <?= nl2br(e($myWork['abstract'])) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- === WIDGET DEADLINE MENDEKAT (FITUR BARU) === -->
                    <?php if (!empty($upcomingMilestones)): ?>
                    <div class="sd-card" style="margin-bottom: 1.5rem; border: 1.5px solid #fef3c7; background: linear-gradient(135deg, #fffbeb 0%, #ffffff 100%);">
                        <div class="sd-card-header" style="background: linear-gradient(to right, #fef3c7, #fffbeb);">
                            <h3 class="sd-card-title" style="color: #92400e;">
                                <i class="bi bi-alarm-fill" style="color: #f59e0b;"></i>
                                Deadline Mendekat
                            </h3>
                            <span style="font-size: 0.72rem; color: #92400e; font-weight: 600; padding: 0.25rem 0.6rem; background: white; border-radius: var(--radius-full); border: 1px solid #fde68a;">
                                <i class="bi bi-bell-fill"></i> <?= count($upcomingMilestones) ?> Aktif
                            </span>
                        </div>
                        <div class="sd-card-body" style="padding: 1rem;">
                            <div style="display: flex; flex-direction: column; gap: 0.65rem;">
                                <?php foreach ($upcomingMilestones as $ms): 
                                    $daysLeft = (int)($ms['days_left'] ?? 0);
                                    
                                    // Urgency color logic
                                    if ($daysLeft <= 0) {
                                        $urgencyColor = '#dc2626'; $urgencyBg = '#fee2e2'; $urgencyText = '#991b1b';
                                        $urgencyIcon = 'exclamation-triangle-fill'; $urgencyLabel = 'HARI INI!';
                                    } elseif ($daysLeft === 1) {
                                        $urgencyColor = '#dc2626'; $urgencyBg = '#fee2e2'; $urgencyText = '#991b1b';
                                        $urgencyIcon = 'exclamation-triangle-fill'; $urgencyLabel = 'BESOK';
                                    } elseif ($daysLeft <= 3) {
                                        $urgencyColor = '#f59e0b'; $urgencyBg = '#fef3c7'; $urgencyText = '#92400e';
                                        $urgencyIcon = 'clock-fill'; $urgencyLabel = "{$daysLeft} hari";
                                    } elseif ($daysLeft <= 7) {
                                        $urgencyColor = '#3b82f6'; $urgencyBg = '#dbeafe'; $urgencyText = '#1e40af';
                                        $urgencyIcon = 'calendar-event'; $urgencyLabel = "{$daysLeft} hari";
                                    } else {
                                        $urgencyColor = '#6b7280'; $urgencyBg = '#f3f4f6'; $urgencyText = '#374151';
                                        $urgencyIcon = 'calendar3'; $urgencyLabel = "{$daysLeft} hari";
                                    }
                                ?>
                                <div style="display: flex; align-items: center; gap: 1rem; padding: 0.85rem; background: <?= $urgencyBg ?>; border-radius: var(--radius-lg); border-left: 4px solid <?= $urgencyColor ?>; transition: all 0.2s;">
                                    <div style="width: 44px; height: 44px; border-radius: var(--radius-md); background: <?= $urgencyColor ?>; color: white; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                                        <i class="bi bi-<?= $urgencyIcon ?>"></i>
                                    </div>
                                    <div style="flex: 1; min-width: 0;">
                                        <div style="font-weight: 700; color: var(--gray-900); font-size: 0.9rem; margin-bottom: 0.15rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <?= e($ms['title']) ?>
                                        </div>
                                        <div style="font-size: 0.78rem; color: <?= $urgencyText ?>; font-weight: 600;">
                                            <i class="bi bi-calendar3"></i>
                                            <?= date('d M Y', strtotime($ms['due_date'])) ?>
                                            <span style="margin: 0 0.25rem;">•</span>
                                            <span style="font-weight: 700;"><?= $urgencyLabel ?></span>
                                        </div>
                                    </div>
                                    <div style="text-align: center; padding: 0.4rem 0.75rem; background: white; border-radius: var(--radius-md); min-width: 56px; border: 1px solid <?= $urgencyColor ?>22;">
                                        <div style="font-size: 1.5rem; font-weight: 800; color: <?= $urgencyColor ?>; line-height: 1;"><?= max(0, $daysLeft) ?></div>
                                        <div style="font-size: 0.65rem; color: var(--gray-500); text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em;">Hari</div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <!-- === AKHIR WIDGET DEADLINE === -->

                    <!-- Documents -->
                    <div class="sd-card">
                        <div class="sd-card-header">
                            <h3 class="sd-card-title">
                                <i class="bi bi-folder-fill"></i>
                                Dokumen Saya
                            </h3>
                        </div>
                        <div class="sd-card-body">
                            <?php if (empty($myDocuments)): ?>
                                <div class="sd-empty" style="padding: 2rem 1rem;">
                                    <i class="bi bi-folder-x" style="font-size: 2rem; opacity: 0.3; display: block; margin-bottom: 0.5rem;"></i>
                                    <p>Belum ada dokumen yang diunggah.</p>
                                    <a href="<?= BASE_URL ?>/student/upload" class="btn btn-primary btn-sm" style="margin-top: 0.5rem;">
                                        <i class="bi bi-cloud-upload"></i> Unggah Dokumen
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="sd-doc-list">
                                    <?php foreach ($myDocuments as $doc): ?>
                                    <div class="sd-doc-item">
                                        <div class="sd-doc-info">
                                            <div class="sd-doc-icon">
                                                <i class="bi bi-file-earmark-pdf-fill"></i>
                                            </div>
                                            <div class="sd-doc-details">
                                                <div class="sd-doc-name" title="<?= e($doc['original_filename']) ?>">
                                                    <?= e($doc['original_filename']) ?>
                                                </div>
                                                <div class="sd-doc-meta">
                                                    <span><?= e($doc['type_name']) ?></span>
                                                    <span class="sd-doc-version">
                                                        <i class="bi bi-git"></i> v<?= $doc['version_number'] ?>
                                                    </span>
                                                    <span><i class="bi bi-clock"></i> <?= date('d/m/Y H:i', strtotime($doc['uploaded_at'])) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <a href="<?= BASE_URL ?>/documents/download?id=<?= $doc['id'] ?>" class="sd-doc-btn">
                                            <i class="bi bi-download"></i> Unduh
                                        </a>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Right: Milestones -->
                <div>
                    <div class="sd-card">
                        <div class="sd-card-header">
                            <h3 class="sd-card-title">
                                <i class="bi bi-flag-fill"></i>
                                Milestone
                            </h3>
                        </div>
                        <div class="sd-card-body">
                            <?php if (empty($myMilestones)): ?>
                                <div class="sd-empty" style="padding: 2rem 1rem;">
                                    <i class="bi bi-calendar-x" style="font-size: 2rem; opacity: 0.3; display: block; margin-bottom: 0.5rem;"></i>
                                    <p>Belum ada milestone yang ditetapkan oleh dosen.</p>
                                </div>
                            <?php else: ?>
                                <div class="sd-milestone-list">
                                    <?php foreach ($myMilestones as $m): 
                                        $isDone = !empty($m['completed_at']);
                                        $isNext = !$isDone && strtotime($m['due_date']) >= time();
                                        $isOverdue = !$isDone && strtotime($m['due_date']) < time();
                                        
                                        $statusClass = $isDone ? 'done' : ($isOverdue ? 'overdue' : '');
                                        $statusText = $isDone ? 'Selesai: ' . date('d/m/Y', strtotime($m['completed_at'])) 
                                                            : ($isOverdue ? 'Terlambat!' : 'Deadline: ' . date('d/m/Y', strtotime($m['due_date'])));
                                    ?>
                                    <div class="sd-milestone-item <?= $isDone ? 'is-done' : ($isNext ? 'is-next' : '') ?>">
                                        <div class="sd-milestone-dot"></div>
                                        <div class="sd-milestone-card">
                                            <div class="sd-milestone-title"><?= e($m['title']) ?></div>
                                            <div class="sd-milestone-desc"><?= e($m['description']) ?></div>
                                            <div class="sd-milestone-date <?= $statusClass ?>">
                                                <i class="bi bi-<?= $isDone ? 'check-circle-fill' : ($isOverdue ? 'exclamation-triangle-fill' : 'calendar-event') ?>"></i>
                                                <?= $statusText ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/admin.js"></script>
    <script>
    (function() {
        'use strict';

        // ===== ALERT AUTO-DISMISS =====
        document.querySelectorAll('.sd-view .alert-dismissible .btn-close').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const alert = e.target.closest('.alert');
                alert.style.transition = 'all 0.3s';
                alert.style.opacity = '0';
                alert.style.transform = 'translateX(30px)';
                setTimeout(() => alert.remove(), 300);
            });
        });

        document.querySelectorAll('.sd-view .alert-success').forEach(alert => {
            setTimeout(() => {
                if (alert.parentElement) {
                    alert.style.transition = 'all 0.3s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }
            }, 5000);
        });

        console.log('%c✓ Student Dashboard Loaded', 'color: #3b82f6; font-weight: bold;');
    })();
    </script>
</body>
</html>