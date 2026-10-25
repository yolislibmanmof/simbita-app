<?php
declare(strict_types=1);
$flashError = $_SESSION['flash_error'] ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

$works = $works ?? [];
$total = count($works);

// Statistik per status
$statusCount = [
    'draft' => 0, 'proposal' => 0, 'writing' => 0,
    'revision' => 0, 'completed' => 0, 'other' => 0
];
$typeCount = [];
foreach ($works as $w) {
    $st = $w['status'] ?? 'draft';
    if (isset($statusCount[$st])) $statusCount[$st]++;
    else $statusCount['other']++;
    $typeName = $w['work_type_name'] ?? 'Lainnya';
    $typeCount[$typeName] = ($typeCount[$typeName] ?? 0) + 1;
}
$inProgress = $statusCount['proposal'] + $statusCount['writing'] + $statusCount['revision'] + $statusCount['other'];

$palette = [
    'linear-gradient(135deg,#8b5cf6,#6d28d9)',
    'linear-gradient(135deg,#3b82f6,#1e40af)',
    'linear-gradient(135deg,#ec4899,#be185d)',
    'linear-gradient(135deg,#10b981,#059669)',
    'linear-gradient(135deg,#f59e0b,#d97706)',
    'linear-gradient(135deg,#06b6d4,#0891b2)',
];

$initials = function(string $name): string {
    $parts = array_filter(explode(' ', trim($name)));
    if (empty($parts)) return '-';
    $first = strtoupper(substr($parts[0], 0, 1));
    $second = count($parts) > 1 ? strtoupper(substr(end($parts), 0, 1)) : '';
    return $first . $second;
};

