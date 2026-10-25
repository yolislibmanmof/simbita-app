<?php
declare(strict_types=1);

/**
 * =====================================================================
 * NOTIFICATION DROPDOWN HELPER
 * =====================================================================
 * Deprecated: Gunakan navbar.php yang sudah integrated.
 * Helper ini disediakan untuk backward compatibility.
 */

/**
 * Render notification dropdown HTML
 * 
 * @param int $limit Jumlah notifikasi yang ditampilkan
 * @param string $theme 'light' atau 'dark'
 * @return string HTML output
 */
function notificationDropdown(int $limit = 5, string $theme = 'light'): string
{
    if (!Auth::check()) {
        return '';
    }

    $userId = Auth::id();
    if ($userId === null) return '';

    // Ambil data dengan fallback aman
    $notifications = [];
    $unread = 0;
    try {
        if (class_exists(Notification::class)) {
            $notifications = Notification::latest($userId, $limit);
            $unread = Notification::unreadCount($userId);
        }
    } catch (Throwable $e) {
        error_log('Notification dropdown error: ' . $e->getMessage());
    }

    ob_start();
    ?>
    <div class="dropdown" style="position: relative;">
        <button class="btn btn-light btn-sm position-relative" onclick="toggleDropdown('notifDropdownHelper')" type="button">
            <i class="bi bi-bell"></i> Notifikasi
            <?php if ($unread > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?= e((string) min($unread, 99)) ?><?= $unread > 99 ? '+' : '' ?>
                </span>
            <?php endif; ?>
        </button>

        <div id="notifDropdownHelper" class="dropdown-menu dropdown-menu-end p-3" 
             style="display: none; position: absolute; right: 0; top: 120%; width: 340px; 
                    background: white; border-radius: 12px; box-shadow: 0 20px 40px -10px rgba(0,0,0,0.15);
                    border: 1px solid #e5e7eb; z-index: 1000;">
            <div class="d-flex justify-content-between align-items-center mb-2 pb-2" style="border-bottom: 1px solid #e5e7eb;">
                <strong style="font-size: 0.95rem;">Notifikasi</strong>
                <a href="<?= e(url('/notifications')) ?>" class="small" style="color: #6366f1; text-decoration: none;">Semua</a>
            </div>

            <?php if (empty($notifications)): ?>
                <p class="text-muted small mb-0 text-center py-3">
                    <i class="bi bi-bell-slash" style="font-size: 1.5rem; opacity: 0.3; display: block; margin-bottom: 0.5rem;"></i>
                    Belum ada notifikasi.
                </p>
            <?php else: ?>
                <div style="max-height: 320px; overflow-y: auto;">
                    <?php foreach ($notifications as $item): ?>
                        <div style="padding: 0.65rem; border-radius: 8px; margin-bottom: 0.4rem; 
                                    background: <?= $item['is_read'] ? 'transparent' : '#eef2ff' ?>;
                                    transition: background 0.2s;"
                             onmouseover="this.style.background='#f3f4f6'"
                             onmouseout="this.style.background='<?= $item['is_read'] ? 'transparent' : '#eef2ff' ?>'">
                            <strong class="small d-block mb-1" style="color: #111827;">
                                <?= e($item['title']) ?>
                            </strong>
                            <p class="small mb-1" style="color: #6b7280; line-height: 1.4;">
                                <?= e(truncate($item['message'], 80)) ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <small class="text-muted" style="font-size: 0.7rem;">
                                    <?= e(time_ago($item['created_at'])) ?>
                                </small>
                                <?php if (!empty($item['link'])): ?>
                                    <a class="btn btn-sm btn-outline-primary" 
                                       style="font-size: 0.7rem; padding: 0.15rem 0.5rem;"
                                       href="<?= e(url($item['link'])) ?>">Buka</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    (function() {
        if (window.__nbDropdownInitialized) return;
        window.__nbDropdownInitialized = true;

        window.toggleDropdown = function(id) {
            const el = document.getElementById(id);
            if (!el) return;
            const isHidden = el.style.display === 'none' || el.style.display === '';
            document.querySelectorAll('.dropdown-menu').forEach(d => {
                if (d.id !== id) d.style.display = 'none';
            });
            el.style.display = isHidden ? 'block' : 'none';
        };

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-menu').forEach(d => {
                    d.style.display = 'none';
                });
            }
        });
    })();
    </script>
    <?php

    return ob_get_clean();
}

/**
 * Kirim notifikasi shortcut
 */
function notify(int $userId, string $title, string $message, ?string $link = null): bool
{
    if (!class_exists(Notification::class)) return false;
    try {
        return Notification::create($userId, $title, $message, $link) > 0;
    } catch (Throwable $e) {
        return false;
    }
}

/**
 * Broadcast notifikasi ke banyak user
 */
function notify_all(array $userIds, string $title, string $message, ?string $link = null): int
{
    if (!class_exists(Notification::class)) return 0;
    try {
        return Notification::broadcast($userIds, $title, $message, $link);
    } catch (Throwable $e) {
        return 0;
    }
}