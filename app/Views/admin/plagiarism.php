<?php
declare(strict_types=1);
$flashError = $_SESSION['flash_error'] ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);
$passed = count(array_filter($rows, fn($r) => $r['status'] === 'passed'));
$failed = count(array_filter($rows, fn($r) => $r['status'] === 'failed'));
$none   = count(array_filter($rows, fn($r) => $r['status'] === 'none'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cek Plagiasi - <?= e(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">
    <style>
        .pg-view{animation:pgFade .4s ease}@keyframes pgFade{from{opacity:0}to{opacity:1}}
        .pg-header{margin-bottom:2rem}
        .pg-header-title{display:flex;align-items:center;gap:.75rem;font-size:1.875rem;font-weight:800;color:var(--gray-900);margin:0 0 .35rem}
        .pg-header-icon{width:48px;height:48px;background:linear-gradient(135deg,#ffedd5,#fed7aa);border-radius:var(--radius-xl);display:flex;align-items:center;justify-content:center;color:#c2410c;font-size:1.5rem}
        .pg-header-sub{color:var(--gray-500);font-size:.95rem;margin:0 0 0 calc(48px + .75rem)}
        .pg-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:1.75rem}
        .pg-stat{background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius-xl);padding:1.15rem 1.25rem;display:flex;align-items:center;gap:.85rem}
        .pg-stat-icon{width:44px;height:44px;border-radius:var(--radius-lg);display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:#fff}
        .pg-stat-icon.i1{background:linear-gradient(135deg,#10b981,#059669)}.pg-stat-icon.i2{background:linear-gradient(135deg,#ef4444,#dc2626)}.pg-stat-icon.i3{background:linear-gradient(135deg,#94a3b8,#64748b)}
        .pg-stat-num{font-size:1.5rem;font-weight:800;color:var(--gray-900)}
        .pg-stat-label{font-size:.72rem;color:var(--gray-500);font-weight:600;text-transform:uppercase}
        .pg-card{background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius-2xl);overflow:hidden}
        .pg-table-wrap{overflow-x:auto}
        .pg-table{width:100%;border-collapse:collapse;min-width:1000px}
        .pg-table thead th{padding:.9rem 1.5rem;text-align:left;font-size:.72rem;font-weight:700;color:var(--gray-500);text-transform:uppercase;background:var(--gray-50);border-bottom:1px solid var(--gray-200)}
        .pg-table tbody tr{border-bottom:1px solid var(--gray-100)}
        .pg-table tbody tr:hover{background:#fff7ed}
        .pg-table td{padding:1rem 1.5rem;vertical-align:middle}
        .pg-title{font-weight:700;color:var(--gray-900);font-size:.92rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
        .pg-sub{font-size:.78rem;color:var(--gray-500)}
        .pg-pill{display:inline-flex;align-items:center;gap:.4rem;padding:.35rem .85rem;border-radius:999px;font-size:.72rem;font-weight:800;text-transform:uppercase}
        .pg-pill::before{content:'';width:7px;height:7px;border-radius:50%;background:currentColor}
        .pg-pill-passed{background:#d1fae5;color:#065f46}
        .pg-pill-failed{background:#fee2e2;color:#991b1b}
        .pg-pill-none{background:var(--gray-100);color:var(--gray-600)}
        .pg-score{font-family:'JetBrains Mono',monospace;font-weight:700;font-size:.95rem}
        .pg-btn{padding:.45rem .95rem;border-radius:var(--radius-md);border:none;background:linear-gradient(135deg,#f97316,#c2410c);color:#fff;font-weight:700;font-size:.78rem;cursor:pointer;font-family:inherit;transition:all .2s}
        .pg-btn:hover{transform:translateY(-2px)}
        .pg-empty{text-align:center;padding:4rem 2rem;color:var(--gray-500)}
        .pg-modal-overlay{position:fixed;inset:0;background:rgba(15,23,42,.55);backdrop-filter:blur(4px);display:none;align-items:center;justify-content:center;z-index:1000;padding:1rem}
        .pg-modal-overlay.is-open{display:flex}
        .pg-modal{background:#fff;border-radius:var(--radius-2xl);max-width:480px;width:100%;padding:2rem}
        .pg-modal h3{font-size:1.2rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.5rem}
        .pg-modal h3 i{color:#c2410c}
        .pg-field{margin-bottom:1rem}
        .pg-label{display:block;font-size:.8rem;font-weight:600;color:var(--gray-700);margin-bottom:.4rem}
        .pg-input,.pg-select{width:100%;padding:.65rem .9rem;border:1.5px solid var(--gray-300);border-radius:var(--radius-md);font-size:.9rem;font-family:inherit;background:#fff}
        .pg-input:focus,.pg-select:focus{outline:none;border-color:#f97316;box-shadow:0 0 0 4px rgba(249,115,22,.12)}
        .pg-modal-actions{display:flex;gap:.75rem;margin-top:1.5rem}
        .pg-btn-cancel{flex:1;padding:.8rem;border-radius:var(--radius-lg);background:var(--gray-100);color:var(--gray-700);border:none;font-weight:600;cursor:pointer;font-family:inherit}
        .pg-btn-save{flex:1;padding:.8rem;border-radius:var(--radius-lg);background:linear-gradient(135deg,#f97316,#c2410c);color:#fff;border:none;font-weight:700;cursor:pointer;font-family:inherit}
    </style>
</head>
<body>
    <?php require dirname(__DIR__) . '/partials/navbar.php'; ?>
    <div class="container pg-view">
        <div class="pg-header">
            <h1 class="pg-header-title"><span class="pg-header-icon"><i class="bi bi-clipboard2-pulse"></i></span> Cek Plagiasi</h1>
            <p class="pg-header-sub">Catat hasil pemeriksaan similarity dari Turnitin atau penyedia lain. Ambang batas saat ini: <strong><?= e(number_format($threshold, 2)) ?>%</strong>.</p>
        </div>

        <?php if ($flashError): ?><div class="alert alert-danger alert-dismissible"><div class="alert-content"><i class="bi bi-exclamation-triangle-fill"></i> <?= e($flashError) ?></div><button class="btn-close">&times;</button></div><?php endif; ?>
        <?php if ($flashSuccess): ?><div class="alert alert-success alert-dismissible"><div class="alert-content"><i class="bi bi-check-circle-fill"></i> <?= e($flashSuccess) ?></div><button class="btn-close">&times;</button></div><?php endif; ?>

        <div class="pg-stats">
            <div class="pg-stat"><div class="pg-stat-icon i1"><i class="bi bi-check-circle-fill"></i></div><div><div class="pg-stat-num"><?= $passed ?></div><div class="pg-stat-label">Lolos</div></div></div>
            <div class="pg-stat"><div class="pg-stat-icon i2"><i class="bi bi-x-circle-fill"></i></div><div><div class="pg-stat-num"><?= $failed ?></div><div class="pg-stat-label">Tidak Lolos</div></div></div>
            <div class="pg-stat"><div class="pg-stat-icon i3"><i class="bi bi-hourglass-split"></i></div><div><div class="pg-stat-num"><?= $none ?></div><div class="pg-stat-label">Belum Dicek</div></div></div>
        </div>

        <div class="pg-card">
            <?php if (empty($rows)): ?>
                <div class="pg-empty">Tidak ada dokumen aktif untuk diperiksa.</div>
            <?php else: ?>
            <div class="pg-table-wrap">
                <table class="pg-table">
                    <thead><tr><th>Mahasiswa / Judul</th><th>Versi Dokumen</th><th>Skor Terakhir</th><th>Penyedia</th><th>Status</th><th style="text-align:center;">Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><div class="pg-title"><?= e($r['title']) ?></div><div class="pg-sub"><?= e($r['student_name']) ?> · <?= e($r['student_number']) ?></div></td>
                            <td><span class="pg-sub">v<?= (int)$r['version_number'] ?> · <?= e(mb_substr($r['original_filename'], 0, 28)) ?></span></td>
                            <td><?php if ($r['check']): ?><span class="pg-score" style="color:<?= $r['status'] === 'passed' ? '#059669' : '#dc2626' ?>;"><?= number_format((float)$r['check']['similarity_score'], 2) ?>%</span><?php else: ?><span class="pg-sub">—</span><?php endif; ?></td>
                            <td><span class="pg-sub"><?= e($r['check']['provider'] ?? '—') ?></span></td>
                            <td>
                                <span class="pg-pill pg-pill-<?= $r['status'] ?>">
                                    <?= $r['status'] === 'passed' ? 'Lolos' : ($r['status'] === 'failed' ? 'Tidak Lolos' : 'Belum Dicek') ?>
                                </span>
                            </td>
                            <td style="text-align:center;">
                                <button type="button" class="pg-btn" data-input='<?= e(json_encode(["version_id" => $r["version_id"], "title" => $r["title"]])) ?>'>Input Hasil</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="pg-modal-overlay" id="pgModal">
        <div class="pg-modal">
            <h3><i class="bi bi-clipboard2-pulse"></i> Input Hasil Cek Plagiasi</h3>
            <p id="pgModalTitle" style="font-size:.85rem;color:var(--gray-600);margin-bottom:1rem;"></p>
            <form method="POST" action="<?= BASE_URL ?>/admin/plagiarism/store">
                <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                <input type="hidden" name="document_version_id" id="pgVersionId">
                <div class="pg-field">
                    <label class="pg-label">Skor Similarity (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="similarity_score" class="pg-input" placeholder="cth: 18.50" required>
                </div>
                <div class="pg-field">
                    <label class="pg-label">Penyedia Layanan</label>
                    <select name="provider" class="pg-select">
                        <option>Turnitin</option><option>DrillBit</option><option>Copyleaks</option><option>PlagiarismCheckerX</option><option>Lainnya</option>
                    </select>
                </div>
                <div class="pg-field">
                    <label class="pg-label">Tautan Laporan (opsional)</label>
                    <input type="url" name="report_url" class="pg-input" placeholder="https://...">
                </div>
                <div class="pg-modal-actions">
                    <button type="button" class="pg-btn-cancel" id="pgCancel">Batal</button>
                    <button type="submit" class="pg-btn-save"><i class="bi bi-save-fill"></i> Simpan Hasil</button>
                </div>
            </form>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/admin.js"></script>
    <script>
    (function(){
        const modal = document.getElementById('pgModal');
        document.querySelectorAll('[data-input]').forEach(btn => {
            btn.addEventListener('click', () => {
                const d = JSON.parse(btn.dataset.input);
                document.getElementById('pgVersionId').value = d.version_id;
                document.getElementById('pgModalTitle').textContent = d.title;
                modal.classList.add('is-open');
            });
        });
        document.getElementById('pgCancel').addEventListener('click', () => modal.classList.remove('is-open'));
        document.querySelectorAll('.alert-dismissible .btn-close').forEach(b => b.addEventListener('click', e => { const a = e.target.closest('.alert'); a.style.transition='all .3s'; a.style.opacity='0'; setTimeout(()=>a.remove(),300); }));
    })();
    </script>
</body>
</html>