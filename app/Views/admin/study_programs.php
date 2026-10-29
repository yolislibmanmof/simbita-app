<?php
declare(strict_types=1);
$flashError = $_SESSION['flash_error'] ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);
$total = count($prodis);
$degreeLabels = ['s1' => 'Sarjana (S1)', 's2' => 'Magister (S2)', 's3' => 'Doktor (S3)'];
$degreeColors = ['s1' => '#3b82f6', 's2' => '#f59e0b', 's3' => '#ec4899'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Program Studi - <?= e(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">
    <style>
        .sp-view { animation: spFade 0.4s ease; }
        @keyframes spFade { from { opacity: 0; } to { opacity: 1; } }
        .sp-header { display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; margin-bottom: 2rem; }
        .sp-header-title { display: flex; align-items: center; gap: 0.75rem; font-size: 1.875rem; font-weight: 800; color: var(--gray-900); margin: 0 0 0.35rem; }
        .sp-header-icon { width: 48px; height: 48px; background: linear-gradient(135deg,#cffafe,#a5f3fc); border-radius: var(--radius-xl); display: flex; align-items: center; justify-content: center; color: #0891b2; font-size: 1.5rem; }
        .sp-header-sub { color: var(--gray-500); font-size: 0.95rem; margin: 0 0 0 calc(48px + 0.75rem); }
        .sp-add-btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: linear-gradient(135deg,#06b6d4,#0891b2); color: white; border-radius: var(--radius-lg); font-weight: 600; text-decoration: none; box-shadow: 0 10px 20px -8px rgba(6,182,212,0.5); transition: all 0.3s; }
        .sp-add-btn:hover { transform: translateY(-3px); color: white; }
        .sp-card { background: white; border: 1px solid var(--gray-200); border-radius: var(--radius-2xl); overflow: hidden; }
        .sp-table-wrap { overflow-x: auto; }
        .sp-table { width: 100%; border-collapse: collapse; min-width: 900px; }
        .sp-table thead th { padding: 0.9rem 1.5rem; text-align: left; font-size: 0.72rem; font-weight: 700; color: var(--gray-500); text-transform: uppercase; letter-spacing: 0.06em; background: var(--gray-50); border-bottom: 1px solid var(--gray-200); }
        .sp-table tbody tr { border-bottom: 1px solid var(--gray-100); transition: all 0.25s; animation: spUp 0.45s ease backwards; }
        .sp-table tbody tr:last-child { border-bottom: none; }
        .sp-table tbody tr:hover { background: #ecfeff; }
        .sp-table td { padding: 1rem 1.5rem; vertical-align: middle; }
        @keyframes spUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
        .sp-name { font-weight: 700; color: var(--gray-900); font-size: 0.95rem; }
        .sp-faculty { color: var(--gray-500); font-size: 0.8rem; }
        .sp-code { font-family: 'JetBrains Mono', monospace; background: #cffafe; color: #155e75; padding: 0.25rem 0.6rem; border-radius: var(--radius-md); font-size: 0.78rem; font-weight: 700; }
        .sp-degree { display: inline-flex; align-items: center; padding: 0.28rem 0.75rem; border-radius: var(--radius-full); font-size: 0.72rem; font-weight: 700; color: white; }
        .sp-stat { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.82rem; color: var(--gray-600); font-weight: 600; }
        .sp-stat i { color: #0891b2; }
        .sp-actions { display: flex; gap: 0.5rem; justify-content: center; }
        .sp-icon-btn { width: 36px; height: 36px; border-radius: var(--radius-md); display: inline-flex; align-items: center; justify-content: center; border: 1.5px solid var(--gray-200); background: white; color: var(--gray-600); cursor: pointer; transition: all 0.2s; text-decoration: none; }
        .sp-icon-btn:hover { transform: translateY(-2px); }
        .sp-icon-edit:hover { background: #fef3c7; border-color: #fcd34d; color: #b45309; }
        .sp-icon-del:hover { background: #fee2e2; border-color: #fca5a5; color: #b91c1c; }
        .sp-empty { text-align: center; padding: 4rem 2rem; color: var(--gray-500); }
        .sp-empty-icon { font-size: 3rem; opacity: 0.4; margin-bottom: 1rem; }
        .sp-modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.55); backdrop-filter: blur(4px); display: none; align-items: center; justify-content: center; z-index: 1000; padding: 1rem; }
        .sp-modal-overlay.is-open { display: flex; }
        .sp-modal { background: white; border-radius: var(--radius-2xl); max-width: 500px; width: 100%; padding: 2rem; animation: spUp 0.35s ease; }
        .sp-modal h3 { font-size: 1.2rem; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem; }
        .sp-modal h3 i { color: #0891b2; }
        .sp-field { margin-bottom: 1rem; }
        .sp-label { display: block; font-size: 0.8rem; font-weight: 600; color: var(--gray-700); margin-bottom: 0.4rem; }
        .sp-input, .sp-select { width: 100%; padding: 0.65rem 0.9rem; border: 1.5px solid var(--gray-300); border-radius: var(--radius-md); font-size: 0.9rem; font-family: inherit; transition: all 0.25s; background: white; }
        .sp-input:focus, .sp-select:focus { outline: none; border-color: #0891b2; box-shadow: 0 0 0 4px rgba(6,182,212,0.12); }
        .sp-modal-actions { display: flex; gap: 0.75rem; margin-top: 1.5rem; }
        .sp-btn { flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.8rem 1rem; border-radius: var(--radius-lg); font-weight: 600; font-size: 0.9rem; border: none; cursor: pointer; transition: all 0.25s; font-family: inherit; }
        .sp-btn-cancel { background: var(--gray-100); color: var(--gray-700); }
        .sp-btn-cancel:hover { background: var(--gray-200); }
        .sp-btn-primary { background: linear-gradient(135deg,#06b6d4,#0891b2); color: white; box-shadow: 0 8px 16px -6px rgba(6,182,212,0.5); }
        .sp-btn-primary:hover { transform: translateY(-2px); }
    </style>
</head>
<body>
    <?php require dirname(__DIR__) . '/partials/navbar.php'; ?>
    <div class="container sp-view">
        <div class="sp-header">
            <div>
                <h1 class="sp-header-title">
                    <span class="sp-header-icon"><i class="bi bi-mortarboard-fill"></i></span>
                    Data Program Studi
                </h1>
                <p class="sp-header-sub">Kelola program studi beserta jenjang dan afiliasinya.</p>
            </div>
            <button type="button" class="sp-add-btn" id="spAddBtn">
                <i class="bi bi-plus-lg"></i> Tambah Prodi
            </button>
        </div>

        <?php if ($flashError): ?>
            <div class="alert alert-danger alert-dismissible"><div class="alert-content"><i class="bi bi-exclamation-triangle-fill"></i> <?= e($flashError) ?></div><button class="btn-close">&times;</button></div>
        <?php endif; ?>
        <?php if ($flashSuccess): ?>
            <div class="alert alert-success alert-dismissible"><div class="alert-content"><i class="bi bi-check-circle-fill"></i> <?= e($flashSuccess) ?></div><button class="btn-close">&times;</button></div>
        <?php endif; ?>

        <div class="sp-card">
            <?php if ($total === 0): ?>
                <div class="sp-empty">
                    <div class="sp-empty-icon"><i class="bi bi-mortarboard"></i></div>
                    <h3>Belum Ada Program Studi</h3>
                    <p>Tambahkan fakultas terlebih dahulu, lalu buat program studi.</p>
                </div>
            <?php else: ?>
                <div class="sp-table-wrap">
                    <table class="sp-table">
                        <thead>
                            <tr>
                                <th>Program Studi</th>
                                <th>Kode</th>
                                <th>Jenjang</th>
                                <th style="text-align:center;">Mahasiswa</th>
                                <th style="text-align:center;">Dosen</th>
                                <th style="text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($prodis as $i => $p): ?>
                                <tr style="animation-delay: <?= min($i * 0.04, 0.4) ?>s">
                                    <td>
                                        <div class="sp-name"><?= e($p['name']) ?></div>
                                        <div class="sp-faculty"><?= e($p['faculty_name']) ?></div>
                                    </td>
                                    <td><span class="sp-code"><?= e($p['code']) ?></span></td>
                                    <td>
                                        <span class="sp-degree" style="background: <?= $degreeColors[$p['degree_level']] ?? '#6b7280' ?>;">
                                            <?= e($degreeLabels[$p['degree_level']] ?? strtoupper($p['degree_level'])) ?>
                                        </span>
                                    </td>
                                    <td style="text-align:center;">
                                        <span class="sp-stat"><i class="bi bi-people-fill"></i> <?= $p['student_count'] ?></span>
                                    </td>
                                    <td style="text-align:center;">
                                        <span class="sp-stat"><i class="bi bi-person-workspace"></i> <?= $p['lecturer_count'] ?></span>
                                    </td>
                                    <td>
                                        <div class="sp-actions">
                                            <button type="button" class="sp-icon-btn sp-icon-edit" title="Edit"
                                                    data-edit='<?= e(json_encode($p)) ?>'>
                                                <i class="bi bi-pencil-fill"></i>
                                            </button>
                                            <button type="button" class="sp-icon-btn sp-icon-del" title="Hapus"
                                                    data-del-id="<?= $p['id'] ?>"
                                                    data-del-name="<?= e($p['name']) ?>"
                                                    data-del-mhs="<?= $p['student_count'] ?>"
                                                    data-del-dos="<?= $p['lecturer_count'] ?>">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="sp-modal-overlay" id="spFormModal">
        <div class="sp-modal">
            <h3><i class="bi bi-mortarboard-fill"></i> <span id="spFormTitle">Tambah Program Studi</span></h3>
            <form method="POST" action="<?= BASE_URL ?>/admin/study-programs/store" id="spForm">
                <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                <input type="hidden" name="id" id="spFormId">
                <div class="sp-field">
                    <label class="sp-label">Fakultas</label>
                    <select name="faculty_id" id="spFormFaculty" class="sp-select" required>
                        <option value="">— Pilih Fakultas —</option>
                        <?php foreach ($faculties as $f): ?>
                            <option value="<?= $f['id'] ?>"><?= e($f['name']) ?> (<?= e($f['code']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="sp-field">
                    <label class="sp-label">Kode Prodi</label>
                    <input type="text" name="code" id="spFormCode" class="sp-input" required maxlength="20">
                </div>
                <div class="sp-field">
                    <label class="sp-label">Nama Program Studi</label>
                    <input type="text" name="name" id="spFormName" class="sp-input" required maxlength="150">
                </div>
                <div class="sp-field">
                    <label class="sp-label">Jenjang</label>
                    <select name="degree_level" id="spFormDegree" class="sp-select" required>
                        <option value="s1">Sarjana (S1)</option>
                        <option value="s2">Magister (S2)</option>
                        <option value="s3">Doktor (S3)</option>
                    </select>
                </div>
                <div class="sp-modal-actions">
                    <button type="button" class="sp-btn sp-btn-cancel" id="spCancel">Batal</button>
                    <button type="submit" class="sp-btn sp-btn-primary"><i class="bi bi-save-fill"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="sp-modal-overlay" id="spDelModal">
        <div class="sp-modal">
            <h3 style="color:#b91c1c;"><i class="bi bi-exclamation-triangle-fill"></i> Hapus Prodi?</h3>
            <p id="spDelMsg" style="color: var(--gray-600); line-height: 1.6;"></p>
            <form method="POST" action="<?= BASE_URL ?>/admin/study-programs/delete" id="spDelForm">
                <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                <input type="hidden" name="id" id="spDelId">
                <div class="sp-modal-actions">
                    <button type="button" class="sp-btn sp-btn-cancel" id="spDelCancel">Batal</button>
                    <button type="submit" class="sp-btn" id="spDelConfirm" style="background: linear-gradient(135deg,#ef4444,#dc2626); color: white;"><i class="bi bi-trash-fill"></i> Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/admin.js"></script>
    <script>
    (function() {
        const formModal = document.getElementById('spFormModal');
        const delModal = document.getElementById('spDelModal');
        const form = document.getElementById('spForm');

        document.getElementById('spAddBtn').addEventListener('click', () => {
            document.getElementById('spFormTitle').textContent = 'Tambah Program Studi';
            form.action = '<?= BASE_URL ?>/admin/study-programs/store';
            document.getElementById('spFormId').value = '';
            document.getElementById('spFormFaculty').value = '';
            document.getElementById('spFormCode').value = '';
            document.getElementById('spFormName').value = '';
            document.getElementById('spFormDegree').value = 's1';
            formModal.classList.add('is-open');
        });

        document.querySelectorAll('[data-edit]').forEach(btn => {
            btn.addEventListener('click', () => {
                const p = JSON.parse(btn.dataset.edit);
                document.getElementById('spFormTitle').textContent = 'Edit Program Studi';
                form.action = '<?= BASE_URL ?>/admin/study-programs/update';
                document.getElementById('spFormId').value = p.id;
                document.getElementById('spFormFaculty').value = p.faculty_id;
                document.getElementById('spFormCode').value = p.code;
                document.getElementById('spFormName').value = p.name;
                document.getElementById('spFormDegree').value = p.degree_level;
                formModal.classList.add('is-open');
            });
        });

        document.getElementById('spCancel').addEventListener('click', () => formModal.classList.remove('is-open'));

        document.querySelectorAll('[data-del-id]').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('spDelId').value = btn.dataset.delId;
                const mhs = parseInt(btn.dataset.delMhs), dos = parseInt(btn.dataset.delDos);
                const blocked = mhs > 0 || dos > 0;
                document.getElementById('spDelMsg').innerHTML = blocked
                    ? 'Prodi <strong>' + btn.dataset.delName + '</strong> memiliki <strong>' + mhs + ' mahasiswa</strong> dan <strong>' + dos + ' dosen</strong>. Pindahkan mereka terlebih dahulu.'
                    : 'Hapus program studi <strong>' + btn.dataset.delName + '</strong>?';
                document.getElementById('spDelConfirm').disabled = blocked;
                delModal.classList.add('is-open');
            });
        });

        document.getElementById('spDelCancel').addEventListener('click', () => delModal.classList.remove('is-open'));

        document.querySelectorAll('.alert-dismissible .btn-close').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const a = e.target.closest('.alert');
                a.style.transition = 'all 0.3s';
                a.style.opacity = '0';
                setTimeout(() => a.remove(), 300);
            });
        });
    })();
    </script>
</body>
</html>