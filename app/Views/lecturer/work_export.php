<?php
declare(strict_types=1);
$work = $work ?? []; $logs = $logs ?? []; $milestones = $milestones ?? [];
$documents = $documents ?? []; $supervisors = $supervisors ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Bimbingan - <?= e($work['title'] ?? '') ?></title>
<style>
    body { font-family: 'Times New Roman', serif; color: #000; margin: 40px; line-height: 1.5; }
    h1 { font-size: 16pt; text-align: center; text-transform: uppercase; text-decoration: underline; margin-bottom: 4px; }
    h2 { font-size: 12pt; text-align: center; margin-top: 0; margin-bottom: 24px; }
    h3 { font-size: 11pt; margin: 18px 0 6px; }
    table { width: 100%; border-collapse: collapse; font-size: 10pt; margin-bottom: 10px; }
    th, td { border: 1px solid #000; padding: 6px 8px; text-align: left; vertical-align: top; }
    th { background: #eee; }
    .info td { border: none; padding: 2px 8px; }
    .sign { margin-top: 50px; display: flex; justify-content: space-between; }
    .sign div { text-align: center; width: 45%; }
    .sign .space { height: 80px; }
    .noprint { position: fixed; top: 10px; right: 10px; }
    .noprint button { padding: 10px 18px; background: #6366f1; color: #fff; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; }
    @media print { .noprint { display: none; } body { margin: 20px; } }
</style>
</head>
<body>
<div class="noprint"><button onclick="window.print()">🖨️ Cetak / Simpan sebagai PDF</button></div>

<h1>Laporan Proses Bimbingan Tugas Akhir</h1>
<h2><?= e(APP_NAME) ?> - Sistem Informasi Bimbingan Tugas Akhir</h2>

<table class="info">
    <tr><td width="140">Nama Mahasiswa</td><td width="10">:</td><td><b><?= e($work['student_name'] ?? '') ?></b></td></tr>
    <tr><td>NIM</td><td>:</td><td><?= e($work['student_number'] ?? '') ?></td></tr>
    <tr><td>Program Studi</td><td>:</td><td><?= e($work['study_program'] ?? '') ?> - <?= e($work['faculty'] ?? '') ?></td></tr>
    <tr><td>Jenis TA</td><td>:</td><td><?= e($work['work_type_name'] ?? '') ?></td></tr>
    <tr><td>Judul</td><td>:</td><td><b><?= e($work['title'] ?? '') ?></b></td></tr>
    <tr><td>Status</td><td>:</td><td><?= e(AcademicWork::statusLabel($work['status'] ?? '')) ?></td></tr>
    <tr><td>Dosen Pembimbing</td><td>:</td><td>
        <?php foreach ($supervisors as $s): ?><?= e($s['name']) ?>, <?= e($s['academic_degree']) ?> (<?= e(str_replace('_',' ',$s['assignment_role'])) ?>)<br><?php endforeach; ?>
    </td></tr>
</table>

<h3>A. Riwayat Milestone</h3>
<table><tr><th width="30">No</th><th width="110">Tenggat</th><th>Milestone</th><th width="110">Status</th></tr>
<?php foreach ($milestones as $i => $m): ?>
<tr><td><?= $i+1 ?></td><td><?= date('d/m/Y', strtotime($m['due_date'])) ?></td><td><?= e($m['title']) ?></td><td><?= !empty($m['completed_at']) ? 'Selesai ('.date('d/m/Y', strtotime($m['completed_at'])).')' : 'Belum selesai' ?></td></tr>
<?php endforeach; if (empty($milestones)): ?><tr><td colspan="4">Tidak ada data.</td></tr><?php endif; ?>
</table>

<h3>B. Riwayat Dokumen & Review</h3>
<table><tr><th width="30">No</th><th>Jenis</th><th>File</th><th width="60">Versi</th><th width="100">Status</th><th width="100">Tanggal</th></tr>
<?php foreach ($documents as $i => $d): ?>
<tr><td><?= $i+1 ?></td><td><?= e($d['type_name']) ?></td><td><?= e($d['original_filename'] ?? '-') ?></td><td>v<?= (int)($d['version_number'] ?? 0) ?></td><td><?= e(ucfirst((string)($d['review_status'] ?? '-'))) ?></td><td><?= $d['uploaded_at'] ? date('d/m/Y', strtotime($d['uploaded_at'])) : '-' ?></td></tr>
<?php endforeach; if (empty($documents)): ?><tr><td colspan="6">Tidak ada data.</td></tr><?php endif; ?>
</table>

<h3>C. Log Bimbingan (<?= count($logs) ?> sesi)</h3>
<table><tr><th width="30">No</th><th width="100">Tanggal</th><th>Topik & Pembahasan</th><th width="120">Pencatat</th></tr>
<?php foreach ($logs as $i => $l): ?>
<tr><td><?= $i+1 ?></td><td><?= date('d/m/Y', strtotime($l['meeting_date'])) ?></td>
<td><b><?= e($l['topic']) ?></b><?php if ($l['discussion_points']): ?><br><?= nl2br(e($l['discussion_points'])) ?><?php endif; ?><?php if ($l['action_items']): ?><br><i>Action: <?= nl2br(e($l['action_items'])) ?></i><?php endif; ?></td>
<td><?= e($l['author_name']) ?></td></tr>
<?php endforeach; if (empty($logs)): ?><tr><td colspan="4">Tidak ada data.</td></tr><?php endif; ?>
</table>

<div class="sign">
    <div>Mengetahui,<br>Dosen Pembimbing<br><div class="space"></div><b><?= e($supervisors[0]['name'] ?? '') ?></b></div>
    <div><?= date('d F Y') ?>,<br>Mahasiswa<br><div class="space"></div><b><?= e($work['student_name'] ?? '') ?></b></div>
</div>
</body>
</html>