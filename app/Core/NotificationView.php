<?php
declare(strict_types=1);

function notificationDropdown(): string
{
    if (!Auth::check()) {
        return '';
    }

    $notifications = Notification::latest(Auth::id(), 5);
    $unread = Notification::unreadCount(Auth::id());

    ob_start();
    ?>
    <div class="dropdown">
      <button class="btn btn-light btn-sm position-relative" data-bs-toggle="dropdown">
        Notifikasi
        <?php if ($unread > 0): ?>
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
            <?= $unread ?>
          </span>
        <?php endif; ?>
      </button>

      <div class="dropdown-menu dropdown-menu-end p-3" style="width: 340px;">
        <div class="d-flex justify-content-between mb-2">
          <strong>Notifikasi</strong>
          <a href="<?= e(BASE_URL) ?>/notifications.php" class="small">Semua</a>
        </div>

        <?php if (!$notifications): ?>
          <p class="text-muted small mb-0">Belum ada notifikasi.</p>
        <?php else: ?>
          <?php foreach ($notifications as $item): ?>
            <div class="border rounded p-2 mb-2 <?= $item['is_read'] ? '' : 'bg-light' ?>">
              <strong class="small d-block"><?= e($item['title']) ?></strong>
              <p class="small mb-1"><?= e($item['message']) ?></p>
              <?php if ($item['link']): ?>
                <a class="btn btn-sm btn-outline-primary"
                   href="<?= e(BASE_URL . $item['link']) ?>">Buka</a>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
    <?php

    return ob_get_clean();
}