<?php
declare(strict_types=1);
$flashError = $_SESSION['flash_error'] ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);
$typeLabels = ['proposal' => 'Sidang Proposal', 'closed_exam' => 'Ujian Tertutup', 'open_exam' => 'Ujian Terbuka', 'final_defense' => 'Sidang Akhir'];
$roleLabels = ['chair' => 'Ketua Sidang', 'examiner_1' => 'Penguji 1', 'examiner_2' => 'Penguji 2', 'secretary' => 'Sekretaris'];
$scheduled = count(array_filter($sessions, fn($s) => $s['status'] === 'scheduled'));
$completed = count(array_filter($sessions, fn($s) => $s['status'] === 'completed'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Sidang - <?= e(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">
    <style>
        .df-view{animation:dfFade .4s ease}@keyframes dfFade{from{opacity:0}to{opacity:1}}
        .df-header{display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap;margin-bottom:2rem}
        .df-header-title{display:flex;align-items:center;gap:.75rem;font-size:1.875rem;font-weight:800;color:var(--gray-900);margin:0 0 .35rem}
        .df-header-icon{width:48px;height:48px;background:linear-gradient(135deg,#fee2e2,#fecaca);border-radius:var(--radius-xl);display:flex;align-items:center;justify-content:center;color:#dc2626;font-size:1.5rem}
        .df-header-sub{color:var(--gray-500);font-size:.95rem;margin:0 0 0 calc(48px + .75rem)}
        .df-add-btn{display:inline-flex;align-items:center;gap:.5rem;padding:.75rem 1.5rem;background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;border-radius:var(--radius-lg);font-weight:600;border:none;cursor:pointer;font-family:inherit;font-size:.9rem;box-shadow:0 10px 20px -8px rgba(239,68,68,.5);transition:all .3s}
        .df-add-btn:hover{transform:translateY(-3px)}
        .df-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:1.75rem}
        .df-stat{background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius-xl);padding:1.15rem 1.25rem;display:flex;align-items:center;gap:.85rem}
        .df-stat-icon{width:44px;height:44px;border-radius:var(--radius-lg);display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:#fff}
        .df-stat-icon.i1{background:linear-gradient(135deg,#f59e0b,#d97706)}.df-stat-icon.i2{background:linear-gradient(135deg,#10b981,#059669)}.df-stat-icon.i3{background:linear-gradient(135deg,#6366f1,#4f46e5)}
        .df-stat-num{font-size:1.5rem;font-weight:800;color:var(--gray-900)}
        .df-stat-label{font-size:.72rem;color:var(--gray-500);font-weight:600;text-transform:uppercase}
        .df-card{background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius-2xl);overflow:hidden}
        .df-table-wrap{overflow-x:auto}
        .df-table{width:100%;border-collapse:collapse;min-width:1000px}
        .df-table thead th{padding:.9rem 1.5rem;text-align:left;font-size:.72rem;font-weight:700;color:var(--gray-500);text-transform:uppercase;background:var(--gray-50);border-bottom:1px solid var(--gray-200)}
        .df-table tbody tr{border-bottom:1px solid var(--gray-100)}
        .df-table tbody tr:hover{background:#fef2f2}
        .df-table td{padding:1rem 1.5rem;vertical-align:middle}
        .df-title{font-weight:700;color:var(--gray-900);font-size:.92rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
        .df-sub{font-size:.78rem;color:var(--gray-500)}
        .df-pill{display:inline-flex;align-items:center;gap:.4rem;padding:.35rem .85rem;border-radius:999px;font-size:.72rem;font-weight:700;text-transform:uppercase}
        .df-pill::before{content:'';width:7px;height:7px;border-radius:50%;background:currentColor}
        .df-pill-scheduled{background:#fef3c7;color:#92400e}
        .df-pill-completed{background:#d1fae5;color:#065f46}
        .df-pill-postponed{background:#fee2e2;color:#991b1b}
        .df-pill-cancelled{background:var(--gray-200);color:var(--gray-600)}
        .df-date{font-family:'JetBrains Mono',monospace;font-size:.82rem;color:var(--gray-700);font-weight:600}
        .df-grade{font-family:'JetBrains Mono',monospace;font-weight:700;color:#4f46e5;font-size:.95rem}
        .df-btn{padding:.45rem .9rem;border-radius:var(--radius-md);border:1.5px solid var(--gray-200);background:#fff;color:var(--gray-700);font-weight:600;font-size:.78rem;cursor:pointer;font-family:inherit;transition:all .2s}
        .df-btn:hover{transform:translateY(-2px)}
        .df-btn-view:hover{background:#e0e7ff;border-color:#a5b4fc;color:#4338ca}
        .df-btn-done{background:linear-gradient(135deg,#10b981,#059669);color:#fff;border:none}
        .df-btn-done:disabled{opacity:.45;cursor:not-allowed;transform:none}
        .df-empty{text-align:center;padding:4rem 2rem;color:var(--gray-500)}
        .df-modal-overlay{position:fixed;inset:0;background:rgba(15,23,42,.55);backdrop-filter:blur(4px);display:none;align-items:center;justify-content:center;z-index:1000;padding:1rem}
        .df-modal-overlay.is-open{display:flex}
        .df-modal{background:#fff;border-radius:var(--radius-2xl);max-width:560px;width:100%;padding:2rem;max-height:90vh;overflow-y:auto}
        .df-modal h3{font-size:1.2rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.5rem}
        .df-modal h3 i{color:#dc2626}
        .df-field{margin-bottom:1rem}
        .df-label{display:block;font-size:.8rem;font-weight:600;color:var(--gray-700);margin-bottom:.4rem}
        .df-input,.df-select{width:100%;padding:.65rem .9rem;border:1.5px solid var(--gray-300);border-radius:var(--radius-md);font-size:.9rem;font-family:inherit;background:#fff}
        .df-input:focus,.df-select:focus{outline:none;border-color:#ef4444;box-shadow:0 0 0 4px rgba(239,68,68,.12)}
        .df-grid2{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
        .df-modal-actions{display:flex;gap:.75rem;margin-top:1.5rem}
        .df-btn-cancel{flex:1;padding:.8rem;border-radius:var(--radius-lg);background:var(--gray-100);color:var(--gray-700);border:none;font-weight:600;cursor:pointer;font-family:inherit}
        .df-btn-save{flex:1;padding:.8rem;border-radius:var(--radius-lg);background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;border:none;font-weight:700;cursor:pointer;font-family:inherit}
        .df-exam-row{display:flex;justify-content:space-between;padding:.6rem 0;border-bottom:1px dashed var(--gray-200);font-size:.88rem}
        .df-exam-row:last-child{border-bottom:none}
        .df-exam-role{color:var(--gray-500);font-size:.75rem;font-weight:700;text-transform:uppercase}
    </style>
</head>
<body>
    <?php require dirname(__DIR__) . '/partials/navbar.php'; ?>
    <div class="container df-view">
        <div class="df-header">
            <div>
                <h1 class="df-header-title"><span class="df-header-icon"><i class="bi bi-person-video3"></i></span> Jadwal Sidang</h1>
                <p class="df-header-sub">Kelola penjadwalan sidang, penunjukan penguji, dan rekapitulasi nilai.</p>
            </div>
            <button type="button" class="df-add-btn" id="dfAddBtn"><i class="bi bi-calendar-plus"></i> Jadwalkan Sidang</button>
        </div>

        <?php if ($flashError): ?><div class="alert alert-danger alert-dismissible"><div class="alert-content"><i class="bi bi-exclamation-triangle-fill"></i> <?= e($flashError) ?></div><button class="btn-close">&times;</button></div><?php endif; ?>
        <?php if ($flashSuccess): ?><div class="alert alert-success alert-dismissible"><div class="alert-content"><i class="bi bi-check-circle-fill"></i> <?= e($flashSuccess) ?></div><button class="btn-close">&times;</button></div><?php endif; ?>

        <div class="df-stats">
            <div class="df-stat"><div class="df-stat-icon i3"><i class="bi bi-collection-fill"></i></div><div><div class="df-stat-num"><?= count($sessions) ?></div><div class="df-stat-label">Total Sidang</div></div></div>
            <div class="df-stat"><div class="df-stat-icon i1"><i class="bi bi-hourglass-split"></i></div><div><div class="df-stat-num"><?= $scheduled ?></div><div class="df-stat-label">Terjadwal</div></div></div>
            <div class="df-stat"><div class="df-stat-icon i2"><i class="bi bi-check-circle-fill"></i></div><div><div class="df-stat-num"><?= $completed ?></div><div class="df-stat-label">Selesai</div></div></div>
        </div>

        <div class="df-card">
            <?php if (empty($sessions)): ?>
                <div class="df-empty"><i class="bi bi-person-video3" style="font-size:3rem;opacity:.4;display:block;margin-bottom:1rem;"></i>Belum ada sidang terjadwal.</div>
            <?php else: ?>
            <div class="df-table-wrap">
                <table class="df-table">
                    <thead><tr><th>Mahasiswa / Judul</th><th>Jenis Sidang</th><th>Jadwal</th><th>Tempat / Tautan</th><th>Status</th><th>Nilai Akhir</th><th style="text-align:center;">Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($sessions as $s): ?>
                        <tr>
                            <td><div class="df-title"><?= e($s['title']) ?></div><div class="df-sub"><?= e($s['student_name']) ?> · <?= e($s['student_number']) ?></div></td>
                            <td><span class="df-sub"><?= e($typeLabels[$s['session_type']] ?? $s['session_type']) ?></span></td>
                            <td><span class="df-date"><?= date('d/m/Y H:i', strtotime($s['scheduled_date'])) ?></span></td>
                            <td><span class="df-sub"><?= e($s['room'] ?: ($s['online_link'] ? 'Daring' : '—')) ?></span></td>
                            <td><span class="df-pill df-pill-<?= e($s['status']) ?>"><?= e(ucfirst(str_replace('_', ' ', $s['status']))) ?></span></td>
                            <td><?php if ($s['final_grade'] !== null): ?><span class="df-grade"><?= number_format($s['final_grade'], 2) ?></span><?php else: ?><span class="df-sub">—</span><?php endif; ?></td>
                            <td style="text-align:center;">
                                <button type="button" class="df-btn df-btn-view" data-detail='<?= e(json_encode($s['examiners'])) ?>' data-role='<?= e(json_encode($roleLabels)) ?>'>Detail</button>
                                <?php if ($s['status'] === 'scheduled'): ?>
                                <form method="POST" action="<?= BASE_URL ?>/admin/defense/complete" style="display:inline;" onsubmit="return confirm('Tandai sidang selesai dan kunci nilai akhir?');">
                                    <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                    <button type="submit" class="df-btn df-btn-done" <?= $s['grades_done'] ? '' : 'disabled title="Nilai penguji belum lengkap"' ?>>Selesai</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Jadwal -->
    <div class="df-modal-overlay" id="dfScheduleModal">
        <div class="df-modal">
            <h3><i class="bi bi-calendar-plus"></i> Jadwalkan Sidang</h3>
            <form method="POST" action="<?= BASE_URL ?>/admin/defense/store">
                <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                <div class="df-field">
                    <label class="df-label">Tugas Akhir</label>
                    <select name="academic_work_id" class="df-select" required>
                        <option value="">— Pilih —</option>
                        <?php foreach ($works as $w): ?><option value="<?= $w['id'] ?>"><?= e($w['student_name']) ?> — <?= e(mb_substr($w['title'], 0, 60)) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="df-grid2">
                    <div class="df-field">
                        <label class="df-label">Jenis Sidang</label>
                        <select name="session_type" class="df-select" required>
                            <option value="proposal">Sidang Proposal</option>
                            <option value="closed_exam">Ujian Tertutup</option>
                            <option value="open_exam">Ujian Terbuka</option>
                            <option value="final_defense">Sidang Akhir</option>
                        </select>
                    </div>
                    <div class="df-field">
                        <label class="df-label">Tanggal & Jam</label>
                        <input type="datetime-local" name="scheduled_date" class="df-input" required>
                    </div>
                </div>
                <div class="df-grid2">
                    <div class="df-field"><label class="df-label">Ruangan (opsional)</label><input type="text" name="room" class="df-input" placeholder="R. Sidang 1"></div>
                    <div class="df-field"><label class="df-label">Tautan Daring (opsional)</label><input type="url" name="online_link" class="df-input" placeholder="https://meet..."></div>
                </div>
                <h4 class="df-label" style="margin-top:1rem;">Dewan Penguji</h4>
                <div class="df-grid2">
                    <?php foreach (['chair' => 'Ketua Sidang', 'examiner_1' => 'Penguji 1', 'examiner_2' => 'Penguji 2', 'secretary' => 'Sekretaris'] as $role => $label): ?>
                    <div class="df-field">
                        <label class="df-label"><?= $label ?></label>
                        <select name="ex_<?= $role ?>" class="df-select">
                            <option value="">— Tidak ada —</option>
                            <?php foreach ($lecturers as $l): ?><option value="<?= $l['id'] ?>"><?= e($l['name']) ?> <?= e($l['academic_degree'] ?? '') ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="df-modal-actions">
                    <button type="button" class="df-btn-cancel" id="dfCancelSchedule">Batal</button>
                    <button type="submit" class="df-btn-save"><i class="bi bi-save-fill"></i> Simpan Jadwal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Detail Penguji -->
    <div class="df-modal-overlay" id="dfDetailModal">
        <div class="df-modal">
            <h3><i class="bi bi-people-fill"></i> Dewan Penguji & Nilai</h3>
            <div id="dfDetailBody"></div>
            <div class="df-modal-actions"><button type="button" class="df-btn-cancel" id="dfCloseDetail">Tutup</button></div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/admin.js"></script>
    <script>
    (function(){
        const sched = document.getElementById('dfScheduleModal');
        const detail = document.getElementById('dfDetailModal');
        document.getElementById('dfAddBtn').addEventListener('click', () => sched.classList.add('is-open'));
        document.getElementById('dfCancelSchedule').addEventListener('click', () => sched.classList.remove('is-open'));
        document.getElementById('dfCloseDetail').addEventListener('click', () => detail.classList.remove('is-open'));

        document.querySelectorAll('[data-detail]').forEach(btn => {
            btn.addEventListener('click', () => {
                const exams = JSON.parse(btn.dataset.detail);
                const roles = JSON.parse(btn.dataset.role);
                const body = document.getElementById('dfDetailBody');
                if (!exams.length) { body.innerHTML = '<p style="color:var(--gray-500);">Belum ada penguji.</p>'; }
                else {
                    body.innerHTML = exams.map(ex =>
                        '<div class="df-exam-row"><div><strong>' + (ex.lecturer_name||'') + '</strong> <span class="df-exam-role">' + (roles[ex.role]||ex.role) + '</span></div><div class="df-grade">' + (ex.grade !== null ? Number(ex.grade).toFixed(2) : '—') + '</div></div>'
                    ).join('');
                }
                detail.classList.add('is-open');
            });
        });

        document.querySelectorAll('.alert-dismissible .btn-close').forEach(b => b.addEventListener('click', e => { const a = e.target.closest('.alert'); a.style.transition='all .3s'; a.style.opacity='0'; setTimeout(()=>a.remove(),300); }));
    })();
    </script>
</body>
</html>