$statusMeta = [
    'draft'            => ['label' => 'Draft',           'class' => 'wl-pill-draft',     'icon' => 'file-earmark'],
    'proposal'         => ['label' => 'Proposal',        'class' => 'wl-pill-proposal',  'icon' => 'file-earmark-text'],
    'feasibility_test' => ['label' => 'Uji Kelayakan',   'class' => 'wl-pill-info',      'icon' => 'clipboard-check'],
    'writing'          => ['label' => 'Penulisan',       'class' => 'wl-pill-writing',   'icon' => 'pencil'],
    'progress_exam'    => ['label' => 'Ujian Kemajuan',  'class' => 'wl-pill-info',      'icon' => 'award'],
    'revision'         => ['label' => 'Revisi',          'class' => 'wl-pill-revision',  'icon' => 'arrow-repeat'],
    'closed_exam'      => ['label' => 'Ujian Tertutup',  'class' => 'wl-pill-info',      'icon' => 'door-closed'],
    'ready_for_defense'=> ['label' => 'Siap Sidang',     'class' => 'wl-pill-proposal',  'icon' => 'check2-circle'],
    'defense'          => ['label' => 'Sidang',          'class' => 'wl-pill-proposal',  'icon' => 'person-video3'],
    'completed'        => ['label' => 'Selesai',         'class' => 'wl-pill-success',   'icon' => 'check-circle-fill'],
    'cancelled'        => ['label' => 'Dibatalkan',      'class' => 'wl-pill-danger',    'icon' => 'x-circle'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Tugas Akhir - <?= e(APP_NAME) ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">
    
    <style>
        /* =========================================================
           WORKS LIST ULTRA PREMIUM - SCOPED STYLES
           Namespace: .wl- | Tema: Violet (#8b5cf6)
           ========================================================= */
        .wl-view { animation: wlFade 0.4s ease; }
        @keyframes wlFade { from { opacity: 0; } to { opacity: 1; } }

        /* --- Page Header --- */
        .wl-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .wl-header-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.875rem;
            font-weight: 800;
            color: var(--gray-900);
            margin: 0 0 0.35rem 0;
        }

        .wl-header-icon {
            width: 48px; height: 48px;
            background: linear-gradient(135deg, #ede9fe, #ddd6fe);
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #7c3aed;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .wl-header-sub {
            color: var(--gray-500);
            font-size: 0.95rem;
            margin: 0 0 0 calc(48px + 0.75rem);
        }

        .wl-add-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            border-radius: var(--radius-lg);
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            box-shadow: 0 10px 20px -8px rgba(139,92,246,0.5);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .wl-add-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 25px -8px rgba(139,92,246,0.6);
        }

        /* --- Stats Strip --- */
        .wl-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 1.75rem;
        }

        .wl-stat {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-xl);
            padding: 1.15rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.85rem;
            transition: all 0.3s;
            animation: wlUp 0.5s ease backwards;
        }

        .wl-stat:nth-child(1) { animation-delay: 0.05s; }
        .wl-stat:nth-child(2) { animation-delay: 0.10s; }
        .wl-stat:nth-child(3) { animation-delay: 0.15s; }
        .wl-stat:nth-child(4) { animation-delay: 0.20s; }
        .wl-stat:nth-child(5) { animation-delay: 0.25s; }

        @keyframes wlUp {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .wl-stat:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .wl-stat-icon {
            width: 44px; height: 44px;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
            flex-shrink: 0;
        }

        .wl-stat-icon.i-total { background: linear-gradient(135deg,#8b5cf6,#6d28d9); }
        .wl-stat-icon.i-draft { background: linear-gradient(135deg,#94a3b8,#64748b); }
        .wl-stat-icon.i-progress { background: linear-gradient(135deg,#f59e0b,#d97706); }
        .wl-stat-icon.i-done { background: linear-gradient(135deg,#10b981,#059669); }
        .wl-stat-icon.i-revision { background: linear-gradient(135deg,#ec4899,#be185d); }

        .wl-stat-num {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--gray-900);
            line-height: 1.1;
            font-variant-numeric: tabular-nums;
        }

        .wl-stat-label {
            font-size: 0.72rem;
            color: var(--gray-500);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        /* --- Card & Toolbar --- */
        .wl-card {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .wl-toolbar {
            padding: 1.25rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            border-bottom: 1px solid var(--gray-200);
            background: linear-gradient(to right, var(--gray-50), white);
        }

        .wl-search-wrap {
            position: relative;
            flex: 1;
            min-width: 260px;
            max-width: 420px;
        }

        .wl-search-wrap i {
            position: absolute;
            left: 1rem; top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            pointer-events: none;
            transition: color 0.3s;
        }

        .wl-search {
            width: 100%;
            padding: 0.7rem 1rem 0.7rem 2.75rem;
            border: 1.5px solid var(--gray-300);
            border-radius: var(--radius-full);
            font-size: 0.9rem;
            font-family: inherit;
            transition: all 0.3s;
            background: white;
        }

        .wl-search:focus {
            outline: none;
            border-color: #8b5cf6;
            box-shadow: 0 0 0 4px rgba(139,92,246,0.12);
        }

        .wl-search-wrap:focus-within i { color: #8b5cf6; }

        .wl-filters { display: flex; gap: 0.4rem; flex-wrap: wrap; }

        .wl-chip {
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

        .wl-chip:hover { border-color: var(--gray-400); }

        .wl-chip.is-on {
            background: linear-gradient(135deg,#8b5cf6,#7c3aed);
            border-color: transparent;
            color: white;
            box-shadow: 0 6px 12px -4px rgba(139,92,246,0.5);
        }

        .wl-chip .wl-chip-count {
            background: rgba(255,255,255,0.25);
            padding: 0.1rem 0.45rem;
            border-radius: var(--radius-full);
            font-size: 0.7rem;
            font-weight: 700;
        }

        .wl-chip:not(.is-on) .wl-chip-count {
            background: var(--gray-100);
            color: var(--gray-600);
        }

        .wl-divider {
            width: 1px;
            background: var(--gray-200);
            margin: 0 0.35rem;
            align-self: stretch;
        }

        /* --- Table --- */
        .wl-table-wrap { overflow-x: auto; }

        .wl-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1100px;
        }

        .wl-table thead th {
            padding: 0.9rem 1.5rem;
            text-align: left;
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            background: var(--gray-50);
            border-bottom: 1px solid var(--gray-200);
            white-space: nowrap;
        }

        .wl-table tbody tr {
            border-bottom: 1px solid var(--gray-100);
            transition: all 0.25s;
            animation: wlUp 0.45s ease backwards;
        }

        .wl-table tbody tr:last-child { border-bottom: none; }

        .wl-table tbody tr:hover { background: #faf5ff; }

        .wl-table td {
            padding: 1rem 1.5rem;
            vertical-align: middle;
        }

        /* Title cell */
        .wl-title-cell { min-width: 280px; }

        .wl-title {
            font-weight: 700;
            color: var(--gray-900);
            font-size: 0.95rem;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: 0.35rem;
            transition: color 0.2s;
        }

        tr:hover .wl-title { color: #7c3aed; }

        .wl-meta-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            color: var(--gray-500);
            flex-wrap: wrap;
        }

        .wl-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.15rem 0.55rem;
            background: #ede9fe;
            color: #6d28d9;
            border-radius: var(--radius-full);
            font-weight: 600;
            font-size: 0.68rem;
        }

        /* Student cell */
        .wl-student {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            min-width: 200px;
        }

        .wl-avatar {
            width: 40px; height: 40px;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            font-size: 0.85rem;
            flex-shrink: 0;
            box-shadow: var(--shadow-sm);
            transition: transform 0.3s;
        }

        tr:hover .wl-avatar { transform: scale(1.08) rotate(-4deg); }

        .wl-student-name {
            font-weight: 600;
            color: var(--gray-900);
            font-size: 0.875rem;
            line-height: 1.3;
        }

        .wl-student-nim {
            font-family: 'JetBrains Mono', monospace;
            color: var(--gray-500);
            font-size: 0.75rem;
            font-weight: 500;
        }

        /* Status pill */
        .wl-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.85rem;
            border-radius: var(--radius-full);
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            white-space: nowrap;
        }

        .wl-pill::before {
            content: '';
            width: 7px; height: 7px;
            border-radius: 50%;
            background: currentColor;
        }

        .wl-pill-draft { background: var(--gray-100); color: var(--gray-600); }
        .wl-pill-proposal { background: #dbeafe; color: #1e40af; }
        .wl-pill-writing { background: #cffafe; color: #155e75; }
        .wl-pill-revision { background: #fef3c7; color: #92400e; }
        .wl-pill-revision::before { animation: wlSpin 2s linear infinite; }
        .wl-pill-success { background: #d1fae5; color: #065f46; }
        .wl-pill-success::before { animation: wlPulse 1.6s infinite; }
        .wl-pill-info { background: #ede9fe; color: #6d28d9; }
        .wl-pill-danger { background: #fee2e2; color: #991b1b; }

        @keyframes wlPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.8); }
        }
        @keyframes wlSpin {
            to { transform: rotate(360deg); }
        }

        /* Date cell */
        .wl-date {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.82rem;
            color: var(--gray-600);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .wl-date i { color: #8b5cf6; }

        /* Actions */
        .wl-actions { display: flex; gap: 0.5rem; }

        .wl-icon-btn {
            width: 36px; height: 36px;
            border-radius: var(--radius-md);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1.5px solid var(--gray-200);
            background: white;
            color: var(--gray-600);
            cursor: pointer;
            transition: all 0.25s;
            text-decoration: none;
            font-size: 0.9rem;
            font-family: inherit;
        }

        .wl-icon-btn:hover { transform: translateY(-2px); }

        .wl-icon-view:hover {
            background: #ede9fe;
            border-color: #c4b5fd;
            color: #7c3aed;
        }

        .wl-icon-edit:hover {
            background: #fef3c7;
            border-color: #fcd34d;
            color: #b45309;
        }

        .wl-icon-del:hover {
            background: #fee2e2;
            border-color: #fca5a5;
            color: #b91c1c;
        }

        /* --- Empty & No Result --- */
        .wl-empty {
            text-align: center;
            padding: 4rem 2rem;
        }

        .wl-empty-icon {
            width: 90px; height: 90px;
            margin: 0 auto 1.25rem;
            border-radius: 50%;
            background: linear-gradient(135deg, #ede9fe, #ddd6fe);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: #7c3aed;
            animation: wlFloat 3s ease-in-out infinite;
        }

        @keyframes wlFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        .wl-empty h3 { font-size: 1.25rem; color: var(--gray-800); margin-bottom: 0.5rem; }
        .wl-empty p { color: var(--gray-500); margin-bottom: 1.5rem; }

        .wl-noresult {
            display: none;
            text-align: center;
            padding: 3rem 2rem;
            color: var(--gray-500);
        }

        .wl-noresult i { font-size: 2.5rem; opacity: 0.4; display: block; margin-bottom: 0.75rem; }

        /* --- Delete Modal --- */
        .wl-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
            padding: 1rem;
        }

        .wl-modal-overlay.is-open { opacity: 1; visibility: visible; }

        .wl-modal {
            background: white;
            border-radius: var(--radius-2xl);
            max-width: 440px;
            width: 100%;
            padding: 2.25rem;
            text-align: center;
            transform: scale(0.9) translateY(15px);
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 25px 60px -15px rgba(0,0,0,0.35);
        }

        .wl-modal-overlay.is-open .wl-modal { transform: scale(1) translateY(0); }

        .wl-modal-icon {
            width: 72px; height: 72px;
            margin: 0 auto 1.25rem;
            border-radius: 50%;
            background: #fee2e2;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: #dc2626;
        }

        .wl-modal h3 { font-size: 1.3rem; color: var(--gray-900); margin-bottom: 0.6rem; }
        .wl-modal p { color: var(--gray-500); font-size: 0.9rem; line-height: 1.6; margin-bottom: 1.75rem; }
        .wl-modal p strong { color: var(--gray-800); display: block; margin-top: 0.3rem; }

        .wl-modal-actions { display: flex; gap: 0.75rem; }

        .wl-btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.8rem 1rem;
            border-radius: var(--radius-lg);
            font-weight: 600;
            font-size: 0.9rem;
            border: none;
            cursor: pointer;
            transition: all 0.25s;
            font-family: inherit;
        }

        .wl-btn-cancel { background: var(--gray-100); color: var(--gray-700); }
        .wl-btn-cancel:hover { background: var(--gray-200); }

        .wl-btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            box-shadow: 0 8px 16px -6px rgba(239,68,68,0.5);
        }

        .wl-btn-danger:hover { transform: translateY(-2px); box-shadow: 0 12px 20px -6px rgba(239,68,68,0.6); }

        @media (max-width: 768px) {
            .wl-header-sub { margin-left: 0; }
            .wl-toolbar { flex-direction: column; align-items: stretch; }
            .wl-search-wrap { max-width: none; }
            .wl-divider { display: none; }
        }
    </style>
</head>
<body>
    <?php require dirname(__DIR__) . '/partials/navbar.php'; ?>

    <div class="container wl-view">
        <!-- Page Header -->
        <div class="wl-header">
            <div>
                <h1 class="wl-header-title">
                    <span class="wl-header-icon"><i class="bi bi-journal-richtext"></i></span>
                    Data Tugas Akhir
                </h1>
                <p class="wl-header-sub">Kelola data skripsi, tesis, dan disertasi mahasiswa.</p>
            </div>
            <a href="<?= BASE_URL ?>/admin/works/create" class="wl-add-btn">
                <i class="bi bi-plus-lg"></i> Tambah Tugas Akhir
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
        <div class="wl-stats">
            <div class="wl-stat">
                <div class="wl-stat-icon i-total"><i class="bi bi-journal-text"></i></div>
                <div>
                    <div class="wl-stat-num"><?= $total ?></div>
                    <div class="wl-stat-label">Total TA</div>
                </div>
            </div>
            <div class="wl-stat">
                <div class="wl-stat-icon i-draft"><i class="bi bi-file-earmark"></i></div>
                <div>
                    <div class="wl-stat-num"><?= $statusCount['draft'] ?></div>
                    <div class="wl-stat-label">Draft</div>
                </div>
            </div>
            <div class="wl-stat">
                <div class="wl-stat-icon i-progress"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="wl-stat-num"><?= $inProgress ?></div>
                    <div class="wl-stat-label">Dalam Proses</div>
                </div>
            </div>
            <div class="wl-stat">
                <div class="wl-stat-icon i-revision"><i class="bi bi-arrow-repeat"></i></div>
                <div>
                    <div class="wl-stat-num"><?= $statusCount['revision'] ?></div>
                    <div class="wl-stat-label">Revisi</div>
                </div>
            </div>
            <div class="wl-stat">
                <div class="wl-stat-icon i-done"><i class="bi bi-check-circle-fill"></i></div>
                <div>
                    <div class="wl-stat-num"><?= $statusCount['completed'] ?></div>
                    <div class="wl-stat-label">Selesai</div>
                </div>
            </div>
        </div>

        <!-- Data Card -->
        <div class="wl-card">
            <!-- Toolbar -->
            <div class="wl-toolbar">
                <div class="wl-search-wrap">
                    <i class="bi bi-search"></i>
                    <input type="text" id="wlSearch" class="wl-search" placeholder="Cari judul, nama mahasiswa, atau NIM...">
                </div>
                <div class="wl-filters">
                    <button class="wl-chip is-on" data-filter="all" data-group="status">
                        Semua <span class="wl-chip-count"><?= $total ?></span>
                    </button>
                    <button class="wl-chip" data-filter="draft" data-group="status">
                        Draft <span class="wl-chip-count"><?= $statusCount['draft'] ?></span>
                    </button>
                    <button class="wl-chip" data-filter="in_progress" data-group="status">
                        Proses <span class="wl-chip-count"><?= $inProgress ?></span>
                    </button>
                    <button class="wl-chip" data-filter="revision" data-group="status">
                        Revisi <span class="wl-chip-count"><?= $statusCount['revision'] ?></span>
                    </button>
                    <button class="wl-chip" data-filter="completed" data-group="status">
                        Selesai <span class="wl-chip-count"><?= $statusCount['completed'] ?></span>
                    </button>
                </div>
            </div>

            <?php if ($total === 0): ?>
                <!-- Empty State -->
                <div class="wl-empty">
                    <div class="wl-empty-icon"><i class="bi bi-journal-richtext"></i></div>
                    <h3>Belum Ada Data Tugas Akhir</h3>
                    <p>Mulai tambahkan tugas akhir untuk mengelola proses bimbingan mahasiswa.</p>
                    <a href="<?= BASE_URL ?>/admin/works/create" class="wl-add-btn">
                        <i class="bi bi-plus-lg"></i> Tambah Tugas Akhir Pertama
                    </a>
                </div>
            <?php else: ?>
                <div class="wl-table-wrap">
                    <table class="wl-table">
                        <thead>
                            <tr>
                                <th>Judul Tugas Akhir</th>
                                <th>Mahasiswa</th>
                                <th>Status</th>
                                <th>Tanggal Mulai</th>
                                <th style="text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="wlTbody">
                            <?php foreach ($works as $i => $w):
                                $bg = $palette[$i % count($palette)];
                                $st = $w['status'] ?? 'draft';
                                $meta = $statusMeta[$st] ?? $statusMeta['draft'];
                                $isInProgress = in_array($st, ['proposal','writing','feasibility_test','progress_exam','ready_for_defense','defense','closed_exam']);
                                $filterStatus = $st === 'draft' ? 'draft' : ($st === 'completed' ? 'completed' : ($st === 'revision' ? 'revision' : 'in_progress'));
                                $searchKey = strtolower(
                                    $w['title'] . ' ' .
                                    $w['student_name'] . ' ' .
                                    ($w['student_number'] ?? '') . ' ' .
                                    ($w['work_type_name'] ?? '')
                                );
                            ?>
                            <tr class="wl-row"
                                data-search="<?= e($searchKey) ?>"
                                data-status="<?= $filterStatus ?>"
                                style="animation-delay: <?= min($i * 0.03, 0.4) ?>s">
                                <td class="wl-title-cell">
                                    <div class="wl-title" title="<?= e($w['title']) ?>"><?= e($w['title']) ?></div>
                                    <div class="wl-meta-row">
                                        <span class="wl-type-badge">
                                            <i class="bi bi-bookmark-fill"></i>
                                            <?= e($w['work_type_name'] ?? 'TA') ?>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="wl-student">
                                        <div class="wl-avatar" style="background: <?= $bg ?>;"><?= e($initials($w['student_name'])) ?></div>
                                        <div>
                                            <div class="wl-student-name"><?= e($w['student_name']) ?></div>
                                            <div class="wl-student-nim"><?= e($w['student_number'] ?? '—') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="wl-pill <?= $meta['class'] ?>">
                                        <i class="bi bi-<?= $meta['icon'] ?>"></i>
                                        <?= $meta['label'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="wl-date">
                                        <i class="bi bi-calendar-event-fill"></i>
                                        <?= date('d/m/Y', strtotime($w['started_at'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="wl-actions" style="justify-content: center;">
                                        <a href="<?= BASE_URL ?>/admin/work?id=<?= $w['id'] ?>" class="wl-icon-btn wl-icon-view" title="Lihat Detail">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/admin/works/edit?id=<?= $w['id'] ?>" class="wl-icon-btn wl-icon-edit" title="Edit">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <button type="button" class="wl-icon-btn wl-icon-del" title="Hapus"
                                                data-delete-id="<?= $w['id'] ?>"
                                                data-delete-name="<?= e($w['title']) ?>">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="wl-noresult" id="wlNoResult">
                        <i class="bi bi-search"></i>
                        Tidak ada tugas akhir yang cocok dengan pencarian atau filter Anda.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="wl-modal-overlay" id="wlDeleteModal">
        <div class="wl-modal">
            <div class="wl-modal-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <h3>Hapus Tugas Akhir?</h3>
            <p>Anda akan menghapus tugas akhir berikut secara permanen. Semua dokumen, milestone, dan riwayat review akan ikut terhapus.<strong id="wlDeleteName">-</strong></p>
            <form method="POST" action="<?= BASE_URL ?>/admin/works/delete" id="wlDeleteForm">
                <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                <input type="hidden" name="id" id="wlDeleteId" value="">
                <div class="wl-modal-actions">
                    <button type="button" class="wl-btn wl-btn-cancel" id="wlCancelDelete">Batal</button>
                    <button type="submit" class="wl-btn wl-btn-danger"><i class="bi bi-trash-fill"></i> Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/admin.js"></script>
    <script>
    (function() {
        'use strict';

        // ===== LIVE SEARCH + STATUS FILTER =====
        const searchInput = document.getElementById('wlSearch');
        const chips = document.querySelectorAll('.wl-chip');
        const rows = document.querySelectorAll('.wl-row');
        const noResult = document.getElementById('wlNoResult');
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

        // ===== DELETE MODAL =====
        const modal = document.getElementById('wlDeleteModal');
        const modalName = document.getElementById('wlDeleteName');
        const modalId = document.getElementById('wlDeleteId');
        const cancelBtn = document.getElementById('wlCancelDelete');

        const openModal = (id, name) => {
            modalId.value = id;
            modalName.textContent = name;
            modal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        };

        const closeModal = () => {
            modal.classList.remove('is-open');
            document.body.style.overflow = '';
        };

        document.querySelectorAll('[data-delete-id]').forEach(btn => {
            btn.addEventListener('click', () => openModal(btn.dataset.deleteId, btn.dataset.deleteName));
        });

        cancelBtn?.addEventListener('click', closeModal);
        modal?.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
        });

        // ===== ALERT AUTO-DISMISS =====
        document.querySelectorAll('.wl-view .alert-dismissible .btn-close').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const alert = e.target.closest('.alert');
                alert.style.transition = 'all 0.3s';
                alert.style.opacity = '0';
                alert.style.transform = 'translateX(30px)';
                setTimeout(() => alert.remove(), 300);
            });
        });

        document.querySelectorAll('.wl-view .alert-success').forEach(alert => {
            setTimeout(() => {
                if (alert.parentElement) {
                    alert.style.transition = 'all 0.3s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }
            }, 5000);
        });

        console.log('%c✓ Works List Loaded', 'color: #8b5cf6; font-weight: bold;');
    })();
    </script>
</body>
</html>