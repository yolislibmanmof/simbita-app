<?php
declare(strict_types=1);
$flashError = $_SESSION['flash_error'] ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

$reviews = $reviews ?? [];
$total = count($reviews);

// Hitung statistik per status
$statusCount = ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'needs_revision' => 0];
foreach ($reviews as $r) {
    $st = $r['review_status'] ?? 'pending';
    if (isset($statusCount[$st])) $statusCount[$st]++;
}

$palette = [
    'linear-gradient(135deg,#10b981,#059669)',
    'linear-gradient(135deg,#3b82f6,#1e40af)',
    'linear-gradient(135deg,#8b5cf6,#6d28d9)',
    'linear-gradient(135deg,#ec4899,#be185d)',
    'linear-gradient(135deg,#f59e0b,#d97706)',
];

$initials = function(string $name): string {
    $parts = array_filter(explode(' ', trim($name)));
    if (empty($parts)) return '-';
    return strtoupper(substr($parts[0], 0, 1)) . (count($parts) > 1 ? strtoupper(substr(end($parts), 0, 1)) : '');
};

$statusMeta = [
    'pending'        => ['label' => 'Menunggu',     'class' => 'lrs-pill-pending',  'icon' => 'hourglass-split'],
    'approved'       => ['label' => 'Disetujui',    'class' => 'lrs-pill-success',  'icon' => 'check-circle-fill'],
    'rejected'       => ['label' => 'Ditolak',       'class' => 'lrs-pill-danger',   'icon' => 'x-circle-fill'],
    'needs_revision' => ['label' => 'Perlu Revisi',  'class' => 'lrs-pill-info',     'icon' => 'pencil-square'],
];

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
    <title>Daftar Review - <?= e(APP_NAME) ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">
    
    <style>
        /* =========================================================
           LECTURER REVIEWS LIST ULTRA PREMIUM - SCOPED STYLES
           Namespace: .lrs- | Tema: Emerald Green (#10b981)
           ========================================================= */
        .lrs-view {
            --lrs-primary: #10b981;
            --lrs-primary-dark: #059669;
            --lrs-primary-light: #d1fae5;
            --lrs-gradient: linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%);
            animation: lrsFade 0.4s ease;
        }
        @keyframes lrsFade { from { opacity: 0; } to { opacity: 1; } }

        /* --- Page Header --- */
        .lrs-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .lrs-header-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.875rem;
            font-weight: 800;
            color: var(--gray-900);
            margin: 0 0 0.35rem 0;
        }

        .lrs-header-icon {
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

        .lrs-header-sub {
            color: var(--gray-500);
            font-size: 0.95rem;
            margin: 0 0 0 calc(48px + 0.75rem);
        }

        .lrs-back-btn {
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

        .lrs-back-btn:hover {
            background: var(--gray-50);
            border-color: var(--gray-400);
            transform: translateX(-3px);
        }

        /* --- Stats Strip --- */
        .lrs-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 1.75rem;
        }

        .lrs-stat {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-xl);
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s;
            animation: lrsUp 0.5s ease backwards;
        }

        .lrs-stat:nth-child(1) { animation-delay: 0.05s; }
        .lrs-stat:nth-child(2) { animation-delay: 0.10s; }
        .lrs-stat:nth-child(3) { animation-delay: 0.15s; }
        .lrs-stat:nth-child(4) { animation-delay: 0.20s; }
        .lrs-stat:nth-child(5) { animation-delay: 0.25s; }

        @keyframes lrsUp {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .lrs-stat:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .lrs-stat-icon {
            width: 46px; height: 46px;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: white;
            flex-shrink: 0;
        }

        .lrs-stat-icon.i-total { background: linear-gradient(135deg,#10b981,#059669); }
        .lrs-stat-icon.i-pending { background: linear-gradient(135deg,#f59e0b,#d97706); }
        .lrs-stat-icon.i-approved { background: linear-gradient(135deg,#10b981,#059669); }
        .lrs-stat-icon.i-revision { background: linear-gradient(135deg,#3b82f6,#1e40af); }
        .lrs-stat-icon.i-rejected { background: linear-gradient(135deg,#ef4444,#dc2626); }

        .lrs-stat-num {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--gray-900);
            line-height: 1.1;
            font-variant-numeric: tabular-nums;
        }

        .lrs-stat-label {
            font-size: 0.72rem;
            color: var(--gray-500);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        /* --- Card & Toolbar --- */
        .lrs-card {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            animation: lrsUp 0.5s ease 0.3s backwards;
        }

        .lrs-toolbar {
            padding: 1.25rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            border-bottom: 1px solid var(--gray-200);
            background: linear-gradient(to right, var(--gray-50), white);
        }

        .lrs-search-wrap {
            position: relative;
            flex: 1;
            min-width: 260px;
            max-width: 420px;
        }

        .lrs-search-wrap i {
            position: absolute;
            left: 1rem; top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            pointer-events: none;
            transition: color 0.3s;
        }

        .lrs-search {
            width: 100%;
            padding: 0.7rem 1rem 0.7rem 2.75rem;
            border: 1.5px solid var(--gray-300);
            border-radius: var(--radius-full);
            font-size: 0.9rem;
            font-family: inherit;
            transition: all 0.3s;
            background: white;
        }

        .lrs-search:focus {
            outline: none;
            border-color: var(--lrs-primary);
            box-shadow: 0 0 0 4px rgba(16,185,129,0.12);
        }

        .lrs-search-wrap:focus-within i { color: var(--lrs-primary); }

        .lrs-filters { display: flex; gap: 0.4rem; flex-wrap: wrap; }

        .lrs-chip {
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

        .lrs-chip:hover { border-color: var(--gray-400); }

        .lrs-chip.is-on {
            background: var(--lrs-gradient);
            border-color: transparent;
            color: white;
            box-shadow: 0 6px 12px -4px rgba(16,185,129,0.5);
        }

        .lrs-chip-count {
            background: rgba(255,255,255,0.25);
            padding: 0.1rem 0.45rem;
            border-radius: var(--radius-full);
            font-size: 0.7rem;
            font-weight: 700;
        }

        .lrs-chip:not(.is-on) .lrs-chip-count {
            background: var(--gray-100);
            color: var(--gray-600);
        }

        /* --- Review List --- */
        .lrs-list { padding: 1rem; }

        .lrs-item {
            display: grid;
            grid-template-columns: auto 2fr 1.5fr 1fr auto auto;
            gap: 1.25rem;
            padding: 1.25rem;
            margin-bottom: 0.75rem;
            background: white;
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-xl);
            transition: all 0.3s;
            align-items: center;
            animation: lrsUp 0.45s ease backwards;
        }

        .lrs-item:last-child { margin-bottom: 0; }

        .lrs-item:hover {
            border-color: var(--lrs-primary);
            box-shadow: 0 10px 25px -8px rgba(16,185,129,0.15);
            transform: translateX(4px);
        }

        .lrs-item.is-pending {
            border-left: 4px solid #f59e0b;
            background: linear-gradient(to right, #fef3c7 0%, white 20%);
        }

        .lrs-item-avatar {
            width: 52px; height: 52px;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            font-size: 1rem;
            flex-shrink: 0;
            box-shadow: var(--shadow-md);
            transition: transform 0.3s;
        }

        .lrs-item:hover .lrs-item-avatar { transform: scale(1.08) rotate(-4deg); }

        .lrs-item-info { min-width: 0; }

        .lrs-item-student {
            font-weight: 700;
            color: var(--gray-900);
            font-size: 0.95rem;
            margin-bottom: 0.25rem;
        }

        .lrs-item-title {
            font-size: 0.85rem;
            color: var(--gray-600);
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .lrs-item-doc {
            min-width: 0;
        }

        .lrs-item-doc-name {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            background: var(--gray-50);
            border-radius: var(--radius-md);
            margin-bottom: 0.35rem;
            transition: all 0.2s;
        }

        .lrs-item-doc-name:hover { background: var(--lrs-primary-light); }

        .lrs-item-doc-name i {
            color: #dc2626;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .lrs-item-doc-name-text {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: 600;
            color: var(--gray-800);
            font-size: 0.85rem;
        }

        .lrs-item-version {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.2rem 0.5rem;
            background: #ede9fe;
            color: #6d28d9;
            border-radius: var(--radius-full);
            font-size: 0.7rem;
            font-weight: 700;
            font-family: 'JetBrains Mono', monospace;
        }

        .lrs-item-time {
            font-size: 0.8rem;
            color: var(--gray-500);
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            white-space: nowrap;
        }

        .lrs-item-time-main {
            font-weight: 600;
            color: var(--gray-700);
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .lrs-item-time-ago {
            font-size: 0.72rem;
            color: var(--gray-400);
            font-style: italic;
        }

        /* Status pill */
        .lrs-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 0.9rem;
            border-radius: var(--radius-full);
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            white-space: nowrap;
        }

        .lrs-pill::before {
            content: '';
            width: 7px; height: 7px;
            border-radius: 50%;
            background: currentColor;
        }

        .lrs-pill-pending { background: #fef3c7; color: #92400e; }
        .lrs-pill-pending::before { animation: lrsPulse 1.6s infinite; }
        .lrs-pill-success { background: #d1fae5; color: #065f46; }
        .lrs-pill-success::before { animation: lrsPulse 1.6s infinite; }
        .lrs-pill-info { background: #dbeafe; color: #1e40af; }
        .lrs-pill-info::before { animation: lrsSpin 2s linear infinite; }
        .lrs-pill-danger { background: #fee2e2; color: #991b1b; }

        @keyframes lrsPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.8); }
        }
        @keyframes lrsSpin { to { transform: rotate(360deg); } }

        .lrs-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.6rem 1.1rem;
            background: var(--lrs-gradient);
            color: white;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 0.85rem;
            text-decoration: none;
            transition: all 0.25s;
            white-space: nowrap;
            box-shadow: 0 4px 10px -4px rgba(16,185,129,0.5);
        }

        .lrs-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px -5px rgba(16,185,129,0.6);
        }

        .lrs-action-btn.is-done {
            background: var(--gray-100);
            color: var(--gray-600);
            box-shadow: none;
        }

        .lrs-action-btn.is-done:hover {
            background: var(--gray-200);
            color: var(--gray-800);
            box-shadow: none;
        }

        /* --- Empty State --- */
        .lrs-empty {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--gray-500);
        }

        .lrs-empty-icon {
            width: 100px; height: 100px;
            margin: 0 auto 1.5rem;
            border-radius: 50%;
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #059669;
            animation: lrsFloat 3s ease-in-out infinite;
        }

        @keyframes lrsFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .lrs-empty h3 { font-size: 1.35rem; color: var(--gray-800); margin-bottom: 0.5rem; }
        .lrs-empty p { color: var(--gray-500); margin-bottom: 0; max-width: 400px; margin-left: auto; margin-right: auto; }

        .lrs-noresult {
            display: none;
            text-align: center;
            padding: 3rem 2rem;
            color: var(--gray-500);
        }

        .lrs-noresult i { font-size: 2.5rem; opacity: 0.4; display: block; margin-bottom: 0.75rem; }

        @media (max-width: 968px) {
            .lrs-item {
                grid-template-columns: auto 1fr;
                gap: 1rem;
            }
            .lrs-item-info { grid-column: 2; }
            .lrs-item-doc, .lrs-item-time { grid-column: 2; padding-left: calc(52px + 1.25rem); }
            .lrs-item > .lrs-pill { grid-column: 2; justify-self: start; }
            .lrs-item > .lrs-action-btn { grid-column: 1 / -1; justify-content: center; }
        }

        @media (max-width: 640px) {
            .lrs-header-sub { margin-left: 0; }
            .lrs-toolbar { flex-direction: column; align-items: stretch; }
            .lrs-search-wrap { max-width: none; }
        }
    </style>
</head>
<body>
    <?php require dirname(__DIR__) . '/partials/navbar.php'; ?>

    <div class="container lrs-view">
        <!-- Page Header -->
        <div class="lrs-header">
            <div>
                <h1 class="lrs-header-title">
                    <span class="lrs-header-icon"><i class="bi bi-clipboard-check-fill"></i></span>
                    Daftar Review
                </h1>
                <p class="lrs-header-sub">Dokumen yang menunggu persetujuan atau revisi dari Anda.</p>
            </div>
            <a href="<?= BASE_URL ?>/lecturer/dashboard" class="lrs-back-btn">
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
        <div class="lrs-stats">
            <div class="lrs-stat">
                <div class="lrs-stat-icon i-total"><i class="bi bi-inbox-fill"></i></div>
                <div>
                    <div class="lrs-stat-num"><?= $total ?></div>
                    <div class="lrs-stat-label">Total Review</div>
                </div>
            </div>
            <div class="lrs-stat">
                <div class="lrs-stat-icon i-pending"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="lrs-stat-num"><?= $statusCount['pending'] ?></div>
                    <div class="lrs-stat-label">Menunggu</div>
                </div>
            </div>
            <div class="lrs-stat">
                <div class="lrs-stat-icon i-approved"><i class="bi bi-check-circle-fill"></i></div>
                <div>
                    <div class="lrs-stat-num"><?= $statusCount['approved'] ?></div>
                    <div class="lrs-stat-label">Disetujui</div>
                </div>
            </div>
            <div class="lrs-stat">
                <div class="lrs-stat-icon i-revision"><i class="bi bi-pencil-square"></i></div>
                <div>
                    <div class="lrs-stat-num"><?= $statusCount['needs_revision'] ?></div>
                    <div class="lrs-stat-label">Perlu Revisi</div>
                </div>
            </div>
            <div class="lrs-stat">
                <div class="lrs-stat-icon i-rejected"><i class="bi bi-x-circle-fill"></i></div>
                <div>
                    <div class="lrs-stat-num"><?= $statusCount['rejected'] ?></div>
                    <div class="lrs-stat-label">Ditolak</div>
                </div>
            </div>
        </div>

        <!-- Main Card -->
        <div class="lrs-card">
            <?php if ($total > 0): ?>
            <!-- Toolbar -->
            <div class="lrs-toolbar">
                <div class="lrs-search-wrap">
                    <i class="bi bi-search"></i>
                    <input type="text" id="lrsSearch" class="lrs-search" placeholder="Cari mahasiswa, judul, atau nama dokumen...">
                </div>
                <div class="lrs-filters">
                    <button class="lrs-chip is-on" data-filter="all">
                        Semua <span class="lrs-chip-count"><?= $total ?></span>
                    </button>
                    <button class="lrs-chip" data-filter="pending">
                        Menunggu <span class="lrs-chip-count"><?= $statusCount['pending'] ?></span>
                    </button>
                    <button class="lrs-chip" data-filter="approved">
                        Disetujui <span class="lrs-chip-count"><?= $statusCount['approved'] ?></span>
                    </button>
                    <button class="lrs-chip" data-filter="needs_revision">
                        Revisi <span class="lrs-chip-count"><?= $statusCount['needs_revision'] ?></span>
                    </button>
                    <button class="lrs-chip" data-filter="rejected">
                        Ditolak <span class="lrs-chip-count"><?= $statusCount['rejected'] ?></span>
                    </button>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($total === 0): ?>
                <!-- Empty State -->
                <div class="lrs-empty">
                    <div class="lrs-empty-icon"><i class="bi bi-check2-all"></i></div>
                    <h3>Semua Sudah Direview!</h3>
                    <p>Tidak ada dokumen yang menunggu review saat ini. Mahasiswa akan diinformasikan untuk mengunggah dokumen baru.</p>
                </div>
            <?php else: ?>
                <div class="lrs-list" id="lrsList">
                    <?php foreach ($reviews as $i => $r):
                        $bg = $palette[$i % count($palette)];
                        $st = $r['review_status'] ?? 'pending';
                        $meta = $statusMeta[$st] ?? $statusMeta['pending'];
                        $isPending = $st === 'pending';
                        $searchKey = strtolower(
                            $r['student_name'] . ' ' .
                            $r['work_title'] . ' ' .
                            $r['original_filename']
                        );
                    ?>
                    <div class="lrs-item lrs-row <?= $isPending ? 'is-pending' : '' ?>"
                         data-search="<?= e($searchKey) ?>"
                         data-status="<?= e($st) ?>"
                         style="animation-delay: <?= min($i * 0.05, 0.5) ?>s">
                        <div class="lrs-item-avatar" style="background: <?= $bg ?>;">
                            <?= e($initials($r['student_name'])) ?>
                        </div>

                        <div class="lrs-item-info">
                            <div class="lrs-item-student"><?= e($r['student_name']) ?></div>
                            <div class="lrs-item-title" title="<?= e($r['work_title']) ?>"><?= e($r['work_title']) ?></div>
                        </div>

                        <div class="lrs-item-doc">
                            <div class="lrs-item-doc-name">
                                <i class="bi bi-file-earmark-pdf-fill"></i>
                                <span class="lrs-item-doc-name-text" title="<?= e($r['original_filename']) ?>">
                                    <?= e($r['original_filename']) ?>
                                </span>
                            </div>
                            <span class="lrs-item-version">
                                <i class="bi bi-git"></i> v<?= e($r['version_number']) ?>
                            </span>
                        </div>

                        <div class="lrs-item-time">
                            <div class="lrs-item-time-main">
                                <i class="bi bi-calendar-event"></i>
                                <?= date('d/m/Y', strtotime($r['created_at'])) ?>
                            </div>
                            <div class="lrs-item-time-ago"><?= $timeAgo($r['created_at']) ?></div>
                        </div>

                        <span class="lrs-pill <?= $meta['class'] ?>">
                            <i class="bi bi-<?= $meta['icon'] ?>"></i>
                            <?= $meta['label'] ?>
                        </span>

                        <a href="<?= BASE_URL ?>/lecturer/review?id=<?= $r['academic_work_id'] ?? $r['id'] ?>"
                           class="lrs-action-btn <?= $isPending ? '' : 'is-done' ?>">
                            <i class="bi bi-<?= $isPending ? 'pencil-square-fill' : 'eye-fill' ?>"></i>
                            <?= $isPending ? 'Review' : 'Lihat' ?>
                        </a>
                    </div>
                    <?php endforeach; ?>
                    <div class="lrs-noresult" id="lrsNoResult">
                        <i class="bi bi-search"></i>
                        Tidak ada review yang cocok dengan pencarian atau filter Anda.
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
        const searchInput = document.getElementById('lrsSearch');
        const chips = document.querySelectorAll('.lrs-chip');
        const rows = document.querySelectorAll('.lrs-row');
        const noResult = document.getElementById('lrsNoResult');
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
        document.querySelectorAll('.lrs-view .alert-dismissible .btn-close').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const alert = e.target.closest('.alert');
                alert.style.transition = 'all 0.3s';
                alert.style.opacity = '0';
                alert.style.transform = 'translateX(30px)';
                setTimeout(() => alert.remove(), 300);
            });
        });

        document.querySelectorAll('.lrs-view .alert-success').forEach(alert => {
            setTimeout(() => {
                if (alert.parentElement) {
                    alert.style.transition = 'all 0.3s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }
            }, 5000);
        });

        // ===== COUNTER ANIMATION =====
        document.querySelectorAll('.lrs-stat-num').forEach(el => {
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

        console.log('%c✓ Lecturer Reviews Loaded', 'color: #10b981; font-weight: bold;');
    })();
    </script>
</body>
</html>