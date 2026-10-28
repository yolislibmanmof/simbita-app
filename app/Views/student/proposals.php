<?php
declare(strict_types=1);
$flashError = $_SESSION['flash_error'] ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

$incoming = $incoming ?? [];
$outgoing = $outgoing ?? [];
$lecturers = $lecturers ?? [];
$workTypes = $workTypes ?? [];
$myWork = $myWork ?? null;
$pendingOutgoing = $pendingOutgoing ?? 0;

$statusMeta = [
    'pending'   => ['label' => 'Menunggu Respon', 'cls' => 'tp-pill-pending', 'icon' => 'hourglass-split'],
    'accepted'  => ['label' => 'Diterima',        'cls' => 'tp-pill-accepted', 'icon' => 'check-circle-fill'],
    'rejected'  => ['label' => 'Ditolak',         'cls' => 'tp-pill-rejected', 'icon' => 'x-circle-fill'],
    'withdrawn' => ['label' => 'Ditarik',         'cls' => 'tp-pill-rejected', 'icon' => 'arrow-return-left'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pengajuan Judul - <?= e(APP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">
<style>
/* ===== HERO HEADER ===== */
.tp-hero{text-align:center;margin-bottom:2.5rem;animation:tpUp .6s cubic-bezier(.16,1,.3,1) both}
.tp-hero-icon{width:76px;height:76px;margin:0 auto 1.2rem;border-radius:1.8rem;background:linear-gradient(135deg,#8b5cf6,#6d28d9);color:#fff;display:flex;align-items:center;justify-content:center;font-size:2.2rem;box-shadow:0 20px 50px -12px rgba(139,92,246,.55);animation:tpFloat 5s ease-in-out infinite;position:relative}
.tp-hero-icon::after{content:'';position:absolute;inset:-10px;border-radius:2.2rem;background:linear-gradient(135deg,#8b5cf6,#6d28d9);opacity:.25;z-index:-1;filter:blur(20px);animation:tpPulse 3s ease-in-out infinite}
@keyframes tpFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}
@keyframes tpPulse{0%,100%{opacity:.25}50%{opacity:.5}}
.tp-hero h1{font-size:2.1rem;font-weight:800;color:var(--gray-900);margin-bottom:.4rem;letter-spacing:-.02em}
.tp-hero h1 span{background:linear-gradient(135deg,#8b5cf6,#6d28d9);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.tp-hero p{color:var(--gray-500);max-width:540px;margin:0 auto;font-size:.95rem;line-height:1.6}

/* ===== STATUS BANNERS ===== */
.tp-banner{border-radius:var(--radius-2xl);padding:1.2rem 1.5rem;margin-bottom:1.8rem;display:flex;gap:1rem;align-items:flex-start;font-size:.9rem;animation:tpUp .6s .1s cubic-bezier(.16,1,.3,1) both;box-shadow:var(--shadow-sm)}
.tp-banner i{font-size:1.3rem;flex-shrink:0;margin-top:.1rem}
.tp-banner-ok{background:linear-gradient(135deg,#ecfdf5,#d1fae5);border:1px solid #a7f3d0;color:#065f46}
.tp-banner-ok i{color:#10b981}
.tp-banner-wait{background:linear-gradient(135deg,#fffbeb,#fef3c7);border:1px solid #fde68a;color:#92400e}
.tp-banner-wait i{color:#f59e0b;animation:tpSpin 2s linear infinite}
@keyframes tpSpin{to{transform:rotate(360deg)}}

/* ===== GRID LAYOUT ===== */
.tp-grid{display:grid;grid-template-columns:1.1fr .9fr;gap:1.8rem;align-items:start}
@media(max-width:968px){.tp-grid{grid-template-columns:1fr}}

/* ===== CARDS ===== */
.tp-card{background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius-2xl);box-shadow:var(--shadow-sm);overflow:hidden;margin-bottom:1.8rem;animation:tpUp .6s both}
.tp-card:nth-child(1){animation-delay:.15s}
.tp-card:nth-child(2){animation-delay:.2s}
.tp-card-head{padding:1.2rem 1.6rem;border-bottom:1px solid var(--gray-200);background:linear-gradient(to right,var(--gray-50),#fff);display:flex;justify-content:space-between;align-items:center;gap:1rem}
.tp-card-title{font-size:1.05rem;font-weight:800;color:var(--gray-900);display:flex;align-items:center;gap:.6rem;margin:0}
.tp-card-title i{color:#8b5cf6;font-size:1.2rem}
.tp-card-body{padding:1.6rem}

/* ===== INCOMING OFFERS ===== */
.tp-offer{position:relative;border:1.5px solid var(--gray-200);border-radius:var(--radius-xl);padding:1.3rem;margin-bottom:1.1rem;background:#fff;transition:.3s cubic-bezier(.16,1,.3,1);overflow:hidden}
.tp-offer::before{content:'';position:absolute;top:0;left:0;width:5px;height:100%;background:linear-gradient(to bottom,#8b5cf6,#6d28d9);opacity:0;transition:.3s}
.tp-offer:hover{border-color:#c7d2fe;box-shadow:0 15px 35px -12px rgba(139,92,246,.2);transform:translateY(-3px)}
.tp-offer:hover::before{opacity:1}
.tp-offer-title{font-weight:800;color:var(--gray-900);font-size:1.02rem;margin-bottom:.4rem;line-height:1.4}
.tp-offer-meta{font-size:.8rem;color:var(--gray-500);margin-bottom:.8rem;display:flex;gap:.8rem;flex-wrap:wrap;align-items:center}
.tp-offer-meta i{color:#94a3b8}
.tp-offer-desc{font-size:.88rem;color:var(--gray-600);line-height:1.65;background:linear-gradient(135deg,var(--gray-50),#fff);border-left:3px solid #8b5cf6;padding:.8rem 1rem;border-radius:0 var(--radius-lg) var(--radius-lg) 0;margin-bottom:1rem}
.tp-actions{display:flex;gap:.7rem;flex-wrap:wrap}
.tp-btn{position:relative;overflow:hidden;display:inline-flex;align-items:center;gap:.45rem;padding:.65rem 1.2rem;border-radius:var(--radius-lg);border:1.5px solid transparent;font-weight:700;font-size:.85rem;cursor:pointer;font-family:inherit;transition:.25s}
.tp-btn-accept{background:linear-gradient(135deg,#10b981,#059669);color:#fff;box-shadow:0 6px 15px -5px rgba(16,185,129,.5)}
.tp-btn-accept:hover{transform:translateY(-2px);box-shadow:0 10px 25px -5px rgba(16,185,129,.6)}
.tp-btn-accept::after{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.3),transparent);transition:.6s}
.tp-btn-accept:hover::after{left:100%}
.tp-btn-reject{background:#fff;color:#dc2626;border-color:#fecaca}
.tp-btn-reject:hover{background:#fee2e2;border-color:#fca5a5}

/* ===== HISTORY TABLE ===== */
.tp-table{width:100%;border-collapse:collapse}
.tp-table th,.tp-table td{padding:.9rem 1.1rem;text-align:left;border-bottom:1px solid var(--gray-100);font-size:.88rem}
.tp-table th{background:var(--gray-50);color:var(--gray-600);font-size:.75rem;text-transform:uppercase;letter-spacing:.06em;font-weight:800}
.tp-table tbody tr{transition:.2s}
.tp-table tbody tr:hover{background:var(--gray-50)}
.tp-table tbody tr:last-child td{border-bottom:none}

/* ===== STATUS PILLS ===== */
.tp-pill{display:inline-flex;align-items:center;gap:.4rem;padding:.35rem .85rem;border-radius:999px;font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.04em}
.tp-pill i{font-size:.8rem}
.tp-pill-pending{background:#fef3c7;color:#92400e}
.tp-pill-accepted{background:#d1fae5;color:#065f46}
.tp-pill-rejected{background:#fee2e2;color:#991b1b}

/* ===== FORM ===== */
.tp-form-card{background:linear-gradient(135deg,#faf5ff,#fff);border:2px solid #c7d2fe;border-radius:var(--radius-2xl);box-shadow:0 20px 50px -12px rgba(139,92,246,.2);overflow:hidden}
.tp-form-head{padding:1.4rem 1.6rem;background:linear-gradient(135deg,#8b5cf6,#6d28d9);color:#fff;text-align:center}
.tp-form-head h3{font-size:1.2rem;font-weight:800;margin:0;display:flex;align-items:center;justify-content:center;gap:.6rem}
.tp-form-head i{font-size:1.4rem}
.tp-form-head p{font-size:.85rem;opacity:.9;margin:.3rem 0 0}
.tp-field{margin-bottom:1.3rem}
.tp-label{display:flex;align-items:center;gap:.4rem;font-size:.82rem;font-weight:700;color:var(--gray-700);margin-bottom:.5rem}
.tp-label i{color:#8b5cf6}
.tp-label .req{color:#ef4444}
.tp-select-wrap{position:relative}
.tp-select-wrap>.lead{position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:#94a3b8;pointer-events:none;transition:.25s;font-size:1rem}
.tp-select-wrap>.chev{position:absolute;right:1rem;top:50%;transform:translateY(-50%);color:#94a3b8;pointer-events:none;transition:.25s;font-size:.9rem}
.tp-select{appearance:none;-webkit-appearance:none;-moz-appearance:none;width:100%;padding:.85rem 2.8rem .85rem 2.8rem;border:1.5px solid var(--gray-200);border-radius:var(--radius-lg);font-size:.92rem;font-weight:600;color:var(--gray-700);background:#fff;cursor:pointer;transition:.25s;font-family:inherit}
.tp-select:hover{border-color:#c7d2fe;background:#fafaff}
.tp-select:focus{outline:none;border-color:#8b5cf6;box-shadow:0 0 0 4px rgba(139,92,246,.12)}
.tp-select-wrap:hover>.lead,.tp-select:focus~.lead{color:#8b5cf6}
.tp-select-wrap:hover>.chev{color:#8b5cf6;transform:translateY(-50%) scale(1.15)}
.tp-select option{padding:.6rem;font-weight:500}
.tp-input,.tp-textarea{width:100%;padding:.85rem 1.1rem;border:1.5px solid var(--gray-200);border-radius:var(--radius-lg);font-size:.92rem;font-family:inherit;background:#fff;transition:.25s}
.tp-input:focus,.tp-textarea:focus{outline:none;border-color:#8b5cf6;box-shadow:0 0 0 4px rgba(139,92,246,.12)}
.tp-textarea{min-height:80px;resize:vertical;line-height:1.6}
.tp-block{background:#fff;border:2px dashed #c7d2fe;border-radius:var(--radius-xl);padding:1.2rem;margin-bottom:1.2rem;transition:.3s}
.tp-block:hover{border-color:#8b5cf6;background:#fafaff}
.tp-block-num{display:inline-flex;align-items:center;gap:.4rem;padding:.3rem .8rem;background:linear-gradient(135deg,#8b5cf6,#6d28d9);color:#fff;border-radius:999px;font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.8rem}
.tp-btn-submit{position:relative;overflow:hidden;display:flex;align-items:center;justify-content:center;gap:.5rem;width:100%;padding:1rem;background:linear-gradient(135deg,#8b5cf6,#6d28d9);color:#fff;border:none;border-radius:var(--radius-xl);font-weight:800;font-size:.95rem;cursor:pointer;font-family:inherit;box-shadow:0 12px 30px -8px rgba(139,92,246,.6);transition:.25s}
.tp-btn-submit:hover{transform:translateY(-2px);box-shadow:0 18px 40px -8px rgba(139,92,246,.7)}
.tp-btn-submit::after{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.3),transparent);transition:.7s}
.tp-btn-submit:hover::after{left:100%}

/* ===== EMPTY STATE ===== */
.tp-empty{text-align:center;padding:3rem 1.5rem;color:var(--gray-500);font-size:.92rem}
.tp-empty-icon{width:80px;height:80px;margin:0 auto 1rem;border-radius:50%;background:linear-gradient(135deg,#f3f4f6,#e5e7eb);display:flex;align-items:center;justify-content:center;font-size:2.2rem;color:#9ca3af;animation:tpBounce 2s ease-in-out infinite}
@keyframes tpBounce{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
.tp-empty h4{color:var(--gray-800);font-size:1rem;font-weight:700;margin-bottom:.3rem}
.tp-empty p{font-size:.85rem;margin:0}

@keyframes tpUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}
</style>
</head>
<body>
<?php require dirname(__DIR__) . '/partials/navbar.php'; ?>

<div class="container" style="padding:2.5rem 0 4rem">
    <!-- HERO -->
    <div class="tp-hero">
        <div class="tp-hero-icon"><i class="bi bi-lightbulb-fill"></i></div>
        <h1>Pengajuan <span>Judul Tugas Akhir</span></h1>
        <p>Ajukan judul pilihan Anda ke dosen pembimbing atau respon tawaran judul dari dosen. Proses transparan dan terstruktur.</p>
    </div>

    <?php if ($flashError): ?><div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill"></i> <?= e($flashError) ?></div><?php endif; ?>
    <?php if ($flashSuccess): ?><div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> <?= e($flashSuccess) ?></div><?php endif; ?>

    <!-- STATUS BANNERS -->
    <?php if ($myWork): ?>
        <div class="tp-banner tp-banner-ok">
            <i class="bi bi-check-circle-fill"></i>
            <div>
                <strong style="font-size:.95rem">Anda sudah memiliki Tugas Akhir</strong><br>
                <span style="font-size:.88rem"><?= e($myWork['title']) ?></span>
            </div>
        </div>
    <?php elseif ($pendingOutgoing > 0): ?>
        <div class="tp-banner tp-banner-wait">
            <i class="bi bi-hourglass-split"></i>
            <div>
                <strong style="font-size:.95rem">Menunggu respon dosen</strong><br>
                <span style="font-size:.88rem">Anda telah mengajukan <?= $pendingOutgoing ?> judul. Silakan tunggu persetujuan dosen sebelum mengajukan lagi.</span>
            </div>
        </div>
    <?php endif; ?>

    <div class="tp-grid">
        <!-- KOLOM KIRI: Tawaran Masuk + Riwayat -->
        <div>
            <div class="tp-card">
                <div class="tp-card-head">
                    <h3 class="tp-card-title"><i class="bi bi-inbox-fill"></i> Tawaran Judul dari Dosen</h3>
                    <span class="tp-pill tp-pill-pending"><i class="bi bi-bell-fill"></i> <?= count($incoming) ?> Masuk</span>
                </div>
                <div class="tp-card-body">
                    <?php if (empty($incoming)): ?>
                        <div class="tp-empty">
                            <div class="tp-empty-icon"><i class="bi bi-inbox"></i></div>
                            <h4>Belum ada tawaran</h4>
                            <p>Tawaran judul dari dosen akan muncul di sini.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($incoming as $p): ?>
                        <div class="tp-offer">
                            <div class="tp-offer-title"><?= e($p['title']) ?></div>
                            <div class="tp-offer-meta">
                                <span><i class="bi bi-person-workspace"></i> <?= e($p['proposer_name']) ?></span>
                                <span><i class="bi bi-bookmark-fill"></i> <?= e($p['work_type_name'] ?? '-') ?></span>
                            </div>
                            <?php if (!empty($p['description'])): ?>
                            <div class="tp-offer-desc"><?= nl2br(e($p['description'])) ?></div>
                            <?php endif; ?>
                            <div class="tp-actions">
                                <form method="POST" action="<?= BASE_URL ?>/student/proposals/respond">
                                    <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                                    <input type="hidden" name="proposal_id" value="<?= (int)$p['id'] ?>">
                                    <input type="hidden" name="action" value="accept">
                                    <button class="tp-btn tp-btn-accept"><i class="bi bi-check-circle-fill"></i> Terima Judul</button>
                                </form>
                                <form method="POST" action="<?= BASE_URL ?>/student/proposals/respond">
                                    <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                                    <input type="hidden" name="proposal_id" value="<?= (int)$p['id'] ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button class="tp-btn tp-btn-reject"><i class="bi bi-x-circle"></i> Tolak</button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="tp-card">
                <div class="tp-card-head">
                    <h3 class="tp-card-title"><i class="bi bi-clock-history"></i> Riwayat Pengajuan Saya</h3>
                </div>
                <div class="tp-card-body" style="padding:0">
                    <?php if (empty($outgoing)): ?>
                        <div class="tp-empty">
                            <div class="tp-empty-icon"><i class="bi bi-send"></i></div>
                            <h4>Belum ada pengajuan</h4>
                            <p>Riwayat pengajuan judul Anda akan muncul di sini.</p>
                        </div>
                    <?php else: ?>
                        <table class="tp-table">
                            <thead><tr><th>Judul</th><th>Dosen Tujuan</th><th>Status</th></tr></thead>
                            <tbody>
                            <?php foreach ($outgoing as $o): $m = $statusMeta[$o['status']] ?? $statusMeta['pending']; ?>
                                <tr>
                                    <td><strong><?= e($o['title']) ?></strong></td>
                                    <td><?= e($o['target_name']) ?></td>
                                    <td><span class="tp-pill <?= $m['cls'] ?>"><i class="bi bi-<?= $m['icon'] ?>"></i> <?= $m['label'] ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- KOLOM KANAN: Form Ajukan -->
        <div>
            <?php if (!$myWork && $pendingOutgoing === 0): ?>
            <div class="tp-form-card">
                <div class="tp-form-head">
                    <h3><i class="bi bi-send-fill"></i> Ajukan Judul Anda</h3>
                    <p>Ajukan hingga 3 pilihan judul ke dosen pilihan Anda</p>
                </div>
                <div style="padding:1.8rem">
                    <form method="POST" action="<?= BASE_URL ?>/student/proposals/submit">
                        <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">

                        <div class="tp-field">
                            <label class="tp-label"><i class="bi bi-person-workspace"></i> Dosen yang Dituju <span class="req">*</span></label>
                            <div class="tp-select-wrap">
                                <i class="bi bi-person-fill lead"></i>
                                <select name="lecturer_user_id" class="tp-select" required>
                                    <option value="">-- Pilih Dosen Pembimbing --</option>
                                    <?php foreach ($lecturers as $l): ?>
                                        <option value="<?= (int)$l['id'] ?>"><?= e($l['name']) ?><?= !empty($l['academic_degree']) ? ', '.e($l['academic_degree']) : '' ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="bi bi-chevron-down chev"></i>
                            </div>
                        </div>

                        <div class="tp-field">
                            <label class="tp-label"><i class="bi bi-bookmark-fill"></i> Jenis Tugas Akhir <span class="req">*</span></label>
                            <div class="tp-select-wrap">
                                <i class="bi bi-journal-text lead"></i>
                                <select name="work_type_id" class="tp-select" required>
                                    <option value="">-- Pilih Jenis TA --</option>
                                    <?php foreach ($workTypes as $wt): ?>
                                        <option value="<?= (int)$wt['id'] ?>"><?= e($wt['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="bi bi-chevron-down chev"></i>
                            </div>
                        </div>

                        <?php for ($i = 1; $i <= 3; $i++): ?>
                        <div class="tp-block">
                            <div class="tp-block-num"><i class="bi bi-<?= $i === 1 ? 'star-fill' : 'circle' ?>"></i> Pilihan Judul <?= $i ?> <?= $i === 1 ? '(Wajib)' : '(Opsional)' ?></div>
                            <div class="tp-field">
                                <input type="text" name="title_<?= $i ?>" class="tp-input" placeholder="Tuliskan judul pilihan <?= $i ?>..." <?= $i === 1 ? 'required' : '' ?>>
                            </div>
                            <div class="tp-field" style="margin-bottom:0">
                                <textarea name="desc_<?= $i ?>" class="tp-textarea" placeholder="Deskripsi singkat / rumusan masalah (opsional)"></textarea>
                            </div>
                        </div>
                        <?php endfor; ?>

                        <button type="submit" class="tp-btn-submit"><i class="bi bi-send-fill"></i> Kirim Pengajuan ke Dosen</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>