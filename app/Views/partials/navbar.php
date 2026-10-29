<?php
declare(strict_types=1);
$userName  = $_SESSION['simbita_user']['name'] ?? 'User';
$userRole  = Auth::role();
$userId    = Auth::id();
$userEmail = $_SESSION['simbita_user']['email'] ?? '';

// Hitung proposal menunggu (badge menu)
$pendingProposals = 0;
try {
    if (class_exists('TitleProposal')) {
        $pendingProposals = TitleProposal::countPendingForUser($userId);
    }
} catch (Throwable $e) {
    $pendingProposals = 0;
}

// Ambil notifikasi via Model
$unreadCount = 0;
$notifications = [];
try {
    $unreadCount = Notification::countUnread($userId);
    $notifications = Notification::latest($userId, 8);
} catch (Throwable $e) {
    error_log('[Navbar Notif Error] ' . $e->getMessage());
}

// Hitung pesan chat belum dibaca (badge menu)
$unreadMessages = 0;
try {
    if (class_exists('DirectMessage') && in_array($userRole, ['student', 'lecturer'], true)) {
        $unreadMessages = DirectMessage::countUnread($userId);
    }
} catch (Throwable $e) {
    $unreadMessages = 0;
}

// Helper relative time
$timeAgo = function(string $date): string {
    $diff = time() - strtotime($date);
    if ($diff < 60) return 'Baru saja';
    if ($diff < 3600) return floor($diff / 60) . 'm lalu';
    if ($diff < 86400) return floor($diff / 3600) . 'j lalu';
    if ($diff < 604800) return floor($diff / 86400) . 'h lalu';
    return date('d/m/y', strtotime($date));
};

// Helper icon per type
$typeMeta = function(string $type): array {
    return match($type) {
        'review' => ['icon' => 'pencil-square-fill', 'class' => 'nb-type-review'],
        'deadline' => ['icon' => 'alarm-fill', 'class' => 'nb-type-deadline'],
        'approval' => ['icon' => 'check-circle-fill', 'class' => 'nb-type-approval'],
        'comment' => ['icon' => 'chat-left-text-fill', 'class' => 'nb-type-comment'],
        default => ['icon' => 'gear-fill', 'class' => 'nb-type-system'],
    };
};

$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$isActive = fn(string $path): string => str_contains($currentPath, $path) ? 'nb-active' : '';

// Helper: tandai trigger dropdown aktif bila salah satu anakannya sedang dibuka
$groupActive = function(array $paths) use ($currentPath): string {
    foreach ($paths as $p) {
        if (str_contains($currentPath, $p)) return 'nb-active';
    }
    return '';
};
?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/darkmode.css">

