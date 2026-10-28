<?php
declare(strict_types=1);
$flashError = $_SESSION['flash_error'] ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

$incoming = $incoming ?? [];
$outgoing = $outgoing ?? [];
$students = $students ?? [];
$workTypes = $workTypes ?? [];

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
.tp-hero-icon{width:76px;height:76px;margin:0 auto 1.2rem;border-radius:1.8rem;background:linear-gradient(135deg,#0ea5e9,#0369a1);color:#fff;display:flex;align-items:center;justify-content:center;font-size:2.2rem;box-shadow:0 20px 50px -12px rgba(14,165,233,.55);animation:tpFloat 5s ease-in-out infinite;position:relative}
.tp-hero-icon::after{content:'';position:absolute;inset:-10px;border-radius:2.2rem;background:linear-gradient(135deg,#0ea5e9,#0369a1);opacity:.25;z-index:-1;filter:blur(20px);animation:tpPulse 3s ease-in-out infinite}
@keyframes tpFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}
@keyframes tpPulse{0%,100%{opacity:.25}50%{opacity:.5}}
.tp-hero h1{font-size:2.1rem;font-weight:800;color:var(--gray-900);margin-bottom:.4rem;letter-spacing:-.02em}
.tp-hero h1 span{background:linear-gradient(135deg,#0ea5e9,#0369a1);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.tp-hero p{color:var(--gray-500);max-width:560px;margin:0 auto;font-size:.95rem;line-height:1.6}
.tp-hero-stats{display:flex;gap:.6rem;justify-content:center;margin-top:1.3rem;flex-wrap:wrap}
.tp-hstat{display:inline-flex;align-items:center;gap:.4rem;padding:.45rem 1rem;background:#fff;border:1px solid var(--gray-200);border-radius:999px;font-size:.78rem;font-weight:700;color:var(--gray-600)}
.tp-hstat i{color:#0ea5e9}
.tp-hstat b{color:#0369a1;margin-left:.2rem}

/* ===== GRID ===== */
.tp-grid{display:grid;grid-template-columns:1.1fr .9fr;gap:1.8rem;align-items:start}
@media(max-width:968px){.tp-grid{grid-template-columns:1fr}}

/* ===== CARDS ===== */
.tp-card{background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius-2xl);box-shadow:var(--shadow-sm);overflow:hidden;margin-bottom:1.8rem;animation:tpUp .6s both}
.tp-card:nth-child(1){animation-delay:.15s}
.tp-card:nth-child(2){animation-delay:.2s}
.tp-card-head{padding:1.2rem 1.6rem;border-bottom:1px solid var(--gray-200);background:linear-gradient(to right,var(--gray-50),#fff);display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap}
.tp-card-title{font-size:1.05rem;font-weight:800;color:var(--gray-900);display:flex;align-items:center;gap:.6rem;margin:0}
.tp-card-title i{color:#0ea5e9;font-size:1.2rem}
.tp-card-body{padding:1.6rem}

/* ===== OFFER CARDS ===== */
.tp-offer{position:relative;border:1.5px solid var(--gray-200);border-radius:var(--radius-xl);padding:1.3rem;margin-bottom:1.1rem;background:#fff;transition:.3s cubic-bezier(.16,1,.3,1);overflow:hidden}
.tp-offer::before{content:'';position:absolute;top:0;left:0;width:5px;height:100%;background:linear-gradient(to bottom,#0ea5e9,#0369a1);opacity:0;transition:.3s}
.tp-offer:hover{border-color:#7dd3fc;box-shadow:0 15px 35px -12px rgba(14,165,233,.2);transform:translateY(-3px)}
.tp-offer:hover::before{opacity:1}
.tp-offer-title{font-weight:800;color:var(--gray-900);font-size:1.02rem;margin-bottom:.4rem;line-height:1.4}
.tp-offer-meta{font-size:.8rem;color:var(--gray-500);margin-bottom:.8rem;display:flex;gap:.8rem;flex-wrap:wrap;align-items:center}
.tp-offer-meta i{color:#94a3b8}
.tp-offer-meta .stud-num{font-family:'JetBrains Mono',monospace;font-size:.72rem;padding:.15rem .55rem;background:#f0f9ff;color:#0369a1;border-radius:999px;font-weight:700}
.tp-offer-desc{font-size:.88rem;color:var(--gray-600);line-height:1.65;background:linear-gradient(135deg,var(--gray-50),#fff);border-left:3px solid #0ea5e9;padding:.8rem 1rem;border-radius:0 var(--radius-lg) var(--radius-lg) 0;margin-bottom:1rem}
.tp-actions{display:flex;gap:.7rem;flex-wrap:wrap}
.tp-btn{position:relative;overflow:hidden;display:inline-flex;align-items:center;gap:.45rem;padding:.65rem 1.2rem;border-radius:var(--radius-lg);border:1.5px solid transparent;font-weight:700;font-size:.85rem;cursor:pointer;font-family:inherit;transition:.25s}
.tp-btn-accept{background:linear-gradient(135deg,#10b981,#059669);color:#fff;box-shadow:0 6px 15px -5px rgba(16,185,129,.5)}
.tp-btn-accept:hover{transform:translateY(-2px);box-shadow:0 10px 25px -5px rgba(16,185,129,.6)}
.tp-btn-accept::after{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.3),transparent);transition:.6s}
.tp-btn-accept:hover::after{left:100%}
.tp-btn-reject{background:#fff;color:#dc2626;border-color:#fecaca}
.tp-btn-reject:hover{background:#fee2e2;border-color:#fca5a5}

/* ===== TABLE ===== */
.tp-table{width:100%;border-collapse:collapse}
.tp-table th,.tp-table td{padding:.9rem 1.1rem;text-align:left;border-bottom:1px solid var(--gray-100);font-size:.88rem}
.tp-table th{background:var(--gray-50);color:var(--gray-600);font-size:.75rem;text-transform:uppercase;letter-spacing:.06em;font-weight:800}
.tp-table tbody tr{transition:.2s}
.tp-table tbody tr:hover{background:var(--gray-50)}
.tp-table tbody tr:last-child td{border-bottom:none}
.tp-table td strong{color:var(--gray-900)}

/* ===== PILLS ===== */
.tp-pill{display:inline-flex;align-items:center;gap:.4rem;padding:.35rem .85rem;border-radius:999px;font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.04em}
.tp-pill i{font-size:.8rem}
.tp-pill-pending{background:#fef3c7;color:#92400e}
.tp-pill-accepted{background:#d1fae5;color:#065f46}
.tp-pill-rejected{background:#fee2e2;color:#991b1b}

/* ===== FORM ===== */
.tp-form-card{background:linear-gradient(135deg,#f0f9ff,#fff);border:2px solid #7dd3fc;border-radius:var(--radius-2xl);box-shadow:0 20px 50px -12px rgba(14,165,233,.2);overflow:hidden;position:sticky;top:90px}
.tp-form-head{padding:1.4rem 1.6rem;background:linear-gradient(135deg,#0ea5e9,#0369a1);color:#fff;text-align:center}
.tp-form-head h3{font-size:1.2rem;font-weight:800;margin:0;display:flex;align-items:center;justify-content:center;gap:.6rem}
.tp-form-head i{font-size:1.4rem}
.tp-form-head p{font-size:.85rem;opacity:.9;margin:.3rem 0 0}
.tp-field{margin-bottom:1.3rem}
.tp-label{display:flex;align-items:center;gap:.4rem;font-size:.82rem;font-weight:700;color:var(--gray-700);margin-bottom:.5rem}
.tp-label i{color:#0ea5e9}
.tp-label .req{color:#ef4444}

.tp-select-wrap{position:relative}
.tp-select-wrap>.lead{position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:#94a3b8;pointer-events:none;transition:.25s;font-size:1rem}
.tp-select-wrap>.chev{position:absolute;right:1rem;top:50%;transform:translateY(-50%);color:#94a3b8;pointer-events:none;transition:.25s;font-size:.9rem}
.tp-select{appearance:none;-webkit-appearance:none;-moz-appearance:none;width:100%;padding:.85rem 2.8rem .85rem 2.8rem;border:1.5px solid var(--gray-200);border-radius:var(--radius-lg);font-size:.92rem;font-weight:600;color:var(--gray-700);background:#fff;cursor:pointer;transition:.25s;font-family:inherit}
.tp-select:hover{border-color:#7dd3fc;background:#f0f9ff}
.tp-select:focus{outline:none;border-color:#0ea5e9;box-shadow:0 0 0 4px rgba(14,165,233,.12)}
.tp-select-wrap:hover>.lead,.tp-select:focus~.lead{color:#0ea5e9}
.tp-select-wrap:hover>.chev{color:#0ea5e9;transform:translateY(-50%) scale(1.15)}
.tp-select option{padding:.6rem;font-weight:500}
.tp-select optgroup{font-weight:700;color:#0369a1;padding:.4rem}

.tp-input,.tp-textarea{width:100%;padding:.85rem 1.1rem;border:1.5px solid var(--gray-200);border-radius:var(--radius-lg);font-size:.92rem;font-family:inherit;background:#fff;transition:.25s}
.tp-input:focus,.tp-textarea:focus{outline:none;border-color:#0ea5e9;box-shadow:0 0 0 4px rgba(14,165,233,.12)}
.tp-textarea{min-height:95px;resize:vertical;line-height:1.6}

.tp-btn-submit{position:relative;overflow:hidden;display:flex;align-items:center;justify-content:center;gap:.5rem;width:100%;padding:1rem;background:linear-gradient(135deg,#0ea5e9,#0369a1);color:#fff;border:none;border-radius:var(--radius-xl);font-weight:800;font-size:.95rem;cursor:pointer;font-family:inherit;box-shadow:0 12px 30px -8px rgba(14,165,233,.6);transition:.25s;margin-top:.5rem}
.tp-btn-submit:hover{transform:translateY(-2px);box-shadow:0 18px 40px -8px rgba(14,165,233,.7)}
.tp-btn-submit::after{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.3),transparent);transition:.7s}
.tp-btn-submit:hover::after{left:100%}

/* ===== EMPTY STATE ===== */
.tp-empty{text-align:center;padding:3rem 1.5rem;color:var(--gray-500);font-size:.92rem}
.tp-empty-icon{width:80px;height:80px;margin:0 auto 1rem;border-radius:50%;background:linear-gradient(135deg,#f0f9ff,#e0f2fe);display:flex;align-items:center;justify-content:center;font-size:2.2rem;color:#0ea5e9;animation:tpBounce 2s ease-in-out infinite}
@keyframes tpBounce{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
.tp-empty h4{color:var(--gray-800);font-size:1rem;font-weight:700;margin-bottom:.3rem}
.tp-empty p{font-size:.85rem;margin:0}

/* ===== TIP BOX ===== */
.tp-tip{background:linear-gradient(135deg,#fef3c7,#fffbeb);border:1px solid #fde68a;border-radius:var(--radius-xl);padding:1rem 1.2rem;margin-bottom:1.5rem;display:flex;gap:.8rem;align-items:flex-start;font-size:.85rem;color:#92400e}
.tp-tip i{font-size:1.2rem;flex-shrink:0;margin-top:.1rem}
.tp-tip b{display:block;margin-bottom:.2rem}

@keyframes tpUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}

@media(max-width:968px){.tp-form-card{position:static}}
</style>
</head>
<body>
<?php require dirname(__DIR__) . '/partials/navbar.php'; ?>

<div class="container" style="padding:2.5rem 0 4rem">
    <!-- HERO -->
    <div class="tp-hero">
        <div class="tp-hero-icon"><i class="bi bi-lightbulb-fill"></i></div>
        <h1>Manajemen <span>Pengajuan Judul</span></h1>
        <p>Review pengajuan judul dari mahasiswa bimbingan Anda atau tawarkan topik riset kepada mahasiswa yang belum memiliki tugas akhir.</p>
        <div class="tp-hero-stats">
            <span class="tp-hstat"><i class="bi bi-inbox-fill"></i> Masuk <b><?= count($incoming) ?></b></span>
            <span class="tp-hstat"><i class="bi bi-send-fill"></i> Diajukan <b><?= count($outgoing) ?></b></span>
            <span class="tp-hstat"><i class="bi bi-people-fill"></i> Calon Bimbingan <b><?= count($students) ?></b></span>
        </div>
    </div>

    <?php if ($flashError): ?><div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill"></i> <?= e($flashError) ?></div><?php endif; ?>
    <?php if ($flashSuccess): ?><div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> <?= e($flashSuccess) ?></div><?php endif; ?>

    <div class="tp-grid">
        <!-- KOLOM KIRI -->
        <div>
            <!-- INCOMING PROPOSALS -->
            <div class="tp-card">
                <div class="tp-card-head">
                    <h3 class="tp-card-title"><i class="bi bi-inbox-fill"></i> Pengajuan dari Mahasiswa</h3>
                    <span class="tp-pill tp-pill-pending"><i class="bi bi-bell-fill"></i> <?= count($incoming) ?> Masuk</span>
                </div>
                <div class="tp-card-body">
                    <?php if (empty($incoming)): ?>
                        <div class="tp-empty">
                            <div class="tp-empty-icon"><i class="bi bi-inbox"></i></div>
                            <h4>Belum ada pengajuan</h4>
                            <p>Pengajuan judul dari mahasiswa akan muncul di sini.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($incoming as $p): ?>
                        <div class="tp-offer">
                            <div class="tp-offer-title"><?= e($p['title']) ?></div>
                            <div class="tp-offer-meta">
                                <span><i class="bi bi-mortarboard-fill"></i> <?= e($p['proposer_name']) ?></span>
                                <?php if (!empty($p['proposer_nim'] ?? null)): ?><span class="stud-num"><?= e($p['proposer_nim']) ?></span><?php endif; ?>
                                <span><i class="bi bi-bookmark-fill"></i> <?= e($p['work_type_name'] ?? '-') ?></span>
                                <span><i class="bi bi-calendar-event"></i> <?= date('d M Y', strtotime($p['created_at'])) ?></span>
                            </div>
                            <?php if (!empty($p['description'])): ?>
                            <div class="tp-offer-desc"><?= nl2br(e($p['description'])) ?></div>
                            <?php endif; ?>
                            <div class="tp-actions">
                                <form method="POST" action="<?= BASE_URL ?>/lecturer/proposals/respond">
                                    <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                                    <input type="hidden" name="proposal_id" value="<?= (int)$p['id'] ?>">
                                    <input type="hidden" name="action" value="accept">
                                    <button class="tp-btn tp-btn-accept"><i class="bi bi-check-circle-fill"></i> Setujui & Jadikan TA</button>
                                </form>
                                <form method="POST" action="<?= BASE_URL ?>/lecturer/proposals/respond" onsubmit="return confirm('Yakin menolak pengajuan ini?')">
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

            <!-- HISTORY -->
            <div class="tp-card">
                <div class="tp-card-head">
                    <h3 class="tp-card-title"><i class="bi bi-clock-history"></i> Riwayat Tawaran Saya</h3>
                </div>
                <div class="tp-card-body" style="padding:0">
                    <?php if (empty($outgoing)): ?>
                        <div class="tp-empty">
                            <div class="tp-empty-icon"><i class="bi bi-send"></i></div>
                            <h4>Belum ada tawaran</h4>
                            <p>Riwayat tawaran judul Anda akan muncul di sini.</p>
                        </div>
                    <?php else: ?>
                        <table class="tp-table">
                            <thead><tr><th>Judul</th><th>Mahasiswa</th><th>Tanggal</th><th>Status</th></tr></thead>
                            <tbody>
                            <?php foreach ($outgoing as $o): $m = $statusMeta[$o['status']] ?? $statusMeta['pending']; ?>
                                <tr>
                                    <td><strong><?= e($o['title']) ?></strong></td>
                                    <td><?= e($o['target_name']) ?></td>
                                    <td style="color:var(--gray-500);font-size:.8rem"><?= date('d M', strtotime($o['created_at'])) ?></td>
                                    <td><span class="tp-pill <?= $m['cls'] ?>"><i class="bi bi-<?= $m['icon'] ?>"></i> <?= $m['label'] ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- KOLOM KANAN: FORM -->
        <div>
            <?php if (empty($students)): ?>
                <div class="tp-card">
                    <div class="tp-card-body">
                        <div class="tp-empty">
                            <div class="tp-empty-icon"><i class="bi bi-people-fill"></i></div>
                            <h4>Semua mahasiswa sudah memiliki TA</h4>
                            <p>Tidak ada mahasiswa yang tersedia untuk ditawarkan judul.</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="tp-form-card">
                    <div class="tp-form-head">
                        <h3><i class="bi bi-send-fill"></i> Tawarkan Judul Riset</h3>
                        <p>Ajukan topik penelitian kepada mahasiswa</p>
                    </div>
                    <div style="padding:1.8rem">
                        <div class="tp-tip">
                            <i class="bi bi-lightbulb-fill"></i>
                            <div>
                                <b>Tips untuk Dosen</b>
                                Tawarkan judul yang sesuai bidang keahlian Anda. Mahasiswa akan mendapat notifikasi dan bisa langsung menyetujui.
                            </div>
                        </div>

                        <form method="POST" action="<?= BASE_URL ?>/lecturer/proposals/submit">
                            <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">

                            <div class="tp-field">
                                <label class="tp-label"><i class="bi bi-mortarboard-fill"></i> Mahasiswa Tujuan <span class="req">*</span></label>
                                <div class="tp-select-wrap">
                                    <i class="bi bi-person-fill lead"></i>
                                    <select name="student_user_id" class="tp-select" required>
                                        <option value="">-- Pilih Mahasiswa --</option>
                                        <?php foreach ($students as $s): ?>
                                            <option value="<?= (int)$s['id'] ?>"><?= e($s['name']) ?> (<?= e($s['student_number']) ?>)</option>
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

                            <div class="tp-field">
                                <label class="tp-label"><i class="bi bi-pencil-fill"></i> Judul yang Ditawarkan <span class="req">*</span></label>
                                <input type="text" name="title" class="tp-input" placeholder="Tuliskan judul riset yang Anda tawarkan..." required>
                            </div>

                            <div class="tp-field">
                                <label class="tp-label"><i class="bi bi-file-text"></i> Deskripsi / Ruang Lingkup</label>
                                <textarea name="description" class="tp-textarea" placeholder="Jelaskan singkat ruang lingkup riset, metode yang digunakan, atau latar belakang masalah..."></textarea>
                            </div>

                            <button type="submit" class="tp-btn-submit"><i class="bi bi-send-fill"></i> Kirim Tawaran ke Mahasiswa</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>