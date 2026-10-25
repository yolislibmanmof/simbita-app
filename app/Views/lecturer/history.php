<?php
declare(strict_types=1);
$flashError = $_SESSION['flash_error'] ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

$history = $history ?? [];
$total = count($history);

// Hitung statistik keputusan
$decisionCount = [
    'approved' => 0,
    'rejected' => 0,
    'needs_revision' => 0,
    'pending' => 0,
];
foreach ($history as $h) {
    $st = $h['review_status'] ?? 'pending';
    if (isset($decisionCount[$st])) $decisionCount[$st]++;
}

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

$decisionMeta = [
    'approved'        => ['label' => 'Disetujui',    'class' => 'lh-pill-success', 'icon' => 'check-circle-fill'],
    'rejected'        => ['label' => 'Ditolak',       'class' => 'lh-pill-danger',  'icon' => 'x-circle-fill'],
    'needs_revision'  => ['label' => 'Perlu Revisi',  'class' => 'lh-pill-info',    'icon' => 'pencil-square'],
    'pending'         => ['label' => 'Menunggu',      'class' => 'lh-pill-warning', 'icon' => 'hourglass-split'],
];

// Helper relative time
$timeAgo = function(string $date): string {
    $diff = time() - strtotime($date);
    if ($diff < 60) return 'Baru saja';
    if ($diff < 3600) return floor($diff / 60) . ' menit lalu';
    if ($diff < 86400) return floor($diff / 3600) . ' jam lalu';
    if ($diff < 604800) return floor($diff / 86400) . ' hari lalu';
    return date('d M Y', strtotime($date));
};
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Review - <?= e(APP_NAME) ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">
    
    <style>
        /* =========================================================
           LECTURER HISTORY ULTRA PREMIUM - SCOPED STYLES
           Namespace: .lh- | Tema: Emerald Green (#10b981)
           ========================================================= */
        .lh-view {
            --lh-primary: #10b981;
            --lh-primary-dark: #059669;
            --lh-primary-light: #d1fae5;
            --lh-gradient: linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%);
            --lh-glow: 0 10px 30px -10px rgba(16, 185, 129, 0.4);
            animation: lhFade 0.4s ease;
        }
        @keyframes lhFade { from { opacity: 0; } to { opacity: 1; } }

        /* --- Page Header --- */
        .lh-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .lh-header-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.875rem;
            font-weight: 800;
            color: var(--gray-900);
            margin: 0 0 0.35rem 0;
        }

        .lh-header-icon {
            width: 48px; height: 48px;
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #059669;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .lh-header-sub {
            color: var(--gray-500);
            font-size: 0.95rem;
            margin: 0 0 0 calc(48px + 0.75rem);
        }

        .lh-back-btn {
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

        .lh-back-btn:hover {
            background: var(--gray-50);
            border-color: var(--gray-400);
            transform: translateX(-3px);
        }

        /* --- Stats Strip --- */
        .lh-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 1.75rem;
        }

        .lh-stat {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-xl);
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s;
            animation: lhUp 0.5s ease backwards;
        }

        .lh-stat:nth-child(1) { animation-delay: 0.05s; }
        .lh-stat:nth-child(2) { animation-delay: 0.10s; }
        .lh-stat:nth-child(3) { animation-delay: 0.15s; }
        .lh-stat:nth-child(4) { animation-delay: 0.20s; }
        .lh-stat:nth-child(5) { animation-delay: 0.25s; }

        @keyframes lhUp {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .lh-stat:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .lh-stat-icon {
            width: 46px; height: 46px;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: white;
            flex-shrink: 0;
        }

        .lh-stat-icon.i-total { background: linear-gradient(135deg,#10b981,#059669); }
        .lh-stat-icon.i-approved { background: linear-gradient(135deg,#10b981,#059669); }
        .lh-stat-icon.i-revision { background: linear-gradient(135deg,#3b82f6,#1e40af); }
        .lh-stat-icon.i-rejected { background: linear-gradient(135deg,#ef4444,#dc2626); }
        .lh-stat-icon.i-pending { background: linear-gradient(135deg,#f59e0b,#d97706); }

        .lh-stat-num {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--gray-900);
            line-height: 1.1;
            font-variant-numeric: tabular-nums;
        }

        .lh-stat-label {
            font-size: 0.72rem;
            color: var(--gray-500);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        /* --- Card & Toolbar --- */
        .lh-card {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            animation: lhUp 0.5s ease 0.3s backwards;
        }

        .lh-toolbar {
            padding: 1.25rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            border-bottom: 1px solid var(--gray-200);
            background: linear-gradient(to right, var(--gray-50), white);
        }

        .lh-search-wrap {
            position: relative;
            flex: 1;
            min-width: 260px;
            max-width: 420px;
        }

        .lh-search-wrap i {
            position: absolute;
            left: 1rem; top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            pointer-events: none;
            transition: color 0.3s;
        }

        .lh-search {
            width: 100%;
            padding: 0.7rem 1rem 0.7rem 2.75rem;
            border: 1.5px solid var(--gray-300);
            border-radius: var(--radius-full);
            font-size: 0.9rem;
            font-family: inherit;
            transition: all 0.3s;
            background: white;
        }

        .lh-search:focus {
            outline: none;
            border-color: var(--lh-primary);
            box-shadow: 0 0 0 4px rgba(16,185,129,0.12);
        }

        .lh-search-wrap:focus-within i { color: var(--lh-primary); }

        .lh-filters { display: flex; gap: 0.4rem; flex-wrap: wrap; }

        .lh-chip {
            padding: 0.45rem 0.9rem;
            border-radius: var(--radius-full);
            border: 1.5px solid var(--gray-200);
            background: white;
            color: var(--gray-600);
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .lh-chip:hover { border-color: var(--gray-400); }

        .lh-chip.is-on {
            background: var(--lh-gradient);
            border-color: transparent;
            color: white;
            box-shadow: 0 6px 12px -4px rgba(16,185,129,0.5);
        }

        .lh-chip-count {
            background: rgba(255,255,255,0.25);
            padding: 0.1rem 0.45rem;
            border-radius: var(--radius-full);
            font-size: 0.7rem;
            font-weight: 700;
        }

        .lh-chip:not(.is-on) .lh-chip-count {
            background: var(--gray-100);
            color: var(--gray-600);
        }

        /* --- Timeline Style --- */
        .lh-timeline-wrap { padding: 2rem 1.5rem; }

        .lh-timeline {
            position: relative;
            padding-left: 2rem;
        }

        .lh-timeline::before {
            content: '';
            position: absolute;
            top: 0; bottom: 0; left: 15px;
            width: 2px;
            background: linear-gradient(to bottom, var(--lh-primary), var(--gray-200));
            border-radius: var(--radius-full);
        }

        .lh-item {
            position: relative;
            padding-bottom: 1.5rem;
            animation: lhUp 0.45s ease backwards;
        }

        .lh-item:last-child { padding-bottom: 0; }

        .lh-item-dot {
            position: absolute;
            left: -2rem; top: 0.25rem;
            width: 30px; height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.85rem;
            box-shadow: 0 4px 8px -2px rgba(0,0,0,0.15);
            z-index: 2;
            transition: transform 0.3s;
        }

        .lh-item:hover .lh-item-dot { transform: scale(1.15); }

        .lh-item-dot.approved { background: linear-gradient(135deg,#10b981,#059669); }
        .lh-item-dot.needs_revision { background: linear-gradient(135deg,#3b82f6,#1e40af); }
        .lh-item-dot.rejected { background: linear-gradient(135deg,#ef4444,#dc2626); }
        .lh-item-dot.pending { background: linear-gradient(135deg,#f59e0b,#d97706); }

        .lh-item-card {
            background: white;
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-xl);
            padding: 1.25rem 1.5rem;
            transition: all 0.3s;
            position: relative;
        }

        .lh-item:hover .lh-item-card {
            border-color: var(--lh-primary);
            box-shadow: 0 10px 25px -8px rgba(16,185,129,0.15);
            transform: translateX(5px);
        }

        .lh-item-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 0.75rem;
            flex-wrap: wrap;
        }

        .lh-item-title {
            font-weight: 700;
            color: var(--gray-900);
            font-size: 0.975rem;
            line-height: 1.4;
            flex: 1;
            min-width: 200px;
        }

        .lh-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.85rem;
            border-radius: var(--radius-full);
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .lh-pill::before {
            content: '';
            width: 7px; height: 7px;
            border-radius: 50%;
            background: currentColor;
        }

        .lh-pill-success { background: #d1fae5; color: #065f46; }
        .lh-pill-success::before { animation: lhPulse 1.6s infinite; }
        .lh-pill-info { background: #dbeafe; color: #1e40af; }
        .lh-pill-info::before { animation: lhSpin 2s linear infinite; }
        .lh-pill-danger { background: #fee2e2; color: #991b1b; }
        .lh-pill-warning { background: #fef3c7; color: #92400e; }

        @keyframes lhPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.8); }
        }
        @keyframes lhSpin { to { transform: rotate(360deg); } }

        .lh-item-meta {
            display: flex;
            gap: 1rem;
            margin-bottom: 0.85rem;
            flex-wrap: wrap;
        }

        .lh-meta-item {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.82rem;
            color: var(--gray-600);
        }

        .lh-meta-item i { color: var(--lh-primary); font-size: 0.95rem; }

        .lh-meta-avatar {
            width: 24px; height: 24px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 0.65rem;
            flex-shrink: 0;
        }

        .lh-item-doc {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.85rem;
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-md);
            font-size: 0.8rem;
            color: var(--gray-700);
            margin-bottom: 0.75rem;
            max-width: 100%;
            transition: all 0.2s;
        }

        .lh-item-doc:hover {
            background: white;
            border-color: var(--lh-primary);
        }

        .lh-item-doc i { color: #dc2626; font-size: 1.1rem; flex-shrink: 0; }

        .lh-item-doc-name {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: 600;
        }

        .lh-item-comment {
            padding: 0.75rem 1rem;
            background: linear-gradient(135deg, #f0fdf4 0%, #d1fae5 100%);
            border-left: 3px solid var(--lh-primary);
            border-radius: var(--radius-md);
            font-size: 0.85rem;
            color: var(--gray-700);
            line-height: 1.6;
            position: relative;
            margin-bottom: 0.5rem;
        }

        .lh-item-comment::before {
            content: '\F2D1';
            font-family: 'bootstrap-icons';
            position: absolute;
            top: -8px; left: 12px;
            font-size: 1rem;
            color: var(--lh-primary);
            background: white;
            padding: 0 4px;
        }

        .lh-item-time {
            font-size: 0.75rem;
            color: var(--gray-500);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        /* --- Empty & No Result --- */
        .lh-empty {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--gray-500);
        }

        .lh-empty-icon {
            width: 90px; height: 90px;
            margin: 0 auto 1.25rem;
            border-radius: 50%;
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: #059669;
            animation: lhFloat 3s ease-in-out infinite;
        }

        @keyframes lhFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        .lh-empty h3 { font-size: 1.25rem; color: var(--gray-800); margin-bottom: 0.5rem; }
        .lh-empty p { color: var(--gray-500); margin-bottom: 1.5rem; }

        .lh-noresult {
            display: none;
            text-align: center;
            padding: 3rem 2rem;
            color: var(--gray-500);
        }

        .lh-noresult i { font-size: 2.5rem; opacity: 0.4; display: block; margin-bottom: 0.75rem; }

        @media (max-width: 768px) {
            .lh-header-sub { margin-left: 0; }
            .lh-toolbar { flex-direction: column; align-items: stretch; }
            .lh-search-wrap { max-width: none; }
            .lh-timeline { padding-left: 1.5rem; }
            .lh-item-dot { width: 26px; height: 26px; font-size: 0.75rem; left: -1.5rem; }
        }
    </style>
</head>
<body>
    <?php require dirname(__DIR__) . '/partials/navbar.php'; ?>

    <div class="container lh-view">
        <!-- Page Header -->
        <div class="lh-header">
            <div>
                <h1 class="lh-header-title">
                    <span class="lh-header-icon"><i class="bi bi-clock-history"></i></span>
                    Riwayat Review
                </h1>
                <p class="lh-header-sub">Dokumen yang telah Anda review di masa lalu.</p>
            </div>
            <a href="<?= BASE_URL ?>/lecturer/dashboard" class="lh-back-btn">
                <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
            </a>
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

        <!-- Stats Strip -->
        <div class="lh-stats">
            <div class="lh-stat">
                <div class="lh-stat-icon i-total"><i class="bi bi-journal-text"></i></div>
                <div>
                    <div class="lh-stat-num"><?= $total ?></div>
                    <div class="lh-stat-label">Total Review</div>
                </div>
            </div>
            <div class="lh-stat">
                <div class="lh-stat-icon i-approved"><i class="bi bi-check-circle-fill"></i></div>
                <div>
                    <div class="lh-stat-num"><?= $decisionCount['approved'] ?></div>
                    <div class="lh-stat-label">Disetujui</div>
                </div>
            </div>
            <div class="lh-stat">
                <div class="lh-stat-icon i-revision"><i class="bi bi-pencil-square"></i></div>
                <div>
                    <div class="lh-stat-num"><?= $decisionCount['needs_revision'] ?></div>
                    <div class="lh-stat-label">Perlu Revisi</div>
                </div>
            </div>
            <div class="lh-stat">
                <div class="lh-stat-icon i-rejected"><i class="bi bi-x-circle-fill"></i></div>
                <div>
                    <div class="lh-stat-num"><?= $decisionCount['rejected'] ?></div>
                    <div class="lh-stat-label">Ditolak</div>
                </div>
            </div>
        </div>

        <!-- Main Card -->
        <div class="lh-card">
            <!-- Toolbar -->
            <div class="lh-toolbar">
                <div class="lh-search-wrap">
                    <i class="bi bi-search"></i>
                    <input type="text" id="lhSearch" class="lh-search" placeholder="Cari judul, mahasiswa, atau nama dokumen...">
                </div>
                <div class="lh-filters">
                    <button class="lh-chip is-on" data-filter="all">
                        Semua <span class="lh-chip-count"><?= $total ?></span>
                    </button>
                    <button class="lh-chip" data-filter="approved">
                        Disetujui <span class="lh-chip-count"><?= $decisionCount['approved'] ?></span>
                    </button>
                    <button class="lh-chip" data-filter="needs_revision">
                        Revisi <span class="lh-chip-count"><?= $decisionCount['needs_revision'] ?></span>
                    </button>
                    <button class="lh-chip" data-filter="rejected">
                        Ditolak <span class="lh-chip-count"><?= $decisionCount['rejected'] ?></span>
                    </button>
                </div>
            </div>

            <?php if ($total === 0): ?>
                <!-- Empty State -->
                <div class="lh-empty">
                    <div class="lh-empty-icon"><i class="bi bi-journal-x"></i></div>
                    <h3>Belum Ada Riwayat</h3>
                    <p>Anda belum melakukan review dokumen apapun.</p>
                </div>
            <?php else: ?>
                <div class="lh-timeline-wrap">
                    <div class="lh-timeline" id="lhTimeline">
                        <?php foreach ($history as $i => $h):
                            $bg = $palette[$i % count($palette)];
                            $st = $h['review_status'] ?? 'pending';
                            $meta = $decisionMeta[$st] ?? $decisionMeta['pending'];
                            $searchKey = strtolower(
                                $h['student_name'] . ' ' .
                                $h['work_title'] . ' ' .
                                $h['original_filename']
                            );
                        ?>
                        <div class="lh-item lh-row"
                             data-search="<?= e($searchKey) ?>"
                             data-status="<?= e($st) ?>"
                             style="animation-delay: <?= min($i * 0.05, 0.5) ?>s">
                            <div class="lh-item-dot <?= e($st) ?>">
                                <i class="bi bi-<?= $meta['icon'] ?>"></i>
                            </div>
                            <div class="lh-item-card">
                                <div class="lh-item-header">
                                    <div class="lh-item-title"><?= e($h['work_title']) ?></div>
                                    <span class="lh-pill <?= $meta['class'] ?>">
                                        <i class="bi bi-<?= $meta['icon'] ?>"></i>
                                        <?= $meta['label'] ?>
                                    </span>
                                </div>

                                <div class="lh-item-meta">
                                    <div class="lh-meta-item">
                                        <div class="lh-meta-avatar" style="background: <?= $bg ?>;">
                                            <?= e($initials($h['student_name'])) ?>
                                        </div>
                                        <span><?= e($h['student_name']) ?></span>
                                    </div>
                                    <div class="lh-meta-item">
                                        <i class="bi bi-calendar-event"></i>
                                        <span><?= date('d M Y, H:i', strtotime($h['created_at'])) ?></span>
                                    </div>
                                    <div class="lh-meta-item">
                                        <i class="bi bi-clock-history"></i>
                                        <span><?= $timeAgo($h['created_at']) ?></span>
                                    </div>
                                </div>

                                <div class="lh-item-doc">
                                    <i class="bi bi-file-earmark-pdf-fill"></i>
                                    <span class="lh-item-doc-name"><?= e($h['original_filename']) ?></span>
                                </div>

                                <?php if (!empty($h['comment'])): ?>
                                    <div class="lh-item-comment">
                                        <?= nl2br(e($h['comment'])) ?>
                                    </div>
                                <?php endif; ?>

                                <div class="lh-item-time">
                                    <i class="bi bi-info-circle"></i>
                                    Direview oleh Anda pada <?= date('d F Y', strtotime($h['created_at'])) ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="lh-noresult" id="lhNoResult">
                        <i class="bi bi-search"></i>
                        Tidak ada riwayat yang cocok dengan pencarian atau filter Anda.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/admin.js"></script>
    <script>
    (function() {
        'use strict';

        // ===== LIVE SEARCH + FILTER =====
        const searchInput = document.getElementById('lhSearch');
        const chips = document.querySelectorAll('.lh-chip');
        const rows = document.querySelectorAll('.lh-row');
        const noResult = document.getElementById('lhNoResult');
        let currentFilter = 'all';

        const applyFilter = () => {
            const q = (searchInput?.value || '').toLowerCase().trim();
            let visible = 0;

            rows.forEach(row => {
                const matchText = !q || (row.dataset.search || '').includes(q);
                const matchStatus = currentFilter === 'all' || row.dataset.status === currentFilter;
                const show = matchText && matchStatus;
                row.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            if (noResult) noResult.style.display = visible === 0 ? 'block' : 'none';
        };

        searchInput?.addEventListener('input', applyFilter);

        chips.forEach(chip => {
            chip.addEventListener('click', () => {
                chips.forEach(c => c.classList.remove('is-on'));
                chip.classList.add('is-on');
                currentFilter = chip.dataset.filter;
                applyFilter();
            });
        });

        // ===== ALERT AUTO-DISMISS =====
        document.querySelectorAll('.lh-view .alert-dismissible .btn-close').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const alert = e.target.closest('.alert');
                alert.style.transition = 'all 0.3s';
                alert.style.opacity = '0';
                alert.style.transform = 'translateX(30px)';
                setTimeout(() => alert.remove(), 300);
            });
        });

        document.querySelectorAll('.lh-view .alert-success').forEach(alert => {
            setTimeout(() => {
                if (alert.parentElement) {
                    alert.style.transition = 'all 0.3s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }
            }, 5000);
        });

        // ===== COUNTER ANIMATION =====
        document.querySelectorAll('.lh-stat-num').forEach(el => {
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

        console.log('%c✓ Lecturer History Loaded', 'color: #10b981; font-weight: bold;');
    })();
    </script>
</body>
</html>