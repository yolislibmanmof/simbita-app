<?php
declare(strict_types=1);
$flashError   = $_SESSION['flash_error']   ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

$history = $history ?? [];
$total = count($history);

// Hitung statistik keputusan
$statusCount = [
    'approved'       => 0,
    'needs_revision' => 0,
    'rejected'       => 0,
    'pending'        => 0,
];
foreach ($history as $h) {
    $st = $h['review_status'] ?? 'pending';
    if (isset($statusCount[$st])) $statusCount[$st]++;
}

$palette = [
    'linear-gradient(135deg,#3b82f6,#1e40af)',
    'linear-gradient(135deg,#10b981,#059669)',
    'linear-gradient(135deg,#8b5cf6,#6d28d9)',
    'linear-gradient(135deg,#f59e0b,#d97706)',
];

$statusMeta = [
    'approved'       => ['label' => 'Disetujui',    'class' => 'sh-pill-success', 'icon' => 'check-circle-fill'],
    'needs_revision' => ['label' => 'Perlu Revisi', 'class' => 'sh-pill-info',    'icon' => 'pencil-square'],
    'rejected'       => ['label' => 'Ditolak',      'class' => 'sh-pill-danger',  'icon' => 'x-circle-fill'],
    'pending'        => ['label' => 'Menunggu',     'class' => 'sh-pill-warning', 'icon' => 'hourglass-split'],
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
    <title>Riwayat Upload - <?= e(APP_NAME) ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">
    
    <style>
        /* =========================================================
           STUDENT HISTORY ULTRA PREMIUM - SCOPED STYLES
           Namespace: .sh- | Tema: Ocean Blue (#3b82f6)
           ========================================================= */
        .sh-view {
            --sh-primary: #3b82f6;
            --sh-primary-dark: #2563eb;
            --sh-primary-light: #dbeafe;
            --sh-gradient: linear-gradient(135deg, #3b82f6 0%, #2563eb 50%, #1e40af 100%);
            --sh-glow: 0 10px 30px -10px rgba(59, 130, 246, 0.4);
            animation: shFade 0.4s ease;
        }
        @keyframes shFade { from { opacity: 0; } to { opacity: 1; } }

        /* --- Page Header --- */
        .sh-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .sh-header-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.875rem;
            font-weight: 800;
            color: var(--gray-900);
            margin: 0 0 0.35rem 0;
        }

        .sh-header-icon {
            width: 48px; height: 48px;
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2563eb;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .sh-header-sub {
            color: var(--gray-500);
            font-size: 0.95rem;
            margin: 0 0 0 calc(48px + 0.75rem);
        }

        .sh-back-btn {
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

        .sh-back-btn:hover {
            background: var(--gray-50);
            border-color: var(--gray-400);
            transform: translateX(-3px);
        }

        /* --- Stats Strip --- */
        .sh-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 1.75rem;
        }

        .sh-stat {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-xl);
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s;
            animation: shUp 0.5s ease backwards;
        }

        .sh-stat:nth-child(1) { animation-delay: 0.05s; }
        .sh-stat:nth-child(2) { animation-delay: 0.10s; }
        .sh-stat:nth-child(3) { animation-delay: 0.15s; }
        .sh-stat:nth-child(4) { animation-delay: 0.20s; }

        @keyframes shUp {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .sh-stat:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .sh-stat-icon {
            width: 46px; height: 46px;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: white;
            flex-shrink: 0;
        }

        .sh-stat-icon.i-total { background: linear-gradient(135deg,#3b82f6,#1e40af); }
        .sh-stat-icon.i-approved { background: linear-gradient(135deg,#10b981,#059669); }
        .sh-stat-icon.i-revision { background: linear-gradient(135deg,#f59e0b,#d97706); }
        .sh-stat-icon.i-pending { background: linear-gradient(135deg,#94a3b8,#64748b); }

        .sh-stat-num {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--gray-900);
            line-height: 1.1;
            font-variant-numeric: tabular-nums;
        }

        .sh-stat-label {
            font-size: 0.72rem;
            color: var(--gray-500);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        /* --- Card & Toolbar --- */
        .sh-card {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            animation: shUp 0.5s ease 0.3s backwards;
        }

        .sh-toolbar {
            padding: 1.25rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            border-bottom: 1px solid var(--gray-200);
            background: linear-gradient(to right, var(--gray-50), white);
        }

        .sh-search-wrap {
            position: relative;
            flex: 1;
            min-width: 260px;
            max-width: 420px;
        }

        .sh-search-wrap i {
            position: absolute;
            left: 1rem; top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            pointer-events: none;
            transition: color 0.3s;
        }

        .sh-search {
            width: 100%;
            padding: 0.7rem 1rem 0.7rem 2.75rem;
            border: 1.5px solid var(--gray-300);
            border-radius: var(--radius-full);
            font-size: 0.9rem;
            font-family: inherit;
            transition: all 0.3s;
            background: white;
        }

        .sh-search:focus {
            outline: none;
            border-color: var(--sh-primary);
            box-shadow: 0 0 0 4px rgba(59,130,246,0.12);
        }

        .sh-search-wrap:focus-within i { color: var(--sh-primary); }

        .sh-filters { display: flex; gap: 0.4rem; flex-wrap: wrap; }

        .sh-chip {
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

        .sh-chip:hover { border-color: var(--gray-400); }

        .sh-chip.is-on {
            background: var(--sh-gradient);
            border-color: transparent;
            color: white;
            box-shadow: 0 6px 12px -4px rgba(59,130,246,0.5);
        }

        .sh-chip-count {
            background: rgba(255,255,255,0.25);
            padding: 0.1rem 0.45rem;
            border-radius: var(--radius-full);
            font-size: 0.7rem;
            font-weight: 700;
        }

        .sh-chip:not(.is-on) .sh-chip-count {
            background: var(--gray-100);
            color: var(--gray-600);
        }

        /* --- Timeline Style --- */
        .sh-timeline-wrap { padding: 2rem 1.5rem; }

        .sh-timeline {
            position: relative;
            padding-left: 2rem;
        }

        .sh-timeline::before {
            content: '';
            position: absolute;
            top: 0; bottom: 0; left: 15px;
            width: 2px;
            background: linear-gradient(to bottom, var(--sh-primary), var(--gray-200));
            border-radius: var(--radius-full);
        }

        .sh-item {
            position: relative;
            padding-bottom: 1.5rem;
            animation: shUp 0.45s ease backwards;
        }

        .sh-item:last-child { padding-bottom: 0; }

        .sh-item-dot {
            position: absolute;
            left: -2rem; top: 0.25rem;
            width: 32px; height: 32px;
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

        .sh-item:hover .sh-item-dot { transform: scale(1.15); }

        .sh-item-dot.approved { background: linear-gradient(135deg,#10b981,#059669); }
        .sh-item-dot.needs_revision { background: linear-gradient(135deg,#f59e0b,#d97706); }
        .sh-item-dot.rejected { background: linear-gradient(135deg,#ef4444,#dc2626); }
        .sh-item-dot.pending { background: linear-gradient(135deg,#94a3b8,#64748b); }

        .sh-item-card {
            background: white;
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-xl);
            padding: 1.25rem 1.5rem;
            transition: all 0.3s;
            position: relative;
        }

        .sh-item:hover .sh-item-card {
            border-color: var(--sh-primary);
            box-shadow: 0 10px 25px -8px rgba(59,130,246,0.15);
            transform: translateX(5px);
        }

        .sh-item-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 0.75rem;
            flex-wrap: wrap;
        }

        .sh-item-title {
            font-weight: 700;
            color: var(--gray-900);
            font-size: 0.975rem;
            line-height: 1.4;
            flex: 1;
            min-width: 200px;
        }

        .sh-pill {
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

        .sh-pill::before {
            content: '';
            width: 7px; height: 7px;
            border-radius: 50%;
            background: currentColor;
        }

        .sh-pill-success { background: #d1fae5; color: #065f46; }
        .sh-pill-success::before { animation: shPulse 1.6s infinite; }
        .sh-pill-info { background: #fef3c7; color: #92400e; }
        .sh-pill-info::before { animation: shSpin 2s linear infinite; }
        .sh-pill-danger { background: #fee2e2; color: #991b1b; }
        .sh-pill-warning { background: #f1f5f9; color: #475569; }

        @keyframes shPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.8); }
        }
        @keyframes shSpin { to { transform: rotate(360deg); } }

        .sh-item-meta {
            display: flex;
            gap: 1rem;
            margin-bottom: 0.85rem;
            flex-wrap: wrap;
        }

        .sh-meta-item {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.82rem;
            color: var(--gray-600);
        }

        .sh-meta-item i { color: var(--sh-primary); font-size: 0.95rem; }

        .sh-item-doc {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 0.85rem;
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-md);
            font-size: 0.85rem;
            color: var(--gray-700);
            margin-bottom: 0.75rem;
            max-width: 100%;
            transition: all 0.2s;
            text-decoration: none;
        }

        .sh-item-doc:hover {
            background: white;
            border-color: var(--sh-primary);
            color: var(--sh-primary);
        }

        .sh-item-doc i { color: #dc2626; font-size: 1.1rem; flex-shrink: 0; }

        .sh-item-doc-name {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: 600;
        }

        .sh-item-comment {
            padding: 0.85rem 1rem;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-left: 4px solid #f59e0b;
            border-radius: var(--radius-md);
            font-size: 0.85rem;
            color: #78350f;
            line-height: 1.6;
            position: relative;
            margin-bottom: 0.5rem;
        }

        .sh-item-comment::before {
            content: '\F4CD'; /* bi-chat-left-quote */
            font-family: 'bootstrap-icons';
            position: absolute;
            top: -10px; left: 12px;
            font-size: 1.1rem;
            color: #f59e0b;
            background: white;
            padding: 0 4px;
            border-radius: 4px;
        }

        .sh-item-comment-label {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.25rem;
            color: #92400e;
        }

        .sh-item-time {
            font-size: 0.75rem;
            color: var(--gray-500);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        /* --- Empty & No Result --- */
        .sh-empty {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--gray-500);
        }

        .sh-empty-icon {
            width: 90px; height: 90px;
            margin: 0 auto 1.25rem;
            border-radius: 50%;
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: #2563eb;
            animation: shFloat 3s ease-in-out infinite;
        }

        @keyframes shFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        .sh-empty h3 { font-size: 1.25rem; color: var(--gray-800); margin-bottom: 0.5rem; }
        .sh-empty p { color: var(--gray-500); margin-bottom: 1.5rem; }

        .sh-noresult {
            display: none;
            text-align: center;
            padding: 3rem 2rem;
            color: var(--gray-500);
        }

        .sh-noresult i { font-size: 2.5rem; opacity: 0.4; display: block; margin-bottom: 0.75rem; }

        @media (max-width: 768px) {
            .sh-header-sub { margin-left: 0; }
            .sh-toolbar { flex-direction: column; align-items: stretch; }
            .sh-search-wrap { max-width: none; }
            .sh-timeline { padding-left: 1.5rem; }
            .sh-item-dot { width: 26px; height: 26px; font-size: 0.75rem; left: -1.5rem; }
        }

        /* --- Revision Resolution Stats --- */
.sh-revision-stats {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.65rem 0.85rem;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-lg);
    margin-bottom: 0.75rem;
    font-size: 0.8rem;
}

.sh-progress-bar {
    flex: 1;
    height: 8px;
    background: var(--gray-200);
    border-radius: var(--radius-full);
    overflow: hidden;
    min-width: 80px;
}

.sh-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #10b981, #059669);
    border-radius: var(--radius-full);
    transition: width 0.6s ease;
}

.sh-progress-fill.partial { background: linear-gradient(90deg, #f59e0b, #d97706); }
.sh-progress-fill.empty { background: linear-gradient(90deg, #ef4444, #dc2626); }

.sh-resolve-btn {
    padding: 0.35rem 0.7rem;
    border-radius: var(--radius-md);
    border: 1.5px solid var(--gray-300);
    background: white;
    color: var(--gray-700);
    font-size: 0.72rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-family: inherit;
}

.sh-resolve-btn:hover { border-color: var(--sh-primary); color: var(--sh-primary); }

.sh-resolve-btn.is-resolved {
    background: linear-gradient(135deg, #d1fae5, #a7f3d0);
    border-color: #10b981;
    color: #065f46;
}

.sh-comment-resolved {
    opacity: 0.65;
    background: #f1f5f9 !important;
    border-left-color: #94a3b8 !important;
    text-decoration: line-through;
}

.sh-comment-carried {
    border-left-color: #8b5cf6 !important;
    background: linear-gradient(135deg, #f5f3ff, #ede9fe) !important;
}

.sh-carried-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.15rem 0.5rem;
    background: #8b5cf6;
    color: white;
    border-radius: var(--radius-full);
    font-size: 0.65rem;
    font-weight: 700;
    margin-right: 0.5rem;
}
    </style>
</head>
<body>
    <?php require dirname(__DIR__) . '/partials/navbar.php'; ?>

    <div class="container sh-view">
        <!-- Page Header -->
        <div class="sh-header">
            <div>
                <h1 class="sh-header-title">
                    <span class="sh-header-icon"><i class="bi bi-clock-history"></i></span>
                    Riwayat Upload & Review
                </h1>
                <p class="sh-header-sub">Pantau versi dokumen yang Anda unggah dan feedback dari dosen pembimbing.</p>
            </div>
            <a href="<?= BASE_URL ?>/student/dashboard" class="sh-back-btn">
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
        <div class="sh-stats">
            <div class="sh-stat">
                <div class="sh-stat-icon i-total"><i class="bi bi-file-earmark-arrow-up"></i></div>
                <div>
                    <div class="sh-stat-num"><?= $total ?></div>
                    <div class="sh-stat-label">Total Upload</div>
                </div>
            </div>
            <div class="sh-stat">
                <div class="sh-stat-icon i-approved"><i class="bi bi-check-circle-fill"></i></div>
                <div>
                    <div class="sh-stat-num"><?= $statusCount['approved'] ?></div>
                    <div class="sh-stat-label">Disetujui</div>
                </div>
            </div>
            <div class="sh-stat">
                <div class="sh-stat-icon i-revision"><i class="bi bi-pencil-square"></i></div>
                <div>
                    <div class="sh-stat-num"><?= $statusCount['needs_revision'] ?></div>
                    <div class="sh-stat-label">Perlu Revisi</div>
                </div>
            </div>
            <div class="sh-stat">
                <div class="sh-stat-icon i-pending"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="sh-stat-num"><?= $statusCount['pending'] ?></div>
                    <div class="sh-stat-label">Menunggu Review</div>
                </div>
            </div>
        </div>

        <!-- Main Card -->
        <div class="sh-card">
            <!-- Toolbar -->
            <?php if ($total > 0): ?>
            <div class="sh-toolbar">
                <div class="sh-search-wrap">
                    <i class="bi bi-search"></i>
                    <input type="text" id="shSearch" class="sh-search" placeholder="Cari nama dokumen atau jenis...">
                </div>
                <div class="sh-filters">
                    <button class="sh-chip is-on" data-filter="all">
                        Semua <span class="sh-chip-count"><?= $total ?></span>
                    </button>
                    <button class="sh-chip" data-filter="approved">
                        Disetujui <span class="sh-chip-count"><?= $statusCount['approved'] ?></span>
                    </button>
                    <button class="sh-chip" data-filter="needs_revision">
                        Revisi <span class="sh-chip-count"><?= $statusCount['needs_revision'] ?></span>
                    </button>
                    <button class="sh-chip" data-filter="pending">
                        Menunggu <span class="sh-chip-count"><?= $statusCount['pending'] ?></span>
                    </button>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($total === 0): ?>
                <!-- Empty State -->
                <div class="sh-empty">
                    <div class="sh-empty-icon"><i class="bi bi-folder-x"></i></div>
                    <h3>Belum Ada Riwayat Upload</h3>
                    <p>Anda belum mengunggah dokumen apapun. Mulai unggah dokumen pertama Anda untuk melihat riwayat di sini.</p>
                    <a href="<?= BASE_URL ?>/student/upload" class="btn btn-primary">
                        <i class="bi bi-cloud-upload"></i> Unggah Dokumen Sekarang
                    </a>
                </div>
            <?php else: ?>
                <div class="sh-timeline-wrap">
                    <div class="sh-timeline" id="shTimeline">
                        <?php foreach ($history as $i => $h):
                            $bg = $palette[$i % count($palette)];
                            $st = $h['review_status'] ?? 'pending';
                            $meta = $statusMeta[$st] ?? $statusMeta['pending'];
                            $searchKey = strtolower(
                                ($h['document_type_name'] ?? '') . ' ' .
                                ($h['original_filename'] ?? '')
                            );
                        ?>
                        <div class="sh-item sh-row"
                             data-search="<?= e($searchKey) ?>"
                             data-status="<?= e($st) ?>"
                             style="animation-delay: <?= min($i * 0.05, 0.5) ?>s">
                            <div class="sh-item-dot <?= e($st) ?>">
                                <i class="bi bi-<?= $meta['icon'] ?>"></i>
                            </div>
<div class="sh-item-card">
    <div class="sh-item-header">
        <div class="sh-item-title">
            <?= e($h['type_name'] ?? 'Dokumen') ?> 
            <span style="color: var(--gray-400); font-weight: 500;">|</span> 
            <?= e($h['original_filename']) ?>
        </div>
        <span class="sh-pill <?= $meta['class'] ?>">
            <i class="bi bi-<?= $meta['icon'] ?>"></i>
            <?= $meta['label'] ?>
        </span>
    </div>

    <div class="sh-item-meta">
        <div class="sh-meta-item">
            <i class="bi bi-git"></i>
            <span style="font-family: 'JetBrains Mono', monospace; font-weight: 600;">v<?= $h['version_number'] ?></span>
        </div>
        <div class="sh-meta-item">
            <i class="bi bi-calendar-event"></i>
            <span><?= date('d M Y, H:i', strtotime($h['uploaded_at'])) ?></span>
        </div>
        
        <?php 
            $annCount = count($annotationsMap[$h['id']] ?? []);
            $stats = $versionStats[$h['id']] ?? ['total' => 0, 'resolved' => 0, 'unresolved' => 0, 'percent' => 0];
            if ($annCount > 0): 
        ?>
        <div class="sh-meta-item" style="color: #f59e0b; font-weight: 700; cursor: pointer;" onclick="toggleAnnotations(<?= $h['id'] ?>)">
            <i class="bi bi-chat-left-text-fill"></i>
            <span><?= $annCount ?> Catatan Dosen</span>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($annCount > 0 && $stats['total'] > 0): ?>
    <div class="sh-revision-stats">
        <i class="bi bi-check2-circle" style="color: #10b981;"></i>
        <span style="font-weight: 600; color: var(--gray-700);">
            <?= $stats['resolved'] ?>/<?= $stats['total'] ?> Selesai
        </span>
        <div class="sh-progress-bar">
            <div class="sh-progress-fill <?= $stats['percent'] === 100 ? '' : ($stats['percent'] > 0 ? 'partial' : 'empty') ?>" 
                 style="width: <?= $stats['percent'] ?>%"></div>
        </div>
        <span style="font-weight: 700; color: var(--gray-700); font-variant-numeric: tabular-nums;"><?= $stats['percent'] ?>%</span>
    </div>
    <?php endif; ?>

    <!-- DAFTAR ANOTASI PER HALAMAN (Accordion Toggle) -->
    <?php if ($annCount > 0): ?>
    <div id="ann-list-<?= $h['id'] ?>" style="display: none; margin-top: 1rem; padding-top: 1rem; border-top: 1px dashed var(--gray-200);">
        <h6 style="font-size: 0.8rem; font-weight: 700; color: var(--gray-700); margin-bottom: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Koreksi & Komentar:</h6>
        <?php foreach ($annotationsMap[$h['id']] as $ann): 
            $isResolved = !empty($ann['is_resolved']);
            $isCarriedOver = !empty($ann['is_carried_over']);
            $priorityColor = match($ann['priority']) {
                'minor' => '#3b82f6',
                'mandatory' => '#ef4444',
                default => '#f59e0b'
            };
            $extraClass = $isResolved ? 'sh-comment-resolved' : ($isCarriedOver ? 'sh-comment-carried' : '');
        ?>
        <div class="<?= $extraClass ?>" style="background: white; border: 1px solid var(--gray-200); border-left: 4px solid <?= $priorityColor ?>; padding: 0.75rem; border-radius: var(--radius-md); margin-bottom: 0.5rem; font-size: 0.85rem; color: var(--gray-700);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.25rem; gap: 0.5rem; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                    <?php if ($isCarriedOver): ?>
                        <span class="sh-carried-badge"><i class="bi bi-arrow-repeat"></i> Carry-over</span>
                    <?php endif; ?>
                    <strong style="color: var(--gray-900);">Halaman <?= $ann['page_number'] ?></strong>
                    <span style="font-size: 0.7rem; padding: 0.1rem 0.4rem; background: <?= $priorityColor ?>; color: white; border-radius: var(--radius-full);"><?= ucfirst($ann['priority']) ?></span>
                </div>
                <button type="button" 
                        class="sh-resolve-btn <?= $isResolved ? 'is-resolved' : '' ?>"
                        data-comment-id="<?= $ann['id'] ?>"
                        data-resolved="<?= $isResolved ? '1' : '0' ?>">
                    <i class="bi bi-<?= $isResolved ? 'check-circle-fill' : 'circle' ?>"></i>
                    <span><?= $isResolved ? 'Selesai' : 'Tandai Selesai' ?></span>
                </button>
            </div>
            <p style="margin: 0; line-height: 1.5;"><?= nl2br(e(preg_replace('/^\[CARRY-OVER\]\s*/', '', $ann['comment_text']))) ?></p>
            <div style="font-size: 0.7rem; color: var(--gray-500); margin-top: 0.5rem;">
                <i class="bi bi-person-fill"></i> <?= e($ann['author_name']) ?> • <?= $timeAgo($ann['created_at']) ?>
                <?php if ($isResolved && !empty($ann['resolved_at'])): ?>
                    • <span style="color: #10b981; font-weight: 600;"><i class="bi bi-check2"></i> <?= $timeAgo($ann['resolved_at']) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <a href="<?= BASE_URL ?>/documents/download?id=<?= $h['id'] ?>" class="sh-item-doc" target="_blank">
        <i class="bi bi-file-earmark-pdf-fill"></i>
        <span class="sh-item-doc-name"><?= e($h['original_filename']) ?></span>
        <i class="bi bi-box-arrow-up-right" style="margin-left: auto; color: var(--sh-primary);"></i>
    </a>

    <!-- CATATAN UMUM KEPUTUSAN DOSEN -->
    <?php if (!empty($h['general_note'])): ?>
        <div class="sh-item-comment" style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); border-left-color: #2563eb;">
            <div class="sh-item-comment-label" style="color: #1e40af;">Keputusan Dosen:</div>
            <?= nl2br(e($h['general_note'])) ?>
        </div>
    <?php endif; ?>

    <div class="sh-item-time">
        <i class="bi bi-info-circle"></i>
        Diunggah oleh Anda pada <?= date('d F Y', strtotime($h['uploaded_at'])) ?>
    </div>
</div>
                        <?php endforeach; ?>
                    </div>
                    <div class="sh-noresult" id="shNoResult">
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
        const searchInput = document.getElementById('shSearch');
        const chips = document.querySelectorAll('.sh-chip');
        const rows = document.querySelectorAll('.sh-row');
        const noResult = document.getElementById('shNoResult');
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
        document.querySelectorAll('.sh-view .alert-dismissible .btn-close').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const alert = e.target.closest('.alert');
                alert.style.transition = 'all 0.3s';
                alert.style.opacity = '0';
                alert.style.transform = 'translateX(30px)';
                setTimeout(() => alert.remove(), 300);
            });
        });

        document.querySelectorAll('.sh-view .alert-success').forEach(alert => {
            setTimeout(() => {
                if (alert.parentElement) {
                    alert.style.transition = 'all 0.3s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }
            }, 5000);
        });

        // ===== COUNTER ANIMATION =====
        document.querySelectorAll('.sh-stat-num').forEach(el => {
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

        console.log('%c✓ Student History Loaded', 'color: #3b82f6; font-weight: bold;');

                // ===== RESOLVE COMMENT HANDLER =====
        document.addEventListener('click', async function(e) {
            const btn = e.target.closest('.sh-resolve-btn');
            if (!btn) return;

            const commentId = btn.dataset.commentId;
            const currentlyResolved = btn.dataset.resolved === '1';
            const newResolved = !currentlyResolved;

            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Memproses...';

            const formData = new FormData();
            formData.append('csrf_token', '<?= e(Csrf::token()) ?>');
            formData.append('comment_id', commentId);
            formData.append('resolve', newResolved ? '1' : '0');

            try {
                const res = await fetch('<?= BASE_URL ?>/api/comments/resolve', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) {
                    // Reload halaman untuk update statistik (cara paling aman)
                    window.location.reload();
                } else {
                    alert('Gagal: ' + (data.error || 'Unknown'));
                    btn.disabled = false;
                    btn.innerHTML = currentlyResolved 
                        ? '<i class="bi bi-check-circle-fill"></i> Selesai' 
                        : '<i class="bi bi-circle"></i> Tandai Selesai';
                }
            } catch (err) {
                alert('Error jaringan');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-circle"></i> Tandai Selesai';
            }
        });
    })();
    </script>
    <script>
    function toggleAnnotations(versionId) {
        const list = document.getElementById('ann-list-' + versionId);
        if (list) {
            list.style.display = (list.style.display === 'none') ? 'block' : 'none';
        }
    }
    </script>
</body>
</html>