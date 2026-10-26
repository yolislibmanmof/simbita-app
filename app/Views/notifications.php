<?php
declare(strict_types=1);
$flashError = $_SESSION['flash_error'] ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

// Normalisasi key agar konsisten (getAll mengembalikan 'data', kita ubah jadi 'items')
$data = $data ?? [];
$items = $data['data'] ?? [];
$totalPages = $data['total_pages'] ?? 1;
$page = $data['page'] ?? 1;
$stats = $stats ?? ['total' => 0, 'unread_count' => 0];
$filter = $_GET['filter'] ?? 'all';
$page = $data['page'] ?? 1;

// Helper relative time
$timeAgo = function(string $date): string {
    $diff = time() - strtotime($date);
    if ($diff < 60) return 'Baru saja';
    if ($diff < 3600) return floor($diff / 60) . ' menit lalu';
    if ($diff < 86400) return floor($diff / 3600) . ' jam lalu';
    if ($diff < 604800) return floor($diff / 86400) . ' hari lalu';
    return date('d M Y', strtotime($date));
};

// Helper icon per type
$typeMeta = function(string $type): array {
    return match($type) {
        'review' => ['icon' => 'pencil-square-fill', 'class' => 'nt-type-review', 'label' => 'Review'],
        'deadline' => ['icon' => 'alarm-fill', 'class' => 'nt-type-deadline', 'label' => 'Deadline'],
        'approval' => ['icon' => 'check-circle-fill', 'class' => 'nt-type-approval', 'label' => 'Approval'],
        'comment' => ['icon' => 'chat-left-text-fill', 'class' => 'nt-type-comment', 'label' => 'Komentar'],
        default => ['icon' => 'gear-fill', 'class' => 'nt-type-system', 'label' => 'Sistem'],
    };
};
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi - <?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">
    <style>
        .nt-view { --nt-primary: #6366f1; animation: ntFade 0.4s ease; }
        @keyframes ntFade { from { opacity: 0; } to { opacity: 1; } }

        .nt-header {
            display: flex; justify-content: space-between; align-items: flex-start;
            gap: 1rem; flex-wrap: wrap; margin-bottom: 2rem;
        }
        .nt-header-title {
            display: flex; align-items: center; gap: 0.75rem;
            font-size: 1.875rem; font-weight: 800; color: var(--gray-900); margin: 0;
        }
        .nt-header-icon {
            width: 56px; height: 56px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: var(--radius-xl);
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 1.5rem;
            box-shadow: 0 10px 25px -8px rgba(99,102,241,0.5);
        }
        .nt-header-sub { color: var(--gray-500); font-size: 0.95rem; margin-top: 0.35rem; }

        .nt-back-btn {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.625rem 1.25rem; background: white;
            color: var(--gray-700); border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-lg); text-decoration: none;
            font-weight: 600; font-size: 0.875rem; transition: all 0.3s;
        }
        .nt-back-btn:hover { background: var(--gray-50); border-color: var(--gray-400); transform: translateX(-3px); }

        .nt-stats {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem; margin-bottom: 1.75rem;
        }
        .nt-stat {
            background: white; border: 1px solid var(--gray-200);
            border-radius: var(--radius-xl); padding: 1.25rem;
            display: flex; align-items: center; gap: 0.85rem;
            transition: all 0.3s;
        }
        .nt-stat:hover { transform: translateY(-3px); box-shadow: var(--shadow-lg); }
        .nt-stat-icon {
            width: 42px; height: 42px; border-radius: var(--radius-lg);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; color: white; flex-shrink: 0;
        }
        .nt-stat-icon.bg-total { background: linear-gradient(135deg,#6366f1,#4f46e5); }
        .nt-stat-icon.bg-unread { background: linear-gradient(135deg,#ef4444,#dc2626); }
        .nt-stat-icon.bg-read { background: linear-gradient(135deg,#10b981,#059669); }
        .nt-stat-num { font-size: 1.6rem; font-weight: 800; color: var(--gray-900); line-height: 1; }
        .nt-stat-label { font-size: 0.72rem; color: var(--gray-500); font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }

        .nt-card {
            background: white; border: 1px solid var(--gray-200);
            border-radius: var(--radius-2xl); box-shadow: var(--shadow-sm); overflow: hidden;
        }
        .nt-toolbar {
            padding: 1rem 1.5rem; display: flex;
            justify-content: space-between; align-items: center;
            gap: 1rem; flex-wrap: wrap; border-bottom: 1px solid var(--gray-200);
            background: linear-gradient(to right, var(--gray-50), white);
        }
        .nt-filters { display: flex; gap: 0.4rem; flex-wrap: wrap; }
        .nt-chip {
            padding: 0.45rem 0.9rem; border-radius: var(--radius-full);
            border: 1.5px solid var(--gray-200); background: white;
            color: var(--gray-600); font-size: 0.75rem; font-weight: 600;
            cursor: pointer; transition: all 0.25s; text-decoration: none;
            display: inline-flex; align-items: center; gap: 0.35rem;
        }
        .nt-chip:hover { border-color: var(--gray-400); }
        .nt-chip.is-on {
            background: linear-gradient(135deg,#6366f1,#8b5cf6);
            border-color: transparent; color: white;
            box-shadow: 0 6px 12px -4px rgba(99,102,241,0.5);
        }
        .nt-chip-count {
            background: rgba(255,255,255,0.25); padding: 0.1rem 0.45rem;
            border-radius: var(--radius-full); font-size: 0.7rem; font-weight: 700;
        }
        .nt-chip:not(.is-on) .nt-chip-count { background: var(--gray-100); color: var(--gray-600); }

        .nt-mark-btn {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.5rem 1rem; background: white;
            color: var(--nt-primary); border: 1.5px solid var(--nt-primary);
            border-radius: var(--radius-md); font-weight: 600;
            font-size: 0.82rem; cursor: pointer; transition: all 0.25s;
            font-family: inherit;
        }
        .nt-mark-btn:hover:not(:disabled) { background: var(--nt-primary); color: white; }
        .nt-mark-btn:disabled { opacity: 0.5; cursor: not-allowed; }

        .nt-list { padding: 1rem 1.5rem; }
        .nt-item {
            display: flex; gap: 1rem; padding: 1rem;
            border: 1.5px solid var(--gray-200); border-radius: var(--radius-lg);
            margin-bottom: 0.75rem; transition: all 0.25s;
            cursor: pointer; background: white;
        }
        .nt-item:hover {
            border-color: var(--nt-primary);
            box-shadow: 0 10px 25px -8px rgba(99,102,241,0.15);
            transform: translateX(3px);
        }
        .nt-item.is-unread { background: #eef2ff; border-color: #c7d2fe; }
        .nt-item-icon {
            width: 44px; height: 44px; border-radius: var(--radius-md);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.15rem; color: white; flex-shrink: 0;
            box-shadow: 0 4px 8px -2px rgba(0,0,0,0.1);
        }
        .nt-type-review { background: linear-gradient(135deg,#3b82f6,#1e40af); }
        .nt-type-deadline { background: linear-gradient(135deg,#f59e0b,#d97706); }
        .nt-type-approval { background: linear-gradient(135deg,#10b981,#059669); }
        .nt-type-comment { background: linear-gradient(135deg,#06b6d4,#0891b2); }
        .nt-type-system { background: linear-gradient(135deg,#8b5cf6,#6d28d9); }
        .nt-item-content { flex: 1; min-width: 0; }
        .nt-item-top {
            display: flex; justify-content: space-between;
            gap: 1rem; margin-bottom: 0.35rem; align-items: flex-start;
        }
        .nt-item-title {
            font-size: 0.95rem; font-weight: 700; color: var(--gray-900);
            line-height: 1.3; flex: 1; min-width: 0;
        }
        .nt-item.is-unread .nt-item-title::before {
            content: ''; display: inline-block;
            width: 8px; height: 8px; border-radius: 50%;
            background: #ef4444; margin-right: 0.5rem;
            animation: ntPulse 2s infinite;
        }
        @keyframes ntPulse { 0%,100%{opacity:1} 50%{opacity:0.5} }
        .nt-item-time {
            font-size: 0.75rem; color: var(--gray-500);
            white-space: nowrap; flex-shrink: 0;
            font-family: 'JetBrains Mono', monospace; font-weight: 600;
        }
        .nt-item-msg {
            font-size: 0.85rem; color: var(--gray-600);
            line-height: 1.5; margin: 0;
        }
        .nt-item-meta {
            display: flex; align-items: center; gap: 0.5rem;
            margin-top: 0.5rem; font-size: 0.75rem;
        }
        .nt-type-badge {
            padding: 0.15rem 0.5rem; border-radius: var(--radius-full);
            font-weight: 600; text-transform: uppercase;
            letter-spacing: 0.04em; font-size: 0.68rem;
        }
        .nt-type-badge.review { background: #dbeafe; color: #1e40af; }
        .nt-type-badge.deadline { background: #fef3c7; color: #92400e; }
        .nt-type-badge.approval { background: #d1fae5; color: #065f46; }
        .nt-type-badge.comment { background: #cffafe; color: #155e75; }
        .nt-type-badge.system { background: #ede9fe; color: #6d28d9; }

        .nt-pagination {
            display: flex; justify-content: center;
            gap: 0.4rem; padding: 1.5rem; border-top: 1px solid var(--gray-200);
            background: var(--gray-50);
        }
        .nt-page-btn {
            padding: 0.5rem 0.9rem; border: 1.5px solid var(--gray-200);
            background: white; color: var(--gray-700);
            border-radius: var(--radius-md); text-decoration: none;
            font-weight: 600; font-size: 0.85rem; transition: all 0.2s;
        }
        .nt-page-btn:hover { border-color: var(--nt-primary); color: var(--nt-primary); }
        .nt-page-btn.is-on { background: var(--nt-primary); color: white; border-color: var(--nt-primary); }
        .nt-page-btn.is-disabled { opacity: 0.4; pointer-events: none; }

        .nt-empty { text-align: center; padding: 4rem 2rem; color: var(--gray-500); }
        .nt-empty-icon {
            width: 90px; height: 90px; margin: 0 auto 1.25rem;
            border-radius: 50%; background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
            display: flex; align-items: center; justify-content: center;
            font-size: 2.5rem; color: #6366f1;
        }
        .nt-empty h3 { font-size: 1.25rem; color: var(--gray-800); margin-bottom: 0.5rem; }
        .nt-empty p { color: var(--gray-500); }
    </style>
</head>
<body>
    <?php require __DIR__ . '/partials/navbar.php'; ?>

    <div class="container nt-view" style="padding: 2rem 0 4rem;">
        <!-- Header -->
        <div class="nt-header">
            <div>
                <h1 class="nt-header-title">
                    <span class="nt-header-icon"><i class="bi bi-bell-fill"></i></span>
                    Pusat Notifikasi
                </h1>
                <p class="nt-header-sub">Kelola semua pemberitahuan dari sistem, dosen, dan rekan bimbingan.</p>
            </div>
            <a href="javascript:history.back()" class="nt-back-btn">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <!-- Flash Messages -->
        <?php if ($flashError): ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill"></i> <?= e($flashError) ?></div>
        <?php endif; ?>
        <?php if ($flashSuccess): ?>
            <div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> <?= e($flashSuccess) ?></div>
        <?php endif; ?>

        <!-- Stats Strip -->
        <div class="nt-stats">
            <div class="nt-stat">
                <div class="nt-stat-icon bg-total"><i class="bi bi-bell-fill"></i></div>
                <div>
                    <div class="nt-stat-num"><?= (int)($stats['total'] ?? 0) ?></div>
                    <div class="nt-stat-label">Total</div>
                </div>
            </div>
            <div class="nt-stat">
                <div class="nt-stat-icon bg-unread"><i class="bi bi-envelope-fill"></i></div>
                <div>
                    <div class="nt-stat-num"><?= (int)($stats['unread_count'] ?? 0) ?></div>
                    <div class="nt-stat-label">Belum Dibaca</div>
                </div>
            </div>
            <div class="nt-stat">
                <div class="nt-stat-icon bg-read"><i class="bi bi-envelope-open-fill"></i></div>
                <div>
                    <div class="nt-stat-num"><?= (int)($stats['total'] ?? 0) - (int)($stats['unread_count'] ?? 0) ?></div>
                    <div class="nt-stat-label">Sudah Dibaca</div>
                </div>
            </div>
        </div>

        <!-- Main Card -->
        <div class="nt-card">
            <!-- Toolbar -->
            <div class="nt-toolbar">
                <div class="nt-filters">
                    <?php
                    $filterOptions = [
                        'all' => ['label' => 'Semua', 'count' => (int)($stats['total'] ?? 0)],
                        'unread' => ['label' => 'Belum Dibaca', 'count' => (int)($stats['unread_count'] ?? 0)],
                        'review' => ['label' => 'Review', 'count' => (int)($stats['review_count'] ?? 0)],
                        'deadline' => ['label' => 'Deadline', 'count' => (int)($stats['deadline_count'] ?? 0)],
                        'approval' => ['label' => 'Approval', 'count' => (int)($stats['approval_count'] ?? 0)],
                        'comment' => ['label' => 'Komentar', 'count' => (int)($stats['comment_count'] ?? 0)],
                        'system' => ['label' => 'Sistem', 'count' => (int)($stats['system_count'] ?? 0)],
                    ];
                    foreach ($filterOptions as $key => $opt):
                    ?>
                        <a href="?filter=<?= $key ?>" class="nt-chip <?= $filter === $key ? 'is-on' : '' ?>">
                            <?= e($opt['label']) ?>
                            <span class="nt-chip-count"><?= $opt['count'] ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
                <?php if ((int)($stats['unread_count'] ?? 0) > 0): ?>
                    <button type="button" class="nt-mark-btn" id="ntMarkAllBtn">
                        <i class="bi bi-check2-all"></i> Tandai Semua Dibaca
                    </button>
                <?php endif; ?>
            </div>

            <!-- List -->
            <?php if (empty($items)): ?>
                <div class="nt-empty">
                    <div class="nt-empty-icon"><i class="bi bi-bell-slash"></i></div>
                    <h3>Tidak Ada Notifikasi</h3>
                    <p>
                        <?php if ($filter === 'all'): ?>
                            Anda belum memiliki notifikasi apapun.
                        <?php elseif ($filter === 'unread'): ?>
                            Semua notifikasi sudah dibaca! 🎉
                        <?php else: ?>
                            Tidak ada notifikasi dengan filter ini.
                        <?php endif; ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="nt-list">
                    <?php foreach ($items as $notif): 
                        $meta = $typeMeta($notif['type'] ?? 'system');
                        $isUnread = empty($notif['is_read']);
                    ?>
                        <div class="nt-item <?= $isUnread ? 'is-unread' : '' ?>"
                             data-notif-id="<?= $notif['id'] ?>"
                             data-link="<?= e(BASE_URL . ($notif['link'] ?? '')) ?>">
                            <div class="nt-item-icon <?= $meta['class'] ?>">
                                <i class="bi bi-<?= $meta['icon'] ?>"></i>
                            </div>
                            <div class="nt-item-content">
                                <div class="nt-item-top">
                                    <div class="nt-item-title"><?= e($notif['title']) ?></div>
                                    <div class="nt-item-time"><?= e($timeAgo($notif['created_at'])) ?></div>
                                </div>
                                <p class="nt-item-msg"><?= nl2br(e($notif['message'])) ?></p>
                                <div class="nt-item-meta">
                                    <span class="nt-type-badge <?= e($notif['type'] ?? 'system') ?>">
                                        <i class="bi bi-<?= $meta['icon'] ?>"></i>
                                        <?= e($meta['label']) ?>
                                    </span>
                                    <span style="color: var(--gray-400);">•</span>
                                    <span style="color: var(--gray-500);"><?= e(date('d M Y H:i', strtotime($notif['created_at']))) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="nt-pagination">
                        <?php
                        $buildUrl = fn($p) => '?filter=' . urlencode($filter) . '&page=' . $p;
                        ?>
                        <a href="<?= $buildUrl(max(1, $page - 1)) ?>" class="nt-page-btn <?= $page <= 1 ? 'is-disabled' : '' ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                        <?php for ($i = 1; $i <= $data['total_pages']; $i++): ?>
                            <a href="<?= $buildUrl($i) ?>" class="nt-page-btn <?= $i === $page ? 'is-on' : '' ?>"><?= $i ?></a>
                        <?php endfor; ?>
                        <a href="<?= $buildUrl(min($data['total_pages'], $page + 1)) ?>" class="nt-page-btn <?= $page >= $data['total_pages'] ? 'is-disabled' : '' ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
    (function() {
        'use strict';

        const CSRF = '<?= e(Csrf::token()) ?>';
        const BASE = '<?= BASE_URL ?>';

        // Click item → mark as read + navigate
        document.querySelectorAll('.nt-item').forEach(item => {
            item.addEventListener('click', async () => {
                const notifId = item.dataset.notifId;
                const link = item.dataset.link;

                if (item.classList.contains('is-unread')) {
                    const fd = new FormData();
                    fd.append('csrf_token', CSRF);
                    fd.append('notification_id', notifId);
                    try {
                        await fetch(`${BASE}/api/notifications/read`, { method: 'POST', body: fd });
                        item.classList.remove('is-unread');
                    } catch (e) {}
                }

                if (link) {
                    setTimeout(() => { window.location.href = link; }, 100);
                }
            });
        });

        // Mark all read
        const markAllBtn = document.getElementById('ntMarkAllBtn');
        if (markAllBtn) {
            markAllBtn.addEventListener('click', async () => {
                if (!confirm('Tandai SEMUA notifikasi sebagai sudah dibaca?')) return;
                markAllBtn.disabled = true;
                markAllBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Memproses...';
                const fd = new FormData();
                fd.append('csrf_token', CSRF);
                try {
                    const res = await fetch(`${BASE}/api/notifications/read-all`, { method: 'POST', body: fd });
                    const data = await res.json();
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Gagal: ' + (data.error || 'Unknown'));
                        markAllBtn.disabled = false;
                    }
                } catch (e) {
                    alert('Error jaringan');
                    markAllBtn.disabled = false;
                }
            });
        }

        console.log('%c✓ Notification Center Loaded', 'color: #6366f1; font-weight: bold;');
    })();
    </script>
</body>
</html>