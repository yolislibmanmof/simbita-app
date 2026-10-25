<?php
declare(strict_types=1);

Auth::start();
Auth::requireRole('admin', 'lecturer', 'student');

$notifications = Notification::latest(Auth::id(), 50);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Notifikasi - <?= e(APP_NAME) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php require __DIR__ . '/partials/navbar.php'; ?>

<div class="container">
  <div class="card shadow-sm">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 mb-0">Semua Notifikasi</h1>
        <button class="btn btn-outline-primary btn-sm" id="mark-all-read">
          Tandai semua sudah dibaca
        </button>
      </div>

      <?php if (!$notifications): ?>
        <div class="alert alert-info">Belum ada notifikasi.</div>
      <?php else: ?>
        <?php foreach ($notifications as $notification): ?>
          <div class="border rounded p-3 mb-2 <?= $notification['is_read'] ? '' : 'bg-light' ?>">
            <div class="d-flex justify-content-between">
              <strong><?= e($notification['title']) ?></strong>
              <small class="text-muted"><?= e($notification['created_at']) ?></small>
            </div>
            <p class="mb-2"><?= e($notification['message']) ?></p>

            <?php if ($notification['link']): ?>
              <a class="btn btn-sm btn-primary"
                 href="<?= e(BASE_URL . $notification['link']) ?>">Buka</a>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
document.getElementById('mark-all-read').addEventListener('click', async function () {
  await fetch('<?= e(BASE_URL) ?>/api/notifications/read-all', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      csrf_token: '<?= e(Csrf::token()) ?>'
    })
  });

  location.reload();
});
</script>
</body>
</html>