<style>
.nb-bar{position:sticky;top:0;z-index:1100;background:rgba(255,255,255,0.94);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);border-bottom:1px solid #e5e7eb;box-shadow:0 1px 3px rgba(0,0,0,0.04)}
.nb-inner{max-width:1400px;margin:0 auto;padding:0.7rem 1.5rem;display:flex;align-items:center;justify-content:space-between;gap:1.5rem}
.nb-brand{display:flex;align-items:center;gap:0.5rem;font-weight:800;font-size:1.25rem;text-decoration:none;background:linear-gradient(135deg,#667eea,#764ba2);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;flex-shrink:0}
.nb-brand i{-webkit-text-fill-color:initial;color:#667eea;font-size:1.45rem}
.nb-menu{display:flex;align-items:center;justify-content:center;gap:0.25rem;list-style:none;margin:0;padding:0;flex:1}
/* Penjaga anti-potong: menu boleh melipat ke baris kedua, bukan memotong elemen kanan */
.nb-menu{flex-wrap:wrap;row-gap:0.2rem;min-width:0}
.nb-menu a{display:flex;align-items:center;gap:0.45rem;padding:0.55rem 0.95rem;border-radius:0.6rem;color:#374151;font-weight:600;font-size:0.875rem;text-decoration:none;transition:all 0.25s;position:relative;white-space:nowrap}
.nb-menu a:hover{background:#f3f4f6;color:#111827}
.nb-menu a.nb-active{background:linear-gradient(135deg,rgba(102,126,234,0.12),rgba(118,75,162,0.12));color:#5b21b6}
.nb-menu a.nb-active::after{content:'';position:absolute;left:0.95rem;right:0.95rem;bottom:0.15rem;height:2px;border-radius:2px;background:linear-gradient(90deg,#667eea,#764ba2)}
/* --- Trigger dropdown menu navigasi --- */
.nb-menu-trigger{display:flex;align-items:center;gap:0.45rem;padding:0.55rem 0.95rem;border-radius:0.6rem;color:#374151;font-weight:600;font-size:0.875rem;background:none;border:none;cursor:pointer;font-family:inherit;transition:all 0.25s;white-space:nowrap;position:relative}
.nb-menu-trigger:hover{background:#f3f4f6;color:#111827}
.nb-menu-trigger.nb-active{background:linear-gradient(135deg,rgba(102,126,234,0.12),rgba(118,75,162,0.12));color:#5b21b6}
.nb-caret{font-size:0.62rem;color:#6b7280;margin-left:0.1rem;transition:transform 0.25s}
.nb-dropdown.nb-is-open .nb-caret{transform:rotate(180deg)}
/* --- Panel dropdown navigasi --- */
.nb-navmenu{min-width:230px;padding:0.5rem}
.nb-navmenu a{display:flex;align-items:center;gap:0.6rem;padding:0.6rem 0.85rem;border-radius:0.6rem;color:#374151;font-weight:600;font-size:0.875rem;text-decoration:none;transition:background 0.2s;white-space:nowrap}
.nb-navmenu a:hover{background:#f3f4f6}
.nb-navmenu a.nb-active{background:rgba(102,126,234,0.12);color:#5b21b6}
.nb-navmenu a.nb-active::after{content:none}
.nb-actions{display:flex;align-items:center;gap:0.6rem;flex-shrink:0;margin-left:auto}
.nb-dropdown{position:relative}
.nb-icon-btn{position:relative;width:40px;height:40px;border-radius:0.7rem;border:1px solid #e5e7eb;background:#fff;color:#4b5563;display:flex;align-items:center;justify-content:center;font-size:1.05rem;cursor:pointer;transition:all 0.25s}
.nb-icon-btn:hover{background:#f9fafb;color:#111827;transform:translateY(-1px);box-shadow:0 4px 10px -4px rgba(0,0,0,0.15)}
.nb-badge{position:absolute;top:-5px;right:-5px;min-width:18px;height:18px;padding:0 4px;border-radius:999px;background:linear-gradient(133deg,#ef4444,#dc2626);color:#fff;font-size:0.65rem;font-weight:800;display:flex;align-items:center;justify-content:center;border:2px solid #fff;animation:nbPulse 2s infinite}
.nb-badge.nb-badge-new{animation:nbBounce 0.6s ease}
.nb-menu-badge{min-width:18px;height:18px;padding:0 5px;border-radius:999px;background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;font-size:0.65rem;font-weight:800;display:inline-flex;align-items:center;justify-content:center;margin-left:0.35rem}
@keyframes nbPulse{0%,100%{transform:scale(1)}50%{transform:scale(1.12)}}
@keyframes nbBounce{0%,100%{transform:translateY(0)}50%{transform:translateY(-6px)}}
.nb-user-btn{display:flex;align-items:center;gap:0.5rem;padding:0.3rem 0.7rem 0.3rem 0.3rem;border-radius:0.8rem;border:1px solid #e5e7eb;background:#fff;cursor:pointer;transition:all 0.25s;font-family:inherit}
.nb-user-btn:hover{background:#f9fafb;box-shadow:0 4px 10px -4px rgba(0,0,0,0.15)}
.nb-avatar{width:32px;height:32px;border-radius:0.6rem;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;font-weight:800;font-size:0.85rem;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.nb-user-name{font-weight:700;font-size:0.875rem;color:#1f2937;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.nb-chev{font-size:0.7rem;color:#6b7280;transition:transform 0.25s}
.nb-dropdown.nb-is-open .nb-chev{transform:rotate(180deg)}
.nb-dropdown-menu{position:absolute;right:0;top:calc(100% + 10px);background:#fff;border:1px solid #e5e7eb;border-radius:1rem;box-shadow:0 20px 45px -18px rgba(0,0,0,0.28);opacity:0;visibility:hidden;transform:translateY(-8px) scale(0.98);transform-origin:top right;transition:all 0.22s cubic-bezier(0.16,1,0.3,1);z-index:1200;overflow:hidden}
.nb-dropdown-menu.nb-open{opacity:1;visibility:visible;transform:translateY(0) scale(1)}
.nb-notif-menu{width:400px;max-width:calc(100vw - 2rem)}
.nb-user-menu{width:250px}
.nb-menu-head{display:flex;justify-content:space-between;align-items:center;padding:0.85rem 1rem;border-bottom:1px solid #f3f4f6;background:#fafafa}
.nb-menu-head strong{font-size:0.9rem;color:#111827;display:flex;align-items:center;gap:0.35rem}
.nb-menu-head-right{display:flex;align-items:center;gap:0.75rem}
.nb-mark-read-btn{font-size:0.75rem;color:#6366f1;text-decoration:none;font-weight:600;cursor:pointer;background:none;border:none;padding:0;font-family:inherit;transition:opacity 0.2s}
.nb-mark-read-btn:hover{text-decoration:underline;opacity:0.8}
.nb-mark-read-btn:disabled{opacity:0.5;cursor:not-allowed}
.nb-see-all{font-size:0.78rem;color:#6366f1;text-decoration:none;font-weight:600}
.nb-see-all:hover{text-decoration:underline}
.nb-notif-list{max-height:400px;overflow-y:auto}
.nb-notif-item{display:flex;gap:0.75rem;padding:0.85rem 1rem;text-decoration:none;color:inherit;border-bottom:1px solid #f9fafb;transition:all 0.2s;cursor:pointer;align-items:flex-start}
.nb-notif-item:last-child{border-bottom:none}
.nb-notif-item:hover{background:#f9fafb}
.nb-notif-item.nb-unread{background:#eef2ff}
.nb-notif-item.nb-unread:hover{background:#e0e7ff}
.nb-notif-icon{width:36px;height:36px;border-radius:0.6rem;display:flex;align-items:center;justify-content:center;font-size:1rem;color:#fff;flex-shrink:0}
.nb-type-review{background:linear-gradient(135deg,#3b82f6,#1e40af)}
.nb-type-deadline{background:linear-gradient(135deg,#f59e0b,#d97706)}
.nb-type-approval{background:linear-gradient(135deg,#10b981,#059669)}
.nb-type-comment{background:linear-gradient(135deg,#06b6d4,#0891b2)}
.nb-type-system{background:linear-gradient(135deg,#8b5cf6,#6d28d9)}
.nb-notif-content{flex:1;min-width:0}
.nb-notif-top{display:flex;justify-content:space-between;gap:0.5rem;margin-bottom:0.2rem;align-items:flex-start}
.nb-notif-title{font-size:0.85rem;font-weight:700;color:#111827;line-height:1.3;flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.nb-notif-time{font-size:0.7rem;color:#9ca3af;white-space:nowrap;flex-shrink:0;font-family:'JetBrains Mono',monospace;font-weight:600}
.nb-notif-msg{font-size:0.78rem;color:#6b7280;margin:0;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.nb-notif-empty{text-align:center;padding:2.5rem 1rem;color:#9ca3af;font-size:0.85rem}
.nb-notif-empty i{display:block;font-size:2rem;margin-bottom:0.5rem;opacity:0.4}
.nb-notif-footer{padding:0.75rem 1rem;border-top:1px solid #f3f4f6;background:#fafafa;text-align:center}
.nb-notif-footer a{font-size:0.82rem;color:#6366f1;text-decoration:none;font-weight:600;display:inline-flex;align-items:center;gap:0.35rem}
.nb-notif-footer a:hover{text-decoration:underline}
.nb-user-head{padding:0.9rem 1rem;border-bottom:1px solid #f3f4f6;background:#fafafa}
.nb-user-fullname{font-weight:700;font-size:0.9rem;color:#111827;margin-bottom:0.1rem}
.nb-user-email{font-size:0.78rem;color:#6b7280;word-break:break-all;margin-bottom:0.5rem}
.nb-role-badge{display:inline-flex;align-items:center;gap:0.3rem;padding:0.2rem 0.6rem;border-radius:999px;font-size:0.68rem;font-weight:800;text-transform:uppercase;letter-spacing:0.04em;background:linear-gradient(135deg,rgba(102,126,234,0.15),rgba(118,75,162,0.15));color:#5b21b6}
.nb-user-body{padding:0.5rem}
.nb-user-link{width:100%;display:flex;align-items:center;gap:0.5rem;padding:0.65rem 0.9rem;background:transparent;border:none;color:#374151;font-weight:600;font-size:0.875rem;border-radius:0.6rem;cursor:pointer;transition:all 0.2s;text-align:left;font-family:inherit;text-decoration:none}
.nb-user-link:hover{background:#f3f4f6}
.nb-logout-btn{width:100%;display:flex;align-items:center;gap:0.5rem;padding:0.65rem 0.9rem;border:none;background:transparent;color:#dc2626;font-weight:700;font-size:0.875rem;cursor:pointer;border-radius:0.6rem;transition:all 0.2s;font-family:inherit;text-align:left}
.nb-logout-btn:hover{background:#fee2e2}
@media (max-width:900px){
    .nb-inner{flex-wrap:wrap;gap:0.75rem}
    .nb-menu{order:3;width:100%;overflow:visible;flex-wrap:wrap;justify-content:flex-start;padding-bottom:0.25rem}
    .nb-user-name{display:none}
    .nb-user-btn{padding-right:0.5rem}
    .nb-notif-menu{right:-1rem}
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
                <li class="nb-dropdown">
                    <button type="button" class="nb-menu-trigger <?= $groupActive(['/admin/students', '/admin/lecturers', '/admin/works']) ?>" data-nb-toggle="nbMenuAkademik" aria-expanded="false">
                        <i class="bi bi-people"></i> Akademik <i class="bi bi-chevron-down nb-caret"></i>
                    </button>
                    <div class="nb-dropdown-menu nb-navmenu" id="nbMenuAkademik">
                        <a href="<?= BASE_URL ?>/admin/students" class="<?= $isActive('/admin/students') ?>"><i class="bi bi-people"></i> Mahasiswa</a>
                        <a href="<?= BASE_URL ?>/admin/lecturers" class="<?= $isActive('/admin/lecturers') ?>"><i class="bi bi-person-workspace"></i> Dosen</a>
                        <a href="<?= BASE_URL ?>/admin/works" class="<?= $isActive('/admin/works') ?>"><i class="bi bi-file-earmark-text"></i> Tugas Akhir</a>
                    </div>
                </li>
                <li><a href="<?= BASE_URL ?>/repository" class="<?= $isActive('/repository') ?>"><i class="bi bi-archive"></i> Repository</a></li>
                <li class="nb-dropdown">
                    <button type="button" class="nb-menu-trigger <?= $groupActive(['/admin/defense', '/admin/plagiarism']) ?>" data-nb-toggle="nbMenuSidang" aria-expanded="false">
                        <i class="bi bi-person-video3"></i> Sidang & Mutu <i class="bi bi-chevron-down nb-caret"></i>
                    </button>
                    <div class="nb-dropdown-menu nb-navmenu" id="nbMenuSidang">
                        <a href="<?= BASE_URL ?>/admin/defense" class="<?= $isActive('/admin/defense') ?>"><i class="bi bi-person-video3"></i> Jadwal Sidang</a>
                        <a href="<?= BASE_URL ?>/admin/plagiarism" class="<?= $isActive('/admin/plagiarism') ?>"><i class="bi bi-clipboard2-pulse"></i> Cek Plagiasi</a>
                    </div>
                </li>
                <li class="nb-dropdown">
                    <button type="button" class="nb-menu-trigger <?= $groupActive(['/admin/faculties', '/admin/study-programs', '/admin/settings']) ?>" data-nb-toggle="nbMenuSistem" aria-expanded="false">
                        <i class="bi bi-gear-fill"></i> Sistem <i class="bi bi-chevron-down nb-caret"></i>
                    </button>
                    <div class="nb-dropdown-menu nb-navmenu" id="nbMenuSistem">
                        <a href="<?= BASE_URL ?>/admin/faculties" class="<?= $isActive('/admin/faculties') ?>"><i class="bi bi-diagram-3"></i> Master Data</a>
                        <a href="<?= BASE_URL ?>/admin/study-programs" class="<?= $isActive('/admin/study-programs') ?>"><i class="bi bi-mortarboard"></i> Program Studi</a>
                        <a href="<?= BASE_URL ?>/admin/settings" class="<?= $isActive('/admin/settings') ?>"><i class="bi bi-sliders"></i> Pengaturan Sistem</a>
                    </div>
                </li>

            <?php elseif ($userRole === 'lecturer'): ?>
                <li><a href="<?= BASE_URL ?>/lecturer/dashboard" class="<?= $isActive('/lecturer/dashboard') ?>"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li><a href="<?= BASE_URL ?>/lecturer/proposals" class="<?= $isActive('/lecturer/proposals') ?>"><i class="bi bi-lightbulb"></i> Pengajuan Judul<?php if ($pendingProposals > 0): ?><span class="nb-menu-badge"><?= $pendingProposals ?></span><?php endif; ?></a></li>
                <li class="nb-dropdown">
                    <button type="button" class="nb-menu-trigger <?= $groupActive(['/lecturer/work', '/lecturer/students', '/lecturer/history']) ?>" data-nb-toggle="nbMenuBimbingan" aria-expanded="false">
                        <i class="bi bi-journal-text"></i> Bimbingan <i class="bi bi-chevron-down nb-caret"></i>
                    </button>
                    <div class="nb-dropdown-menu nb-navmenu" id="nbMenuBimbingan">
                        <a href="<?= BASE_URL ?>/lecturer/work" class="<?= $isActive('/lecturer/work') ?>"><i class="bi bi-journal-text"></i> Tugas Akhir</a>
                        <a href="<?= BASE_URL ?>/lecturer/students" class="<?= $isActive('/lecturer/students') ?>"><i class="bi bi-people"></i> Mahasiswa Bimbingan</a>
                        <a href="<?= BASE_URL ?>/lecturer/history" class="<?= $isActive('/lecturer/history') ?>"><i class="bi bi-clock-history"></i> Riwayat</a>
                    </div>
                </li>
                <li class="nb-dropdown">
                    <button type="button" class="nb-menu-trigger <?= $groupActive(['/lecturer/reviews', '/lecturer/review', '/lecturer/grading']) ?>" data-nb-toggle="nbMenuReview" aria-expanded="false">
                        <i class="bi bi-clipboard-check"></i> Review & Nilai <i class="bi bi-chevron-down nb-caret"></i>
                    </button>
                    <div class="nb-dropdown-menu nb-navmenu" id="nbMenuReview">
                        <a href="<?= BASE_URL ?>/lecturer/reviews" class="<?= $isActive('/lecturer/reviews') ?>"><i class="bi bi-clipboard-check"></i> Review Dokumen</a>
                        <a href="<?= BASE_URL ?>/lecturer/grading" class="<?= $isActive('/lecturer/grading') ?>"><i class="bi bi-award"></i> Penilaian Sidang</a>
                    </div>
                </li>
                <li><a href="<?= BASE_URL ?>/repository" class="<?= $isActive('/repository') ?>"><i class="bi bi-archive"></i> Repository</a></li>
                <li><a href="<?= BASE_URL ?>/lecturer/messages" class="<?= $isActive('/lecturer/messages') ?>"><i class="bi bi-chat-dots"></i> Chat<?php if ($unreadMessages > 0): ?><span class="nb-menu-badge"><?= $unreadMessages ?></span><?php endif; ?></a></li>

            <?php elseif ($userRole === 'student'): ?>
                <li><a href="<?= BASE_URL ?>/student/dashboard" class="<?= $isActive('/student/dashboard') ?>"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li><a href="<?= BASE_URL ?>/student/proposals" class="<?= $isActive('/student/proposals') ?>"><i class="bi bi-lightbulb"></i> Pengajuan Judul<?php if ($pendingProposals > 0): ?><span class="nb-menu-badge"><?= $pendingProposals ?></span><?php endif; ?></a></li>
                <li class="nb-dropdown">
                    <button type="button" class="nb-menu-trigger <?= $groupActive(['/student/work', '/student/upload', '/student/history']) ?>" data-nb-toggle="nbMenuTugas" aria-expanded="false">
                        <i class="bi bi-journal-text"></i> Tugas Akhir <i class="bi bi-chevron-down nb-caret"></i>
                    </button>
                    <div class="nb-dropdown-menu nb-navmenu" id="nbMenuTugas">
                        <a href="<?= BASE_URL ?>/student/work" class="<?= $isActive('/student/work') ?>"><i class="bi bi-journal-text"></i> Ringkasan Tugas Akhir</a>
                        <a href="<?= BASE_URL ?>/student/upload" class="<?= $isActive('/student/upload') ?>"><i class="bi bi-cloud-upload"></i> Upload Dokumen</a>
                        <a href="<?= BASE_URL ?>/student/history" class="<?= $isActive('/student/history') ?>"><i class="bi bi-clock-history"></i> Riwayat</a>
                    </div>
                </li>
                <li><a href="<?= BASE_URL ?>/student/recommend" class="<?= $isActive('/student/recommend') ?>"><i class="bi bi-magic"></i> Cari Dosen</a></li>
                <li><a href="<?= BASE_URL ?>/repository" class="<?= $isActive('/repository') ?>"><i class="bi bi-archive"></i> Repository</a></li>
                <li><a href="<?= BASE_URL ?>/student/messages" class="<?= $isActive('/student/messages') ?>"><i class="bi bi-chat-dots"></i> Chat Pembimbing<?php if ($unreadMessages > 0): ?><span class="nb-menu-badge"><?= $unreadMessages ?></span><?php endif; ?></a></li>
            <?php endif; ?>
        </ul>

        <div class="nb-actions">
            <!-- Theme Toggle -->
            <button type="button" class="nb-icon-btn" id="nbThemeBtn" title="Ganti Tema Terang/Gelap">
                <i class="bi bi-moon-stars-fill" id="nbThemeIcon"></i>
            </button>

            <!-- Notifications Dropdown -->
            <div class="nb-dropdown">
                <button type="button" class="nb-icon-btn" id="nbNotifBtn" data-nb-toggle="nbNotifMenu" aria-expanded="false" title="Notifikasi">
                    <i class="bi bi-bell-fill"></i>
                    <?php if ($unreadCount > 0): ?>
                        <span class="nb-badge" id="nbNotifBadge"><?= $unreadCount > 99 ? '99+' : $unreadCount ?></span>
                    <?php endif; ?>
                </button>

                <div class="nb-dropdown-menu nb-notif-menu" id="nbNotifMenu">
                    <div class="nb-menu-head">
                        <strong><i class="bi bi-bell-fill"></i> Notifikasi</strong>
                        <div class="nb-menu-head-right">
                            <?php if ($unreadCount > 0): ?>
                                <button type="button" class="nb-mark-read-btn" id="nbMarkAllRead">
                                    <i class="bi bi-check2-all"></i> Tandai semua dibaca
                                </button>
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>/notifications" class="nb-see-all">Lihat semua →</a>
                        </div>
                    </div>
                    <div class="nb-notif-list" id="nbNotifList">
                        <?php if (empty($notifications)): ?>
                            <div class="nb-notif-empty">
                                <i class="bi bi-bell-slash"></i>
                                Tidak ada notifikasi
                            </div>
                        <?php else: ?>
                            <?php foreach ($notifications as $notif):
                                $meta = $typeMeta($notif['type'] ?? 'system');
                            ?>
                                <div class="nb-notif-item <?= $notif['is_read'] ? '' : 'nb-unread' ?>"
                                     data-notif-id="<?= $notif['id'] ?>"
                                     data-link="<?= e(BASE_URL . ($notif['link'] ?? '/notifications')) ?>">
                                    <div class="nb-notif-icon <?= $meta['class'] ?>">
                                        <i class="bi bi-<?= $meta['icon'] ?>"></i>
                                    </div>
                                    <div class="nb-notif-content">
                                        <div class="nb-notif-top">
                                            <span class="nb-notif-title"><?= e($notif['title']) ?></span>
                                            <span class="nb-notif-time"><?= e($timeAgo($notif['created_at'])) ?></span>
                                        </div>
                                        <p class="nb-notif-msg"><?= e($notif['message']) ?></p>
                                    </div>
                                </div>
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
                        <a href="<?= BASE_URL ?>/change-password" class="nb-user-link">
                            <i class="bi bi-key-fill" style="color: #f59e0b;"></i> Ubah Password
                        </a>
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

    const CSRF_TOKEN = '<?= e(Csrf::token()) ?>';
    const BASE = '<?= BASE_URL ?>';

    // ===== Dropdown Toggle =====
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

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.nb-dropdown')) closeAll();
    });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeAll(); });

    // ===== Theme Toggle (Dark Mode) =====
    const themeRoot = document.documentElement;
    const applyTheme = (t) => {
        if (t === 'dark') themeRoot.setAttribute('data-theme', 'dark');
        else themeRoot.removeAttribute('data-theme');
        const icon = document.getElementById('nbThemeIcon');
        if (icon) icon.className = 'bi ' + (t === 'dark' ? 'bi-sun-fill' : 'bi-moon-stars-fill');
    };
    applyTheme(localStorage.getItem('simbita_theme') || 'light');
    document.getElementById('nbThemeBtn')?.addEventListener('click', () => {
        const next = themeRoot.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        localStorage.setItem('simbita_theme', next);
        applyTheme(next);
    });

    // ===== Mark One Read + Navigate =====
    document.querySelectorAll('.nb-notif-item').forEach(item => {
        item.addEventListener('click', async () => {
            const notifId = item.dataset.notifId;
            const link = item.dataset.link;

            if (item.classList.contains('nb-unread')) {
                const fd = new FormData();
                fd.append('csrf_token', CSRF_TOKEN);
                fd.append('notification_id', notifId);
                try {
                    await fetch(`${BASE}/api/notifications/read`, { method: 'POST', body: fd });
                } catch (e) { /* ignore */ }
            }

            if (link) window.location.href = link;
        });
    });

    // ===== Mark All Read =====
    const markAllBtn = document.getElementById('nbMarkAllRead');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', async (e) => {
            e.stopPropagation();
            markAllBtn.disabled = true;
            markAllBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Memproses...';

            const fd = new FormData();
            fd.append('csrf_token', CSRF_TOKEN);

            try {
                const res = await fetch(`${BASE}/api/notifications/read-all`, { method: 'POST', body: fd });
                const data = await res.json();
                if (data.success) {
                    document.querySelectorAll('.nb-notif-item.nb-unread').forEach(i => i.classList.remove('nb-unread'));
                    const badge = document.getElementById('nbNotifBadge');
                    if (badge) badge.remove();
                    markAllBtn.innerHTML = '<i class="bi bi-check2"></i> Selesai!';
                    setTimeout(() => markAllBtn.remove(), 1500);
                }
            } catch (err) {
                markAllBtn.disabled = false;
                markAllBtn.innerHTML = '<i class="bi bi-check2-all"></i> Tandai semua dibaca';
            }
        });
    }

    // ===== Real-time Polling Notifikasi (30 detik) =====
    let lastCount = <?= (int)$unreadCount ?>;

    const pollNotifCount = async () => {
        try {
            const res = await fetch(`${BASE}/api/notifications/count`);
            const data = await res.json();
            if (!data.success) return;

            const newCount = data.count;
            const badge = document.getElementById('nbNotifBadge');
            const btn = document.getElementById('nbNotifBtn');

            if (newCount !== lastCount) {
                if (newCount > lastCount && newCount > 0) {
                    if (badge) {
                        badge.textContent = newCount > 99 ? '99+' : newCount;
                        badge.classList.add('nb-badge-new');
                        setTimeout(() => badge.classList.remove('nb-badge-new'), 600);
                    } else {
                        const newBadge = document.createElement('span');
                        newBadge.id = 'nbNotifBadge';
                        newBadge.className = 'nb-badge nb-badge-new';
                        newBadge.textContent = newCount > 99 ? '99+' : newCount;
                        btn.appendChild(newBadge);
                        setTimeout(() => newBadge.classList.remove('nb-badge-new'), 600);
                    }
                } else if (newCount === 0) {
                    if (badge) badge.remove();
                } else {
                    if (badge) badge.textContent = newCount > 99 ? '99+' : newCount;
                }
                lastCount = newCount;
            }
        } catch (e) {
            // Silent fail
        }
    };

    let pollInterval = null;
    const startPolling = () => {
        if (pollInterval) return;
        pollInterval = setInterval(pollNotifCount, 30000);
    };
    const stopPolling = () => {
        if (pollInterval) {
            clearInterval(pollInterval);
            pollInterval = null;
        }
    };

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) stopPolling();
        else { startPolling(); pollNotifCount(); }
    });

    startPolling();

    console.log('%c✓ Navbar SIMBITA Loaded (Notifications + Dark Mode)', 'color: #6366f1; font-weight: bold;');
})();
</script>