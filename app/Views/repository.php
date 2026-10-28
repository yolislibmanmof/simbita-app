<?php
declare(strict_types=1);
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);
$result = $result ?? ['data' => [], 'total' => 0, 'page' => 1, 'total_pages' => 0];
$q = $q ?? ''; $filters = $filters ?? []; $workTypes = $workTypes ?? []; $programs = $programs ?? []; $years = $years ?? [];

$buildUrl = function (array $override = []) use ($q, $filters) {
    $params = array_merge([
        'q' => $q,
        'work_type_id' => $filters['work_type_id'] ?? '',
        'study_program' => $filters['study_program'] ?? '',
        'year' => $filters['year'] ?? '',
    ], $override);
    $params = array_filter($params, fn($v) => $v !== '' && $v !== null);
    return BASE_URL . '/repository' . ($params ? '?' . http_build_query($params) : '');
};
$hasFilter = ($q !== '' || !empty($filters['work_type_id']) || !empty($filters['study_program']) || !empty($filters['year']));
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Repository Tugas Akhir - <?= e(APP_NAME) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">
<style>
/* ===== HERO ===== */
.rp-hero{text-align:center;margin-bottom:2.5rem;animation:rpUp .6s cubic-bezier(.16,1,.3,1) both}
.rp-hero-icon{width:76px;height:76px;margin:0 auto 1.2rem;border-radius:1.6rem;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;display:flex;align-items:center;justify-content:center;font-size:2.2rem;box-shadow:0 20px 40px -12px rgba(99,102,241,.55);animation:rpFloat 5s ease-in-out infinite}
@keyframes rpFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}
.rp-hero h1{font-size:2.2rem;font-weight:800;color:var(--gray-900);margin-bottom:.5rem;letter-spacing:-.02em}
.rp-hero h1 span{background:linear-gradient(135deg,#6366f1,#06b6d4);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.rp-hero p{color:var(--gray-500);max-width:520px;margin:0 auto}
.rp-hero-stats{display:flex;gap:.6rem;justify-content:center;margin-top:1.2rem;flex-wrap:wrap}
.rp-hstat{display:inline-flex;align-items:center;gap:.45rem;padding:.45rem 1rem;background:#fff;border:1px solid var(--gray-200);border-radius:999px;font-size:.8rem;font-weight:700;color:var(--gray-600)}
.rp-hstat i{color:#6366f1}

/* ===== SEARCH CONSOLE ===== */
.rp-console{background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius-2xl);box-shadow:var(--shadow-sm);padding:1.5rem;margin-bottom:1.5rem;animation:rpUp .6s .1s cubic-bezier(.16,1,.3,1) both}
.rp-search{position:relative;margin-bottom:1.1rem}
.rp-search>i{position:absolute;left:1.1rem;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:1.05rem;transition:.25s;pointer-events:none}
.rp-search input{width:100%;padding:1rem 3rem 1rem 3rem;border:1.5px solid var(--gray-200);border-radius:1rem;font-size:.95rem;font-family:inherit;background:var(--gray-50);transition:.25s}
.rp-search input:focus{outline:none;border-color:#6366f1;background:#fff;box-shadow:0 0 0 4px rgba(99,102,241,.12)}
.rp-search:focus-within>i{color:#6366f1}
.rp-clear{position:absolute;right:.9rem;top:50%;transform:translateY(-50%);width:30px;height:30px;border:none;border-radius:50%;background:var(--gray-100);color:var(--gray-500);cursor:pointer;display:none;align-items:center;justify-content:center;transition:.2s}
.rp-clear:hover{background:#fee2e2;color:#dc2626}
.rp-filter-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:1rem;margin-bottom:1.1rem}
.rp-field label{display:flex;align-items:center;gap:.4rem;font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:var(--gray-500);margin-bottom:.45rem}
.rp-field label i{color:#6366f1;font-size:.85rem}
.rp-select-wrap{position:relative}
.rp-select-wrap>.lead{position:absolute;left:.95rem;top:50%;transform:translateY(-50%);color:#94a3b8;pointer-events:none;transition:.25s;font-size:.95rem}
.rp-select-wrap>.chev{position:absolute;right:.95rem;top:50%;transform:translateY(-50%);color:#94a3b8;pointer-events:none;transition:.25s;font-size:.8rem}
.rp-select{appearance:none;-webkit-appearance:none;-moz-appearance:none;width:100%;padding:.8rem 2.5rem .8rem 2.6rem;border:1.5px solid var(--gray-200);border-radius:.9rem;font-size:.9rem;font-weight:600;color:var(--gray-700);background:#fff;cursor:pointer;transition:.25s;font-family:inherit}
.rp-select:hover{border-color:#c7d2fe;background:#fafaff}
.rp-select:focus{outline:none;border-color:#6366f1;box-shadow:0 0 0 4px rgba(99,102,241,.12)}
.rp-select-wrap:hover>.lead,.rp-select:focus~.lead{color:#6366f1}
.rp-select-wrap:hover>.chev{color:#6366f1;transform:translateY(-50%) scale(1.15)}
.rp-select option{padding:.5rem;font-weight:500}
.rp-actions{display:flex;gap:.7rem;flex-wrap:wrap}
.rp-btn-search{position:relative;overflow:hidden;display:inline-flex;align-items:center;gap:.5rem;padding:.8rem 1.8rem;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;border-radius:.9rem;font-weight:700;font-size:.9rem;cursor:pointer;font-family:inherit;box-shadow:0 10px 25px -8px rgba(99,102,241,.6);transition:.25s}
.rp-btn-search:hover{transform:translateY(-2px);box-shadow:0 15px 30px -8px rgba(99,102,241,.7)}
.rp-btn-search::after{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.35),transparent);transition:.6s}
.rp-btn-search:hover::after{left:100%}
.rp-btn-reset{display:inline-flex;align-items:center;gap:.5rem;padding:.8rem 1.4rem;background:#fff;color:var(--gray-600);border:1.5px solid var(--gray-200);border-radius:.9rem;font-weight:600;font-size:.9rem;cursor:pointer;font-family:inherit;text-decoration:none;transition:.25s}
.rp-btn-reset:hover{border-color:#fecaca;color:#dc2626;background:#fff5f5}

/* ===== ACTIVE FILTER CHIPS ===== */
.rp-chips{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;margin-bottom:1.5rem;animation:rpUp .4s both}
.rp-chips-label{font-size:.8rem;font-weight:700;color:var(--gray-500);display:inline-flex;align-items:center;gap:.35rem}
.rp-chip{display:inline-flex;align-items:center;gap:.45rem;padding:.35rem .5rem .35rem .9rem;background:#eef2ff;color:#4338ca;border:1px solid #c7d2fe;border-radius:999px;font-size:.78rem;font-weight:700}
.rp-chip a{display:flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:50%;background:rgba(67,56,202,.12);color:#4338ca;transition:.2s}
.rp-chip a:hover{background:#4338ca;color:#fff}
.rp-chip-clear{font-size:.78rem;font-weight:700;color:#dc2626;text-decoration:none;margin-left:.3rem}
.rp-chip-clear:hover{text-decoration:underline}

/* ===== RESULT META ===== */
.rp-meta-bar{display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;margin-bottom:1.2rem}
.rp-found{font-size:.85rem;color:var(--gray-500)}
.rp-found b{color:#6366f1}

/* ===== CARDS ===== */
.rp-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:1.5rem}
.rp-card{position:relative;background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius-2xl);padding:1.6rem;display:flex;flex-direction:column;gap:.75rem;transition:.3s cubic-bezier(.16,1,.3,1);overflow:hidden;animation:rpUp .6s both}
.rp-card:nth-child(1){animation-delay:.05s}.rp-card:nth-child(2){animation-delay:.1s}.rp-card:nth-child(3){animation-delay:.15s}
.rp-card:nth-child(4){animation-delay:.2s}.rp-card:nth-child(5){animation-delay:.25s}.rp-card:nth-child(6){animation-delay:.3s}
.rp-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,#6366f1,#8b5cf6,#06b6d4);opacity:0;transition:.3s}
.rp-card:hover{transform:translateY(-6px);box-shadow:0 25px 50px -12px rgba(99,102,241,.25);border-color:#c7d2fe}
.rp-card:hover::before{opacity:1}
.rp-type{position:absolute;top:1.1rem;right:1.1rem;padding:.3rem .8rem;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border-radius:999px;font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.05em}
.rp-title{font-weight:800;color:var(--gray-900);font-size:1.02rem;line-height:1.45;padding-right:5.5rem}
.rp-meta{font-size:.78rem;color:var(--gray-500);display:flex;gap:.8rem;flex-wrap:wrap}
.rp-meta i{color:#94a3b8}
.rp-abs{font-size:.85rem;color:var(--gray-600);line-height:1.65;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
.rp-foot{margin-top:auto;padding-top:.9rem;border-top:1px dashed var(--gray-200);display:flex;justify-content:space-between;align-items:center}
.rp-count{font-size:.75rem;color:var(--gray-500);font-weight:600;display:inline-flex;align-items:center;gap:.35rem}
.rp-dl{display:inline-flex;align-items:center;gap:.45rem;padding:.55rem 1.1rem;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border-radius:.7rem;font-weight:700;font-size:.8rem;text-decoration:none;box-shadow:0 6px 15px -5px rgba(99,102,241,.5);transition:.25s}
.rp-dl:hover{transform:translateY(-2px);box-shadow:0 10px 20px -5px rgba(99,102,241,.6)}

/* ===== EMPTY & PAGER ===== */
.rp-empty{text-align:center;padding:4rem 1rem;color:var(--gray-500);animation:rpUp .6s both}
.rp-empty-icon{width:90px;height:90px;margin:0 auto 1.2rem;border-radius:50%;background:linear-gradient(135deg,#eef2ff,#e0e7ff);display:flex;align-items:center;justify-content:center;font-size:2.4rem;color:#818cf8}
.rp-pager{display:flex;gap:.45rem;justify-content:center;margin-top:2.5rem;flex-wrap:wrap}
.rp-page{min-width:40px;height:40px;display:inline-flex;align-items:center;justify-content:center;border:1.5px solid var(--gray-200);border-radius:.7rem;background:#fff;color:var(--gray-600);font-weight:700;font-size:.85rem;text-decoration:none;transition:.25s;padding:0 .6rem}
.rp-page:hover{border-color:#6366f1;color:#6366f1;transform:translateY(-2px)}
.rp-page-on{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff !important;border-color:transparent;box-shadow:0 8px 18px -6px rgba(99,102,241,.6)}

@keyframes rpUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}
@media(max-width:640px){.rp-title{padding-right:0}.rp-type{position:static;display:inline-block;width:max-content;margin-bottom:.3rem}}
</style>
</head>
<body>
<?php require __DIR__ . '/partials/navbar.php'; ?>
<div class="container" style="padding:2.5rem 0 4rem">

    <div class="rp-hero">
        <div class="rp-hero-icon"><i class="bi bi-archive-fill"></i></div>
        <h1>Repository <span>Tugas Akhir</span></h1>
        <p>Kumpulan skripsi, tesis, dan disertasi yang telah selesai dan dipublikasikan sebagai referensi akademik.</p>
        <div class="rp-hero-stats">
            <span class="rp-hstat"><i class="bi bi-collection-fill"></i> <?= (int)$result['total'] ?> Publikasi</span>
            <span class="rp-hstat"><i class="bi bi-mortarboard-fill"></i> Referensi Terpercaya</span>
            <span class="rp-hstat"><i class="bi bi-shield-check"></i> Terverifikasi Dosen</span>
        </div>
    </div>

    <?php if ($flashError): ?><div class="alert alert-danger"><?= e($flashError) ?></div><?php endif; ?>

    <!-- SEARCH CONSOLE -->
    <form method="GET" action="<?= BASE_URL ?>/repository" class="rp-console" id="rpForm">
        <div class="rp-search">
            <i class="bi bi-search"></i>
            <input type="text" name="q" id="rpQ" value="<?= e($q) ?>" placeholder="Cari judul, kata kunci, atau nama mahasiswa..." autocomplete="off">
            <button type="button" class="rp-clear" id="rpClear" title="Bersihkan"><i class="bi bi-x"></i></button>
        </div>

        <div class="rp-filter-grid">
            <div class="rp-field">
                <label><i class="bi bi-bookmark-fill"></i> Jenis Tugas Akhir</label>
                <div class="rp-select-wrap">
                    <i class="bi bi-journal-text lead"></i>
                    <select name="work_type_id" class="rp-select">
                        <option value="">Semua Jenis</option>
                        <?php foreach ($workTypes as $wt): ?>
                        <option value="<?= (int)$wt['id'] ?>" <?= (($filters['work_type_id'] ?? '') == $wt['id']) ? 'selected' : '' ?>><?= e($wt['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <i class="bi bi-chevron-down chev"></i>
                </div>
            </div>
            <div class="rp-field">
                <label><i class="bi bi-building"></i> Program Studi</label>
                <div class="rp-select-wrap">
                    <i class="bi bi-mortarboard lead"></i>
                    <select name="study_program" class="rp-select">
                        <option value="">Semua Prodi</option>
                        <?php foreach ($programs as $p): ?>
                        <option value="<?= e($p) ?>" <?= (($filters['study_program'] ?? '') === $p) ? 'selected' : '' ?>><?= e($p) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <i class="bi bi-chevron-down chev"></i>
                </div>
            </div>
            <div class="rp-field">
                <label><i class="bi bi-calendar3"></i> Tahun Publikasi</label>
                <div class="rp-select-wrap">
                    <i class="bi bi-clock-history lead"></i>
                    <select name="year" class="rp-select">
                        <option value="">Semua Tahun</option>
                        <?php foreach ($years as $y): ?>
                        <option value="<?= (int)$y ?>" <?= (($filters['year'] ?? '') == $y) ? 'selected' : '' ?>><?= (int)$y ?></option>
                        <?php endforeach; ?>
                    </select>
                    <i class="bi bi-chevron-down chev"></i>
                </div>
            </div>
        </div>

        <div class="rp-actions">
            <button type="submit" class="rp-btn-search"><i class="bi bi-search"></i> Cari Publikasi</button>
            <a href="<?= BASE_URL ?>/repository" class="rp-btn-reset"><i class="bi bi-arrow-counterclockwise"></i> Reset Filter</a>
        </div>
    </form>

    <!-- ACTIVE FILTER CHIPS -->
    <?php if ($hasFilter): ?>
    <div class="rp-chips">
        <span class="rp-chips-label"><i class="bi bi-funnel-fill"></i> Filter aktif:</span>
        <?php if ($q !== ''): ?>
            <span class="rp-chip">"<?= e($q) ?>"<a href="<?= e($buildUrl(['q' => ''])) ?>"><i class="bi bi-x"></i></a></span>
        <?php endif; ?>
        <?php if (!empty($filters['work_type_id'])): ?>
            <?php foreach ($workTypes as $wt): if ((int)$wt['id'] === (int)$filters['work_type_id']): ?>
                <span class="rp-chip"><?= e($wt['name']) ?><a href="<?= e($buildUrl(['work_type_id' => ''])) ?>"><i class="bi bi-x"></i></a></span>
            <?php endif; endforeach; ?>
        <?php endif; ?>
        <?php if (!empty($filters['study_program'])): ?>
            <span class="rp-chip"><?= e($filters['study_program']) ?><a href="<?= e($buildUrl(['study_program' => ''])) ?>"><i class="bi bi-x"></i></a></span>
        <?php endif; ?>
        <?php if (!empty($filters['year'])): ?>
            <span class="rp-chip">Tahun <?= (int)$filters['year'] ?><a href="<?= e($buildUrl(['year' => ''])) ?>"><i class="bi bi-x"></i></a></span>
        <?php endif; ?>
        <a class="rp-chip-clear" href="<?= BASE_URL ?>/repository">Bersihkan semua</a>
    </div>
    <?php endif; ?>

    <div class="rp-meta-bar">
        <span class="rp-found">Menampilkan <b><?= count($result['data']) ?></b> dari <b><?= (int)$result['total'] ?></b> publikasi</span>
    </div>

    <!-- RESULTS -->
    <div class="rp-grid">
        <?php foreach ($result['data'] as $r): ?>
        <div class="rp-card">
            <span class="rp-type"><?= e($r['work_type_name']) ?></span>
            <div class="rp-title"><?= e($r['title']) ?></div>
            <div class="rp-meta">
                <span><i class="bi bi-person-fill"></i> <?= e($r['student_name']) ?></span>
                <span><i class="bi bi-building"></i> <?= e($r['study_program']) ?></span>
            </div>
            <div class="rp-meta">
                <span><i class="bi bi-calendar-event"></i> <?= date('d M Y', strtotime($r['published_at'])) ?></span>
                <span><i class="bi bi-hash"></i> <?= e($r['student_number'] ?? '') ?></span>
            </div>
            <div class="rp-abs"><?= e(mb_substr((string)$r['abstract'], 0, 180)) ?>...</div>
            <div class="rp-foot">
                <span class="rp-count"><i class="bi bi-download"></i> <?= (int)$r['download_count'] ?>x diunduh</span>
                <a href="<?= BASE_URL ?>/repository/download?id=<?= (int)$r['id'] ?>" class="rp-dl"><i class="bi bi-download"></i> Unduh</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($result['data'])): ?>
    <div class="rp-empty">
        <div class="rp-empty-icon"><i class="bi bi-archive"></i></div>
        <h3 style="color:var(--gray-800);margin-bottom:.4rem">Belum ada publikasi</h3>
        <p style="font-size:.9rem;margin:0">Tugas akhir yang telah selesai dan dipublikasikan dosen akan muncul di sini.</p>
    </div>
    <?php endif; ?>

    <!-- PAGINATION -->
    <?php if ($result['total_pages'] > 1): ?>
    <div class="rp-pager">
        <?php if ($result['page'] > 1): ?><a href="<?= e($buildUrl(['page' => $result['page'] - 1])) ?>" class="rp-page"><i class="bi bi-chevron-left"></i></a><?php endif; ?>
        <?php for ($i = 1; $i <= $result['total_pages']; $i++): ?>
            <a href="<?= e($buildUrl(['page' => $i])) ?>" class="rp-page <?= $i === $result['page'] ? 'rp-page-on' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($result['page'] < $result['total_pages']): ?><a href="<?= e($buildUrl(['page' => $result['page'] + 1])) ?>" class="rp-page"><i class="bi bi-chevron-right"></i></a><?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<script>
(function(){
    'use strict';
    // Auto-submit saat filter dropdown berubah (UX premium)
    document.querySelectorAll('.rp-select').forEach(s => {
        s.addEventListener('change', () => s.form.submit());
    });
    // Tombol clear pada search
    const q = document.getElementById('rpQ');
    const clr = document.getElementById('rpClear');
    const sync = () => { clr.style.display = q.value ? 'flex' : 'none'; };
    q.addEventListener('input', sync); sync();
    clr.addEventListener('click', () => { q.value = ''; q.focus(); sync(); });
})();
</script>
</body>
</html>