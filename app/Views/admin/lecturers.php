<?php
declare(strict_types=1);
$flashError = $_SESSION['flash_error'] ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

$lecturers = $lecturers ?? [];
$total = count($lecturers);
$active = count(array_filter($lecturers, fn($l) => (int)$l['is_active'] === 1));
$inactive = $total - $active;
$departments = count(array_unique(array_filter(array_column($lecturers, 'department'))));

// Palet gradient untuk avatar
$palette = [
    'linear-gradient(135deg,#667eea,#764ba2)',
    'linear-gradient(135deg,#10b981,#059669)',
    'linear-gradient(135deg,#f59e0b,#d97706)',
    'linear-gradient(135deg,#ec4899,#be185d)',
    'linear-gradient(135deg,#06b6d4,#0891b2)',
    'linear-gradient(135deg,#8b5cf6,#6d28d9)',
];

// Helper inisial nama
$initials = function(string $name): string {
    $parts = array_filter(explode(' ', trim($name)));
    $first = strtoupper(substr($parts[0] ?? '-', 0, 1));
    $second = '';
    if (count($parts) > 1) {
        $last = end($parts);
        // Lewati gelar singkat di akhir jika ada koma
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
    <title>Data Dosen - <?= e(APP_NAME) ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">
    
    <style>
        /* =========================================================
           LECTURERS LIST ULTRA PREMIUM - SCOPED STYLES
           Namespace: .ll- | Halaman DAFTAR Dosen (bukan form)
           ========================================================= */
        .ll-view { animation: llFade 0.4s ease; }
        @keyframes llFade { from { opacity: 0; } to { opacity: 1; } }

        /* --- Page Header --- */
        .ll-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .ll-header-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.875rem;
            font-weight: 800;
            color: var(--gray-900);
            margin: 0 0 0.35rem 0;
        }

        .ll-header-icon {
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

        .ll-header-sub {
            color: var(--gray-500);
            font-size: 0.95rem;
            margin: 0 0 0 calc(48px + 0.75rem);
        }

        .ll-add-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border-radius: var(--radius-lg);
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            box-shadow: 0 10px 20px -8px rgba(16,185,129,0.5);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .ll-add-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 25px -8px rgba(16,185,129,0.6);
        }

        /* --- Stats Strip --- */
        .ll-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.75rem;
        }

        .ll-stat {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-xl);
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s;
            animation: llUp 0.5s ease backwards;
        }

        .ll-stat:nth-child(1) { animation-delay: 0.05s; }
        .ll-stat:nth-child(2) { animation-delay: 0.12s; }
        .ll-stat:nth-child(3) { animation-delay: 0.19s; }
        .ll-stat:nth-child(4) { animation-delay: 0.26s; }

        @keyframes llUp {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .ll-stat:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .ll-stat-icon {
            width: 46px; height: 46px;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: white;
            flex-shrink: 0;
        }

        .ll-stat-icon.i-total { background: linear-gradient(135deg,#667eea,#764ba2); }
        .ll-stat-icon.i-active { background: linear-gradient(135deg,#10b981,#059669); }
        .ll-stat-icon.i-inactive { background: linear-gradient(135deg,#94a3b8,#64748b); }
        .ll-stat-icon.i-dept { background: linear-gradient(135deg,#06b6d4,#0891b2); }

        .ll-stat-num {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--gray-900);
            line-height: 1.1;
            font-variant-numeric: tabular-nums;
        }

        .ll-stat-label {
            font-size: 0.75rem;
            color: var(--gray-500);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        /* --- Toolbar (Search + Filter) --- */
        .ll-card {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .ll-toolbar {
            padding: 1.25rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            border-bottom: 1px solid var(--gray-200);
            background: linear-gradient(to right, var(--gray-50), white);
        }

        .ll-search-wrap {
            position: relative;
            flex: 1;
            min-width: 240px;
            max-width: 420px;
        }

        .ll-search-wrap i {
            position: absolute;
            left: 1rem; top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            pointer-events: none;
            transition: color 0.3s;
        }

        .ll-search {
            width: 100%;
            padding: 0.7rem 1rem 0.7rem 2.75rem;
            border: 1.5px solid var(--gray-300);
            border-radius: var(--radius-full);
            font-size: 0.9rem;
            font-family: inherit;
            transition: all 0.3s;
            background: white;
        }

        .ll-search:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 4px rgba(16,185,129,0.12);
        }

        .ll-search-wrap:focus-within i { color: #10b981; }

        .ll-filters {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .ll-filter-chip {
            padding: 0.5rem 1rem;
            border-radius: var(--radius-full);
            border: 1.5px solid var(--gray-200);
            background: white;
            color: var(--gray-600);
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s;
            font-family: inherit;
        }

        .ll-filter-chip:hover { border-color: var(--gray-400); }

        .ll-filter-chip.is-on {
            background: linear-gradient(135deg,#10b981,#059669);
            border-color: transparent;
            color: white;
            box-shadow: 0 6px 12px -4px rgba(16,185,129,0.5);
        }

        /* --- Table --- */
        .ll-table-wrap { overflow-x: auto; }

        .ll-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }

        .ll-table thead th {
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

        .ll-table tbody tr {
            border-bottom: 1px solid var(--gray-100);
            transition: all 0.25s;
            animation: llUp 0.45s ease backwards;
        }

        .ll-table tbody tr:last-child { border-bottom: none; }

        .ll-table tbody tr:hover {
            background: #f0fdf4;
        }

        .ll-table td {
            padding: 1rem 1.5rem;
            vertical-align: middle;
        }

        /* Person cell */
        .ll-person {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            min-width: 220px;
        }

        .ll-avatar {
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

        tr:hover .ll-avatar { transform: scale(1.08) rotate(-4deg); }

        .ll-name {
            font-weight: 700;
            color: var(--gray-900);
            font-size: 0.925rem;
            line-height: 1.3;
        }

        .ll-email {
            color: var(--gray-500);
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .ll-mono {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--gray-600);
            background: var(--gray-100);
            padding: 0.25rem 0.6rem;
            border-radius: var(--radius-md);
            display: inline-block;
        }

        .ll-dept { font-weight: 600; color: var(--gray-800); font-size: 0.875rem; }
        .ll-degree { color: var(--gray-500); font-size: 0.8rem; }

        /* Expertise tags */
        .ll-tags { display: flex; flex-wrap: wrap; gap: 0.35rem; max-width: 260px; }

        .ll-tag {
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.25rem 0.6rem;
            background: #ede9fe;
            color: #6d28d9;
            border-radius: var(--radius-full);
            white-space: nowrap;
        }

        .ll-tag.more { background: var(--gray-100); color: var(--gray-600); }

        /* Status pill */
        .ll-pill {
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

        .ll-pill::before {
            content: '';
            width: 7px; height: 7px;
            border-radius: 50%;
            background: currentColor;
        }

        .ll-pill-on { background: #d1fae5; color: #065f46; }
        .ll-pill-on::before { animation: llPulse 1.6s infinite; }
        .ll-pill-off { background: var(--gray-200); color: var(--gray-600); }

        @keyframes llPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.8); }
        }

        /* Actions */
        .ll-actions { display: flex; gap: 0.5rem; }

        .ll-icon-btn {
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
        }

        .ll-icon-btn:hover { transform: translateY(-2px); }

        .ll-icon-edit:hover {
            background: #fef3c7;
            border-color: #fcd34d;
            color: #b45309;
        }

        .ll-icon-del:hover {
            background: #fee2e2;
            border-color: #fca5a5;
            color: #b91c1c;
        }

        /* --- Empty & No Result --- */
        .ll-empty {
            text-align: center;
            padding: 4rem 2rem;
        }

        .ll-empty-icon {
            width: 90px; height: 90px;
            margin: 0 auto 1.25rem;
            border-radius: 50%;
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: #059669;
            animation: llFloat 3s ease-in-out infinite;
        }

        @keyframes llFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        .ll-empty h3 { font-size: 1.25rem; color: var(--gray-800); margin-bottom: 0.5rem; }
        .ll-empty p { color: var(--gray-500); margin-bottom: 1.5rem; }

        .ll-noresult {
            display: none;
            text-align: center;
            padding: 3rem 2rem;
            color: var(--gray-500);
        }

        .ll-noresult i { font-size: 2.5rem; opacity: 0.4; display: block; margin-bottom: 0.75rem; }

        /* --- Delete Modal --- */
        .ll-modal-overlay {
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

        .ll-modal-overlay.is-open { opacity: 1; visibility: visible; }

        .ll-modal {
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

        .ll-modal-overlay.is-open .ll-modal { transform: scale(1) translateY(0); }

        .ll-modal-icon {
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

        .ll-modal h3 { font-size: 1.3rem; color: var(--gray-900); margin-bottom: 0.6rem; }

        .ll-modal p { color: var(--gray-500); font-size: 0.9rem; line-height: 1.6; margin-bottom: 1.75rem; }
        .ll-modal p strong { color: var(--gray-800); }

        .ll-modal-actions { display: flex; gap: 0.75rem; }

        .ll-btn {
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

        .ll-btn-cancel { background: var(--gray-100); color: var(--gray-700); }
        .ll-btn-cancel:hover { background: var(--gray-200); }

        .ll-btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            box-shadow: 0 8px 16px -6px rgba(239,68,68,0.5);
        }

        .ll-btn-danger:hover { transform: translateY(-2px); box-shadow: 0 12px 20px -6px rgba(239,68,68,0.6); }

        @media (max-width: 768px) {
            .ll-header-sub { margin-left: 0; }
            .ll-toolbar { flex-direction: column; align-items: stretch; }
            .ll-search-wrap { max-width: none; }
        }
    </style>
</head>
<body>
    <?php require dirname(__DIR__) . '/partials/navbar.php'; ?>

    <div class="container ll-view">
        <!-- Page Header -->
        <div class="ll-header">
            <div>
                <h1 class="ll-header-title">
                    <span class="ll-header-icon"><i class="bi bi-person-workspace"></i></span>
                    Data Dosen
                </h1>
                <p class="ll-header-sub">Kelola dan pantau seluruh dosen pembimbing dalam sistem.</p>
            </div>
            <a href="<?= BASE_URL ?>/admin/lecturers/create" class="ll-add-btn">
                <i class="bi bi-plus-lg"></i> Tambah Dosen
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
        <div class="ll-stats">
            <div class="ll-stat">
                <div class="ll-stat-icon i-total"><i class="bi bi-people-fill"></i></div>
                <div>
                    <div class="ll-stat-num"><?= $total ?></div>
                    <div class="ll-stat-label">Total Dosen</div>
                </div>
            </div>
            <div class="ll-stat">
                <div class="ll-stat-icon i-active"><i class="bi bi-check-circle-fill"></i></div>
                <div>
                    <div class="ll-stat-num"><?= $active ?></div>
                    <div class="ll-stat-label">Aktif Mengajar</div>
                </div>
            </div>
            <div class="ll-stat">
                <div class="ll-stat-icon i-inactive"><i class="bi bi-pause-circle-fill"></i></div>
                <div>
                    <div class="ll-stat-num"><?= $inactive ?></div>
                    <div class="ll-stat-label">Nonaktif</div>
                </div>
            </div>
            <div class="ll-stat">
                <div class="ll-stat-icon i-dept"><i class="bi bi-diagram-3-fill"></i></div>
                <div>
                    <div class="ll-stat-num"><?= $departments ?></div>
                    <div class="ll-stat-label">Departemen</div>
                </div>
            </div>
        </div>

        <!-- Data Card -->
        <div class="ll-card">
            <!-- Toolbar -->
            <div class="ll-toolbar">
                <div class="ll-search-wrap">
                    <i class="bi bi-search"></i>
                    <input type="text" id="llSearch" class="ll-search" placeholder="Cari nama, email, atau NIDN dosen...">
                </div>
                <div class="ll-filters">
                    <button class="ll-filter-chip is-on" data-filter="all">Semua</button>
                    <button class="ll-filter-chip" data-filter="active">Aktif</button>
                    <button class="ll-filter-chip" data-filter="inactive">Nonaktif</button>
                </div>
            </div>

            <?php if ($total === 0): ?>
                <!-- Empty State -->
                <div class="ll-empty">
                    <div class="ll-empty-icon"><i class="bi bi-person-workspace"></i></div>
                    <h3>Belum Ada Data Dosen</h3>
                    <p>Mulai tambahkan dosen pembimbing untuk mengelola bimbingan tugas akhir.</p>
                    <a href="<?= BASE_URL ?>/admin/lecturers/create" class="ll-add-btn">
                        <i class="bi bi-plus-lg"></i> Tambah Dosen Pertama
                    </a>
                </div>
            <?php else: ?>
                <div class="ll-table-wrap">
                    <table class="ll-table">
                        <thead>
                            <tr>
                                <th>Dosen</th>
                                <th>NIDN / NIP</th>
                                <th>Departemen & Gelar</th>
                                <th>Keahlian</th>
                                <th>Status</th>
                                <th style="text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="llTbody">
                            <?php foreach ($lecturers as $i => $l):
                                $bg = $palette[$i % count($palette)];
                                $isActive = (int)$l['is_active'] === 1;
                                $searchKey = strtolower($l['name'] . ' ' . $l['email'] . ' ' . ($l['lecturer_number'] ?? '') . ' ' . ($l['department'] ?? ''));
                                $tags = array_values(array_filter(array_map('trim', explode(',', (string)($l['expertise'] ?? '')))));
                                $shownTags = array_slice($tags, 0, 2);
                                $moreTags = count($tags) - count($shownTags);
                            ?>
                            <tr class="ll-row" data-search="<?= e($searchKey) ?>" data-status="<?= $isActive ? 'active' : 'inactive' ?>" style="animation-delay: <?= min($i * 0.04, 0.4) ?>s">
                                <td>
                                    <div class="ll-person">
                                        <div class="ll-avatar" style="background: <?= $bg ?>;"><?= e($initials($l['name'])) ?></div>
                                        <div>
                                            <div class="ll-name"><?= e($l['name']) ?></div>
                                            <div class="ll-email"><i class="bi bi-envelope"></i> <?= e($l['email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($l['lecturer_number'])): ?>
                                        <span class="ll-mono"><?= e($l['lecturer_number']) ?></span>
                                    <?php else: ?>
                                        <span style="color: var(--gray-400);">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="ll-dept"><?= e($l['department'] ?: '—') ?></div>
                                    <div class="ll-degree"><?= e($l['academic_degree'] ?: 'Tanpa gelar') ?></div>
                                </td>
                                <td>
                                    <?php if (empty($tags)): ?>
                                        <span style="color: var(--gray-400); font-size: 0.85rem;">—</span>
                                    <?php else: ?>
                                        <div class="ll-tags">
                                            <?php foreach ($shownTags as $t): ?>
                                                <span class="ll-tag"><?= e($t) ?></span>
                                            <?php endforeach; ?>
                                            <?php if ($moreTags > 0): ?>
                                                <span class="ll-tag more">+<?= $moreTags ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="ll-pill <?= $isActive ? 'll-pill-on' : 'll-pill-off' ?>">
                                        <?= $isActive ? 'Aktif' : 'Nonaktif' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="ll-actions" style="justify-content: center;">
                                        <a href="<?= BASE_URL ?>/admin/lecturers/edit?id=<?= $l['id'] ?>" class="ll-icon-btn ll-icon-edit" title="Edit <?= e($l['name']) ?>">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <button type="button" class="ll-icon-btn ll-icon-del" title="Hapus <?= e($l['name']) ?>"
                                                data-delete-id="<?= $l['id'] ?>" data-delete-name="<?= e($l['name']) ?>">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="ll-noresult" id="llNoResult">
                        <i class="bi bi-search"></i>
                        Tidak ada dosen yang cocok dengan pencarian atau filter Anda.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="ll-modal-overlay" id="llDeleteModal">
        <div class="ll-modal">
            <div class="ll-modal-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <h3>Hapus Data Dosen?</h3>
            <p>Anda akan menghapus <strong id="llDeleteName">-</strong> secara permanen. Seluruh profil dan riwayat terkait dosen ini akan ikut terhapus.</p>
            <form method="POST" action="<?= BASE_URL ?>/admin/lecturers/delete" id="llDeleteForm">
                <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                <input type="hidden" name="id" id="llDeleteId" value="">
                <div class="ll-modal-actions">
                    <button type="button" class="ll-btn ll-btn-cancel" id="llCancelDelete">Batal</button>
                    <button type="submit" class="ll-btn ll-btn-danger"><i class="bi bi-trash-fill"></i> Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/admin.js"></script>
    <script>
    (function() {
        'use strict';

        // ===== LIVE SEARCH + STATUS FILTER =====
        const searchInput = document.getElementById('llSearch');
        const chips = document.querySelectorAll('.ll-filter-chip');
        const rows = document.querySelectorAll('.ll-row');
        const noResult = document.getElementById('llNoResult');
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
        const modal = document.getElementById('llDeleteModal');
        const modalName = document.getElementById('llDeleteName');
        const modalId = document.getElementById('llDeleteId');
        const cancelBtn = document.getElementById('llCancelDelete');

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
        document.querySelectorAll('.ll-view .alert-dismissible .btn-close').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const alert = e.target.closest('.alert');
                alert.style.transition = 'all 0.3s';
                alert.style.opacity = '0';
                alert.style.transform = 'translateX(30px)';
                setTimeout(() => alert.remove(), 300);
            });
        });

        document.querySelectorAll('.ll-view .alert-success').forEach(alert => {
            setTimeout(() => {
                if (alert.parentElement) {
                    alert.style.transition = 'all 0.3s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }
            }, 5000);
        });

        console.log('%c✓ Lecturers List Loaded', 'color: #10b981; font-weight: bold;');
    })();
    </script>
</body>
</html>