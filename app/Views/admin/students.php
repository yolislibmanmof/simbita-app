<?php
declare(strict_types=1);
$flashError = $_SESSION['flash_error'] ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

$students = $students ?? [];
$total = count($students);
$active = count(array_filter($students, fn($s) => (int)$s['is_active'] === 1));
$inactive = $total - $active;

// Hitung per jenjang
$countByDegree = ['s1' => 0, 's2' => 0, 's3' => 0];
$years = [];
foreach ($students as $s) {
    $deg = strtolower($s['degree_level'] ?? '');
    if (isset($countByDegree[$deg])) $countByDegree[$deg]++;
    if (!empty($s['enrollment_year'])) $years[] = (int)$s['enrollment_year'];
}
$latestYear = empty($years) ? date('Y') : max($years);
$currentBatch = count(array_filter($students, fn($s) => (int)$s['enrollment_year'] === $latestYear));

// Palet gradient untuk avatar
$palette = [
    'linear-gradient(135deg,#3b82f6,#1e40af)',
    'linear-gradient(135deg,#8b5cf6,#6d28d9)',
    'linear-gradient(135deg,#ec4899,#be185d)',
    'linear-gradient(135deg,#06b6d4,#0891b2)',
    'linear-gradient(135deg,#10b981,#059669)',
    'linear-gradient(135deg,#f59e0b,#d97706)',
];

// Jenjang info
$degreeInfo = [
    's1' => ['name' => 'Sarjana', 'color' => '#3b82f6', 'bg' => '#dbeafe'],
    's2' => ['name' => 'Magister', 'color' => '#8b5cf6', 'bg' => '#ede9fe'],
    's3' => ['name' => 'Doktor', 'color' => '#ec4899', 'bg' => '#fce7f3'],
];

