<?php
declare(strict_types=1);
$flashError = $_SESSION['flash_error'] ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

$myStudents = $myStudents ?? [];
$pendingReviews = $pendingReviews ?? [];

$total = count($myStudents);
$pending = count($pendingReviews);

// Statistik per status
$statusCount = ['draft' => 0, 'proposal' => 0, 'writing' => 0, 'revision' => 0, 'completed' => 0, 'other' => 0];
foreach ($myStudents as $s) {
    $st = $s['status'] ?? 'draft';
    if (isset($statusCount[$st])) $statusCount[$st]++;
    else $statusCount['other']++;
}
$inProgress = $statusCount['proposal'] + $statusCount['writing'] + $statusCount['other'];

$palette = [
    'linear-gradient(135deg,#10b981,#059669)',
    'linear-gradient(135deg,#3b82f6,#1e40af)',
    'linear-gradient(135deg,#8b5cf6,#6d28d9)',
    'linear-gradient(135deg,#ec4899,#be185d)',
    'linear-gradient(135deg,#f59e0b,#d97706)',
    'linear-gradient(135deg,#06b6d4,#0891b2)',
];

$initials = function(string $name): string {
    $parts = array_filter(explode(' ', trim($name)));
    if (empty($parts)) return '-';
    return strtoupper(substr($parts[0], 0, 1)) . (count($parts) > 1 ? strtoupper(substr(end($parts), 0, 1)) : '');
};

// Greeting
$hour = (int) date('H');
$greeting = $hour < 11 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 18 ? 'Selamat Sore' : 'Selamat Malam'));
$userName = $_SESSION['simbita_user']['name'] ?? 'Dosen';
$firstName = explode(' ', $userName)[0];

