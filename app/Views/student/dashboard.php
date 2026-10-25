<?php
declare(strict_types=1);
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mahasiswa Dashboard - <?= e(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php require dirname(__DIR__) . '/partials/navbar.php'; ?>

    <div class="container-fluid mt-4">
        <?php if ($flashError): ?>
            <div class="alert alert-danger"><?= e($flashError) ?></div>
        <?php endif; ?>

        <h1 class="mb-4">Dashboard Mahasiswa</h1>

        <?php if (!$myWork): ?>
            <div class="alert alert-info">
                <h5><i class="bi bi-info-circle"></i> Belum Ada Tugas Akhir</h5>
                <p>Anda belum memiliki tugas akhir yang terdaftar. Silakan hubungi administrator untuk memulai.</p>
            </div>
        <?php else: ?>
            <div class="row">
                <!-- Academic Work Info -->
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Tugas Akhir Saya</h5>
                        </div>
                        <div class="card-body">
                            <h4><?= e($myWork['title']) ?></h4>
                            <p class="text-muted"><?= e($myWork['work_type_name']) ?></p>
                            
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <p><strong>Status:</strong> 
                                        <span class="badge bg-info"><?= e($myWork['status']) ?></span>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Mulai:</strong> <?= date('d/m/Y', strtotime($myWork['started_at'])) ?></p>
                                </div>
                            </div>

                            <?php if ($myWork['abstract']): ?>
                            <hr>
                            <h6>Abstrak:</h6>
                            <p class="small"><?= e($myWork['abstract']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Documents -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-folder"></i> Dokumen Saya</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($myDocuments)): ?>
                                <p class="text-muted">Belum ada dokumen yang diunggah.</p>
                            <?php else: ?>
                                <div class="list-group">
                                    <?php foreach ($myDocuments as $doc): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><?= e($doc['type_name']) ?></h6>
                                                <p class="mb-1 small">
                                                    Versi <?= $doc['version_number'] ?>: 
                                                    <?= e($doc['original_filename']) ?>
                                                </p>
                                                <small class="text-muted">
                                                    <i class="bi bi-clock"></i> 
                                                    <?= date('d/m/Y H:i', strtotime($doc['uploaded_at'])) ?>
                                                </small>
                                            </div>
                                            <a href="<?= BASE_URL ?>/documents/download?id=<?= $doc['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-download"></i> Unduh
                                            </a>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Milestones -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-flag"></i> Milestone</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($myMilestones)): ?>
                                <p class="text-muted">Belum ada milestone yang ditetapkan.</p>
                            <?php else: ?>
                                <div class="list-group">
                                    <?php foreach ($myMilestones as $milestone): ?>
                                    <div class="list-group-item <?= $milestone['completed_at'] ? 'list-group-item-success' : '' ?>">
                                        <h6 class="mb-1"><?= e($milestone['title']) ?></h6>
                                        <p class="mb-1 small"><?= e($milestone['description']) ?></p>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar"></i> Deadline: 
                                            <?= date('d/m/Y', strtotime($milestone['due_date'])) ?>
                                        </small>
                                        <?php if ($milestone['completed_at']): ?>
                                            <br>
                                            <small class="text-success">
                                                <i class="bi bi-check-circle"></i> 
                                                Selesai: <?= date('d/m/Y', strtotime($milestone['completed_at'])) ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>