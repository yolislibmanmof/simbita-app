<?php
declare(strict_types=1);

Auth::start();

if (!Auth::check()) {
    redirect('/login.php');
}

$notifications = Notification::latest(Auth::id(), 20);
$unreadCount = Notification::unreadCount(Auth::id());
?>
<div class="dropdown">
  <button class="btn btn-light btn-sm position-relative" data-bs-toggle="dropdown">
    Notifikasi
    <?php if ($unreadCount > 0): ?>
      <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
        <?= $unreadCount ?>
      </span>
    <?php endif; ?>
  </button>

  <div class="dropdown-menu dropdown-menu-end p-3" style="width: 360px; max-height: 420px; overflow-y: auto;">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <strong>Notifikasi</strong>
      <a href="<?= e(BASE_URL) ?>/notifications.php" class="small">Lihat semua</a>
    </div>

    <?php if (!$notifications): ?>
      <p class="text-muted small mb-0">Belum ada notifikasi.</p>
    <?php else: ?>
      <?php foreach ($notifications as $notification): ?>
        <div class="border rounded p-2 mb-2 <?= $notification['is_read'] ? '' : 'bg-light' ?>">
          <div class="d-flex justify-content-between">
            <strong class="small"><?= e($notification['title']) ?></strong>
            <small class="text-muted"><?= e($notification['created_at']) ?></small>
          </div>
          <p class="small mb-1"><?= e($notification['message']) ?></p>

          <?php if ($notification['link']): ?>
            <a class="btn btn-sm btn-outline-primary"
               href="<?= e(BASE_URL . $notification['link']) ?>">
              Buka
            </a>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>