// Helper inisial nama
$initials = function(string $name): string {
    $parts = array_filter(explode(' ', trim($name)));
    if (empty($parts)) return '-';
    $first = strtoupper(substr($parts[0], 0, 1));
    $second = '';
    if (count($parts) > 1) {
        $last = end($parts);
        $second = strtoupper(substr($last, 0, 1));
    }
    return $first . $second;
};
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mahasiswa - <?= e(APP_NAME) ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">
    
    <style>
        /* =========================================================
           STUDENTS LIST ULTRA PREMIUM - SCOPED STYLES
           Namespace: .sl- | Halaman DAFTAR Mahasiswa (Tema Biru)
           ========================================================= */
        .sl-view { animation: slFade 0.4s ease; }
        @keyframes slFade { from { opacity: 0; } to { opacity: 1; } }

        /* --- Page Header --- */
        .sl-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .sl-header-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.875rem;
            font-weight: 800;
            color: var(--gray-900);
            margin: 0 0 0.35rem 0;
        }

        .sl-header-icon {
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

        .sl-header-sub {
            color: var(--gray-500);
            font-size: 0.95rem;
            margin: 0 0 0 calc(48px + 0.75rem);
        }

        .sl-add-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border-radius: var(--radius-lg);
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            box-shadow: 0 10px 20px -8px rgba(59,130,246,0.5);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .sl-add-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 25px -8px rgba(59,130,246,0.6);
        }

        /* --- Stats Strip --- */
        .sl-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.75rem;
        }

        .sl-stat {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-xl);
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s;
            animation: slUp 0.5s ease backwards;
        }

        .sl-stat:nth-child(1) { animation-delay: 0.05s; }
        .sl-stat:nth-child(2) { animation-delay: 0.12s; }
        .sl-stat:nth-child(3) { animation-delay: 0.19s; }
        .sl-stat:nth-child(4) { animation-delay: 0.26s; }
        .sl-stat:nth-child(5) { animation-delay: 0.33s; }

        @keyframes slUp {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .sl-stat:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .sl-stat-icon {
            width: 46px; height: 46px;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: white;
            flex-shrink: 0;
        }

        .sl-stat-icon.i-total { background: linear-gradient(135deg,#3b82f6,#1e40af); }
        .sl-stat-icon.i-active { background: linear-gradient(135deg,#10b981,#059669); }
        .sl-stat-icon.i-inactive { background: linear-gradient(135deg,#94a3b8,#64748b); }
        .sl-stat-icon.i-batch { background: linear-gradient(135deg,#8b5cf6,#6d28d9); }
        .sl-stat-icon.i-degree { background: linear-gradient(135deg,#ec4899,#be185d); }

        .sl-stat-num {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--gray-900);
            line-height: 1.1;
            font-variant-numeric: tabular-nums;
        }

        .sl-stat-label {
            font-size: 0.75rem;
            color: var(--gray-500);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        /* --- Card & Toolbar --- */
        .sl-card {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .sl-toolbar {
            padding: 1.25rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            border-bottom: 1px solid var(--gray-200);
            background: linear-gradient(to right, var(--gray-50), white);
        }

        .sl-search-wrap {
            position: relative;
            flex: 1;
            min-width: 260px;
            max-width: 420px;
        }

        .sl-search-wrap i {
            position: absolute;
            left: 1rem; top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            pointer-events: none;
            transition: color 0.3s;
        }

        .sl-search {
            width: 100%;
            padding: 0.7rem 1rem 0.7rem 2.75rem;
            border: 1.5px solid var(--gray-300);
            border-radius: var(--radius-full);
            font-size: 0.9rem;
            font-family: inherit;
            transition: all 0.3s;
            background: white;
        }

        .sl-search:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59,130,246,0.12);
        }

        .sl-search-wrap:focus-within i { color: #3b82f6; }

        .sl-filters { display: flex; gap: 0.4rem; flex-wrap: wrap; }

        .sl-filter-chip {
            padding: 0.5rem 1rem;
            border-radius: var(--radius-full);
            border: 1.5px solid var(--gray-200);
            background: white;
            color: var(--gray-600);
            font-size: 0.78rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s;
            font-family: inherit;
        }

        .sl-filter-chip:hover { border-color: var(--gray-400); }

        .sl-filter-chip.is-on {
            background: linear-gradient(135deg,#3b82f6,#2563eb);
            border-color: transparent;
            color: white;
            box-shadow: 0 6px 12px -4px rgba(59,130,246,0.5);
        }

        .sl-filter-divider {
            width: 1px;
            background: var(--gray-200);
            margin: 0 0.35rem;
            align-self: stretch;
        }

        /* --- Table --- */
        .sl-table-wrap { overflow-x: auto; }

        .sl-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px;
        }

        .sl-table thead th {
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

        .sl-table tbody tr {
            border-bottom: 1px solid var(--gray-100);
            transition: all 0.25s;
            animation: slUp 0.45s ease backwards;
        }

        .sl-table tbody tr:last-child { border-bottom: none; }

        .sl-table tbody tr:hover { background: #eff6ff; }

        .sl-table td {
            padding: 1rem 1.5rem;
            vertical-align: middle;
        }

        /* Person cell */
        .sl-person {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            min-width: 240px;
        }

        .sl-avatar {
            width: 44px; height: 44px;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            font-size: 0.95rem;
            flex-shrink: 0;
            box-shadow: var(--shadow-md);
            transition: transform 0.3s;
        }

        tr:hover .sl-avatar { transform: scale(1.08) rotate(-4deg); }

        .sl-name {
            font-weight: 700;
            color: var(--gray-900);
            font-size: 0.925rem;
            line-height: 1.3;
        }

        .sl-email {
            color: var(--gray-500);
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .sl-mono {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.82rem;
            font-weight: 600;
            color: #2563eb;
            background: #dbeafe;
            padding: 0.3rem 0.7rem;
            border-radius: var(--radius-md);
            display: inline-block;
            letter-spacing: 0.03em;
        }

        .sl-prodi { font-weight: 600; color: var(--gray-800); font-size: 0.9rem; }
        .sl-fak { color: var(--gray-500); font-size: 0.8rem; }

        /* Jenjang pill */
        .sl-jenjang {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.3rem 0.75rem;
            border-radius: var(--radius-full);
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .sl-jenjang i { font-size: 0.85rem; }

        /* Year badge */
        .sl-year {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 600;
            color: var(--gray-700);
            font-size: 0.875rem;
        }

        .sl-year i { color: #3b82f6; }

        /* Status pill */
        .sl-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.85rem;
            border-radius: var(--radius-full);
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .sl-pill::before {
            content: '';
            width: 7px; height: 7px;
            border-radius: 50%;
            background: currentColor;
        }

        .sl-pill-on { background: #d1fae5; color: #065f46; }
        .sl-pill-on::before { animation: slPulse 1.6s infinite; }
        .sl-pill-off { background: var(--gray-200); color: var(--gray-600); }

        @keyframes slPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.8); }
        }

        /* Actions */
        .sl-actions { display: flex; gap: 0.5rem; }

        .sl-icon-btn {
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

        .sl-icon-btn:hover { transform: translateY(-2px); }

        .sl-icon-edit:hover {
            background: #fef3c7;
            border-color: #fcd34d;
            color: #b45309;
        }

        .sl-icon-del:hover {
            background: #fee2e2;
            border-color: #fca5a5;
            color: #b91c1c;
        }

        /* --- Empty & No Result --- */
        .sl-empty {
            text-align: center;
            padding: 4rem 2rem;
        }

        .sl-empty-icon {
            width: 90px; height: 90px;
            margin: 0 auto 1.25rem;
            border-radius: 50%;
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: #2563eb;
            animation: slFloat 3s ease-in-out infinite;
        }

        @keyframes slFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        .sl-empty h3 { font-size: 1.25rem; color: var(--gray-800); margin-bottom: 0.5rem; }
        .sl-empty p { color: var(--gray-500); margin-bottom: 1.5rem; }

        .sl-noresult {
            display: none;
            text-align: center;
            padding: 3rem 2rem;
            color: var(--gray-500);
        }

        .sl-noresult i { font-size: 2.5rem; opacity: 0.4; display: block; margin-bottom: 0.75rem; }

        /* --- Delete Modal --- */
        .sl-modal-overlay {
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

        .sl-modal-overlay.is-open { opacity: 1; visibility: visible; }

        .sl-modal {
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

        .sl-modal-overlay.is-open .sl-modal { transform: scale(1) translateY(0); }

        .sl-modal-icon {
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

        .sl-modal h3 { font-size: 1.3rem; color: var(--gray-900); margin-bottom: 0.6rem; }
        .sl-modal p { color: var(--gray-500); font-size: 0.9rem; line-height: 1.6; margin-bottom: 1.75rem; }
        .sl-modal p strong { color: var(--gray-800); }

        .sl-modal-actions { display: flex; gap: 0.75rem; }

        .sl-btn {
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

        .sl-btn-cancel { background: var(--gray-100); color: var(--gray-700); }
        .sl-btn-cancel:hover { background: var(--gray-200); }

        .sl-btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            box-shadow: 0 8px 16px -6px rgba(239,68,68,0.5);
        }

        .sl-btn-danger:hover { transform: translateY(-2px); box-shadow: 0 12px 20px -6px rgba(239,68,68,0.6); }

        @media (max-width: 768px) {
            .sl-header-sub { margin-left: 0; }
            .sl-toolbar { flex-direction: column; align-items: stretch; }
            .sl-search-wrap { max-width: none; }
            .sl-filter-divider { display: none; }
        }
    </style>
</head>
<body>
    <?php require dirname(__DIR__) . '/partials/navbar.php'; ?>

    <div class="container sl-view">
        <!-- Page Header -->
        <div class="sl-header">
            <div>
                <h1 class="sl-header-title">
                    <span class="sl-header-icon"><i class="bi bi-mortarboard-fill"></i></span>
                    Data Mahasiswa
                </h1>
                <p class="sl-header-sub">Kelola dan pantau seluruh mahasiswa yang terdaftar dalam sistem.</p>
            </div>
            <a href="<?= BASE_URL ?>/admin/students/create" class="sl-add-btn">
                <i class="bi bi-plus-lg"></i> Tambah Mahasiswa
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
        <div class="sl-stats">
            <div class="sl-stat">
                <div class="sl-stat-icon i-total"><i class="bi bi-people-fill"></i></div>
                <div>
                    <div class="sl-stat-num"><?= $total ?></div>
                    <div class="sl-stat-label">Total Mahasiswa</div>
                </div>
            </div>
            <div class="sl-stat">
                <div class="sl-stat-icon i-active"><i class="bi bi-check-circle-fill"></i></div>
                <div>
                    <div class="sl-stat-num"><?= $active ?></div>
                    <div class="sl-stat-label">Aktif</div>
                </div>
            </div>
            <div class="sl-stat">
                <div class="sl-stat-icon i-inactive"><i class="bi bi-pause-circle-fill"></i></div>
                <div>
                    <div class="sl-stat-num"><?= $inactive ?></div>
                    <div class="sl-stat-label">Nonaktif</div>
                </div>
            </div>
            <div class="sl-stat">
                <div class="sl-stat-icon i-batch"><i class="bi bi-calendar3"></i></div>
                <div>
                    <div class="sl-stat-num"><?= $currentBatch ?></div>
                    <div class="sl-stat-label">Angkatan <?= $latestYear ?></div>
                </div>
            </div>
            <div class="sl-stat">
                <div class="sl-stat-icon i-degree"><i class="bi bi-award-fill"></i></div>
                <div>
                    <div class="sl-stat-num"><?= $countByDegree['s1'] + $countByDegree['s2'] + $countByDegree['s3'] ?></div>
                    <div class="sl-stat-label">S1/S2/S3</div>
                </div>
            </div>
        </div>

        <!-- Data Card -->
        <div class="sl-card">
            <!-- Toolbar -->
            <div class="sl-toolbar">
                <div class="sl-search-wrap">
                    <i class="bi bi-search"></i>
                    <input type="text" id="slSearch" class="sl-search" placeholder="Cari nama, NIM, email, atau prodi...">
                </div>
                <div class="sl-filters">
                    <button class="sl-filter-chip is-on" data-filter="all" data-group="status">Semua</button>
                    <button class="sl-filter-chip" data-filter="active" data-group="status">Aktif</button>
                    <button class="sl-filter-chip" data-filter="inactive" data-group="status">Nonaktif</button>
                    <div class="sl-filter-divider"></div>
                    <button class="sl-filter-chip is-on" data-filter="all" data-group="degree">Semua Jenjang</button>
                    <button class="sl-filter-chip" data-filter="s1" data-group="degree">S1</button>
                    <button class="sl-filter-chip" data-filter="s2" data-group="degree">S2</button>
                    <button class="sl-filter-chip" data-filter="s3" data-group="degree">S3</button>
                </div>
            </div>

            <?php if ($total === 0): ?>
                <!-- Empty State -->
                <div class="sl-empty">
                    <div class="sl-empty-icon"><i class="bi bi-mortarboard-fill"></i></div>
                    <h3>Belum Ada Data Mahasiswa</h3>
                    <p>Mulai tambahkan mahasiswa untuk mengelola data akademik dan bimbingan.</p>
                    <a href="<?= BASE_URL ?>/admin/students/create" class="sl-add-btn">
                        <i class="bi bi-plus-lg"></i> Tambah Mahasiswa Pertama
                    </a>
                </div>
            <?php else: ?>
                <div class="sl-table-wrap">
                    <table class="sl-table">
                        <thead>
                            <tr>
                                <th>Mahasiswa</th>
                                <th>NIM</th>
                                <th>Program Studi</th>
                                <th>Jenjang</th>
                                <th>Angkatan</th>
                                <th>Status</th>
                                <th style="text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="slTbody">
                            <?php foreach ($students as $i => $s):
                                $bg = $palette[$i % count($palette)];
                                $isActive = (int)$s['is_active'] === 1;
                                $deg = strtolower($s['degree_level'] ?? 's1');
                                $degMeta = $degreeInfo[$deg] ?? $degreeInfo['s1'];
                                $searchKey = strtolower(
                                    $s['name'] . ' ' .
                                    $s['email'] . ' ' .
                                    ($s['student_number'] ?? '') . ' ' .
                                    ($s['study_program'] ?? '') . ' ' .
                                    ($s['faculty'] ?? '')
                                );
                            ?>
                            <tr class="sl-row"
                                data-search="<?= e($searchKey) ?>"
                                data-status="<?= $isActive ? 'active' : 'inactive' ?>"
                                data-degree="<?= e($deg) ?>"
                                style="animation-delay: <?= min($i * 0.04, 0.4) ?>s">
                                <td>
                                    <div class="sl-person">
                                        <div class="sl-avatar" style="background: <?= $bg ?>;"><?= e($initials($s['name'])) ?></div>
                                        <div>
                                            <div class="sl-name"><?= e($s['name']) ?></div>
                                            <div class="sl-email"><i class="bi bi-envelope"></i> <?= e($s['email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="sl-mono"><?= e($s['student_number']) ?></span>
                                </td>
                                <td>
                                    <div class="sl-prodi"><?= e($s['study_program']) ?></div>
                                    <div class="sl-fak"><?= e($s['faculty'] ?: '—') ?></div>
                                </td>
                                <td>
                                    <span class="sl-jenjang" style="background: <?= $degMeta['bg'] ?>; color: <?= $degMeta['color'] ?>;">
                                        <i class="bi bi-<?= $deg === 's3' ? 'award-fill' : 'mortarboard-fill' ?>"></i>
                                        <?= strtoupper(e($deg)) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="sl-year">
                                        <i class="bi bi-calendar-event-fill"></i>
                                        <?= e((string)$s['enrollment_year']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="sl-pill <?= $isActive ? 'sl-pill-on' : 'sl-pill-off' ?>">
                                        <?= $isActive ? 'Aktif' : 'Nonaktif' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="sl-actions" style="justify-content: center;">
                                        <a href="<?= BASE_URL ?>/admin/students/edit?id=<?= $s['id'] ?>" class="sl-icon-btn sl-icon-edit" title="Edit <?= e($s['name']) ?>">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <button type="button" class="sl-icon-btn sl-icon-del" title="Hapus <?= e($s['name']) ?>"
                                                data-delete-id="<?= $s['id'] ?>" data-delete-name="<?= e($s['name']) ?>">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="sl-noresult" id="slNoResult">
                        <i class="bi bi-search"></i>
                        Tidak ada mahasiswa yang cocok dengan pencarian atau filter Anda.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="sl-modal-overlay" id="slDeleteModal">
        <div class="sl-modal">
            <div class="sl-modal-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <h3>Hapus Data Mahasiswa?</h3>
            <p>Anda akan menghapus <strong id="slDeleteName">-</strong> secara permanen. Seluruh profil, tugas akhir, dan riwayat bimbingan mahasiswa ini akan ikut terhapus.</p>
            <form method="POST" action="<?= BASE_URL ?>/admin/students/delete" id="slDeleteForm">
                <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                <input type="hidden" name="id" id="slDeleteId" value="">
                <div class="sl-modal-actions">
                    <button type="button" class="sl-btn sl-btn-cancel" id="slCancelDelete">Batal</button>
                    <button type="submit" class="sl-btn sl-btn-danger"><i class="bi bi-trash-fill"></i> Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/admin.js"></script>
    <script>
    (function() {
        'use strict';

        // ===== LIVE SEARCH + MULTI-GROUP FILTER =====
        const searchInput = document.getElementById('slSearch');
        const chips = document.querySelectorAll('.sl-filter-chip');
        const rows = document.querySelectorAll('.sl-row');
        const noResult = document.getElementById('slNoResult');

        const activeFilters = {
            status: 'all',
            degree: 'all'
        };

        const applyFilter = () => {
            const q = (searchInput?.value || '').toLowerCase().trim();
            let visible = 0;

            rows.forEach(row => {
                const matchText = !q || (row.dataset.search || '').includes(q);
                const matchStatus = activeFilters.status === 'all' || row.dataset.status === activeFilters.status;
                const matchDegree = activeFilters.degree === 'all' || row.dataset.degree === activeFilters.degree;
                const show = matchText && matchStatus && matchDegree;
                row.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            if (noResult) noResult.style.display = visible === 0 ? 'block' : 'none';
        };

        searchInput?.addEventListener('input', applyFilter);

        chips.forEach(chip => {
            chip.addEventListener('click', () => {
                const group = chip.dataset.group;
                // Hanya nonaktifkan chip dalam grup yang sama
                document.querySelectorAll(`.sl-filter-chip[data-group="${group}"]`).forEach(c => c.classList.remove('is-on'));
                chip.classList.add('is-on');
                activeFilters[group] = chip.dataset.filter;
                applyFilter();
            });
        });

        // ===== DELETE MODAL =====
        const modal = document.getElementById('slDeleteModal');
        const modalName = document.getElementById('slDeleteName');
        const modalId = document.getElementById('slDeleteId');
        const cancelBtn = document.getElementById('slCancelDelete');

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
        document.querySelectorAll('.sl-view .alert-dismissible .btn-close').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const alert = e.target.closest('.alert');
                alert.style.transition = 'all 0.3s';
                alert.style.opacity = '0';
                alert.style.transform = 'translateX(30px)';
                setTimeout(() => alert.remove(), 300);
            });
        });

        document.querySelectorAll('.sl-view .alert-success').forEach(alert => {
            setTimeout(() => {
                if (alert.parentElement) {
                    alert.style.transition = 'all 0.3s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }
            }, 5000);
        });

        console.log('%c✓ Students List Loaded', 'color: #3b82f6; font-weight: bold;');
    })();
    </script>
</body>
</html>