<?php
declare(strict_types=1);
$flashError = $_SESSION['flash_error'] ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

// Greeting berdasarkan waktu
$hour = (int) date('H');
$greeting = $hour < 11 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 18 ? 'Selamat Sore' : 'Selamat Malam'));
$greetingIcon = $hour < 11 ? 'sunrise' : ($hour < 15 ? 'sun' : ($hour < 18 ? 'sunset' : 'moon-stars'));
$userName = $_SESSION['simbita_user']['name'] ?? 'Administrator';
$firstName = explode(' ', $userName)[0];

// Stats default
$stats = $stats ?? [
    'total_students' => 0,
    'total_lecturers' => 0,
    'active_works' => 0,
    'pending_reviews' => 0,
];

$recentWorks = $recentWorks ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrator - <?= e(APP_NAME) ?></title>
    <meta name="description" content="Dashboard Administrator SIMBITA">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <style>
        /* =========================================================
           DASHBOARD ULTRA PREMIUM - SCOPED STYLES
           Namespace: .dash-view
           ========================================================= */
        .dash-view { --dash-accent: #667eea; --dash-accent-2: #764ba2; }

        /* --- Welcome Banner --- */
        .dash-welcome {
            position: relative;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: var(--radius-2xl);
            padding: 2.5rem;
            color: white;
            overflow: hidden;
            margin-bottom: 2rem;
            box-shadow: 0 20px 40px -10px rgba(102, 126, 234, 0.4);
            animation: dashSlideIn 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes dashSlideIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .dash-welcome::before {
            content: '';
            position: absolute;
            top: -50%; right: -10%;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            border-radius: 50%;
            animation: dashFloat 6s ease-in-out infinite;
        }

        .dash-welcome::after {
            content: '';
            position: absolute;
            bottom: -30%; left: 10%;
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(236,72,153,0.2) 0%, transparent 70%);
            border-radius: 50%;
            animation: dashFloat 8s ease-in-out infinite reverse;
        }

        @keyframes dashFloat {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(-20px, -20px); }
        }

        .dash-welcome-content {
            position: relative;
            z-index: 2;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .dash-welcome-text h2 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .dash-welcome-text h2 .wave {
            display: inline-block;
            animation: dashWave 2s ease-in-out infinite;
            transform-origin: 70% 70%;
        }

        @keyframes dashWave {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(20deg); }
            75% { transform: rotate(-15deg); }
        }

        .dash-welcome-text p {
            font-size: 1.05rem;
            opacity: 0.9;
            max-width: 500px;
        }

        .dash-welcome-date {
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

        /* --- Enhanced Stat Cards --- */
        .dash-stat-card {
            background: white;
            border-radius: var(--radius-xl);
            padding: 1.75rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .dash-stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(102,126,234,0.05), transparent);
            transition: left 0.7s;
        }

        .dash-stat-card:hover::before { left: 100%; }

        .dash-stat-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 30px -10px rgba(0,0,0,0.15);
            border-color: var(--primary-300);
        }

        .dash-stat-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .dash-stat-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .dash-stat-trend {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius-full);
        }

        .dash-stat-trend.up { background: #d1fae5; color: #065f46; }
        .dash-stat-trend.down { background: #fee2e2; color: #991b1b; }
        .dash-stat-trend.stable { background: var(--gray-100); color: var(--gray-600); }

        .dash-stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--gray-900);
            line-height: 1;
            margin-bottom: 0.5rem;
            font-variant-numeric: tabular-nums;
        }

        .dash-stat-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 1rem;
            border-top: 1px dashed var(--gray-200);
        }

        .dash-stat-desc {
            font-size: 0.8rem;
            color: var(--gray-500);
        }

        .dash-stat-icon {
            width: 44px; height: 44px;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
            flex-shrink: 0;
            transition: transform 0.4s;
        }

        .dash-stat-card:hover .dash-stat-icon { transform: scale(1.1) rotate(-5deg); }

        .dash-stat-icon.bg-primary { background: var(--gradient-primary); }
        .dash-stat-icon.bg-success { background: var(--gradient-success); }
        .dash-stat-icon.bg-warning { background: var(--gradient-warning); }
        .dash-stat-icon.bg-danger { background: var(--gradient-danger); }

        /* --- Grid Layouts --- */
        .dash-grid-2 {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .dash-grid-3 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 968px) {
            .dash-grid-2 { grid-template-columns: 1fr; }
        }

        /* --- Chart Container --- */
        .dash-chart-card {
            background: white;
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
        }

        .dash-chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .dash-chart-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
        }

        .dash-chart-title i { color: var(--primary-600); }

        .dash-chart-legend {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .dash-chart-legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            color: var(--gray-600);
            font-weight: 500;
        }

        .dash-chart-legend-dot {
            width: 10px; height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .dash-chart-body {
            position: relative;
            height: 280px;
        }

        /* --- Activity Timeline --- */
        .dash-timeline {
            position: relative;
            padding-left: 0;
        }

        .dash-timeline::before {
            content: '';
            position: absolute;
            top: 8px; bottom: 8px; left: 15px;
            width: 2px;
            background: linear-gradient(to bottom, var(--primary-300), var(--gray-200));
            border-radius: var(--radius-full);
        }

        .dash-timeline-item {
            position: relative;
            padding-left: 45px;
            padding-bottom: 1.25rem;
        }

        .dash-timeline-item:last-child { padding-bottom: 0; }

        .dash-timeline-dot {
            position: absolute;
            left: 7px; top: 4px;
            width: 18px; height: 18px;
            border-radius: 50%;
            background: white;
            border: 3px solid var(--primary-500);
            z-index: 2;
        }

        .dash-timeline-dot.success { border-color: var(--accent-green); }
        .dash-timeline-dot.warning { border-color: var(--accent-yellow); }
        .dash-timeline-dot.danger { border-color: var(--accent-red); }
        .dash-timeline-dot.info { border-color: var(--accent-cyan); }

        .dash-timeline-content {
            background: var(--gray-50);
            padding: 0.75rem 1rem;
            border-radius: var(--radius-md);
            border: 1px solid var(--gray-100);
            transition: all 0.3s;
        }

        .dash-timeline-content:hover {
            background: white;
            border-color: var(--primary-200);
            transform: translateX(4px);
        }

        .dash-timeline-title {
            font-weight: 600;
            color: var(--gray-800);
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .dash-timeline-time {
            font-size: 0.75rem;
            color: var(--gray-500);
        }

        /* --- Quick Actions --- */
        .dash-quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
        }

        .dash-quick-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1rem;
            background: white;
            border-radius: var(--radius-xl);
            border: 1.5px solid var(--gray-200);
            color: var(--gray-700);
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
            gap: 0.75rem;
        }

        .dash-quick-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--gradient-primary);
            opacity: 0;
            transition: opacity 0.35s;
            z-index: 0;
        }

        .dash-quick-btn:hover::before { opacity: 1; }
        .dash-quick-btn:hover {
            color: white;
            transform: translateY(-4px);
            border-color: transparent;
            box-shadow: 0 15px 30px -10px rgba(102,126,234,0.5);
        }

        .dash-quick-btn > * { position: relative; z-index: 1; }

        .dash-quick-btn i {
            font-size: 2rem;
            color: var(--primary-600);
            transition: all 0.35s;
        }

        .dash-quick-btn:hover i {
            color: white;
            transform: scale(1.15) rotate(-5deg);
        }

        .dash-quick-btn.success i { color: var(--accent-green); }
        .dash-quick-btn.success::before { background: var(--gradient-success); }
        .dash-quick-btn.warning i { color: var(--accent-orange); }
        .dash-quick-btn.warning::before { background: var(--gradient-warning); }
        .dash-quick-btn.info i { color: var(--accent-cyan); }
        .dash-quick-btn.info::before { background: var(--gradient-info); }

        /* --- Recent Works Enhanced --- */
        .dash-recent-card {
            background: white;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }

        .dash-recent-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(to right, var(--gray-50), white);
        }

        .dash-recent-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
        }

        .dash-recent-title i { color: var(--primary-600); }

        .dash-work-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 0.8fr 0.8fr 0.5fr;
            gap: 1rem;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--gray-100);
            align-items: center;
            transition: all 0.25s;
        }

        .dash-work-row:last-child { border-bottom: none; }
        .dash-work-row:hover {
            background: var(--primary-50);
            padding-left: 2rem;
        }

        .dash-work-row.header {
            background: var(--gray-50);
            font-weight: 600;
            font-size: 0.75rem;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.875rem 1.5rem;
        }

        .dash-work-row.header:hover { padding-left: 1.5rem; background: var(--gray-50); }

        .dash-work-title {
            font-weight: 600;
            color: var(--gray-900);
            font-size: 0.925rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .dash-work-student { font-size: 0.85rem; }
        .dash-work-student strong { color: var(--gray-800); display: block; }
        .dash-work-student small { color: var(--gray-500); font-family: var(--font-mono, monospace); }

        .dash-work-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.3rem 0.75rem;
            border-radius: var(--radius-full);
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .dash-work-status-pill::before {
            content: '';
            width: 6px; height: 6px;
            border-radius: 50%;
            background: currentColor;
        }

        .pill-draft { background: var(--gray-100); color: var(--gray-600); }
        .pill-proposal { background: var(--primary-100); color: var(--primary-700); }
        .pill-writing { background: #cffafe; color: #155e75; }
        .pill-revision { background: #fef3c7; color: #92400e; }
        .pill-completed { background: #d1fae5; color: #065f46; }

        .dash-work-action {
            width: 34px; height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--gray-100);
            color: var(--gray-700);
            border-radius: var(--radius-md);
            text-decoration: none;
            transition: all 0.3s;
        }

        .dash-work-action:hover {
            background: var(--gradient-primary);
            color: white;
            transform: scale(1.1) rotate(-5deg);
            box-shadow: 0 8px 15px -5px rgba(102,126,234,0.4);
        }

        @media (max-width: 768px) {
            .dash-work-row { grid-template-columns: 1fr 1fr; font-size: 0.85rem; }
            .dash-work-row.header { display: none; }
            .dash-welcome { padding: 1.5rem; }
            .dash-welcome-text h2 { font-size: 1.5rem; }
        }

        /* --- Empty State Premium --- */
        .dash-empty {
            text-align: center;
            padding: 3rem 2rem;
        }

        .dash-empty-icon {
            width: 80px; height: 80px;
            margin: 0 auto 1rem;
            background: var(--primary-50);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-500);
            font-size: 2.5rem;
            animation: dashEmptyPulse 2s ease-in-out infinite;
        }

        @keyframes dashEmptyPulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(102,126,234,0.3); }
            50% { transform: scale(1.05); box-shadow: 0 0 0 15px rgba(102,126,234,0); }
        }

        .dash-empty h3 {
            font-size: 1.2rem;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
        }

        .dash-empty p {
            color: var(--gray-500);
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <?php require dirname(__DIR__) . '/partials/navbar.php'; ?>

    <div class="container dash-view">
        <!-- ===== WELCOME BANNER ===== -->
        <div class="dash-welcome">
            <div class="dash-welcome-content">
                <div class="dash-welcome-text">
                    <h2>
                        <span class="wave">👋</span>
                        <?= $greeting ?>, <?= e($firstName) ?>!
                    </h2>
                    <p>Berikut ringkasan aktivitas sistem SIMBITA hari ini. Pantau progress mahasiswa dan dosen bimbingan Anda.</p>
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

        <!-- ===== STATISTICS CARDS ===== -->
        <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
            <div class="dash-stat-card">
                <div class="dash-stat-top">
                    <div class="dash-stat-label">
                        <i class="bi bi-mortarboard"></i> Mahasiswa
                    </div>
                    <span class="dash-stat-trend up"><i class="bi bi-arrow-up-right"></i> Aktif</span>
                </div>
                <div class="dash-stat-value" data-count="<?= $stats['total_students'] ?? 0 ?>">0</div>
                <div class="dash-stat-footer">
                    <span class="dash-stat-desc">Total terdaftar</span>
                    <div class="dash-stat-icon bg-primary"><i class="bi bi-people-fill"></i></div>
                </div>
            </div>

            <div class="dash-stat-card">
                <div class="dash-stat-top">
                    <div class="dash-stat-label">
                        <i class="bi bi-person-badge"></i> Dosen
                    </div>
                    <span class="dash-stat-trend stable"><i class="bi bi-dash"></i> Stabil</span>
                </div>
                <div class="dash-stat-value" data-count="<?= $stats['total_lecturers'] ?? 0 ?>">0</div>
                <div class="dash-stat-footer">
                    <span class="dash-stat-desc">Pembimbing aktif</span>
                    <div class="dash-stat-icon bg-success"><i class="bi bi-person-workspace"></i></div>
                </div>
            </div>

            <div class="dash-stat-card">
                <div class="dash-stat-top">
                    <div class="dash-stat-label">
                        <i class="bi bi-journal-text"></i> Tugas Aktif
                    </div>
                    <span class="dash-stat-trend up"><i class="bi bi-graph-up-arrow"></i> Berjalan</span>
                </div>
                <div class="dash-stat-value" data-count="<?= $stats['active_works'] ?? 0 ?>">0</div>
                <div class="dash-stat-footer">
                    <span class="dash-stat-desc">Dalam proses</span>
                    <div class="dash-stat-icon bg-warning"><i class="bi bi-file-earmark-text-fill"></i></div>
                </div>
            </div>

            <div class="dash-stat-card">
                <div class="dash-stat-top">
                    <div class="dash-stat-label">
                        <i class="bi bi-hourglass-split"></i> Pending
                    </div>
                    <span class="dash-stat-trend <?= ($stats['pending_reviews'] ?? 0) > 0 ? 'down' : 'stable' ?>">
                        <i class="bi bi-<?= ($stats['pending_reviews'] ?? 0) > 0 ? 'exclamation' : 'check' ?>"></i>
                        <?= ($stats['pending_reviews'] ?? 0) > 0 ? 'Perlu Tindakan' : 'Aman' ?>
                    </span>
                </div>
                <div class="dash-stat-value" data-count="<?= $stats['pending_reviews'] ?? 0 ?>">0</div>
                <div class="dash-stat-footer">
                    <span class="dash-stat-desc">Review menunggu</span>
                    <div class="dash-stat-icon bg-danger"><i class="bi bi-clock-fill"></i></div>
                </div>
            </div>
        </div>

        <!-- ===== CHART + ACTIVITY TIMELINE ===== -->
        <div class="dash-grid-2">
            <!-- Chart Distribusi Status -->
            <div class="dash-chart-card">
                <div class="dash-chart-header">
                    <h3 class="dash-chart-title">
                        <i class="bi bi-pie-chart-fill"></i> Distribusi Status Tugas Akhir
                    </h3>
                    <div class="dash-chart-legend">
                        <span class="dash-chart-legend-item"><span class="dash-chart-legend-dot" style="background:#667eea"></span>Draft</span>
                        <span class="dash-chart-legend-item"><span class="dash-chart-legend-dot" style="background:#3b82f6"></span>Proposal</span>
                        <span class="dash-chart-legend-item"><span class="dash-chart-legend-dot" style="background:#06b6d4"></span>Penulisan</span>
                        <span class="dash-chart-legend-item"><span class="dash-chart-legend-dot" style="background:#f59e0b"></span>Revisi</span>
                        <span class="dash-chart-legend-item"><span class="dash-chart-legend-dot" style="background:#10b981"></span>Selesai</span>
                    </div>
                </div>
                <div class="dash-chart-body">
                    <canvas id="dashStatusChart"></canvas>
                </div>
            </div>

            <!-- Activity Timeline -->
            <div class="dash-chart-card">
                <div class="dash-chart-header">
                    <h3 class="dash-chart-title">
                        <i class="bi bi-activity"></i> Aktivitas Terbaru
                    </h3>
                </div>
                <div class="dash-timeline">
                    <?php
                    $activities = [];
                    foreach ($recentWorks as $w) {
                        $activities[] = [
                            'title' => 'Tugas: ' . mb_strimwidth($w['title'], 0, 40, '...'),
                            'user' => $w['student_name'],
                            'time' => $w['created_at'],
                            'type' => match($w['status']) {
                                'completed' => 'success',
                                'revision' => 'warning',
                                'draft' => 'stable',
                                default => 'info'
                            },
                            'icon' => match($w['status']) {
                                'completed' => 'check-circle-fill',
                                'revision' => 'pencil-fill',
                                'draft' => 'file-earmark-fill',
                                default => 'clock-fill'
                            }
                        ];
                    }
                    $activities = array_slice($activities, 0, 5);
                    ?>
                    <?php if (empty($activities)): ?>
                        <div class="dash-empty" style="padding: 2rem 1rem;">
                            <div class="dash-empty-icon" style="width: 60px; height: 60px; font-size: 1.75rem;">
                                <i class="bi bi-inbox"></i>
                            </div>
                            <p style="font-size: 0.875rem; margin: 0;">Belum ada aktivitas</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($activities as $act): ?>
                            <div class="dash-timeline-item">
                                <div class="dash-timeline-dot <?= $act['type'] ?>"></div>
                                <div class="dash-timeline-content">
                                    <div class="dash-timeline-title"><?= e($act['title']) ?></div>
                                    <div class="dash-timeline-time">
                                        <i class="bi bi-person-fill"></i> <?= e($act['user']) ?>
                                        • <?= date('d/m H:i', strtotime($act['time'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ===== QUICK ACTIONS ===== -->
        <div class="dash-chart-card" style="margin-bottom: 1.5rem;">
            <div class="dash-chart-header">
                <h3 class="dash-chart-title">
                    <i class="bi bi-lightning-charge-fill"></i> Aksi Cepat
                </h3>
                <span style="color: var(--gray-500); font-size: 0.85rem;">Pintasan ke fitur utama</span>
            </div>
            <div class="dash-quick-actions">
                <a href="<?= BASE_URL ?>/admin/students/create" class="dash-quick-btn">
                    <i class="bi bi-person-plus-fill"></i>
                    <span>Tambah Mahasiswa</span>
                </a>
                <a href="<?= BASE_URL ?>/admin/lecturers/create" class="dash-quick-btn success">
                    <i class="bi bi-person-workspace"></i>
                    <span>Tambah Dosen</span>
                </a>
                <a href="<?= BASE_URL ?>/admin/works/create" class="dash-quick-btn warning">
                    <i class="bi bi-file-earmark-plus-fill"></i>
                    <span>Buat Tugas Akhir</span>
                </a>
                <a href="<?= BASE_URL ?>/admin/works" class="dash-quick-btn info">
                    <i class="bi bi-folder-fill"></i>
                    <span>Kelola Tugas</span>
                </a>
            </div>
        </div>

        <!-- ===== RECENT WORKS ===== -->
        <div class="dash-recent-card">
            <div class="dash-recent-header">
                <h3 class="dash-recent-title">
                    <i class="bi bi-clock-history"></i> Tugas Akhir Terbaru
                </h3>
                <a href="<?= BASE_URL ?>/admin/works" class="btn btn-primary btn-sm">
                    <i class="bi bi-arrow-right"></i> Lihat Semua
                </a>
            </div>

            <?php if (empty($recentWorks)): ?>
                <div class="dash-empty">
                    <div class="dash-empty-icon">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <h3>Belum Ada Tugas Akhir</h3>
                    <p>Tugas akhir yang terdaftar akan muncul di sini.</p>
                    <a href="<?= BASE_URL ?>/admin/works/create" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Tambah Tugas Akhir Pertama
                    </a>
                </div>
            <?php else: ?>
                <div class="dash-work-row header">
                    <div>Judul</div>
                    <div>Mahasiswa</div>
                    <div>Jenis</div>
                    <div>Status</div>
                    <div>Tanggal</div>
                    <div>Aksi</div>
                </div>
                <?php foreach (array_slice($recentWorks, 0, 5) as $work): 
                    $pillClass = match($work['status']) {
                        'completed' => 'pill-completed',
                        'draft' => 'pill-draft',
                        'proposal' => 'pill-proposal',
                        'revision' => 'pill-revision',
                        'writing' => 'pill-writing',
                        default => 'pill-draft'
                    };
                ?>
                <div class="dash-work-row">
                    <div class="dash-work-title" title="<?= e($work['title']) ?>">
                        <?= e($work['title']) ?>
                    </div>
                    <div class="dash-work-student">
                        <strong><?= e($work['student_name']) ?></strong>
                        <small><?= e($work['student_number']) ?></small>
                    </div>
                    <div>
                        <span class="badge badge-info"><?= e($work['work_type_name']) ?></span>
                    </div>
                    <div>
                        <span class="dash-work-status-pill <?= $pillClass ?>">
                            <?= ucfirst(e($work['status'])) ?>
                        </span>
                    </div>
                    <div style="color: var(--gray-500); font-size: 0.85rem;">
                        <?= date('d/m/Y', strtotime($work['created_at'])) ?>
                    </div>
                    <div>
                        <a href="<?= BASE_URL ?>/admin/work?id=<?= $work['id'] ?>" class="dash-work-action" title="Lihat Detail">
                            <i class="bi bi-eye-fill"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/admin.js"></script>
    <script>
    /**
     * Dashboard SIMBITA - Scoped JavaScript
     * IIFE untuk menghindari polusi namespace global
     */
    (function() {
        'use strict';

        // ===== COUNTER ANIMATION =====
        const animateCounters = () => {
            document.querySelectorAll('.dash-view [data-count]').forEach(el => {
                const target = parseInt(el.dataset.count);
                if (isNaN(target)) return;
                const duration = 1500;
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
        };

        // ===== CHART INITIALIZATION =====
        const initChart = () => {
            const ctx = document.getElementById('dashStatusChart');
            if (!ctx || typeof Chart === 'undefined') return;

            // Hitung distribusi status dari data recentWorks
            const statusCount = { draft: 0, proposal: 0, writing: 0, revision: 0, completed: 0 };
            const recentWorks = <?= json_encode($recentWorks) ?>;
            recentWorks.forEach(w => {
                if (statusCount.hasOwnProperty(w.status)) statusCount[w.status]++;
                else statusCount.draft++;
            });

            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Draft', 'Proposal', 'Penulisan', 'Revisi', 'Selesai'],
                    datasets: [{
                        data: [statusCount.draft, statusCount.proposal, statusCount.writing, statusCount.revision, statusCount.completed],
                        backgroundColor: ['#94a3b8', '#667eea', '#06b6d4', '#f59e0b', '#10b981'],
                        borderWidth: 0,
                        hoverOffset: 15,
                        spacing: 3,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1f2937',
                            padding: 12,
                            cornerRadius: 8,
                            titleFont: { size: 13, weight: 'bold' },
                            bodyFont: { size: 12 },
                            callbacks: {
                                label: (ctx) => ` ${ctx.label}: ${ctx.parsed} tugas`
                            }
                        }
                    },
                    animation: {
                        animateRotate: true,
                        animateScale: true,
                        duration: 1500,
                        easing: 'easeOutQuart'
                    }
                }
            });
        };

        // ===== STAGGER ANIMATION FOR STAT CARDS =====
        const staggerCards = () => {
            document.querySelectorAll('.dash-view .dash-stat-card').forEach((card, i) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s cubic-bezier(0.16, 1, 0.3, 1)';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100 + i * 80);
            });
        };

        // ===== ALERT AUTO-DISMISS (Scoped) =====
        const initAlerts = () => {
            document.querySelectorAll('.dash-view .alert-dismissible .btn-close').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const alert = e.target.closest('.alert');
                    alert.style.transition = 'all 0.3s';
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateX(30px)';
                    setTimeout(() => alert.remove(), 300);
                });
            });

            document.querySelectorAll('.dash-view .alert-success').forEach(alert => {
                setTimeout(() => {
                    if (alert.parentElement) {
                        alert.style.transition = 'all 0.3s';
                        alert.style.opacity = '0';
                        alert.style.transform = 'translateX(30px)';
                        setTimeout(() => alert.remove(), 300);
                    }
                }, 5000);
            });
        };

        // ===== INIT =====
        document.addEventListener('DOMContentLoaded', () => {
            animateCounters();
            initChart();
            staggerCards();
            initAlerts();
        });
    })();
    </script>
</body>
</html>