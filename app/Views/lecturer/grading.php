<?php
declare(strict_types=1);
$flashError = $_SESSION['flash_error'] ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);
$roleLabels = ['chair' => 'Ketua Sidang', 'examiner_1' => 'Penguji 1', 'examiner_2' => 'Penguji 2', 'secretary' => 'Sekretaris'];
$typeLabels = ['proposal' => 'Sidang Proposal', 'closed_exam' => 'Ujian Tertutup', 'open_exam' => 'Ujian Terbuka', 'final_defense' => 'Sidang Akhir'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Penilaian Sidang - <?= e(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">
    <style>
        .gr-view{animation:grFade .4s ease}@keyframes grFade{from{opacity:0}to{opacity:1}}
        .gr-header{margin-bottom:2rem}
        .gr-header-title{display:flex;align-items:center;gap:.75rem;font-size:1.875rem;font-weight:800;color:var(--gray-900);margin:0 0 .35rem}
        .gr-header-icon{width:48px;height:48px;background:linear-gradient(135deg,#fef3c7,#fde68a);border-radius:var(--radius-xl);display:flex;align-items:center;justify-content:center;color:#b45309;font-size:1.5rem}
        .gr-header-sub{color:var(--gray-500);font-size:.95rem;margin:0 0 0 calc(48px + .75rem)}
        .gr-list{display:flex;flex-direction:column;gap:1.25rem}
        .gr-card{background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius-2xl);overflow:hidden;animation:grUp .5s ease backwards}
        @keyframes grUp{from{opacity:0;transform:translateY(15px)}to{opacity:1;transform:translateY(0)}}
        .gr-card-head{padding:1.25rem 1.5rem;background:linear-gradient(to right,var(--gray-50),#fff);border-bottom:1px solid var(--gray-200);display:flex;justify-content:space-between;gap:1rem;flex-wrap:wrap;align-items:center}
        .gr-card-title{font-weight:700;color:var(--gray-900);font-size:1rem;margin:0}
        .gr-card-sub{font-size:.8rem;color:var(--gray-500);margin-top:.2rem}
        .gr-role-pill{padding:.35rem .9rem;border-radius:999px;background:#fef3c7;color:#92400e;font-size:.72rem;font-weight:800;text-transform:uppercase}
        .gr-card-body{padding:1.5rem;display:grid;grid-template-columns:1fr 1fr;gap:1rem}
        @media(max-width:768px){.gr-card-body{grid-template-columns:1fr}}
        .gr-label{display:block;font-size:.8rem;font-weight:600;color:var(--gray-700);margin-bottom:.4rem}
        .gr-input,.gr-textarea{width:100%;padding:.65rem .9rem;border:1.5px solid var(--gray-300);border-radius:var(--radius-md);font-size:.9rem;font-family:inherit}
        .gr-textarea{min-height:90px;resize:vertical}
        .gr-input:focus,.gr-textarea:focus{outline:none;border-color:#f59e0b;box-shadow:0 0 0 4px rgba(245,158,11,.12)}
        .gr-card-foot{padding:1rem 1.5rem;border-top:1px solid var(--gray-200);display:flex;justify-content:flex-end}
        .gr-save{padding:.7rem 1.75rem;border:none;border-radius:var(--radius-lg);background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;font-weight:700;font-family:inherit;cursor:pointer;transition:all .3s}
        .gr-save:hover{transform:translateY(-2px)}
        .gr-save:disabled{opacity:.5;cursor:not-allowed}
        .gr-locked{padding:1.25rem 1.5rem;color:var(--gray-500);font-size:.88rem;display:flex;align-items:center;gap:.5rem}
        .gr-empty{text-align:center;padding:4rem 2rem;color:var(--gray-500);background:#fff;border-radius:var(--radius-2xl);border:1px solid var(--gray-200)}
    </style>
</head>
<body>
    <?php require dirname(__DIR__) . '/partials/navbar.php'; ?>
    <div class="container gr-view">
        <div class="gr-header">
            <h1 class="gr-header-title"><span class="gr-header-icon"><i class="bi bi-award"></i></span> Penilaian Sidang</h1>
            <p class="gr-header-sub">Masukkan nilai dan catatan Anda sebagai anggota dewan penguji.</p>
        </div>

        <?php if ($flashError): ?><div class="alert alert-danger alert-dismissible"><div class="alert-content"><i class="bi bi-exclamation-triangle-fill"></i> <?= e($flashError) ?></div><button class="btn-close">&times;</button></div><?php endif; ?>
        <?php if ($flashSuccess): ?><div class="alert alert-success alert-dismissible"><div class="alert-content"><i class="bi bi-check-circle-fill"></i> <?= e($flashSuccess) ?></div><button class="btn-close">&times;</button></div><?php endif; ?>

        <?php if (empty($assignments)): ?>
            <div class="gr-empty"><i class="bi bi-award" style="font-size:3rem;opacity:.4;display:block;margin-bottom:1rem;"></i>Anda belum ditugaskan sebagai penguji pada sidang mana pun.</div>
        <?php else: ?>
        <div class="gr-list">
            <?php foreach ($assignments as $i => $a): $locked = $a['session_status'] === 'completed'; ?>
            <div class="gr-card" style="animation-delay:<?= min($i*0.06,0.4) ?>s">
                <div class="gr-card-head">
                    <div>
                        <h3 class="gr-card-title"><?= e($a['title']) ?></h3>
                        <div class="gr-card-sub"><?= e($a['student_name']) ?> · <?= e($typeLabels[$a['session_type']] ?? $a['session_type']) ?> · <?= date('d/m/Y H:i', strtotime($a['scheduled_date'])) ?></div>
                    </div>
                    <span class="gr-role-pill"><?= e($roleLabels[$a['role']] ?? $a['role']) ?></span>
                </div>
                <?php if ($locked): ?>
                    <div class="gr-locked"><i class="bi bi-lock-fill"></i> Sidang telah selesai. Nilai terkunci: <strong><?= number_format((float)$a['grade'], 2) ?></strong></div>
                <?php else: ?>
                <form method="POST" action="<?= BASE_URL ?>/lecturer/grading/save">
                    <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                    <input type="hidden" name="session_id" value="<?= $a['defense_session_id'] ?>">
                    <div class="gr-card-body">
                        <div>
                            <label class="gr-label">Nilai (0–100)</label>
                            <input type="number" step="0.01" min="0" max="100" name="grade" class="gr-input" value="<?= e($a['grade'] !== null ? (string)$a['grade'] : '') ?>" placeholder="cth: 85.50" required>
                        </div>
                        <div>
                            <label class="gr-label">Catatan Penguji</label>
                            <textarea name="notes" class="gr-textarea" placeholder="Catatan revisi atau masukan untuk mahasiswa..."><?= e($a['notes'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="gr-card-foot">
                        <button type="submit" class="gr-save"><i class="bi bi-save-fill"></i> Simpan Nilai</button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <script src="<?= BASE_URL ?>/assets/js/admin.js"></script>
</body>
</html>