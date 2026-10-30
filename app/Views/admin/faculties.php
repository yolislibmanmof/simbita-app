<?php
declare(strict_types=1);
$flashError = $_SESSION['flash_error'] ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);
$total = count($faculties ?? []);
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Data Fakultas - <?= e(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/darkmode.css" rel="stylesheet">
    <style>
        .fc-view { animation: fcFade 0.4s ease; }
        @keyframes fcFade { from { opacity: 0; } to { opacity: 1; } }
        .fc-header { display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; margin-bottom: 2rem; }
        .fc-header-title { display: flex; align-items: center; gap: 0.75rem; font-size: 1.875rem; font-weight: 800; color: var(--gray-900); margin: 0 0 0.35rem; }
        .fc-header-icon { width: 48px; height: 48px; background: linear-gradient(135deg,#fce7f3,#fbcfe8); border-radius: var(--radius-xl); display: flex; align-items: center; justify-content: center; color: #be185d; font-size: 1.5rem; }
        .fc-header-sub { color: var(--gray-500); font-size: 0.95rem; margin: 0; }
        .fc-add-btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: linear-gradient(135deg,#ec4899,#be185d); color: white; border-radius: var(--radius-lg); font-weight: 600; text-decoration: none; box-shadow: 0 10px 20px -8px rgba(236,72,153,0.5); transition: all 0.3s; border: none; cursor: pointer; font-family: inherit; font-size: 0.9rem; }
        .fc-add-btn:hover { transform: translateY(-3px); box-shadow: 0 15px 25px -8px rgba(236,72,153,0.6); color: white; }
        .fc-card { background: white; border: 1px solid var(--gray-200); border-radius: var(--radius-2xl); overflow: hidden; }
        .fc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1rem; padding: 1.5rem; }
        .fc-item { background: white; border: 1.5px solid var(--gray-200); border-radius: var(--radius-xl); padding: 1.25rem; transition: all 0.25s; animation: fcUp 0.5s ease backwards; }
        .fc-item:hover { transform: translateY(-4px); border-color: #f9a8d4; box-shadow: 0 15px 30px -12px rgba(236,72,153,0.25); }
        @keyframes fcUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
        .fc-item-head { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.85rem; }
        .fc-code { font-family: 'JetBrains Mono', monospace; background: #fce7f3; color: #be185d; padding: 0.25rem 0.6rem; border-radius: var(--radius-md); font-size: 0.78rem; font-weight: 700; }
        .fc-actions { display: flex; gap: 0.4rem; }
        .fc-icon-btn { width: 32px; height: 32px; border-radius: var(--radius-md); display: inline-flex; align-items: center; justify-content: center; border: 1.5px solid var(--gray-200); background: white; color: var(--gray-600); cursor: pointer; transition: all 0.2s; text-decoration: none; font-size: 0.85rem; }
        .fc-icon-btn:hover { transform: translateY(-2px); }
        .fc-icon-edit:hover { background: #fef3c7; border-color: #fcd34d; color: #b45309; }
        .fc-icon-del:hover { background: #fee2e2; border-color: #fca5a5; color: #b91c1c; }
        .fc-item-name { font-size: 1.05rem; font-weight: 700; color: var(--gray-900); margin-bottom: 0.75rem; }
        .fc-item-stat { display: flex; align-items: center; gap: 0.4rem; font-size: 0.8rem; color: var(--gray-500); }
        .fc-item-stat i { color: #ec4899; }
        .fc-empty { text-align: center; padding: 4rem 2rem; }
        .fc-empty-icon { width: 90px; height: 90px; margin: 0 auto 1.25rem; border-radius: 50%; background: linear-gradient(135deg,#fce7f3,#fbcfe8); display: flex; align-items: center; justify-content: center; font-size: 2.5rem; color: #be185d; }
        .fc-modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.55); backdrop-filter: blur(4px); display: none; align-items: center; justify-content: center; z-index: 1000; padding: 1rem; }
        .fc-modal-overlay.is-open { display: flex; }
        .fc-modal { background: white; border-radius: var(--radius-2xl); max-width: 460px; width: 100%; padding: 2rem; animation: fcUp 0.35s ease; }
        .fc-modal h3 { font-size: 1.2rem; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem; }
        .fc-modal h3 i { color: #ec4899; }
        .fc-field { margin-bottom: 1rem; }
        .fc-label { display: block; font-size: 0.8rem; font-weight: 600; color: var(--gray-700); margin-bottom: 0.4rem; }
        .fc-input { width: 100%; padding: 0.65rem 0.9rem; border: 1.5px solid var(--gray-300); border-radius: var(--radius-md); font-size: 0.9rem; font-family: inherit; transition: all 0.25s; }
        .fc-input:focus { outline: none; border-color: #ec4899; box-shadow: 0 0 0 4px rgba(236,72,153,0.12); }
        .fc-modal-actions { display: flex; gap: 0.75rem; margin-top: 1.5rem; }
        .fc-btn { flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.8rem 1rem; border-radius: var(--radius-lg); font-weight: 600; font-size: 0.9rem; border: none; cursor: pointer; transition: all 0.25s; font-family: inherit; }
        .fc-btn-cancel { background: var(--gray-100); color: var(--gray-700); }
        .fc-btn-cancel:hover { background: var(--gray-200); }
        .fc-btn-primary { background: linear-gradient(135deg,#ec4899,#be185d); color: white; box-shadow: 0 8px 16px -6px rgba(236,72,153,0.5); }
        .fc-btn-primary:hover { transform: translateY(-2px); }
    </style>
</head>
<body>
   <?php require dirname(__DIR__) . '/partials/navbar.php'; ?>

    <div class="container fc-view">
        <div class="fc-header">
            <div>
                <h1 class="fc-header-title">
                    <span class="fc-header-icon"><i class="bi bi-building"></i></span>
                    Data Fakultas
                </h1>
                <p class="fc-header-sub">Kelola struktur fakultas dan unit akademik universitas.</p>
            </div>
            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <a href="<?= BASE_URL ?>/admin/study-programs" class="fc-add-btn" style="background: linear-gradient(135deg,#06b6d4,#0891b2); box-shadow: 0 10px 20px -8px rgba(6,182,212,0.5);">
                    <i class="bi bi-mortarboard-fill"></i> Kelola Prodi
                </a>
                <button type="button" class="fc-add-btn" id="fcAddBtn">
                    <i class="bi bi-plus-lg"></i> Tambah Fakultas
                </button>
            </div>
        </div>

        <?php if ($flashError): ?>
            <div class="alert alert-danger alert-dismissible"><div class="alert-content"><i class="bi bi-exclamation-triangle-fill"></i> <?= e($flashError) ?></div><button class="btn-close">&times;</button></div>
        <?php endif; ?>
        <?php if ($flashSuccess): ?>
            <div class="alert alert-success alert-dismissible"><div class="alert-content"><i class="bi bi-check-circle-fill"></i> <?= e($flashSuccess) ?></div><button class="btn-close">&times;</button></div>
        <?php endif; ?>

        <div class="fc-card">
            <?php if ($total === 0): ?>
                <div class="fc-empty">
                    <div class="fc-empty-icon"><i class="bi bi-building"></i></div>
                    <h3>Belum Ada Fakultas</h3>
                    <p>Tambahkan fakultas pertama untuk mulai mengelola program studi.</p>
                </div>
            <?php else: ?>
                <div class="fc-grid">
                    <?php foreach ($faculties as $i => $f): ?>
                        <div class="fc-item" style="animation-delay: <?= min($i * 0.05, 0.4) ?>s">
                            <div class="fc-item-head">
                                <span class="fc-code"><?= e($f['code']) ?></span>
                                <div class="fc-actions">
                                    <button type="button" class="fc-icon-btn fc-icon-edit" title="Edit"
                                            data-edit-id="<?= $f['id'] ?>"
                                            data-edit-code="<?= e($f['code']) ?>"
                                            data-edit-name="<?= e($f['name']) ?>">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    <button type="button" class="fc-icon-btn fc-icon-del" title="Hapus"
                                            data-del-id="<?= $f['id'] ?>"
                                            data-del-name="<?= e($f['name']) ?>"
                                            data-del-count="<?= $f['prodi_count'] ?>">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="fc-item-name"><?= e($f['name']) ?></div>
                            <div class="fc-item-stat">
                                <i class="bi bi-mortarboard-fill"></i>
                                <?= $f['prodi_count'] ?> Program Studi
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Form -->
    <div class="fc-modal-overlay" id="fcFormModal">
        <div class="fc-modal">
            <h3><i class="bi bi-building"></i> <span id="fcFormTitle">Tambah Fakultas</span></h3>
            <form method="POST" action="<?= BASE_URL ?>/admin/faculties/store" id="fcForm">
                <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                <input type="hidden" name="id" id="fcFormId">
                <div class="fc-field">
                    <label class="fc-label">Kode Fakultas</label>
                    <input type="text" name="code" id="fcFormCode" class="fc-input" placeholder="FTI, FH, FK..." required maxlength="20">
                </div>
                <div class="fc-field">
                    <label class="fc-label">Nama Fakultas</label>
                    <input type="text" name="name" id="fcFormName" class="fc-input" placeholder="Fakultas Teknik & Informasi" required maxlength="150">
                </div>
                <div class="fc-modal-actions">
                    <button type="button" class="fc-btn fc-btn-cancel" id="fcCancel">Batal</button>
                    <button type="submit" class="fc-btn fc-btn-primary"><i class="bi bi-save-fill"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Delete -->
    <div class="fc-modal-overlay" id="fcDelModal">
        <div class="fc-modal">
            <h3 style="color:#b91c1c;"><i class="bi bi-exclamation-triangle-fill"></i> Hapus Fakultas?</h3>
            <p id="fcDelMsg" style="color: var(--gray-600); line-height: 1.6;"></p>
            <form method="POST" action="<?= BASE_URL ?>/admin/faculties/delete" id="fcDelForm">
                <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                <input type="hidden" name="id" id="fcDelId">
                <div class="fc-modal-actions">
                    <button type="button" class="fc-btn fc-btn-cancel" id="fcDelCancel">Batal</button>
                    <button type="submit" class="fc-btn" style="background: linear-gradient(135deg,#ef4444,#dc2626); color: white;"><i class="bi bi-trash-fill"></i> Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/admin.js"></script>
    <script>
    (function() {
        const formModal = document.getElementById('fcFormModal');
        const delModal = document.getElementById('fcDelModal');
        const fcForm = document.getElementById('fcForm');
        const fcAddBtn = document.getElementById('fcAddBtn');

        // Fungsi untuk reset form dan modal dengan cara yang PASTI
        const resetForm = () => {
            fcForm.reset();
            document.getElementById('fcFormId').value = '';
            document.getElementById('fcFormCode').value = '';
            document.getElementById('fcFormName').value = '';
            document.getElementById('fcFormTitle').textContent = 'Tambah Fakultas';
            fcForm.action = '<?= BASE_URL ?>/admin/faculties/store';
        };

        // Event listener untuk tombol Tambah
        if (fcAddBtn) {
            fcAddBtn.addEventListener('click', () => {
                resetForm();
                formModal.classList.add('is-open');
                // Fokus ke input pertama setelah modal terbuka
                setTimeout(() => document.getElementById('fcFormCode').focus(), 100);
            });
        }

        // Event listener untuk tombol Cancel
        document.getElementById('fcCancel').addEventListener('click', () => {
            formModal.classList.remove('is-open');
            resetForm();
        });

        // Tutup modal jika klik di luar
        formModal.addEventListener('click', (e) => {
            if (e.target === formModal) {
                formModal.classList.remove('is-open');
                resetForm();
            }
        });

        // Event listener untuk tombol Edit
        document.querySelectorAll('[data-edit-id]').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('fcFormTitle').textContent = 'Edit Fakultas';
                document.getElementById('fcFormId').value = btn.dataset.editId;
                document.getElementById('fcFormCode').value = btn.dataset.editCode;
                document.getElementById('fcFormName').value = btn.dataset.editName;
                fcForm.action = '<?= BASE_URL ?>/admin/faculties/update';
                formModal.classList.add('is-open');
            });
        });

        // Event listener untuk tombol Delete
        document.querySelectorAll('[data-del-id]').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('fcDelId').value = btn.dataset.delId;
                const count = parseInt(btn.dataset.delCount);
                document.getElementById('fcDelMsg').innerHTML = count > 0
                    ? 'Fakultas <strong>' + btn.dataset.delName + '</strong> memiliki <strong>' + count + ' program studi</strong> dan tidak dapat dihapus. Hapus prodi terkait terlebih dahulu.'
                    : 'Hapus fakultas <strong>' + btn.dataset.delName + '</strong>? Tindakan ini tidak dapat dibatalkan.';
                delModal.classList.add('is-open');
            });
        });

        document.getElementById('fcDelCancel').addEventListener('click', () => delModal.classList.remove('is-open'));

        // Auto-dismiss alert
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