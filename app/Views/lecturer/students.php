<?php
declare(strict_types=1);
$flashError = $_SESSION['flash_error'] ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

$myStudents = $myStudents ?? [];
$total = count($myStudents);

// Statistik per status
$statusCount = [
    'draft' => 0, 'proposal' => 0, 'writing' => 0,
    'revision' => 0, 'completed' => 0, 'other' => 0
];
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

$statusMeta = [
    'draft'            => ['label' => 'Draft',          'class' => 'ls-pill-draft',    'icon' => 'file-earmark',      'filter' => 'draft'],
    'proposal'         => ['label' => 'Proposal',       'class' => 'ls-pill-proposal', 'icon' => 'file-earmark-text', 'filter' => 'in_progress'],
    'feasibility_test' => ['label' => 'Uji Kelayakan',  'class' => 'ls-pill-info',     'icon' => 'clipboard-check',   'filter' => 'in_progress'],
    'writing'          => ['label' => 'Penulisan',      'class' => 'ls-pill-writing',  'icon' => 'pencil',            'filter' => 'in_progress'],
    'progress_exam'    => ['label' => 'Ujian Kemajuan', 'class' => 'ls-pill-info',     'icon' => 'award',             'filter' => 'in_progress'],
    'revision'         => ['label' => 'Revisi',         'class' => 'ls-pill-revision', 'icon' => 'arrow-repeat',      'filter' => 'revision'],
    'closed_exam'      => ['label' => 'Ujian Tertutup', 'class' => 'ls-pill-info',     'icon' => 'door-closed',       'filter' => 'in_progress'],
    'ready_for_defense'=> ['label' => 'Siap Sidang',    'class' => 'ls-pill-proposal', 'icon' => 'check2-circle',     'filter' => 'in_progress'],
    'defense'          => ['label' => 'Sidang',         'class' => 'ls-pill-proposal', 'icon' => 'person-video3',     'filter' => 'in_progress'],
    'completed'        => ['label' => 'Selesai',        'class' => 'ls-pill-success',  'icon' => 'check-circle-fill', 'filter' => 'completed'],
    'cancelled'        => ['label' => 'Dibatalkan',     'class' => 'ls-pill-danger',   'icon' => 'x-circle',          'filter' => 'cancelled'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mahasiswa Bimbingan - <?= e(APP_NAME) ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">
    
    <style>
        /* =========================================================
           LECTURER STUDENTS LIST ULTRA PREMIUM - SCOPED STYLES
           Namespace: .ls- | Tema: Emerald Green (#10b981)
           ========================================================= */
        .ls-view {
            --ls-primary: #10b981;
            --ls-primary-dark: #059669;
            --ls-primary-light: #d1fae5;
            --ls-gradient: linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%);
            --ls-glow: 0 10px 30px -10px rgba(16, 185, 129, 0.4);
            animation: lsFade 0.4s ease;
        }
        @keyframes lsFade { from { opacity: 0; } to { opacity: 1; } }

        /* --- Page Header --- */
        .ls-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .ls-header-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.875rem;
            font-weight: 800;
            color: var(--gray-900);
            margin: 0 0 0.35rem 0;
        }

        .ls-header-icon {
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

        .ls-header-sub {
            color: var(--gray-500);
            font-size: 0.95rem;
            margin: 0 0 0 calc(48px + 0.75rem);
        }

        .ls-back-btn {
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

        .ls-back-btn:hover {
            background: var(--gray-50);
            border-color: var(--gray-400);
            transform: translateX(-3px);
        }

        /* --- Stats Strip --- */
        .ls-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 1.75rem;
        }

        .ls-stat {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-xl);
            padding: 1.15rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.85rem;
            transition: all 0.3s;
            animation: lsUp 0.5s ease backwards;
        }

        .ls-stat:nth-child(1) { animation-delay: 0.05s; }
        .ls-stat:nth-child(2) { animation-delay: 0.10s; }
        .ls-stat:nth-child(3) { animation-delay: 0.15s; }
        .ls-stat:nth-child(4) { animation-delay: 0.20s; }
        .ls-stat:nth-child(5) { animation-delay: 0.25s; }

        @keyframes lsUp {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .ls-stat:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .ls-stat-icon {
            width: 44px; height: 44px;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
            flex-shrink: 0;
        }

        .ls-stat-icon.i-total { background: linear-gradient(135deg,#10b981,#059669); }
        .ls-stat-icon.i-draft { background: linear-gradient(135deg,#94a3b8,#64748b); }
        .ls-stat-icon.i-progress { background: linear-gradient(135deg,#f59e0b,#d97706); }
        .ls-stat-icon.i-revision { background: linear-gradient(135deg,#ec4899,#be185d); }
        .ls-stat-icon.i-done { background: linear-gradient(135deg,#3b82f6,#1e40af); }

        .ls-stat-num {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--gray-900);
            line-height: 1.1;
            font-variant-numeric: tabular-nums;
        }

        .ls-stat-label {
            font-size: 0.72rem;
            color: var(--gray-500);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        /* --- Card & Toolbar --- */
        .ls-card {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            animation: lsUp 0.5s ease 0.3s backwards;
        }

        .ls-toolbar {
            padding: 1.25rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            border-bottom: 1px solid var(--gray-200);
            background: linear-gradient(to right, var(--gray-50), white);
        }

        .ls-search-wrap {
            position: relative;
            flex: 1;
            min-width: 260px;
            max-width: 420px;
        }

        .ls-search-wrap i {
            position: absolute;
            left: 1rem; top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            pointer-events: none;
            transition: color 0.3s;
        }

        .ls-search {
            width: 100%;
            padding: 0.7rem 1rem 0.7rem 2.75rem;
            border: 1.5px solid var(--gray-300);
            border-radius: var(--radius-full);
            font-size: 0.9rem;
            font-family: inherit;
            transition: all 0.3s;
            background: white;
        }

        .ls-search:focus {
            outline: none;
            border-color: var(--ls-primary);
            box-shadow: 0 0 0 4px rgba(16,185,129,0.12);
        }

        .ls-search-wrap:focus-within i { color: var(--ls-primary); }

        .ls-filters { display: flex; gap: 0.4rem; flex-wrap: wrap; }

        .ls-chip {
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

        .ls-chip:hover { border-color: var(--gray-400); }

        .ls-chip.is-on {
            background: var(--ls-gradient);
            border-color: transparent;
            color: white;
            box-shadow: 0 6px 12px -4px rgba(16,185,129,0.5);
        }

        .ls-chip-count {
            background: rgba(255,255,255,0.25);
            padding: 0.1rem 0.45rem;
            border-radius: var(--radius-full);
            font-size: 0.7rem;
            font-weight: 700;
        }

        .ls-chip:not(.is-on) .ls-chip-count {
            background: var(--gray-100);
            color: var(--gray-600);
        }

        /* --- Table --- */
        .ls-table-wrap { overflow-x: auto; }

        .ls-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px;
        }

        .ls-table thead th {
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

        .ls-table tbody tr {
            border-bottom: 1px solid var(--gray-100);
            transition: all 0.25s;
            animation: lsUp 0.45s ease backwards;
        }

        .ls-table tbody tr:last-child { border-bottom: none; }

        .ls-table tbody tr:hover { background: #f0fdf4; }

        .ls-table td {
            padding: 1rem 1.5rem;
            vertical-align: middle;
        }

        /* Student cell */
        .ls-student {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            min-width: 240px;
        }

        .ls-avatar {
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

        tr:hover .ls-avatar { transform: scale(1.08) rotate(-4deg); }

        .ls-student-name {
            font-weight: 700;
            color: var(--gray-900);
            font-size: 0.925rem;
            line-height: 1.3;
            margin-bottom: 0.15rem;
        }

        .ls-student-nim {
            font-family: 'JetBrains Mono', monospace;
            color: var(--ls-primary);
            font-size: 0.78rem;
            font-weight: 600;
        }

        /* Type badge */
        .ls-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.3rem 0.75rem;
            background: #ede9fe;
            color: #6d28d9;
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            white-space: nowrap;
        }

        .ls-type-badge i { font-size: 0.8rem; }

        /* Title cell */
        .ls-title-cell {
            min-width: 250px;
            max-width: 350px;
        }

        .ls-title {
            font-size: 0.875rem;
            color: var(--gray-700);
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            transition: color 0.2s;
        }

        tr:hover .ls-title { color: #059669; }

        /* Status pill */
        .ls-pill {
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

        .ls-pill::before {
            content: '';
            width: 7px; height: 7px;
            border-radius: 50%;
            background: currentColor;
        }

        .ls-pill-draft { background: var(--gray-100); color: var(--gray-600); }
        .ls-pill-proposal { background: #dbeafe; color: #1e40af; }
        .ls-pill-writing { background: #cffafe; color: #155e75; }
        .ls-pill-revision { background: #fef3c7; color: #92400e; }
        .ls-pill-revision::before { animation: lsSpin 2s linear infinite; }
        .ls-pill-success { background: #d1fae5; color: #065f46; }
        .ls-pill-success::before { animation: lsPulse 1.6s infinite; }
        .ls-pill-info { background: #ede9fe; color: #6d28d9; }
        .ls-pill-danger { background: #fee2e2; color: #991b1b; }

        @keyframes lsPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.8); }
        }
        @keyframes lsSpin { to { transform: rotate(360deg); } }

        /* Action Button */
        .ls-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.55rem 1.1rem;
            background: var(--ls-gradient);
            color: white;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 0.82rem;
            text-decoration: none;
            transition: all 0.25s;
            white-space: nowrap;
            box-shadow: 0 4px 10px -4px rgba(16,185,129,0.5);
        }

        .ls-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px -5px rgba(16,185,129,0.6);
        }

        /* --- Empty State --- */
        .ls-empty {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--gray-500);
        }

        .ls-empty-icon {
            width: 100px; height: 100px;
            margin: 0 auto 1.5rem;
            border-radius: 50%;
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #059669;
            animation: lsFloat 3s ease-in-out infinite;
        }

        @keyframes lsFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .ls-empty h3 { font-size: 1.35rem; color: var(--gray-800); margin-bottom: 0.5rem; }
        .ls-empty p { color: var(--gray-500); margin: 0; max-width: 400px; margin-left: auto; margin-right: auto; }

        .ls-noresult {
            display: none;
            text-align: center;
            padding: 3rem 2rem;
            color: var(--gray-500);
        }

        .ls-noresult i { font-size: 2.5rem; opacity: 0.4; display: block; margin-bottom: 0.75rem; }

        @media (max-width: 768px) {
            .ls-header-sub { margin-left: 0; }
            .ls-toolbar { flex-direction: column; align-items: stretch; }
            .ls-search-wrap { max-width: none; }
            .ls-table td { padding: 0.85rem 1rem; }
        }
    </style>
</head>
<body>
    <?php require dirname(__DIR__) . '/partials/navbar.php'; ?>

    <div class="container ls-view">
        <!-- Page Header -->
        <div class="ls-header">
            <div>
                <h1 class="ls-header-title">
                    <span class="ls-header-icon"><i class="bi bi-mortarboard-fill"></i></span>
                    Mahasiswa Bimbingan
                </h1>
                <p class="ls-header-sub">Pantau progress tugas akhir mahasiswa yang Anda bimbing.</p>
            </div>
            <a href="<?= BASE_URL ?>/lecturer/dashboard" class="ls-back-btn">
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
        <div class="ls-stats">
            <div class="ls-stat">
                <div class="ls-stat-icon i-total"><i class="bi bi-people-fill"></i></div>
                <div>
                    <div class="ls-stat-num"><?= $total ?></div>
                    <div class="ls-stat-label">Total Bimbingan</div>
                </div>
            </div>
            <div class="ls-stat">
                <div class="ls-stat-icon i-draft"><i class="bi bi-file-earmark"></i></div>
                <div>
                    <div class="ls-stat-num"><?= $statusCount['draft'] ?></div>
                    <div class="ls-stat-label">Draft</div>
                </div>
            </div>
            <div class="ls-stat">
                <div class="ls-stat-icon i-progress"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="ls-stat-num"><?= $inProgress ?></div>
                    <div class="ls-stat-label">Dalam Proses</div>
                </div>
            </div>
            <div class="ls-stat">
                <div class="ls-stat-icon i-revision"><i class="bi bi-arrow-repeat"></i></div>
                <div>
                    <div class="ls-stat-num"><?= $statusCount['revision'] ?></div>
                    <div class="ls-stat-label">Revisi</div>
                </div>
            </div>
            <div class="ls-stat">
                <div class="ls-stat-icon i-done"><i class="bi bi-check-circle-fill"></i></div>
                <div>
                    <div class="ls-stat-num"><?= $statusCount['completed'] ?></div>
                    <div class="ls-stat-label">Selesai</div>
                </div>
            </div>
        </div>

        <!-- Main Card -->
        <div class="ls-card">
            <?php if ($total > 0): ?>
            <!-- Toolbar -->
            <div class="ls-toolbar">
                <div class="ls-search-wrap">
                    <i class="bi bi-search"></i>
                    <input type="text" id="lsSearch" class="ls-search" placeholder="Cari nama, NIM, judul, atau jenis TA...">
                </div>
                <div class="ls-filters">
                    <button class="ls-chip is-on" data-filter="all">
                        Semua <span class="ls-chip-count"><?= $total ?></span>
                    </button>
                    <button class="ls-chip" data-filter="draft">
                        Draft <span class="ls-chip-count"><?= $statusCount['draft'] ?></span>
                    </button>
                    <button class="ls-chip" data-filter="in_progress">
                        Proses <span class="ls-chip-count"><?= $inProgress ?></span>
                    </button>
                    <button class="ls-chip" data-filter="revision">
                        Revisi <span class="ls-chip-count"><?= $statusCount['revision'] ?></span>
                    </button>
                    <button class="ls-chip" data-filter="completed">
                        Selesai <span class="ls-chip-count"><?= $statusCount['completed'] ?></span>
                    </button>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($total === 0): ?>
                <!-- Empty State -->
                <div class="ls-empty">
                    <div class="ls-empty-icon"><i class="bi bi-inbox-fill"></i></div>
                    <h3>Belum Ada Mahasiswa Bimbingan</h3>
                    <p>Anda belum ditugaskan sebagai pembimbing untuk tugas akhir apapun. Mahasiswa akan muncul di sini setelah ditugaskan.</p>
                </div>
            <?php else: ?>
                <div class="ls-table-wrap">
                    <table class="ls-table">
                        <thead>
                            <tr>
                                <th>Mahasiswa</th>
                                <th>Jenis TA</th>
                                <th>Judul Tugas Akhir</th>
                                <th>Status</th>
                                <th style="text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="lsTbody">
                            <?php foreach ($myStudents as $i => $s):
                                $bg = $palette[$i % count($palette)];
                                $st = $s['status'] ?? 'draft';
                                $meta = $statusMeta[$st] ?? $statusMeta['draft'];
                                $filterGroup = $meta['filter'] ?? 'draft';
                                $searchKey = strtolower(
                                    $s['student_name'] . ' ' .
                                    ($s['student_number'] ?? '') . ' ' .
                                    $s['title'] . ' ' .
                                    ($s['work_type_name'] ?? '')
                                );
                            ?>
                            <tr class="ls-row"
                                data-search="<?= e($searchKey) ?>"
                                data-status="<?= e($filterGroup) ?>"
                                style="animation-delay: <?= min($i * 0.03, 0.4) ?>s">
                                <td>
                                    <div class="ls-student">
                                        <div class="ls-avatar" style="background: <?= $bg ?>;">
                                            <?= e($initials($s['student_name'])) ?>
                                        </div>
                                        <div>
                                            <div class="ls-student-name"><?= e($s['student_name']) ?></div>
                                            <div class="ls-student-nim"><?= e($s['student_number'] ?? '—') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="ls-type-badge">
                                        <i class="bi bi-bookmark-fill"></i>
                                        <?= e($s['work_type_name'] ?? 'TA') ?>
                                    </span>
                                </td>
                                <td class="ls-title-cell">
                                    <div class="ls-title" title="<?= e($s['title']) ?>">
                                        <?= e($s['title']) ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="ls-pill <?= $meta['class'] ?>">
                                        <i class="bi bi-<?= $meta['icon'] ?>"></i>
                                        <?= $meta['label'] ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <a href="<?= BASE_URL ?>/lecturer/review?id=<?= $s['id'] ?>" class="ls-action-btn">
                                        <i class="bi bi-pencil-square-fill"></i> Review
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="ls-noresult" id="lsNoResult">
                        <i class="bi bi-search"></i>
                        Tidak ada mahasiswa yang cocok dengan pencarian atau filter Anda.
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
        const searchInput = document.getElementById('lsSearch');
        const chips = document.querySelectorAll('.ls-chip');
        const rows = document.querySelectorAll('.ls-row');
        const noResult = document.getElementById('lsNoResult');
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
        document.querySelectorAll('.ls-view .alert-dismissible .btn-close').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const alert = e.target.closest('.alert');
                alert.style.transition = 'all 0.3s';
                alert.style.opacity = '0';
                alert.style.transform = 'translateX(30px)';
                setTimeout(() => alert.remove(), 300);
            });
        });

        document.querySelectorAll('.ls-view .alert-success').forEach(alert => {
            setTimeout(() => {
                if (alert.parentElement) {
                    alert.style.transition = 'all 0.3s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }
            }, 5000);
        });

        // ===== COUNTER ANIMATION =====
        document.querySelectorAll('.ls-stat-num').forEach(el => {
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

        console.log('%c✓ Lecturer Students Loaded', 'color: #10b981; font-weight: bold;');
    })();
    </script>
</body>
</html>