$statusMeta = [
    'draft'            => ['label' => 'Draft',          'class' => 'ld-pill-draft',     'icon' => 'file-earmark'],
    'proposal'         => ['label' => 'Proposal',       'class' => 'ld-pill-proposal',  'icon' => 'file-earmark-text'],
    'feasibility_test' => ['label' => 'Uji Kelayakan',  'class' => 'ld-pill-info',      'icon' => 'clipboard-check'],
    'writing'          => ['label' => 'Penulisan',      'class' => 'ld-pill-writing',   'icon' => 'pencil'],
    'progress_exam'    => ['label' => 'Ujian Kemajuan', 'class' => 'ld-pill-info',      'icon' => 'award'],
    'revision'         => ['label' => 'Revisi',         'class' => 'ld-pill-revision',  'icon' => 'arrow-repeat'],
    'closed_exam'      => ['label' => 'Ujian Tertutup', 'class' => 'ld-pill-info',      'icon' => 'door-closed'],
    'ready_for_defense'=> ['label' => 'Siap Sidang',    'class' => 'ld-pill-proposal',  'icon' => 'check2-circle'],
    'defense'          => ['label' => 'Sidang',         'class' => 'ld-pill-proposal',  'icon' => 'person-video3'],
    'completed'        => ['label' => 'Selesai',        'class' => 'ld-pill-success',   'icon' => 'check-circle-fill'],
    'cancelled'        => ['label' => 'Dibatalkan',     'class' => 'ld-pill-danger',    'icon' => 'x-circle'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Dosen - <?= e(APP_NAME) ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">
    
    <style>
        /* =========================================================
           LECTURER DASHBOARD ULTRA PREMIUM - SCOPED STYLES
           Namespace: .ld- | Tema: Emerald Green (#10b981)
           ========================================================= */
        .ld-view {
            --ld-primary: #10b981;
            --ld-primary-dark: #059669;
            --ld-primary-light: #d1fae5;
            --ld-gradient: linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%);
            --ld-glow: 0 10px 30px -10px rgba(16, 185, 129, 0.4);
            animation: ldFade 0.4s ease;
        }
        @keyframes ldFade { from { opacity: 0; } to { opacity: 1; } }

        /* --- Welcome Banner --- */
        .ld-welcome {
            background: var(--ld-gradient);
            border-radius: var(--radius-2xl);
            padding: 2.5rem;
            color: white;
            position: relative;
            overflow: hidden;
            margin-bottom: 2rem;
            box-shadow: 0 20px 40px -10px rgba(16,185,129,0.4);
            animation: ldSlideIn 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes ldSlideIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .ld-welcome::before {
            content: '';
            position: absolute;
            top: -50%; right: -10%;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            border-radius: 50%;
            animation: ldFloat 6s ease-in-out infinite;
        }

        .ld-welcome::after {
            content: '';
            position: absolute;
            bottom: -30%; left: 10%;
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(245,158,11,0.2) 0%, transparent 70%);
            border-radius: 50%;
            animation: ldFloat 8s ease-in-out infinite reverse;
        }

        @keyframes ldFloat {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(-20px, -20px); }
        }

        .ld-welcome-content {
            position: relative;
            z-index: 2;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .ld-welcome-text h2 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .ld-wave {
            display: inline-block;
            animation: ldWave 2s ease-in-out infinite;
            transform-origin: 70% 70%;
        }

        @keyframes ldWave {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(20deg); }
            75% { transform: rotate(-15deg); }
        }

        .ld-welcome-text p {
            font-size: 1.05rem;
            opacity: 0.9;
            max-width: 500px;
        }

        .ld-welcome-badges {
            display: flex;
            gap: 0.6rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .ld-welcome-badge {
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

        .ld-welcome-badge.pulse {
            animation: ldBadgePulse 2s infinite;
        }

        @keyframes ldBadgePulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(255,255,255,0.4); }
            50% { box-shadow: 0 0 0 8px rgba(255,255,255,0); }
        }

        .ld-welcome-date {
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
        .ld-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .ld-stat {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
            animation: ldUp 0.5s ease backwards;
        }

        .ld-stat:nth-child(1) { animation-delay: 0.05s; }
        .ld-stat:nth-child(2) { animation-delay: 0.1s; }
        .ld-stat:nth-child(3) { animation-delay: 0.15s; }
        .ld-stat:nth-child(4) { animation-delay: 0.2s; }

        @keyframes ldUp {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .ld-stat::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(16,185,129,0.05), transparent);
            transition: left 0.7s;
        }

        .ld-stat:hover::before { left: 100%; }

        .ld-stat:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 30px -10px rgba(0,0,0,0.12);
            border-color: var(--ld-primary);
        }

        .ld-stat-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .ld-stat-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .ld-stat-icon {
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

        .ld-stat:hover .ld-stat-icon { transform: scale(1.1) rotate(-5deg); }

        .ld-stat-icon.i-green { background: linear-gradient(135deg,#10b981,#059669); }
        .ld-stat-icon.i-yellow { background: linear-gradient(135deg,#f59e0b,#d97706); }
        .ld-stat-icon.i-blue { background: linear-gradient(135deg,#3b82f6,#1e40af); }
        .ld-stat-icon.i-pink { background: linear-gradient(135deg,#ec4899,#be185d); }

        .ld-stat-value {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--gray-900);
            line-height: 1;
            margin-bottom: 0.5rem;
            font-variant-numeric: tabular-nums;
        }

        .ld-stat-desc {
            font-size: 0.8rem;
            color: var(--gray-500);
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .ld-trend-up {
            display: inline-flex;
            align-items: center;
            gap: 0.2rem;
            color: var(--ld-primary);
            font-weight: 700;
            font-size: 0.75rem;
        }

        /* --- Main Grid --- */
        .ld-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
        }

        @media (max-width: 968px) {
            .ld-grid { grid-template-columns: 1fr; }
        }

        /* --- Cards --- */
        .ld-card {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            animation: ldUp 0.5s ease 0.2s backwards;
        }

        .ld-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            background: linear-gradient(to right, var(--gray-50), white);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .ld-card-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
        }

        .ld-card-title i { color: var(--ld-primary); }

        .ld-card-count {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.3rem 0.75rem;
            background: var(--ld-primary-light);
            color: #065f46;
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 700;
        }

        /* --- Students Table --- */
        .ld-search-bar {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--gray-100);
        }

        .ld-search-wrap {
            position: relative;
            max-width: 320px;
        }

        .ld-search-wrap i {
            position: absolute;
            left: 1rem; top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            pointer-events: none;
            transition: color 0.3s;
        }

        .ld-search {
            width: 100%;
            padding: 0.6rem 1rem 0.6rem 2.5rem;
            border: 1.5px solid var(--gray-300);
            border-radius: var(--radius-full);
            font-size: 0.85rem;
            font-family: inherit;
            transition: all 0.3s;
            background: white;
        }

        .ld-search:focus {
            outline: none;
            border-color: var(--ld-primary);
            box-shadow: 0 0 0 4px rgba(16,185,129,0.12);
        }

        .ld-search-wrap:focus-within i { color: var(--ld-primary); }

        .ld-students-wrap { max-height: 520px; overflow-y: auto; }

        .ld-student-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 1rem;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--gray-100);
            align-items: center;
            transition: all 0.25s;
        }

        .ld-student-row:last-child { border-bottom: none; }
        .ld-student-row:hover { background: #f0fdf4; padding-left: 2rem; }

        .ld-student-info {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            min-width: 0;
        }

        .ld-avatar {
            width: 44px; height: 44px;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            font-size: 0.9rem;
            flex-shrink: 0;
            box-shadow: var(--shadow-md);
            transition: transform 0.3s;
        }

        .ld-student-row:hover .ld-avatar { transform: scale(1.08) rotate(-4deg); }

        .ld-student-name {
            font-weight: 700;
            color: var(--gray-900);
            font-size: 0.9rem;
            line-height: 1.3;
            margin-bottom: 0.15rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ld-student-meta {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.75rem;
            color: var(--gray-500);
        }

        .ld-student-nim {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 600;
            color: var(--ld-primary);
        }

        .ld-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.2rem 0.6rem;
            background: #dbeafe;
            color: #1e40af;
            border-radius: var(--radius-full);
            font-size: 0.7rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .ld-title-cell {
            min-width: 0;
        }

        .ld-title-text {
            font-size: 0.85rem;
            color: var(--gray-700);
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Status pill */
        .ld-pill {
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

        .ld-pill::before {
            content: '';
            width: 6px; height: 6px;
            border-radius: 50%;
            background: currentColor;
        }

        .ld-pill-draft { background: var(--gray-100); color: var(--gray-600); }
        .ld-pill-proposal { background: #dbeafe; color: #1e40af; }
        .ld-pill-writing { background: #cffafe; color: #155e75; }
        .ld-pill-revision { background: #fef3c7; color: #92400e; }
        .ld-pill-revision::before { animation: ldSpin 2s linear infinite; }
        .ld-pill-success { background: #d1fae5; color: #065f46; }
        .ld-pill-success::before { animation: ldPulse 1.6s infinite; }
        .ld-pill-info { background: #ede9fe; color: #6d28d9; }
        .ld-pill-danger { background: #fee2e2; color: #991b1b; }

        @keyframes ldPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.8); }
        }
        @keyframes ldSpin { to { transform: rotate(360deg); } }

        .ld-review-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 1rem;
            background: var(--ld-gradient);
            color: white;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 0.8rem;
            text-decoration: none;
            transition: all 0.25s;
            white-space: nowrap;
            box-shadow: 0 4px 8px -3px rgba(16,185,129,0.4);
        }

        .ld-review-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px -5px rgba(16,185,129,0.5);
        }

        /* --- Pending Reviews Card --- */
        .ld-pending-card {
            animation: ldUp 0.5s ease 0.3s backwards;
        }

        .ld-pending-card .ld-card-header {
            background: linear-gradient(to right, #fef3c7, white);
        }

        .ld-pending-card .ld-card-title i { color: var(--accent-orange); }

        .ld-pending-wrap {
            max-height: 520px;
            overflow-y: auto;
            padding: 0.5rem;
        }

        .ld-pending-item {
            padding: 1rem;
            margin-bottom: 0.5rem;
            background: var(--gray-50);
            border: 1.5px solid var(--gray-100);
            border-radius: var(--radius-lg);
            transition: all 0.25s;
            position: relative;
            overflow: hidden;
        }

        .ld-pending-item::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 3px;
            background: linear-gradient(180deg, #f59e0b, #d97706);
        }

        .ld-pending-item:hover {
            background: white;
            border-color: #fcd34d;
            transform: translateX(3px);
            box-shadow: var(--shadow-md);
        }

        .ld-pending-title {
            font-weight: 700;
            color: var(--gray-900);
            font-size: 0.875rem;
            line-height: 1.4;
            margin-bottom: 0.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .ld-pending-meta {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            font-size: 0.75rem;
            color: var(--gray-500);
            margin-bottom: 0.75rem;
        }

        .ld-pending-meta-item {
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .ld-pending-meta-item i { color: var(--accent-orange); font-size: 0.85rem; }

        .ld-pending-doc {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 0.6rem;
            background: #fee2e2;
            color: #991b1b;
            border-radius: var(--radius-md);
            font-size: 0.72rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }

        .ld-pending-doc i { font-size: 0.9rem; }

        .ld-pending-action {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 0.85rem;
            background: white;
            color: var(--gray-700);
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 0.78rem;
            text-decoration: none;
            transition: all 0.25s;
            width: 100%;
            justify-content: center;
        }

        .ld-pending-action:hover {
            background: var(--ld-gradient);
            color: white;
            border-color: transparent;
        }

        /* --- Empty State --- */
        .ld-empty {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--gray-500);
        }

        .ld-empty-icon {
            width: 80px; height: 80px;
            margin: 0 auto 1rem;
            border-radius: 50%;
            background: var(--ld-primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.25rem;
            color: var(--ld-primary);
            animation: ldEmptyPulse 2s ease-in-out infinite;
        }

        @keyframes ldEmptyPulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(16,185,129,0.3); }
            50% { transform: scale(1.05); box-shadow: 0 0 0 15px rgba(16,185,129,0); }
        }

        .ld-empty h3 { font-size: 1.1rem; color: var(--gray-800); margin-bottom: 0.35rem; }
        .ld-empty p { font-size: 0.85rem; margin: 0; }

        /* --- Alerts --- */
        .ld-view .alert-dismissible {
            animation: ldSlideRight 0.4s ease;
        }

        @keyframes ldSlideRight {
            from { opacity: 0; transform: translateX(30px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @media (max-width: 768px) {
            .ld-welcome { padding: 1.5rem; }
            .ld-welcome-text h2 { font-size: 1.5rem; }
            .ld-student-row {
                grid-template-columns: 1fr;
                gap: 0.75rem;
                padding: 1rem;
            }
            .ld-student-row:hover { padding-left: 1rem; }
        }
    </style>
</head>
<body>
    <?php require dirname(__DIR__) . '/partials/navbar.php'; ?>

    <div class="container ld-view">
        <!-- Welcome Banner -->
        <div class="ld-welcome">
            <div class="ld-welcome-content">
                <div class="ld-welcome-text">
                    <h2>
                        <span class="ld-wave">👋</span>
                        <?= $greeting ?>, <?= e($firstName) ?>!
                    </h2>
                    <p>Pantau progress bimbingan mahasiswa Anda dan selesaikan review dokumen yang menunggu.</p>
                    <div class="ld-welcome-badges">
                        <span class="ld-welcome-badge">
                            <i class="bi bi-mortarboard-fill"></i>
                            <?= $total ?> Mahasiswa Bimbingan
                        </span>
                        <?php if ($pending > 0): ?>
                        <span class="ld-welcome-badge pulse">
                            <i class="bi bi-bell-fill"></i>
                            <?= $pending ?> Review Menunggu
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="ld-welcome-date">
                    <i class="bi bi-calendar-event"></i>
                    <?= strftime('%A, %d %B %Y', time()) ?>
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

        <!-- Stats -->
        <div class="ld-stats">
            <div class="ld-stat">
                <div class="ld-stat-top">
                    <div class="ld-stat-label"><i class="bi bi-people-fill"></i> Total Bimbingan</div>
                    <div class="ld-stat-icon i-green"><i class="bi bi-mortarboard-fill"></i></div>
                </div>
                <div class="ld-stat-value"><?= $total ?></div>
                <div class="ld-stat-desc">
                    <span class="ld-trend-up"><i class="bi bi-check-circle-fill"></i> Aktif dibimbing</span>
                </div>
            </div>
            <div class="ld-stat">
                <div class="ld-stat-top">
                    <div class="ld-stat-label"><i class="bi bi-hourglass-split"></i> Review Pending</div>
                    <div class="ld-stat-icon i-yellow"><i class="bi bi-clock-fill"></i></div>
                </div>
                <div class="ld-stat-value"><?= $pending ?></div>
                <div class="ld-stat-desc">
                    <?php if ($pending > 0): ?>
                        <span style="color: var(--accent-orange); font-weight: 600;">Perlu tindakan</span>
                    <?php else: ?>
                        <span class="ld-trend-up"><i class="bi bi-check2-circle"></i> Semua selesai</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="ld-stat">
                <div class="ld-stat-top">
                    <div class="ld-stat-label"><i class="bi bi-activity"></i> Dalam Proses</div>
                    <div class="ld-stat-icon i-blue"><i class="bi bi-graph-up-arrow"></i></div>
                </div>
                <div class="ld-stat-value"><?= $inProgress ?></div>
                <div class="ld-stat-desc">Mahasiswa aktif</div>
            </div>
            <div class="ld-stat">
                <div class="ld-stat-top">
                    <div class="ld-stat-label"><i class="bi bi-trophy-fill"></i> Selesai</div>
                    <div class="ld-stat-icon i-pink"><i class="bi bi-check-circle-fill"></i></div>
                </div>
                <div class="ld-stat-value"><?= $statusCount['completed'] ?></div>
                <div class="ld-stat-desc">Lulus & selesai</div>
            </div>
        </div>

        <!-- Main Grid -->
        <div class="ld-grid">
            <!-- Left: Students -->
            <div class="ld-card">
                <div class="ld-card-header">
                    <h3 class="ld-card-title">
                        <i class="bi bi-mortarboard-fill"></i>
                        Mahasiswa Bimbingan
                    </h3>
                    <span class="ld-card-count">
                        <i class="bi bi-people-fill"></i>
                        <?= $total ?> mahasiswa
                    </span>
                </div>

                <?php if ($total > 0): ?>
                <div class="ld-search-bar">
                    <div class="ld-search-wrap">
                        <i class="bi bi-search"></i>
                        <input type="text" id="ldSearch" class="ld-search" placeholder="Cari mahasiswa atau judul...">
                    </div>
                </div>
                <?php endif; ?>

                <div class="ld-students-wrap">
                    <?php if (empty($myStudents)): ?>
                        <div class="ld-empty">
                            <div class="ld-empty-icon"><i class="bi bi-inbox-fill"></i></div>
                            <h3>Belum Ada Mahasiswa Bimbingan</h3>
                            <p>Anda belum ditugaskan sebagai pembimbing.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($myStudents as $i => $student):
                            $bg = $palette[$i % count($palette)];
                            $st = $student['status'] ?? 'draft';
                            $meta = $statusMeta[$st] ?? $statusMeta['draft'];
                            $searchKey = strtolower(
                                $student['student_name'] . ' ' .
                                ($student['student_number'] ?? '') . ' ' .
                                $student['title'] . ' ' .
                                ($student['work_type_name'] ?? '')
                            );
                        ?>
                        <div class="ld-student-row ld-row" data-search="<?= e($searchKey) ?>">
                            <div class="ld-student-info">
                                <div class="ld-avatar" style="background: <?= $bg ?>;">
                                    <?= e($initials($student['student_name'])) ?>
                                </div>
                                <div style="min-width: 0;">
                                    <div class="ld-student-name"><?= e($student['student_name']) ?></div>
                                    <div class="ld-student-meta">
                                        <span class="ld-student-nim"><?= e($student['student_number'] ?? '—') ?></span>
                                        <span class="ld-type-badge">
                                            <i class="bi bi-bookmark-fill"></i>
                                            <?= e($student['work_type_name'] ?? 'TA') ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="ld-title-cell">
                                <div class="ld-title-text" title="<?= e($student['title']) ?>">
                                    <?= e($student['title']) ?>
                                </div>
                            </div>

                            <div>
                                <span class="ld-pill <?= $meta['class'] ?>">
                                    <i class="bi bi-<?= $meta['icon'] ?>"></i>
                                    <?= $meta['label'] ?>
                                </span>
                            </div>

                            <div>
                                <a href="<?= BASE_URL ?>/lecturer/review?id=<?= $student['id'] ?>" class="ld-review-btn">
                                    <i class="bi bi-pencil-square-fill"></i> Review
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <div id="ldNoResult" style="display: none;" class="ld-empty">
                            <i class="bi bi-search" style="font-size: 2rem; opacity: 0.4; display: block; margin-bottom: 0.5rem;"></i>
                            <p>Tidak ada mahasiswa yang cocok.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right: Pending Reviews -->
            <div class="ld-card ld-pending-card">
                <div class="ld-card-header">
                    <h3 class="ld-card-title">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        Review Pending
                    </h3>
                    <span class="ld-card-count" style="background: #fef3c7; color: #92400e;">
                        <i class="bi bi-clock-fill"></i>
                        <?= $pending ?>
                    </span>
                </div>

                <div class="ld-pending-wrap">
                    <?php if (empty($pendingReviews)): ?>
                        <div class="ld-empty" style="padding: 2.5rem 1rem;">
                            <div class="ld-empty-icon" style="width: 70px; height: 70px; font-size: 1.75rem;">
                                <i class="bi bi-check2-circle"></i>
                            </div>
                            <h3>Semua Sudah Direview!</h3>
                            <p>Tidak ada dokumen yang menunggu review.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($pendingReviews as $review): ?>
                        <div class="ld-pending-item">
                            <div class="ld-pending-title"><?= e($review['work_title']) ?></div>
                            <div class="ld-pending-meta">
                                <div class="ld-pending-meta-item">
                                    <i class="bi bi-person-fill"></i>
                                    <span><?= e($review['student_name']) ?></span>
                                </div>
                                <div class="ld-pending-meta-item">
                                    <i class="bi bi-clock-fill"></i>
                                    <span><?= date('d/m/Y H:i', strtotime($review['created_at'])) ?></span>
                                </div>
                            </div>
                            <div class="ld-pending-doc">
                                <i class="bi bi-file-earmark-pdf-fill"></i>
                                <?= e($review['original_filename']) ?>
                            </div>
                            <a href="<?= BASE_URL ?>/lecturer/review?id=<?= $review['academic_work_id'] ?? $review['id'] ?>" class="ld-pending-action">
                                <i class="bi bi-pencil-square"></i> Mulai Review
                            </a>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/admin.js"></script>
    <script>
    (function() {
        'use strict';

        // ===== LIVE SEARCH =====
        const searchInput = document.getElementById('ldSearch');
        const rows = document.querySelectorAll('.ld-row');
        const noResult = document.getElementById('ldNoResult');

        if (searchInput) {
            searchInput.addEventListener('input', () => {
                const q = searchInput.value.toLowerCase().trim();
                let visible = 0;

                rows.forEach(row => {
                    const match = !q || (row.dataset.search || '').includes(q);
                    row.style.display = match ? '' : 'none';
                    if (match) visible++;
                });

                if (noResult) noResult.style.display = visible === 0 ? 'block' : 'none';
            });
        }

        // ===== ALERT AUTO-DISMISS =====
        document.querySelectorAll('.ld-view .alert-dismissible .btn-close').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const alert = e.target.closest('.alert');
                alert.style.transition = 'all 0.3s';
                alert.style.opacity = '0';
                alert.style.transform = 'translateX(30px)';
                setTimeout(() => alert.remove(), 300);
            });
        });

        document.querySelectorAll('.ld-view .alert-success').forEach(alert => {
            setTimeout(() => {
                if (alert.parentElement) {
                    alert.style.transition = 'all 0.3s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }
            }, 5000);
        });

        // ===== COUNTER ANIMATION =====
        document.querySelectorAll('.ld-stat-value').forEach(el => {
            const target = parseInt(el.textContent.replace(/\D/g, ''));
            if (isNaN(target)) return;
            const duration = 1200;
            const startTime = performance.now();

            const update = (now) => {
                const progress = Math.min((now - startTime) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = Math.floor(eased * target).toLocaleString('id-ID');
                if (progress < 1) requestAnimationFrame(update);
                else el.textContent = target.toLocaleString('id-ID');
            };
            requestAnimationFrame(update);
        });

        console.log('%c✓ Lecturer Dashboard Loaded', 'color: #10b981; font-weight: bold;');
    })();
    </script>
</body>
</html>