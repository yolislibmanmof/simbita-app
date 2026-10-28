<?php
declare(strict_types=1);
$flashError = $_SESSION['flash_error'] ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);
$work = $work ?? null; $logs = $logs ?? []; $milestones = $milestones ?? [];
$documents = $documents ?? []; $supervisors = $supervisors ?? [];
$bookings = $bookings ?? []; $slots = $slots ?? []; $progress = $progress ?? 0;
$doneMs = count(array_filter($milestones, fn($m) => !empty($m['completed_at'])));
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tugas Akhir Saya - <?= e(APP_NAME) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">
<style>
.wk-view{animation:wkFade .4s ease}@keyframes wkFade{from{opacity:0}to{opacity:1}}
.wk-head{display:flex;justify-content:space-between;gap:1rem;flex-wrap:wrap;align-items:center;margin-bottom:1.5rem}
.wk-title{font-size:1.6rem;font-weight:800;color:var(--gray-900);margin:0;display:flex;align-items:center;gap:.7rem}
.wk-title-icon{width:50px;height:50px;border-radius:var(--radius-xl);background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.3rem}
.wk-card{background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius-2xl);box-shadow:var(--shadow-sm);overflow:hidden;margin-bottom:1.5rem}
.wk-card-head{padding:1.1rem 1.5rem;border-bottom:1px solid var(--gray-200);background:linear-gradient(to right,var(--gray-50),#fff);font-weight:700;color:var(--gray-900);display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap}
.wk-card-body{padding:1.5rem}
.wk-tabs{display:flex;gap:.4rem;flex-wrap:wrap;margin-bottom:1.5rem}
.wk-tab{padding:.6rem 1.1rem;border-radius:.7rem;border:1px solid var(--gray-200);background:#fff;color:var(--gray-600);font-weight:600;font-size:.85rem;cursor:pointer;font-family:inherit;display:inline-flex;gap:.45rem;align-items:center}
.wk-tab-active{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border-color:transparent}
.wk-panel{display:none}.wk-panel-active{display:block}
.wk-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:1rem;margin-bottom:1.5rem}
.wk-stat{background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius-xl);padding:1.1rem;text-align:center}
.wk-stat b{display:block;font-size:1.6rem;color:var(--gray-900)}
.wk-stat span{font-size:.75rem;color:var(--gray-500);text-transform:uppercase;letter-spacing:.04em}
.wk-track{height:10px;background:var(--gray-100);border-radius:999px;overflow:hidden}
.wk-fill{height:100%;background:linear-gradient(90deg,#6366f1,#06b6d4);border-radius:999px;transition:width 1s ease}
.wk-chip{display:inline-flex;align-items:center;gap:.4rem;padding:.35rem .8rem;background:var(--gray-50);border:1px solid var(--gray-200);border-radius:999px;font-size:.8rem;font-weight:600;color:var(--gray-700);margin:.2rem}
.wk-table{width:100%;border-collapse:collapse}
.wk-table th,.wk-table td{padding:.75rem 1rem;text-align:left;border-bottom:1px solid var(--gray-100);font-size:.85rem}
.wk-table th{background:var(--gray-50);color:var(--gray-600);font-size:.72rem;text-transform:uppercase}
.wk-field{margin-bottom:1rem}.wk-label{display:block;font-size:.8rem;font-weight:700;color:var(--gray-700);margin-bottom:.35rem}
.wk-input,.wk-select,.wk-textarea{width:100%;padding:.65rem .85rem;border:1.5px solid var(--gray-200);border-radius:var(--radius-md);font-size:.9rem;font-family:inherit}
.wk-input:focus,.wk-select:focus,.wk-textarea:focus{outline:none;border-color:#6366f1}
.wk-textarea{min-height:70px}
.wk-btn{display:inline-flex;align-items:center;gap:.4rem;padding:.55rem 1rem;border-radius:var(--radius-md);border:none;font-weight:700;font-size:.8rem;cursor:pointer;font-family:inherit}
.wk-btn-primary{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff}
.wk-btn-green{background:#10b981;color:#fff}.wk-btn-red{background:#fff;color:#dc2626;border:1px solid #fecaca}
.wk-log{border:1px solid var(--gray-200);border-left:4px solid #6366f1;border-radius:var(--radius-lg);padding:1rem;margin-bottom:.8rem}
.wk-log-date{font-size:.75rem;color:var(--gray-500);font-weight:700}
.wk-log-topic{font-weight:700;color:var(--gray-900);margin:.2rem 0}
.wk-log p{font-size:.85rem;color:var(--gray-600);margin:.2rem 0}
.wk-empty{text-align:center;padding:2.5rem 1rem;color:var(--gray-500)}
.wk-empty i{display:block;font-size:2.5rem;opacity:.3;margin-bottom:.6rem}
.wk-pill{display:inline-flex;padding:.25rem .7rem;border-radius:999px;font-size:.7rem;font-weight:700}
.wk-pill-ok{background:#d1fae5;color:#065f46}.wk-pill-wait{background:#fef3c7;color:#92400e}.wk-pill-no{background:#fee2e2;color:#991b1b}
</style>
</head>
<body>
<?php require dirname(__DIR__) . '/partials/navbar.php'; ?>
<div class="container wk-view" style="padding:2rem 0 4rem">
    <div class="wk-head">
        <h1 class="wk-title"><span class="wk-title-icon"><i class="bi bi-journal-text"></i></span> Tugas Akhir Saya</h1>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap">
            <a href="<?= BASE_URL ?>/student/recommend" class="wk-btn" style="background:#fff;border:1px solid var(--gray-200);color:var(--gray-700)"><i class="bi bi-magic"></i> Cari Dosen Cocok</a>
            <?php if ($work): ?><a href="<?= BASE_URL ?>/lecturer/work/export?id=<?= (int)$work['id'] ?>" class="wk-btn" style="background:#fff;border:1px solid var(--gray-200);color:var(--gray-700)"><i class="bi bi-file-earmark-pdf"></i> Export Laporan</a><?php endif; ?>
        </div>
    </div>

    <?php if ($flashError): ?><div class="alert alert-danger"><?= e($flashError) ?></div><?php endif; ?>
    <?php if ($flashSuccess): ?><div class="alert alert-success"><?= e($flashSuccess) ?></div><?php endif; ?>

    <?php if (!$work): ?>
        <div class="wk-card"><div class="wk-empty"><i class="bi bi-journal-x"></i>
            <h3>Belum Ada Tugas Akhir</h3>
            <p>Ajukan judul melalui menu <b>Pengajuan Judul</b> atau tunggu tawaran dari dosen.</p>
            <a href="<?= BASE_URL ?>/student/proposals" class="wk-btn wk-btn-primary" style="margin-top:.8rem"><i class="bi bi-lightbulb"></i> Ajukan Judul</a>
        </div></div>
    <?php else: ?>
        <div class="wk-card"><div class="wk-card-body">
            <h2 style="font-size:1.2rem;font-weight:800;margin-bottom:.5rem"><?= e($work['title']) ?></h2>
            <div style="margin-bottom:.8rem">
                <span class="wk-chip"><i class="bi bi-bookmark-fill"></i> <?= e($work['work_type_name']) ?></span>
                <span class="wk-chip"><i class="bi bi-flag-fill"></i> <?= e(AcademicWork::statusLabel($work['status'])) ?></span>
                <?php foreach ($supervisors as $s): ?><span class="wk-chip"><i class="bi bi-person-workspace"></i> <?= e($s['name']) ?> (<?= e(str_replace('_',' ',$s['assignment_role'])) ?>)</span><?php endforeach; ?>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:.8rem;font-weight:700;color:var(--gray-600);margin-bottom:.4rem">
                <span>Progress Keseluruhan</span><span><?= $progress ?>%</span>
            </div>
            <div class="wk-track"><div class="wk-fill" style="width:<?= $progress ?>%"></div></div>
        </div></div>

        <div class="wk-tabs">
            <button class="wk-tab wk-tab-active" data-wk-tab="overview"><i class="bi bi-speedometer2"></i> Overview</button>
            <button class="wk-tab" data-wk-tab="docs"><i class="bi bi-folder-fill"></i> Dokumen</button>
            <button class="wk-tab" data-wk-tab="log"><i class="bi bi-journal-check"></i> Log Bimbingan</button>
            <button class="wk-tab" data-wk-tab="sched"><i class="bi bi-calendar-event"></i> Jadwal</button>
        </div>

        <!-- OVERVIEW -->
        <div class="wk-panel wk-panel-active" id="panel-overview">
            <div class="wk-stats">
                <div class="wk-stat"><b><?= count($documents) ?></b><span>Dokumen</span></div>
                <div class="wk-stat"><b><?= count($logs) ?></b><span>Sesi Bimbingan</span></div>
                <div class="wk-stat"><b><?= $doneMs ?>/<?= count($milestones) ?></b><span>Milestone</span></div>
                <div class="wk-stat"><b><?= $progress ?>%</b><span>Progress</span></div>
            </div>
            <div class="wk-card"><div class="wk-card-head">Timeline Milestone</div><div class="wk-card-body">
                <?php if (empty($milestones)): ?><div class="wk-empty"><i class="bi bi-calendar-x"></i>Belum ada milestone.</div>
                <?php else: foreach ($milestones as $m):
                    $isDone = !empty($m['completed_at']);
                    $over = !$isDone && strtotime($m['due_date']) < time();
                ?>
                <div class="wk-log" style="border-left-color:<?= $isDone ? '#10b981' : ($over ? '#dc2626' : '#6366f1') ?>">
                    <div class="wk-log-date"><?= date('d M Y', strtotime($m['due_date'])) ?> <?php if ($over): ?><span class="wk-pill wk-pill-no">TERLAMBAT</span><?php elseif ($isDone): ?><span class="wk-pill wk-pill-ok">SELESAI</span><?php endif; ?></div>
                    <div class="wk-log-topic"><?= e($m['title']) ?></div>
                    <p><?= e($m['description'] ?? '') ?></p>
                </div>
                <?php endforeach; endif; ?>
            </div></div>
        </div>

        <!-- DOKUMEN -->
        <div class="wk-panel" id="panel-docs">
            <div class="wk-card"><div class="wk-card-head">Dokumen Terunggah
                <a href="<?= BASE_URL ?>/student/upload" class="wk-btn wk-btn-primary"><i class="bi bi-cloud-upload"></i> Upload Versi Baru</a>
            </div><div class="wk-card-body" style="padding:0">
                <?php if (empty($documents)): ?><div class="wk-empty"><i class="bi bi-folder-x"></i>Belum ada dokumen.</div>
                <?php else: ?><table class="wk-table"><thead><tr><th>Jenis</th><th>File</th><th>Versi</th><th>Status</th><th></th></tr></thead><tbody>
                <?php foreach ($documents as $d): ?>
                <tr>
                    <td><?= e($d['type_name']) ?></td>
                    <td><?= e($d['original_filename'] ?? '-') ?></td>
                    <td>v<?= (int)($d['version_number'] ?? 0) ?></td>
                    <td><?php $rs = $d['review_status'] ?? null; ?>
                        <?php if ($rs === 'approved'): ?><span class="wk-pill wk-pill-ok">Disetujui</span>
                        <?php elseif ($rs === 'revision'): ?><span class="wk-pill wk-pill-no">Revisi</span>
                        <?php elseif ($rs === 'pending'): ?><span class="wk-pill wk-pill-wait">Direview</span>
                        <?php else: ?><span class="wk-pill wk-pill-wait">-</span><?php endif; ?>
                    </td>
                    <td><?php if (!empty($d['id'])): ?><a href="<?= BASE_URL ?>/documents/download?id=<?= (int)$d['id'] ?>" class="wk-btn" style="background:var(--gray-50);color:var(--gray-700)"><i class="bi bi-download"></i></a><?php endif; ?></td>
                </tr>
                <?php endforeach; ?></tbody></table><?php endif; ?>
            </div></div>
        </div>

        <!-- LOG BIMBINGAN -->
        <div class="wk-panel" id="panel-log">
            <div class="wk-card"><div class="wk-card-head">Catat Sesi Bimbingan</div><div class="wk-card-body">
                <form method="POST" action="<?= BASE_URL ?>/student/bimbingan/store">
                    <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                    <input type="hidden" name="academic_work_id" value="<?= (int)$work['id'] ?>">
                    <div style="display:grid;grid-template-columns:1fr 2fr;gap:1rem">
                        <div class="wk-field"><label class="wk-label">Tanggal</label><input type="date" name="meeting_date" class="wk-input" value="<?= date('Y-m-d') ?>" required></div>
                        <div class="wk-field"><label class="wk-label">Topik Pembahasan *</label><input type="text" name="topic" class="wk-input" required placeholder="Misal: Revisi BAB 3"></div>
                    </div>
                    <div class="wk-field"><label class="wk-label">Poin Diskusi</label><textarea name="discussion_points" class="wk-textarea" placeholder="Ringkasan pembahasan..."></textarea></div>
                    <div class="wk-field"><label class="wk-label">Action Items (Tindak Lanjut)</label><textarea name="action_items" class="wk-textarea" placeholder="1. Perbaiki diagram... 2. ..."></textarea></div>
                    <button class="wk-btn wk-btn-primary"><i class="bi bi-save"></i> Simpan Log</button>
                </form>
            </div></div>
            <div class="wk-card"><div class="wk-card-head">Riwayat Bimbingan (<?= count($logs) ?>)</div><div class="wk-card-body">
                <?php if (empty($logs)): ?><div class="wk-empty"><i class="bi bi-journal-x"></i>Belum ada log bimbingan.</div>
                <?php else: foreach ($logs as $l): ?>
                <div class="wk-log">
                    <div class="wk-log-date"><?= date('d M Y', strtotime($l['meeting_date'])) ?> • oleh <?= e($l['author_name']) ?> (<?= e($l['author_role']) ?>)</div>
                    <div class="wk-log-topic"><?= e($l['topic']) ?></div>
                    <?php if ($l['discussion_points']): ?><p><b>Diskusi:</b> <?= nl2br(e($l['discussion_points'])) ?></p><?php endif; ?>
                    <?php if ($l['action_items']): ?><p><b>Action:</b> <?= nl2br(e($l['action_items'])) ?></p><?php endif; ?>
                </div>
                <?php endforeach; endif; ?>
            </div></div>
        </div>

        <!-- JADWAL -->
        <div class="wk-panel" id="panel-sched">
            <div class="wk-card"><div class="wk-card-head">Slot Bimbingan Tersedia</div><div class="wk-card-body" style="padding:0">
                <?php if (empty($slots)): ?><div class="wk-empty"><i class="bi bi-calendar-x"></i>Belum ada slot dibuka dosen.</div>
                <?php else: ?><table class="wk-table"><thead><tr><th>Dosen</th><th>Tanggal</th><th>Jam</th><th>Mode</th><th>Kuota</th><th>Booking</th></tr></thead><tbody>
                <?php foreach ($slots as $s): $full = (int)$s['booked_count'] >= (int)$s['max_students']; ?>
                <tr>
                    <td><?= e($s['lecturer_name']) ?></td>
                    <td><?= date('d M Y', strtotime($s['slot_date'])) ?></td>
                    <td><?= substr((string)$s['start_time'],0,5) ?> - <?= substr((string)$s['end_time'],0,5) ?></td>
                    <td><?= $s['mode'] === 'online' ? '🌐 Online' : '🏫 Offline' ?> <?= e($s['location'] ?? '') ?> <?php if ($s['link_meeting']): ?><a href="<?= e($s['link_meeting']) ?>" target="_blank">Link</a><?php endif; ?></td>
                    <td><?= (int)$s['booked_count'] ?>/<?= (int)$s['max_students'] ?></td>
                    <td>
                        <?php if (!$full): ?>
                        <form method="POST" action="<?= BASE_URL ?>/student/schedule/book" style="display:flex;gap:.4rem">
                            <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                            <input type="hidden" name="schedule_slot_id" value="<?= (int)$s['id'] ?>">
                            <input type="text" name="agenda" class="wk-input" style="width:140px;padding:.4rem" placeholder="Agenda">
                            <button class="wk-btn wk-btn-green"><i class="bi bi-check2"></i> Book</button>
                        </form>
                        <?php else: ?><span class="wk-pill wk-pill-no">Penuh</span><?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?></tbody></table><?php endif; ?>
            </div></div>
            <div class="wk-card"><div class="wk-card-head">Booking Saya</div><div class="wk-card-body" style="padding:0">
                <?php if (empty($bookings)): ?><div class="wk-empty"><i class="bi bi-calendar-x"></i>Belum ada booking.</div>
                <?php else: ?><table class="wk-table"><thead><tr><th>Tanggal</th><th>Jam</th><th>Dosen</th><th>Agenda</th><th>Status</th><th></th></tr></thead><tbody>
                <?php foreach ($bookings as $b): ?>
                <tr>
                    <td><?= date('d M Y', strtotime($b['slot_date'])) ?></td>
                    <td><?= substr((string)$b['start_time'],0,5) ?></td>
                    <td><?= e($b['lecturer_name']) ?></td>
                    <td><?= e($b['agenda'] ?? '-') ?></td>
                    <td><?php $st = $b['status']; ?>
                        <span class="wk-pill <?= $st==='approved'?'wk-pill-ok':($st==='rejected'||$st==='cancelled'?'wk-pill-no':'wk-pill-wait') ?>"><?= ucfirst($st) ?></span></td>
                    <td><?php if (in_array($b['status'], ['pending','approved'])): ?>
                        <form method="POST" action="<?= BASE_URL ?>/student/schedule/cancel">
                            <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                            <input type="hidden" name="booking_id" value="<?= (int)$b['id'] ?>">
                            <button class="wk-btn wk-btn-red"><i class="bi bi-x"></i> Batal</button>
                        </form><?php endif; ?></td>
                </tr>
                <?php endforeach; ?></tbody></table><?php endif; ?>
            </div></div>
        </div>
    <?php endif; ?>
</div>
<script>
document.querySelectorAll('.wk-tab').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.wk-tab').forEach(b => b.classList.remove('wk-tab-active'));
        btn.classList.add('wk-tab-active');
        document.querySelectorAll('.wk-panel').forEach(p => p.classList.remove('wk-panel-active'));
        const panel = document.getElementById('panel-' + btn.dataset.wkTab);
        if (panel) panel.classList.add('wk-panel-active');
    });
});
</script>
</body>
</html>