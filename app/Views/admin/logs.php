<?php
declare(strict_types=1);
$flashError = $_SESSION['flash_error'] ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Log - <?= e(APP_NAME) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">
    <style>
        .log-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
        .log-title { font-size: 1.5rem; font-weight: 800; color: var(--gray-900); display: flex; align-items: center; gap: 0.5rem; }
        .log-filters { background: white; padding: 1rem; border-radius: var(--radius-lg); border: 1px solid var(--gray-200); display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1rem; align-items: flex-end; }
        .log-filter-group { display: flex; flex-direction: column; gap: 0.25rem; }
        .log-filter-group label { font-size: 0.75rem; font-weight: 600; color: var(--gray-600); }
        .log-filter-group input, .log-filter-group select { padding: 0.5rem; border: 1px solid var(--gray-300); border-radius: var(--radius-md); font-size: 0.85rem; }
        .log-btn { padding: 0.5rem 1rem; border-radius: var(--radius-md); border: none; font-weight: 600; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem; text-decoration: none; }
        .log-btn-primary { background: var(--primary-600); color: white; }
        .log-btn-primary:hover { background: var(--primary-700); }
        .log-btn-outline { background: white; color: var(--gray-700); border: 1px solid var(--gray-300); }
        .log-btn-outline:hover { background: var(--gray-50); }
        .log-table { width: 100%; border-collapse: collapse; background: white; border-radius: var(--radius-lg); overflow: hidden; border: 1px solid var(--gray-200); }
        .log-table th, .log-table td { padding: 0.85rem 1rem; text-align: left; border-bottom: 1px solid var(--gray-100); font-size: 0.85rem; }
        .log-table th { background: var(--gray-50); font-weight: 600; color: var(--gray-700); text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; }
        .log-table tr:hover td { background: #f9fafb; }
        .log-badge { padding: 0.2rem 0.6rem; border-radius: var(--radius-full); font-size: 0.7rem; font-weight: 600; background: var(--gray-100); color: var(--gray-700); }
        .log-pagination { display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; padding: 0 0.5rem; font-size: 0.85rem; color: var(--gray-600); }
        .log-page-btn { padding: 0.4rem 0.8rem; border: 1px solid var(--gray-300); background: white; border-radius: var(--radius-md); text-decoration: none; color: var(--gray-700); font-weight: 600; }
        .log-page-btn:hover { background: var(--gray-50); }
        .log-page-btn.disabled { opacity: 0.5; pointer-events: none; }
    </style>
</head>
<body>
    <?php require dirname(__DIR__) . '/partials/navbar.php'; ?>
    <div class="container" style="padding: 2rem 0 4rem;">
        <div class="log-header">
            <h1 class="log-title"><i class="bi bi-shield-lock-fill" style="color: var(--primary-600);"></i> Audit Log Aktivitas</h1>
            <div style="display: flex; gap: 0.5rem;">
                <a href="<?= BASE_URL ?>/admin/logs/export?<?= http_build_query($_GET) ?>" class="log-btn log-btn-outline">
                    <i class="bi bi-download"></i> Export CSV
                </a>
                <a href="<?= BASE_URL ?>/admin/dashboard" class="log-btn log-btn-outline">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <form method="GET" action="<?= BASE_URL ?>/admin/logs" class="log-filters">
            <div class="log-filter-group" style="flex: 1; min-width: 200px;">
                <label>Cari User / Deskripsi / IP</label>
                <input type="text" name="search" value="<?= e($_GET['search'] ?? '') ?>" placeholder="Ketik untuk mencari...">
            </div>
            <div class="log-filter-group">
                <label>Jenis Aksi</label>
                <select name="action">
                    <option value="">Semua Aksi</option>
                    <?php foreach ($actions as $act): ?>
                        <option value="<?= e($act) ?>" <?= ($_GET['action'] ?? '') === $act ? 'selected' : '' ?>><?= e(ucfirst(str_replace('_', ' ', $act))) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="log-filter-group">
                <label>Dari Tanggal</label>
                <input type="date" name="date_from" value="<?= e($_GET['date_from'] ?? '') ?>">
            </div>
            <div class="log-filter-group">
                <label>Sampai Tanggal</label>
                <input type="date" name="date_to" value="<?= e($_GET['date_to'] ?? '') ?>">
            </div>
            <button type="submit" class="log-btn log-btn-primary" style="height: 38px;">
                <i class="bi bi-funnel"></i> Filter
            </button>
            <?php if (!empty($_GET)): ?>
                <a href="<?= BASE_URL ?>/admin/logs" class="log-btn log-btn-outline" style="height: 36px;">Reset</a>
            <?php endif; ?>
        </form>

        <table class="log-table">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>User</th>
                    <th>Role</th>
                    <th>Aksi</th>
                    <th>Deskripsi</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="6" style="text-align: center; padding: 2rem; color: var(--gray-500);">Tidak ada data log yang sesuai dengan filter.</td></tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): 
                        $roleClass = match($log['user_role'] ?? '') {
                            'admin' => 'background: #fee2e2; color: #991b1b;',
                            'lecturer' => 'background: #d1fae5; color: #065f46;',
                            'student' => 'background: #dbeafe; color: #1e40af;',
                            default => 'background: var(--gray-100); color: var(--gray-700);'
                        };
                    ?>
                    <tr>
                        <td style="white-space: nowrap; font-family: 'JetBrains Mono', monospace; font-size: 0.8rem;"><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
                        <td><strong><?= e($log['user_name'] ?? 'System') ?></strong></td>
                        <td><span class="log-badge" style="<?= $roleClass ?>"><?= e(ucfirst($log['user_role'] ?? 'N/A')) ?></span></td>
                        <td><code style="background: var(--gray-100); padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.75rem;"><?= e(str_replace('_', ' ', $log['action'])) ?></code></td>
                        <td style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= e($log['description']) ?>"><?= e($log['description'] ?? '-') ?></td>
                        <td style="font-family: 'JetBrains Mono', monospace; font-size: 0.8rem;"><?= e($log['ip_address'] ?? '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="log-pagination">
            <span>Menampilkan <?= count($logs) ?> dari <?= $total ?> total log</span>
            <div style="display: flex; gap: 0.5rem;">
                <?php 
                $currentPage = $page;
                $totalPages = (int)ceil($total / 20);
                $queryParams = $_GET;
                $buildUrl = fn($p) => BASE_URL . '/admin/logs?' . http_build_query(array_merge($queryParams, ['page' => $p]));
                ?>
                <a href="<?= $buildUrl($currentPage - 1) ?>" class="log-page-btn <?= $currentPage <= 1 ? 'disabled' : '' ?>">← Prev</a>
                <span style="padding: 0.4rem 0.8rem; background: var(--gray-100); border-radius: var(--radius-md); font-weight: 600;">Halaman <?= $currentPage ?> / <?= max(1, $totalPages) ?></span>
                <a href="<?= $buildUrl($currentPage + 1) ?>" class="log-page-btn <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">Next →</a>
            </div>
        </div>
    </div>
</body>
</html>