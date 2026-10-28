<?php
declare(strict_types=1);
$title = $title ?? ''; $abstract = $abstract ?? ''; $results = $results ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cari Dosen Cocok - <?= e(APP_NAME) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">
<style>
/* ===== HERO ===== */
.rc-hero{text-align:center;margin-bottom:2.5rem;animation:rcUp .6s cubic-bezier(.16,1,.3,1) both}
.rc-hero-icon{width:80px;height:80px;margin:0 auto 1.3rem;border-radius:2rem;background:linear-gradient(135deg,#8b5cf6,#06b6d4);color:#fff;display:flex;align-items:center;justify-content:center;font-size:2.4rem;box-shadow:0 20px 50px -12px rgba(139,92,246,.55);animation:rcFloat 5s ease-in-out infinite;position:relative}
.rc-hero-icon::after{content:'';position:absolute;inset:-10px;border-radius:2.4rem;background:linear-gradient(135deg,#8b5cf6,#06b6d4);opacity:.25;z-index:-1;filter:blur(20px);animation:rcPulse 3s ease-in-out infinite}
@keyframes rcFloat{0%,100%{transform:translateY(0) rotate(0deg)}50%{transform:translateY(-12px) rotate(5deg)}}
@keyframes rcPulse{0%,100%{opacity:.25}50%{opacity:.5}}
.rc-hero h1{font-size:2.2rem;font-weight:800;color:var(--gray-900);margin-bottom:.5rem;letter-spacing:-.02em}
.rc-hero h1 span{background:linear-gradient(135deg,#8b5cf6,#06b6d4);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.rc-hero p{color:var(--gray-500);max-width:560px;margin:0 auto;font-size:1rem;line-height:1.6}
.rc-hero-features{display:flex;gap:.6rem;justify-content:center;margin-top:1.3rem;flex-wrap:wrap}
.rc-hfeat{display:inline-flex;align-items:center;gap:.4rem;padding:.45rem 1rem;background:#fff;border:1px solid var(--gray-200);border-radius:999px;font-size:.78rem;font-weight:700;color:var(--gray-600)}
.rc-hfeat i{color:#8b5cf6}

/* ===== INPUT CONSOLE ===== */
.rc-console{background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius-2xl);box-shadow:var(--shadow-sm);padding:2rem;margin-bottom:2rem;animation:rcUp .6s .1s cubic-bezier(.16,1,.3,1) both}
.rc-field{margin-bottom:1.2rem}
.rc-label{display:flex;align-items:center;gap:.4rem;font-size:.82rem;font-weight:700;color:var(--gray-700);margin-bottom:.5rem}
.rc-label i{color:#8b5cf6}
.rc-label .req{color:#ef4444;margin-left:.1rem}
.rc-input,.rc-textarea{width:100%;padding:.85rem 1.1rem;border:1.5px solid var(--gray-200);border-radius:.9rem;font-size:.95rem;font-family:inherit;background:var(--gray-50);transition:.25s}
.rc-input:focus,.rc-textarea:focus{outline:none;border-color:#8b5cf6;background:#fff;box-shadow:0 0 0 4px rgba(139,92,246,.12)}
.rc-textarea{min-height:110px;resize:vertical;line-height:1.6}
.rc-submit{position:relative;overflow:hidden;display:inline-flex;align-items:center;gap:.5rem;padding:.95rem 2rem;background:linear-gradient(135deg,#8b5cf6,#06b6d4);color:#fff;border:none;border-radius:1rem;font-weight:700;font-size:.95rem;cursor:pointer;font-family:inherit;box-shadow:0 12px 30px -8px rgba(139,92,246,.6);transition:.25s;width:100%}
.rc-submit:hover{transform:translateY(-2px);box-shadow:0 18px 40px -8px rgba(139,92,246,.7)}
.rc-submit::after{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.3),transparent);transition:.7s}
.rc-submit:hover::after{left:100%}

/* ===== RESULTS HEADER ===== */
.rc-results-head{display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem;animation:rcUp .5s both}
.rc-results-title{font-size:1.1rem;font-weight:800;color:var(--gray-900);display:flex;align-items:center;gap:.5rem}
.rc-results-title i{color:#8b5cf6}
.rc-results-count{padding:.4rem 1rem;background:linear-gradient(135deg,#eef2ff,#e0e7ff);color:#4338ca;border-radius:999px;font-size:.78rem;font-weight:800}

/* ===== RANKING CARDS ===== */
.rc-card{position:relative;background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius-2xl);padding:1.6rem;margin-bottom:1.1rem;display:grid;grid-template-columns:auto 1fr auto;gap:1.3rem;align-items:center;transition:.3s cubic-bezier(.16,1,.3,1);animation:rcUp .6s both;overflow:hidden}
.rc-card:nth-child(1){animation-delay:.05s}.rc-card:nth-child(2){animation-delay:.1s}.rc-card:nth-child(3){animation-delay:.15s}
.rc-card:nth-child(4){animation-delay:.2s}.rc-card:nth-child(5){animation-delay:.25s}
.rc-card::before{content:'';position:absolute;top:0;left:0;width:5px;height:100%;background:linear-gradient(to bottom,#8b5cf6,#06b6d4);opacity:0;transition:.3s}
.rc-card:hover{transform:translateY(-4px);box-shadow:0 20px 40px -12px rgba(139,92,246,.2);border-color:#c7d2fe}
.rc-card:hover::before{opacity:1}

/* Podium effect untuk top 3 */
.rc-card.rc-rank-1{background:linear-gradient(135deg,#fef9c3 0%,#fff 30%);border-color:#fde68a}
.rc-card.rc-rank-1::before{background:linear-gradient(to bottom,#f59e0b,#fbbf24);opacity:1}
.rc-card.rc-rank-2{background:linear-gradient(135deg,#f3f4f6 0%,#fff 30%);border-color:#e5e7eb}
.rc-card.rc-rank-2::before{background:linear-gradient(to bottom,#9ca3af,#d1d5db);opacity:1}
.rc-card.rc-rank-3{background:linear-gradient(135deg,#fed7aa 0%,#fff 30%);border-color:#fdba74}
.rc-card.rc-rank-3::before{background:linear-gradient(to bottom,#ea580c,#fb923c);opacity:1}

.rc-rank-badge{width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,#8b5cf6,#06b6d4);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1.3rem;flex-shrink:0;box-shadow:0 8px 20px -6px rgba(139,92,246,.5);position:relative}
.rc-rank-badge::after{content:'';position:absolute;inset:-4px;border-radius:50%;border:2px dashed rgba(139,92,246,.3);animation:rcSpin 12s linear infinite}
@keyframes rcSpin{to{transform:rotate(360deg)}}
.rc-rank-1 .rc-rank-badge{background:linear-gradient(135deg,#f59e0b,#fbbf24);box-shadow:0 8px 20px -6px rgba(245,158,11,.6)}
.rc-rank-1 .rc-rank-badge::after{border-color:rgba(245,158,11,.3)}
.rc-rank-2 .rc-rank-badge{background:linear-gradient(135deg,#6b7280,#9ca3af);box-shadow:0 8px 20px -6px rgba(107,114,128,.6)}
.rc-rank-2 .rc-rank-badge::after{border-color:rgba(107,114,128,.3)}
.rc-rank-3 .rc-rank-badge{background:linear-gradient(135deg,#ea580c,#fb923c);box-shadow:0 8px 20px -6px rgba(234,88,12,.6)}
.rc-rank-3 .rc-rank-badge::after{border-color:rgba(234,88,12,.3)}

.rc-info{min-width:0}
.rc-name{font-weight:800;font-size:1.1rem;color:var(--gray-900);margin-bottom:.15rem;display:flex;align-items:center;gap:.5rem;flex-wrap:wrap}
.rc-name .crown{font-size:1.1rem}
.rc-dept{font-size:.85rem;color:var(--gray-500);margin-bottom:.7rem}
.rc-dept i{color:#94a3b8;margin-right:.3rem}

/* ===== KEYWORD CHIPS ===== */
.rc-kws{display:flex;gap:.4rem;flex-wrap:wrap;margin-bottom:.8rem}
.rc-kw{display:inline-flex;align-items:center;gap:.3rem;padding:.3rem .75rem;background:linear-gradient(135deg,#eef2ff,#e0e7ff);color:#4338ca;border:1px solid #c7d2fe;border-radius:999px;font-size:.75rem;font-weight:700;animation:rcPop .4s cubic-bezier(.16,1,.3,1) both}
.rc-kw:nth-child(1){animation-delay:.1s}.rc-kw:nth-child(2){animation-delay:.15s}.rc-kw:nth-child(3){animation-delay:.2s}
@keyframes rcPop{from{opacity:0;transform:scale(.8)}to{opacity:1;transform:scale(1)}}
.rc-kw i{font-size:.7rem}

/* ===== SCORE BAR ===== */
.rc-score-wrap{display:flex;align-items:center;gap:.7rem}
.rc-score-label{font-size:.7rem;font-weight:700;color:var(--gray-500);text-transform:uppercase;letter-spacing:.05em}
.rc-score-bar{flex:1;max-width:180px;height:10px;background:var(--gray-100);border-radius:999px;overflow:hidden;position:relative}
.rc-score-fill{height:100%;background:linear-gradient(90deg,#8b5cf6,#06b6d4);border-radius:999px;position:relative;animation:rcGrow 1.2s cubic-bezier(.16,1,.3,1) both}
@keyframes rcGrow{from{width:0 !important}}
.rc-score-fill::after{content:'';position:absolute;inset:0;background:linear-gradient(90deg,transparent,rgba(255,255,255,.5),transparent);animation:rcShimmer 2s infinite}
@keyframes rcShimmer{0%{transform:translateX(-100%)}100%{transform:translateX(100%)}}
.rc-score-val{font-size:.85rem;font-weight:800;color:#8b5cf6;min-width:40px}

/* ===== ACTION ===== */
.rc-action{flex-shrink:0}
.rc-act-btn{display:inline-flex;align-items:center;gap:.45rem;padding:.7rem 1.2rem;background:linear-gradient(135deg,#8b5cf6,#06b6d4);color:#fff;border-radius:.8rem;font-weight:700;font-size:.82rem;text-decoration:none;box-shadow:0 8px 18px -6px rgba(139,92,246,.5);transition:.25s;white-space:nowrap}
.rc-act-btn:hover{transform:translateY(-2px);box-shadow:0 12px 25px -6px rgba(139,92,246,.6);color:#fff}

/* ===== EMPTY STATE ===== */
.rc-empty{text-align:center;padding:3.5rem 1.5rem;animation:rcUp .6s both}
.rc-empty-icon{width:100px;height:100px;margin:0 auto 1.2rem;border-radius:50%;background:linear-gradient(135deg,#fef3c7,#fde68a);display:flex;align-items:center;justify-content:center;font-size:2.8rem;color:#d97706;animation:rcShake 3s ease-in-out infinite}
@keyframes rcShake{0%,100%{transform:rotate(0)}25%{transform:rotate(-8deg)}75%{transform:rotate(8deg)}}
.rc-empty h3{color:var(--gray-800);font-size:1.15rem;font-weight:800;margin-bottom:.4rem}
.rc-empty p{color:var(--gray-500);font-size:.9rem;margin:0 0 1.2rem}
.rc-empty a{display:inline-flex;align-items:center;gap:.4rem;padding:.6rem 1.2rem;background:#fef3c7;color:#92400e;border-radius:.7rem;font-weight:700;font-size:.85rem;text-decoration:none}
.rc-empty a:hover{background:#fde68a}

@keyframes rcUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}

@media(max-width:720px){
    .rc-card{grid-template-columns:auto 1fr;gap:1rem}
    .rc-action{grid-column:1/-1;justify-self:stretch;text-align:center}
    .rc-act-btn{width:100%;justify-content:center}
    .rc-name{font-size:1rem}
}
</style>
</head>
<body>
<?php require dirname(__DIR__) . '/partials/navbar.php'; ?>
<div class="container" style="padding:2.5rem 0 4rem;max-width:900px">

    <!-- HERO -->
    <div class="rc-hero">
        <div class="rc-hero-icon"><i class="bi bi-magic"></i></div>
        <h1>Smart Dosen <span>Recommendation</span></h1>
        <p>Sistem AI kami menganalisis judul dan deskripsi rencana tugas akhir Anda, lalu mencocokkannya dengan keahlian dosen untuk merekomendasikan pembimbing paling tepat.</p>
        <div class="rc-hero-features">
            <span class="rc-hfeat"><i class="bi bi-cpu"></i> AI Matching</span>
            <span class="rc-hfeat"><i class="bi bi-lightning-charge-fill"></i> Real-time</span>
            <span class="rc-hfeat"><i class="bi bi-shield-check"></i> Akurat</span>
        </div>
    </div>

    <!-- INPUT CONSOLE -->
    <form method="GET" action="<?= BASE_URL ?>/student/recommend" class="rc-console">
        <div class="rc-field">
            <label class="rc-label"><i class="bi bi-pencil-fill"></i> Rencana Judul Tugas Akhir <span class="req">*</span></label>
            <input type="text" name="title" value="<?= e($title) ?>" required placeholder="Misal: Sistem Deteksi Penyakit Tanaman menggunakan CNN dan Transfer Learning" class="rc-input">
        </div>
        <div class="rc-field">
            <label class="rc-label"><i class="bi bi-file-text"></i> Abstrak / Deskripsi (Opsional)</label>
            <textarea name="abstract" placeholder="Jelaskan singkat tentang ruang lingkup penelitian, metode yang digunakan, atau masalah yang ingin dipecahkan..." class="rc-textarea"><?= e($abstract) ?></textarea>
        </div>
        <button type="submit" class="rc-submit"><i class="bi bi-stars"></i> Analisis Kecocokan Dosen</button>
    </form>

    <?php if ($title !== ''): ?>
        <?php if (empty($results)): ?>
            <!-- EMPTY STATE -->
            <div class="rc-empty">
                <div class="rc-empty-icon"><i class="bi bi-emoji-frown"></i></div>
                <h3>Belum Ada Dosen yang Cocok</h3>
                <p>Sistem tidak menemukan dosen dengan keahlian yang sesuai dengan judul Anda. Coba gunakan kata kunci berbeda atau pilih dosen manual.</p>
                <a href="<?= BASE_URL ?>/student/proposals"><i class="bi bi-arrow-right"></i> Pilih Dosen Manual di Pengajuan Judul</a>
            </div>
        <?php else: 
            $max = max(array_column($results, 'score'));
            $rankLabels = ['🥇', '🥈', '🥉'];
        ?>
            <!-- RESULTS HEADER -->
            <div class="rc-results-head">
                <div class="rc-results-title"><i class="bi bi-trophy-fill"></i> Dosen Paling Cocok untuk Anda</div>
                <span class="rc-results-count"><?= count($results) ?> kandidat ditemukan</span>
            </div>

            <!-- RANKING CARDS -->
            <?php foreach ($results as $i => $r): 
                $rank = $i + 1;
                $rankClass = $rank <= 3 ? " rc-rank-$rank" : '';
                $scorePercent = (int)round($r['score'] / $max * 100);
            ?>
            <div class="rc-card<?= $rankClass ?>">
                <div class="rc-rank-badge">
                    <?= $rank <= 3 ? $rankLabels[$i] : "#$rank" ?>
                </div>
                <div class="rc-info">
                    <div class="rc-name">
                        <?= e($r['name']) ?><?php if (!empty($r['degree'])): ?>, <span style="color:var(--gray-500);font-weight:600"><?= e($r['degree']) ?></span><?php endif; ?>
                    </div>
                    <div class="rc-dept"><i class="bi bi-building"></i> <?= e($r['department'] ?? 'Departemen belum diatur') ?></div>
                    <div class="rc-kws">
                        <?php foreach ($r['matches'] as $m): ?>
                        <span class="rc-kw"><i class="bi bi-check-circle-fill"></i> <?= e($m) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="rc-score-wrap">
                        <span class="rc-score-label">Kecocokan</span>
                        <div class="rc-score-bar">
                            <div class="rc-score-fill" style="width:<?= $scorePercent ?>%"></div>
                        </div>
                        <span class="rc-score-val"><?= $scorePercent ?>%</span>
                    </div>
                </div>
                <div class="rc-action">
                    <a href="<?= BASE_URL ?>/student/proposals" class="rc-act-btn"><i class="bi bi-send-fill"></i> Ajukan Judul</a>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>