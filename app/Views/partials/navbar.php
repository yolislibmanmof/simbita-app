<?php
declare(strict_types=1);
$userName  = $_SESSION['simbita_user']['name'] ?? 'User';
$userRole  = Auth::role();
$userId    = Auth::id();
$userEmail = $_SESSION['simbita_user']['email'] ?? '';

// Ambil notifikasi
$unreadCount = 0;
$notifications = [];
try {
    $db = Database::connection();
    $stmt = $db->prepare('SELECT COUNT(*) AS total FROM notifications WHERE user_id = ? AND is_read = 0');
    $stmt->execute([$userId]);
    $unreadCount = (int) $stmt->fetch()['total'];

    $stmt = $db->prepare('SELECT id, title, message, link, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10');
    $stmt->execute([$userId]);
    $notifications = $stmt->fetchAll();
} catch (Exception $e) {
    // abaikan
}

// Helper menu aktif
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$isActive = fn(string $path): string => str_contains($currentPath, $path) ? 'nb-active' : '';
?>

<style>
/* =========================================================
   NAVBAR SIMBITA - SELF-CONTAINED STYLES
   Namespace: .nb- | Tidak bergantung admin.css / admin.js
   ========================================================= */
.nb-bar {
    position: sticky;
    top: 0;
    z-index: 1100;
    background: rgba(255, 255, 255, 0.94);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-bottom: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.nb-inner {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0.7rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

/* --- Brand --- */
.nb-brand {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 800;
    font-size: 1.25rem;
    text-decoration: none;
    background: linear-gradient(135deg, #667eea, #764ba2);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    flex-shrink: 0;
}

.nb-brand i {
    -webkit-text-fill-color: initial;
    color: #667eea;
    font-size: 1.45rem;
}

/* --- Menu --- */
.nb-menu {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    list-style: none;
    margin: 0;
    padding: 0;
    flex: 1;
    min-width: 0;
}

.nb-menu a {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.55rem 0.95rem;
    border-radius: 0.6rem;
    color: #374151;
    font-weight: 600;
    font-size: 0.875rem;
    text-decoration: none;
    transition: all 0.25s;
    position: relative;
    white-space: nowrap;
}

.nb-menu a:hover {
    background: #f3f4f6;
    color: #111827;
}

.nb-menu a.nb-active {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.12), rgba(118, 75, 162, 0.12));
    color: #5b21b6;
}

.nb-menu a.nb-active::after {
    content: '';
    position: absolute;
    left: 0.95rem;
    right: 0.95rem;
    bottom: 0.15rem;
    height: 2px;
    border-radius: 2px;
    background: linear-gradient(90deg, #667eea, #764ba2);
}

/* --- Actions --- */
.nb-actions {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-left: auto;
    flex-shrink: 0;
}

.nb-dropdown { position: relative; }

.nb-icon-btn {
    position: relative;
    width: 40px;
    height: 40px;
    border-radius: 0.7rem;
    border: 1px solid #e5e7eb;
    background: #ffffff;
    color: #4b5563;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    cursor: pointer;
    transition: all 0.25s;
}

.nb-icon-btn:hover {
    background: #f9fafb;
    color: #111827;
    transform: translateY(-1px);
    box-shadow: 0 4px 10px -4px rgba(0, 0, 0, 0.15);
}

.nb-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    min-width: 18px;
    height: 18px;
    padding: 0 4px;
    border-radius: 999px;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #fff;
    font-size: 0.65rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #fff;
    animation: nbPulse 2s infinite;
}

@keyframes nbPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.12); }
}

.nb-user-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.3rem 0.7rem 0.3rem 0.3rem;
    border-radius: 0.8rem;
    border: 1px solid #e5e7eb;
    background: #ffffff;
    cursor: pointer;
    transition: all 0.25s;
    font-family: inherit;
}

.nb-user-btn:hover {
    background: #f9fafb;
    box-shadow: 0 4px 10px -4px rgba(0, 0, 0, 0.15);
}

.nb-avatar {
    width: 32px;
    height: 32px;
    border-radius: 0.6rem;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
    font-weight: 800;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.nb-user-name {
    font-weight: 700;
    font-size: 0.875rem;
    color: #1f2937;
    max-width: 140px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.nb-chev {
    font-size: 0.7rem;
    color: #6b7280;
    transition: transform 0.25s;
}

.nb-dropdown.nb-is-open .nb-chev { transform: rotate(180deg); }

/* --- Dropdown Menu (sendiri, bukan .dropdown-menu admin.css) --- */
.nb-dropdown-menu {
    position: absolute;
    right: 0;
    top: calc(100% + 10px);
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 1rem;
    box-shadow: 0 20px 45px -18px rgba(0, 0, 0, 0.28);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-8px) scale(0.98);
    transform-origin: top right;
    transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    z-index: 1200;
    overflow: hidden;
}

.nb-dropdown-menu.nb-open {
    opacity: 1;
    visibility: visible;
    transform: translateY(0) scale(1);
}

.nb-notif-menu { width: 360px; max-width: calc(100vw - 2rem); }
.nb-user-menu { width: 250px; }

.nb-menu-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.85rem 1rem;
    border-bottom: 1px solid #f3f4f6;
    background: #fafafa;
}

.nb-menu-head strong { font-size: 0.9rem; color: #111827; }

.nb-menu-head a {
    font-size: 0.78rem;
    color: #6366f1;
    text-decoration: none;
    font-weight: 600;
}

.nb-menu-head a:hover { text-decoration: underline; }

.nb-notif-list {
    max-height: 320px;
    overflow-y: auto;
}

.nb-notif-item {
    display: block;
    padding: 0.8rem 1rem;
    text-decoration: none;
    color: inherit;
    border-bottom: 1px solid #f9fafb;
    transition: background 0.2s;
}

.nb-notif-item:last-child { border-bottom: none; }
.nb-notif-item:hover { background: #f9fafb; }
.nb-notif-item.nb-unread { background: #eef2ff; }
.nb-notif-item.nb-unread:hover { background: #e0e7ff; }

.nb-notif-top {
    display: flex;
    justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 0.25rem;
}

.nb-notif-title {
    font-size: 0.85rem;
    font-weight: 700;
    color: #111827;
    line-height: 1.3;
}

.nb-notif-time {
    font-size: 0.7rem;
    color: #9ca3af;
    white-space: nowrap;
    flex-shrink: 0;
}

.nb-notif-msg {
    font-size: 0.78rem;
    color: #6b7280;
    margin: 0;
    line-height: 1.45;
}

.nb-notif-empty {
    text-align: center;
    padding: 2rem 1rem;
    color: #9ca3af;
    font-size: 0.85rem;
}

.nb-notif-empty i {
    display: block;
    font-size: 1.75rem;
    margin-bottom: 0.5rem;
    opacity: 0.5;
}

.nb-user-head {
    padding: 0.9rem 1rem;
    border-bottom: 1px solid #f3f4f6;
    background: #fafafa;
}

.nb-user-fullname {
    font-weight: 700;
    font-size: 0.9rem;
    color: #111827;
    margin-bottom: 0.1rem;
}

.nb-user-email {
    font-size: 0.78rem;
    color: #6b7280;
    word-break: break-all;
    margin-bottom: 0.5rem;
}

.nb-role-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.2rem 0.6rem;
    border-radius: 999px;
    font-size: 0.68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.15), rgba(118, 75, 162, 0.15));
    color: #5b21b6;
}

.nb-user-body { padding: 0.5rem; }

.nb-logout-btn {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.65rem 0.9rem;
    border: none;
    background: transparent;
    color: #dc2626;
    font-weight: 700;
    font-size: 0.875rem;
    cursor: pointer;
    border-radius: 0.6rem;
    transition: all 0.2s;
    font-family: inherit;
    text-align: left;
}

.nb-logout-btn:hover { background: #fee2e2; }

/* --- Responsive --- */
@media (max-width: 900px) {
    .nb-inner { flex-wrap: wrap; gap: 0.75rem; }
    .nb-menu {
        order: 3;
        width: 100%;
        overflow-x: auto;
        padding-bottom: 0.25rem;
    }
    .nb-user-name { display: none; }
    .nb-user-btn { padding-right: 0.5rem; }
}
</style>

<nav class="nb-bar">
    <div class="nb-inner">
        <a href="<?= BASE_URL ?>" class="nb-brand">
            <i class="bi bi-book-half"></i>
            <span><?= e(APP_NAME) ?></span>
        </a>

        <ul class="nb-menu">
            <?php if ($userRole === 'admin'): ?>
                <li><a href="<?= BASE_URL ?>/admin/dashboard" class="<?= $isActive('/admin/dashboard') ?>"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li><a href="<?= BASE_URL ?>/admin/students" class="<?= $isActive('/admin/students') ?>"><i class="bi bi-people"></i> Mahasiswa</a></li>
                <li><a href="<?= BASE_URL ?>/admin/lecturers" class="<?= $isActive('/admin/lecturers') ?>"><i class="bi bi-person-workspace"></i> Dosen</a></li>
                <li><a href="<?= BASE_URL ?>/admin/works" class="<?= $isActive('/admin/works') ?>"><i class="bi bi-file-earmark-text"></i> Tugas Akhir</a></li>
            <?php elseif ($userRole === 'lecturer'): ?>
                <li><a href="<?= BASE_URL ?>/lecturer/dashboard" class="<?= $isActive('/lecturer/dashboard') ?>"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li><a href="<?= BASE_URL ?>/lecturer/students" class="<?= $isActive('/lecturer/students') ?>"><i class="bi bi-people"></i> Mahasiswa Bimbingan</a></li>
                <li><a href="<?= BASE_URL ?>/lecturer/reviews" class="<?= $isActive('/lecturer/reviews') ?>"><i class="bi bi-clipboard-check"></i> Review</a></li>
                <li><a href="<?= BASE_URL ?>/lecturer/history" class="<?= $isActive('/lecturer/history') ?>"><i class="bi bi-clock-history"></i> Riwayat</a></li>
            <?php elseif ($userRole === 'student'): ?>
                <li><a href="<?= BASE_URL ?>/student/dashboard" class="<?= $isActive('/student/dashboard') ?>"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li><a href="<?= BASE_URL ?>/student/upload" class="<?= $isActive('/student/upload') ?>"><i class="bi bi-cloud-upload"></i> Upload</a></li>
                <li><a href="<?= BASE_URL ?>/student/history" class="<?= $isActive('/student/history') ?>"><i class="bi bi-clock-history"></i> Riwayat</a></li>
            <?php endif; ?>
        </ul>

        <div class="nb-actions">
            <!-- Notifications Dropdown -->
            <div class="nb-dropdown">
                <button type="button" class="nb-icon-btn" data-nb-toggle="nbNotifMenu" aria-expanded="false" title="Notifikasi">
                    <i class="bi bi-bell-fill"></i>
                    <?php if ($unreadCount > 0): ?>
                        <span class="nb-badge"><?= $unreadCount > 99 ? '99+' : $unreadCount ?></span>
                    <?php endif; ?>
                </button>

                <div class="nb-dropdown-menu nb-notif-menu" id="nbNotifMenu">
                    <div class="nb-menu-head">
                        <strong><i class="bi bi-bell"></i> Notifikasi</strong>
                        <a href="<?= BASE_URL ?>/notifications">Lihat semua</a>
                    </div>
                    <div class="nb-notif-list">
                        <?php if (empty($notifications)): ?>
                            <div class="nb-notif-empty">
                                <i class="bi bi-bell-slash"></i>
                                Tidak ada notifikasi
                            </div>
                        <?php else: ?>
                            <?php foreach ($notifications as $notif): ?>
                                <a class="nb-notif-item <?= $notif['is_read'] ? '' : 'nb-unread' ?>"
                                   href="<?= BASE_URL . ($notif['link'] ?? '/notifications') ?>">
                                    <div class="nb-notif-top">
                                        <span class="nb-notif-title"><?= e($notif['title']) ?></span>
                                        <span class="nb-notif-time"><?= date('d/m H:i', strtotime($notif['created_at'])) ?></span>
                                    </div>
                                    <p class="nb-notif-msg">
                                        <?= e(mb_strimwidth($notif['message'], 0, 80, '...')) ?>
                                    </p>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- User Profile Dropdown -->
            <div class="nb-dropdown">
                <button type="button" class="nb-user-btn" data-nb-toggle="nbUserMenu" aria-expanded="false">
                    <span class="nb-avatar"><?= e(strtoupper(substr($userName, 0, 1))) ?></span>
                    <span class="nb-user-name"><?= e(explode(' ', $userName)[0]) ?></span>
                    <i class="bi bi-chevron-down nb-chev"></i>
                </button>

                <div class="nb-dropdown-menu nb-user-menu" id="nbUserMenu">
                    <div class="nb-user-head">
                        <div class="nb-user-fullname"><?= e($userName) ?></div>
                        <div class="nb-user-email"><?= e($userEmail) ?></div>
                        <span class="nb-role-badge">
                            <i class="bi bi-shield-check"></i>
                            <?= e(ucfirst((string) $userRole)) ?>
                        </span>
                    </div>
                    <div class="nb-user-body">
                        <form method="POST" action="<?= BASE_URL ?>/logout">
                            <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                            <button type="submit" class="nb-logout-btn">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
(function () {
    'use strict';

    const closeAll = () => {
        document.querySelectorAll('.nb-dropdown-menu.nb-open').forEach(m => m.classList.remove('nb-open'));
        document.querySelectorAll('.nb-dropdown.nb-is-open').forEach(d => d.classList.remove('nb-is-open'));
        document.querySelectorAll('[data-nb-toggle]').forEach(b => b.setAttribute('aria-expanded', 'false'));
    };

    document.querySelectorAll('[data-nb-toggle]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const menu = document.getElementById(btn.dataset.nbToggle);
            if (!menu) return;

            const wasOpen = menu.classList.contains('nb-open');
            closeAll();

            if (!wasOpen) {
                menu.classList.add('nb-open');
                btn.closest('.nb-dropdown')?.classList.add('nb-is-open');
                btn.setAttribute('aria-expanded', 'true');
            }
        });
    });

    // Tutup saat klik di luar
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.nb-dropdown')) closeAll();
    });

    // Tutup dengan Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeAll();
    });
})();
</script>