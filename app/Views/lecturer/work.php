<?php
declare(strict_types=1);
$flashError = $_SESSION['flash_error'] ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);
$works = $works ?? []; $work = $work ?? null; $logs = $logs ?? [];
$milestones = $milestones ?? []; $documents = $documents ?? [];
$slots = $slots ?? []; $bookings = $bookings ?? []; $analytics = $analytics ?? [];
$doneMs = count(array_filter($milestones, fn($m) => !empty($m['completed_at'])));
$progress = count($milestones) > 0 ? (int)round($doneMs / count($milestones) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tugas Akhir Bimbingan - <?= e(APP_NAME) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">
<style>
/* ===== HERO ===== */
.wk-hero{background:linear-gradient(135deg,#0ea5e9 0%,#0369a1 100%);border-radius:var(--radius-2xl);padding:2rem;margin-bottom:2rem;color:#fff;position:relative;overflow:hidden;animation:wkUp .6s cubic-bezier(.16,1,.3,1) both}
.wk-hero::before{content:'';position:absolute;top:-50%;right:-10%;width:400px;height:400px;background:radial-gradient(circle,rgba(255,255,255,.15) 0%,transparent 70%);border-radius:50%;animation:wkFloat 6s ease-in-out infinite}
.wk-hero::after{content:'';position:absolute;bottom:-30%;left:10%;width:300px;height:300px;background:radial-gradient(circle,rgba(6,182,212,.3) 0%,transparent 70%);border-radius:50%;animation:wkFloat 8s ease-in-out infinite reverse}
@keyframes wkFloat{0%,100%{transform:translate(0,0)}50%{transform:translate(-20px,-20px)}}
.wk-hero-content{position:relative;z-index:2;display:flex;justify-content:space-between;align-items:center;gap:2rem;flex-wrap:wrap}
.wk-hero h1{font-size:1.8rem;font-weight:800;margin-bottom:.3rem;display:flex;align-items:center;gap:.7rem}
.wk-hero h1 i{font-size:1.6rem}
.wk-hero p{opacity:.9;font-size:.95rem;margin:0}
.wk-hero-stats{display:flex;gap:.6rem;flex-wrap:wrap}
.wk-hero-stat{background:rgba(255,255,255,.15);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.2);border-radius:var(--radius-lg);padding:.7rem 1rem;min-width:90px;text-align:center}
.wk-hero-stat b{display:block;font-size:1.4rem;font-weight:800}
.wk-hero-stat span{font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;opacity:.9}

/* ===== STUDENT SELECTOR ===== */
.wk-students{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1rem;margin-bottom:2rem}
.wk-student{position:relative;background:#fff;border:1.5px solid var(--gray-200);border-radius:var(--radius-xl);padding:1.2rem;text-decoration:none;color:inherit;transition:.3s cubic-bezier(.16,1,.3,1);overflow:hidden;display:flex;gap:1rem;align-items:center}
.wk-student::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,#0ea5e9,#06b6d4);opacity:0;transition:.3s}
.wk-student:hover{transform:translateY(-4px);border-color:#7dd3fc;box-shadow:0 15px 35px -12px rgba(14,165,233,.25)}
.wk-student:hover::before{opacity:1}
.wk-student-on{background:linear-gradient(135deg,#f0f9ff,#fff);border-color:#0ea5e9;box-shadow:0 10px 25px -8px rgba(14,165,233,.3)}
.wk-student-on::before{opacity:1}
.wk-avatar{width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,#0ea5e9,#0369a1);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1.1rem;flex-shrink:0}
.wk-student-on .wk-avatar{box-shadow:0 0 0 3px rgba(14,165,233,.25)}
.wk-student-info{flex:1;min-width:0}
.wk-student-name{font-weight:700;color:var(--gray-900);font-size:.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:.2rem}
.wk-student-meta{font-size:.72rem;color:var(--gray-500);font-family:'JetBrains Mono',monospace}
.wk-student-status{position:absolute;top:.7rem;right:.7rem;font-size:.65rem;padding:.2rem .6rem;border-radius:999px;font-weight:700;text-transform:uppercase;letter-spacing:.04em}

/* ===== WORK HEADER ===== */
.wk-work-header{background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius-2xl);padding:1.6rem;margin-bottom:1.5rem;animation:wkUp .6s .1s cubic-bezier(.16,1,.3,1) both;box-shadow:var(--shadow-sm)}
.wk-work-title{font-size:1.3rem;font-weight:800;color:var(--gray-900);margin-bottom:.8rem;line-height:1.4}
.wk-chips{display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1rem}
.wk-chip{display:inline-flex;align-items:center;gap:.4rem;padding:.4rem .9rem;background:var(--gray-50);border:1px solid var(--gray-200);border-radius:999px;font-size:.78rem;font-weight:600;color:var(--gray-700)}
.wk-chip i{color:#0ea5e9}
.wk-progress-wrap{margin-top:1rem}
.wk-progress-label{display:flex;justify-content:space-between;font-size:.78rem;font-weight:700;color:var(--gray-600);margin-bottom:.4rem}
.wk-progress-bar{height:12px;background:var(--gray-100);border-radius:999px;overflow:hidden;position:relative}
.wk-progress-fill{height:100%;background:linear-gradient(90deg,#0ea5e9,#06b6d4);border-radius:999px;transition:width 1s cubic-bezier(.16,1,.3,1);position:relative;overflow:hidden}
.wk-progress-fill::after{content:'';position:absolute;inset:0;background:linear-gradient(90deg,transparent,rgba(255,255,255,.5),transparent);animation:wkShimmer 2s infinite}
@keyframes wkShimmer{0%{transform:translateX(-100%)}100%{transform:translateX(100%)}}
.wk-actions{display:flex;gap:.6rem;flex-wrap:wrap;margin-top:1.2rem;padding-top:1.2rem;border-top:1px solid var(--gray-100)}
.wk-action-btn{display:inline-flex;align-items:center;gap:.45rem;padding:.65rem 1.2rem;border-radius:var(--radius-lg);font-weight:700;font-size:.85rem;text-decoration:none;transition:.25s;cursor:pointer;border:none;font-family:inherit}
.wk-action-primary{background:linear-gradient(135deg,#0ea5e9,#0369a1);color:#fff;box-shadow:0 6px 15px -5px rgba(14,165,233,.5)}
.wk-action-primary:hover{transform:translateY(-2px);box-shadow:0 10px 25px -5px rgba(14,165,233,.6)}
.wk-action-secondary{background:#fff;color:var(--gray-700);border:1.5px solid var(--gray-200)}
.wk-action-secondary:hover{border-color:#0ea5e9;color:#0ea5e9;background:#f0f9ff}

/* ===== TABS ===== */
.wk-tabs{display:flex;gap:.4rem;flex-wrap:wrap;margin-bottom:1.5rem;padding:.4rem;background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius-xl);box-shadow:var(--shadow-sm);animation:wkUp .6s .15s cubic-bezier(.16,1,.3,1) both}
.wk-tab{padding:.7rem 1.2rem;border-radius:var(--radius-lg);border:none;background:transparent;color:var(--gray-600);font-weight:700;font-size:.85rem;cursor:pointer;font-family:inherit;display:inline-flex;gap:.5rem;align-items:center;transition:.25s;flex:1;justify-content:center;min-width:140px}
.wk-tab:hover{color:#0ea5e9;background:var(--gray-50)}
.wk-tab-active{background:linear-gradient(135deg,#0ea5e9,#0369a1);color:#fff;box-shadow:0 6px 15px -5px rgba(14,165,233,.5)}
.wk-tab i{font-size:1rem}
.wk-panel{display:none;animation:wkFade .4s ease}
.wk-panel-active{display:block}
@keyframes wkFade{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}

/* ===== CARDS ===== */
.wk-card{background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius-2xl);box-shadow:var(--shadow-sm);overflow:hidden;margin-bottom:1.5rem;animation:wkUp .6s both}
.wk-card-head{padding:1.2rem 1.6rem;border-bottom:1px solid var(--gray-200);background:linear-gradient(to right,var(--gray-50),#fff);font-weight:800;color:var(--gray-900);display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap}
.wk-card-head h3{font-size:1.05rem;margin:0;display:flex;align-items:center;gap:.6rem}
.wk-card-head h3 i{color:#0ea5e9}
.wk-card-body{padding:1.6rem}

/* ===== MILESTONE TIMELINE ===== */
.wk-timeline{position:relative;padding-left:2.5rem}
.wk-timeline::before{content:'';position:absolute;left:.9rem;top:.8rem;bottom:.8rem;width:2px;background:linear-gradient(to bottom,#0ea5e9,#06b6d4,#e5e7eb);border-radius:999px}
.wk-ms{position:relative;margin-bottom:1.2rem;padding:1rem 1.2rem;background:var(--gray-50);border:1px solid var(--gray-200);border-radius:var(--radius-lg);transition:.25s}
.wk-ms:last-child{margin-bottom:0}
.wk-ms:hover{background:#fff;border-color:#7dd3fc;transform:translateX(4px)}
.wk-ms-dot{position:absolute;left:-2.1rem;top:1.1rem;width:20px;height:20px;border-radius:50%;background:#fff;border:3px solid var(--gray-300);z-index:2}
.wk-ms-done{border-color:#10b981;background:linear-gradient(135deg,#ecfdf5,#fff)}
.wk-ms-done .wk-ms-dot{background:#10b981;border-color:#10b981}
.wk-ms-overdue{border-color:#fca5a5;background:linear-gradient(135deg,#fef2f2,#fff)}
.wk-ms-overdue .wk-ms-dot{background:#dc2626;border-color:#dc2626;animation:wkPulse 2s infinite}
@keyframes wkPulse{0%,100%{box-shadow:0 0 0 0 rgba(220,38,38,.5)}50%{box-shadow:0 0 0 8px rgba(220,38,38,0)}}
.wk-ms-title{font-weight:800;color:var(--gray-900);font-size:.95rem;margin-bottom:.3rem;display:flex;align-items:center;gap:.5rem;flex-wrap:wrap}
.wk-ms-date{font-size:.78rem;color:var(--gray-500);font-family:'JetBrains Mono',monospace;display:flex;align-items:center;gap:.3rem}

/* ===== DOCUMENTS TABLE ===== */
.wk-table{width:100%;border-collapse:collapse}
.wk-table th,.wk-table td{padding:.85rem 1.1rem;text-align:left;border-bottom:1px solid var(--gray-100);font-size:.88rem}
.wk-table th{background:var(--gray-50);color:var(--gray-600);font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;font-weight:800}
.wk-table tbody tr{transition:.2s}
.wk-table tbody tr:hover{background:var(--gray-50)}
.wk-table tbody tr:last-child td{border-bottom:none}
.wk-ver{font-family:'JetBrains Mono',monospace;font-size:.78rem;padding:.2rem .6rem;background:#eef2ff;color:#4338ca;border-radius:999px;font-weight:700}

/* ===== PILLS ===== */
.wk-pill{display:inline-flex;align-items:center;gap:.35rem;padding:.3rem .8rem;border-radius:999px;font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.04em}
.wk-pill i{font-size:.8rem}
.wk-pill-ok{background:#d1fae5;color:#065f46}
.wk-pill-wait{background:#fef3c7;color:#92400e}
.wk-pill-no{background:#fee2e2;color:#991b1b}
.wk-pill-info{background:#dbeafe;color:#1e40af}
.wk-pill-online{background:#dbeafe;color:#1e40af}
.wk-pill-offline{background:#fef3c7;color:#92400e}

/* ===== LOG BIMBINGAN ===== */
.wk-log-form{background:linear-gradient(135deg,#f0f9ff,#fff);border:1.5px solid #7dd3fc;border-radius:var(--radius-xl);padding:1.5rem;margin-bottom:1.5rem}
.wk-field{margin-bottom:1.1rem}
.wk-label{display:flex;align-items:center;gap:.4rem;font-size:.82rem;font-weight:700;color:var(--gray-700);margin-bottom:.4rem}
.wk-label i{color:#0ea5e9}
.wk-label .req{color:#ef4444}
.wk-input,.wk-select,.wk-textarea{width:100%;padding:.8rem 1rem;border:1.5px solid var(--gray-200);border-radius:var(--radius-lg);font-size:.9rem;font-family:inherit;background:#fff;transition:.25s}
.wk-input:focus,.wk-select:focus,.wk-textarea:focus{outline:none;border-color:#0ea5e9;box-shadow:0 0 0 4px rgba(14,165,233,.12)}
.wk-textarea{min-height:80px;resize:vertical;line-height:1.6}
.wk-grid-2{display:grid;grid-template-columns:1fr 2fr;gap:1rem}
@media(max-width:640px){.wk-grid-2{grid-template-columns:1fr}}
.wk-btn-submit{position:relative;overflow:hidden;display:inline-flex;align-items:center;gap:.5rem;padding:.85rem 1.5rem;background:linear-gradient(135deg,#0ea5e9,#0369a1);color:#fff;border:none;border-radius:var(--radius-lg);font-weight:800;font-size:.9rem;cursor:pointer;font-family:inherit;box-shadow:0 10px 25px -8px rgba(14,165,233,.6);transition:.25s}
.wk-btn-submit:hover{transform:translateY(-2px);box-shadow:0 15px 30px -8px rgba(14,165,233,.7)}
.wk-btn-submit::after{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.3),transparent);transition:.7s}
.wk-btn-submit:hover::after{left:100%}
.wk-btn-sm{padding:.5rem .9rem;font-size:.78rem;border-radius:var(--radius-md)}

.wk-log-item{position:relative;border:1px solid var(--gray-200);border-left:4px solid #0ea5e9;border-radius:var(--radius-lg);padding:1.2rem;margin-bottom:1rem;background:#fff;transition:.25s}
.wk-log-item:hover{box-shadow:0 10px 25px -8px rgba(14,165,233,.15);transform:translateX(2px)}
.wk-log-item-student{border-left-color:#8b5cf6}
.wk-log-head{display:flex;justify-content:space-between;align-items:flex-start;gap:.8rem;margin-bottom:.6rem;flex-wrap:wrap}
.wk-log-author{display:flex;align-items:center;gap:.6rem}
.wk-log-avatar{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#0ea5e9,#0369a1);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.85rem;flex-shrink:0}
.wk-log-item-student .wk-log-avatar{background:linear-gradient(135deg,#8b5cf6,#6d28d9)}
.wk-log-author-info b{display:block;font-size:.88rem;color:var(--gray-900)}
.wk-log-author-info span{font-size:.72rem;color:var(--gray-500)}
.wk-log-date-pill{font-size:.72rem;font-family:'JetBrains Mono',monospace;padding:.3rem .7rem;background:var(--gray-50);border-radius:999px;color:var(--gray-600);font-weight:700}
.wk-log-topic{font-weight:800;color:var(--gray-900);font-size:1rem;margin-bottom:.6rem}
.wk-log-section{margin-top:.7rem;padding:.7rem .9rem;background:var(--gray-50);border-radius:var(--radius-md);font-size:.85rem;color:var(--gray-600);line-height:1.6}
.wk-log-section b{display:block;color:var(--gray-800);font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem}

/* ===== SCHEDULE ===== */
.wk-slot-form{background:linear-gradient(135deg,#f0f9ff,#fff);border:1.5px solid #7dd3fc;border-radius:var(--radius-xl);padding:1.5rem;margin-bottom:1.5rem}
.wk-slot-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem}
.wk-slot-card{background:#fff;border:1.5px solid var(--gray-200);border-radius:var(--radius-lg);padding:1rem;margin-bottom:.8rem;display:flex;justify-content:space-between;align-items:center;gap:1rem;transition:.25s}
.wk-slot-card:hover{border-color:#7dd3fc;box-shadow:0 6px 15px -5px rgba(14,165,233,.15)}
.wk-slot-info b{display:block;font-size:.92rem;color:var(--gray-900);margin-bottom:.2rem;font-family:'JetBrains Mono',monospace}
.wk-slot-info span{font-size:.78rem;color:var(--gray-500);display:flex;gap:.8rem;flex-wrap:wrap}
.wk-slot-quota{display:flex;align-items:center;gap:.4rem;padding:.3rem .8rem;background:var(--gray-50);border-radius:999px;font-size:.78rem;font-weight:700;color:var(--gray-600)}
.wk-slot-quota-full{background:#fee2e2;color:#991b1b}

.wk-booking-card{background:#fff;border:1.5px solid var(--gray-200);border-radius:var(--radius-lg);padding:1.1rem;margin-bottom:.8rem;display:flex;justify-content:space-between;align-items:center;gap:1rem;transition:.25s;flex-wrap:wrap}
.wk-booking-card:hover{border-color:#7dd3fc}
.wk-booking-info{flex:1;min-width:200px}
.wk-booking-info b{display:block;font-size:.92rem;color:var(--gray-900);margin-bottom:.2rem}
.wk-booking-info span{font-size:.78rem;color:var(--gray-500);display:flex;gap:.8rem;flex-wrap:wrap;align-items:center}
.wk-booking-actions{display:flex;gap:.5rem}

/* ===== ANALYTICS ===== */
.wk-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:1.5rem}
.wk-stat{background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius-xl);padding:1.2rem;text-align:center;transition:.25s;position:relative;overflow:hidden}
.wk-stat::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#0ea5e9,#06b6d4)}
.wk-stat:hover{transform:translateY(-4px);box-shadow:0 10px 25px -8px rgba(14,165,233,.2)}
.wk-stat-icon{width:44px;height:44px;border-radius:var(--radius-lg);margin:0 auto .7rem;display:flex;align-items:center;justify-content:center;font-size:1.3rem;color:#fff;background:linear-gradient(135deg,#0ea5e9,#0369a1)}
.wk-stat b{display:block;font-size:1.6rem;color:var(--gray-900);font-weight:800}
.wk-stat span{font-size:.72rem;color:var(--gray-500);text-transform:uppercase;letter-spacing:.05em;font-weight:700}

/* ===== EMPTY ===== */
.wk-empty{text-align:center;padding:3rem 1.5rem;color:var(--gray-500)}
.wk-empty-icon{width:80px;height:80px;margin:0 auto 1rem;border-radius:50%;background:linear-gradient(135deg,#f0f9ff,#e0f2fe);display:flex;align-items:center;justify-content:center;font-size:2.2rem;color:#0ea5e9;animation:wkBounce 2s ease-in-out infinite}
@keyframes wkBounce{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
.wk-empty h4{color:var(--gray-800);font-size:1rem;font-weight:700;margin-bottom:.3rem}
.wk-empty p{font-size:.85rem;margin:0}

@keyframes wkUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}
</style>
</head>
<body>
<?php require dirname(__DIR__) . '/partials/navbar.php'; ?>
<div class="container" style="padding:2.5rem 0 4rem">

    <!-- HERO -->
    <div class="wk-hero">
        <div class="wk-hero-content">
            <div>
                <h1><i class="bi bi-journal-text"></i> Tugas Akhir Bimbingan</h1>
                <p>Kelola progress, jadwal, dan evaluasi mahasiswa bimbingan Anda.</p>
            </div>
            <div class="wk-hero-stats">
                <div class="wk-hero-stat"><b><?= count($works) ?></b><span>Mahasiswa</span></div>
                <div class="wk-hero-stat"><b><?= count($bookings) ?></b><span>Booking</span></div>
                <div class="wk-hero-stat"><b><?= count($logs) ?></b><span>Sesi Log</span></div>
            </div>
        </div>
    </div>

    <?php if ($flashError): ?><div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill"></i> <?= e($flashError) ?></div><?php endif; ?>
    <?php if ($flashSuccess): ?><div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> <?= e($flashSuccess) ?></div><?php endif; ?>

    <?php if (empty($works)): ?>
        <div class="wk-card">
            <div class="wk-empty">
                <div class="wk-empty-icon"><i class="bi bi-people-fill"></i></div>
                <h4>Belum Ada Mahasiswa Bimbingan</h4>
                <p>Tawarkan judul melalui menu <b>Pengajuan Judul</b> atau tunggu admin menugaskan mahasiswa.</p>
                <a href="<?= BASE_URL ?>/lecturer/proposals" class="wk-btn-submit" style="margin-top:1rem"><i class="bi bi-lightbulb-fill"></i> Tawarkan Judul Sekarang</a>
            </div>
        </div>
    <?php else: ?>
        <!-- STUDENT SELECTOR -->
        <div class="wk-students">
            <?php foreach ($works as $w): 
                $isOn = $work && (int)$work['id'] === (int)$w['id'];
                $initial = strtoupper(substr((string)$w['student_name'], 0, 1));
                $statusColor = match (true) {
                    $w['status'] === 'completed' => 'background:#d1fae5;color:#065f46',
                    in_array($w['status'], ['revision','writing','proposal']) => 'background:#dbeafe;color:#1e40af',
                    default => 'background:var(--gray-100);color:var(--gray-600)',
                };
            ?>
                <a href="<?= BASE_URL ?>/lecturer/work?id=<?= (int)$w['id'] ?>" class="wk-student <?= $isOn ? 'wk-student-on' : '' ?>">
                    <span class="wk-student-status" style="<?= $statusColor ?>"><?= e(AcademicWork::statusLabel($w['status'])) ?></span>
                    <div class="wk-avatar"><?= e($initial) ?></div>
                    <div class="wk-student-info">
                        <div class="wk-student-name"><?= e($w['student_name']) ?></div>
                        <div class="wk-student-meta"><?= e($w['student_number'] ?? '-') ?></div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ($work): ?>
        <!-- WORK HEADER -->
        <div class="wk-work-header">
            <div class="wk-work-title"><?= e($work['title']) ?></div>
            <div class="wk-chips">
                <span class="wk-chip"><i class="bi bi-mortarboard-fill"></i> <?= e($work['student_name']) ?></span>
                <span class="wk-chip"><i class="bi bi-bookmark-fill"></i> <?= e($work['work_type_name']) ?></span>
                <span class="wk-chip"><i class="bi bi-flag-fill"></i> <?= e(AcademicWork::statusLabel($work['status'])) ?></span>
                <?php if (!empty($work['study_program'])): ?><span class="wk-chip"><i class="bi bi-building"></i> <?= e($work['study_program']) ?></span><?php endif; ?>
            </div>
            <div class="wk-progress-wrap">
                <div class="wk-progress-label">
                    <span><i class="bi bi-graph-up-arrow"></i> Progress Milestone</span>
                    <span><b><?= $doneMs ?>/<?= count($milestones) ?></b> (<?= $progress ?>%)</span>
                </div>
                <div class="wk-progress-bar">
                    <div class="wk-progress-fill" style="width:<?= $progress ?>%"></div>
                </div>
            </div>
            <div class="wk-actions">
                <a href="<?= BASE_URL ?>/lecturer/work/export?id=<?= (int)$work['id'] ?>" class="wk-action-btn wk-action-primary"><i class="bi bi-file-earmark-pdf-fill"></i> Export Laporan PDF</a>
                <form method="POST" action="<?= BASE_URL ?>/repository/publish" style="display:inline">
                    <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                    <input type="hidden" name="academic_work_id" value="<?= (int)$work['id'] ?>">
                    <button class="wk-action-btn wk-action-secondary"><i class="bi bi-archive-fill"></i> Publish ke Repository</button>
                </form>
            </div>
        </div>

        <!-- TABS -->
        <div class="wk-tabs">
            <button class="wk-tab wk-tab-active" data-wk-tab="progress"><i class="bi bi-graph-up"></i> Progress & Dokumen</button>
            <button class="wk-tab" data-wk-tab="log"><i class="bi bi-journal-check"></i> Log Bimbingan</button>
            <button class="wk-tab" data-wk-tab="sched"><i class="bi bi-calendar-event"></i> Jadwal & Booking</button>
            <button class="wk-tab" data-wk-tab="analytics"><i class="bi bi-bar-chart-fill"></i> Analytics</button>
        </div>

        <!-- PANEL: PROGRESS -->
        <div class="wk-panel wk-panel-active" id="panel-progress">
            <div class="wk-card">
                <div class="wk-card-head">
                    <h3><i class="bi bi-flag-fill"></i> Timeline Milestone</h3>
                    <span class="wk-pill wk-pill-info"><i class="bi bi-list-check"></i> <?= $doneMs ?>/<?= count($milestones) ?> Selesai</span>
                </div>
                <div class="wk-card-body">
                    <?php if (empty($milestones)): ?>
                        <div class="wk-empty">
                            <div class="wk-empty-icon"><i class="bi bi-calendar-x"></i></div>
                            <h4>Belum ada milestone</h4>
                            <p>Milestone akan ditambahkan oleh admin untuk tugas akhir ini.</p>
                        </div>
                    <?php else: ?>
                        <div class="wk-timeline">
                            <?php foreach ($milestones as $m): 
                                $isDone = !empty($m['completed_at']);
                                $isOver = !$isDone && strtotime($m['due_date']) < time();
                                $cls = $isDone ? 'wk-ms-done' : ($isOver ? 'wk-ms-overdue' : '');
                            ?>
                            <div class="wk-ms <?= $cls ?>">
                                <div class="wk-ms-dot"></div>
                                <div class="wk-ms-title">
                                    <?= e($m['title']) ?>
                                    <?php if ($isDone): ?><span class="wk-pill wk-pill-ok"><i class="bi bi-check-circle-fill"></i> Selesai</span><?php endif; ?>
                                    <?php if ($isOver): ?><span class="wk-pill wk-pill-no"><i class="bi bi-exclamation-triangle-fill"></i> Terlambat</span><?php endif; ?>
                                </div>
                                <div class="wk-ms-date">
                                    <i class="bi bi-calendar-event"></i>
                                    Tenggat: <?= date('d M Y', strtotime($m['due_date'])) ?>
                                    <?php if ($isDone): ?> • Selesai: <?= date('d M Y', strtotime($m['completed_at'])) ?><?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="wk-card">
                <div class="wk-card-head">
                    <h3><i class="bi bi-folder-fill"></i> Dokumen Terunggah</h3>
                    <span class="wk-pill wk-pill-info"><i class="bi bi-file-earmark"></i> <?= count($documents) ?> file</span>
                </div>
                <div class="wk-card-body" style="padding:0">
                    <?php if (empty($documents)): ?>
                        <div class="wk-empty">
                            <div class="wk-empty-icon"><i class="bi bi-folder-x"></i></div>
                            <h4>Belum ada dokumen</h4>
                            <p>Mahasiswa belum mengunggah dokumen apapun.</p>
                        </div>
                    <?php else: ?>
                        <table class="wk-table">
                            <thead><tr><th>Jenis</th><th>File</th><th>Versi</th><th>Tanggal</th><th>Status Review</th></tr></thead>
                            <tbody>
                            <?php foreach ($documents as $d): ?>
                                <tr>
                                    <td><b><?= e($d['type_name']) ?></b></td>
                                    <td><?= e($d['original_filename'] ?? '-') ?></td>
                                    <td><span class="wk-ver">v<?= (int)($d['version_number'] ?? 0) ?></span></td>
                                    <td style="font-size:.8rem;color:var(--gray-500)"><?= $d['uploaded_at'] ? date('d M Y', strtotime($d['uploaded_at'])) : '-' ?></td>
                                    <td>
                                        <?php $rs = $d['review_status'] ?? null; ?>
                                        <span class="wk-pill <?= $rs==='approved'?'wk-pill-ok':($rs==='revision'?'wk-pill-no':($rs==='pending'?'wk-pill-wait':'wk-pill-wait')) ?>">
                                            <?php if ($rs === 'approved'): ?><i class="bi bi-check-circle-fill"></i>
                                            <?php elseif ($rs === 'revision'): ?><i class="bi bi-arrow-repeat"></i>
                                            <?php elseif ($rs === 'pending'): ?><i class="bi bi-hourglass-split"></i>
                                            <?php endif; ?>
                                            <?= e(ucfirst((string)($rs ?? '-'))) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- PANEL: LOG BIMBINGAN -->
        <div class="wk-panel" id="panel-log">
            <div class="wk-card">
                <div class="wk-card-head">
                    <h3><i class="bi bi-pencil-square"></i> Catat Sesi Bimbingan</h3>
                </div>
                <div class="wk-card-body">
                    <form method="POST" action="<?= BASE_URL ?>/lecturer/bimbingan/store" class="wk-log-form">
                        <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                        <input type="hidden" name="academic_work_id" value="<?= (int)$work['id'] ?>">
                        <div class="wk-grid-2">
                            <div class="wk-field">
                                <label class="wk-label"><i class="bi bi-calendar-event"></i> Tanggal <span class="req">*</span></label>
                                <input type="date" name="meeting_date" class="wk-input" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="wk-field">
                                <label class="wk-label"><i class="bi bi-chat-square-text"></i> Topik Pembahasan <span class="req">*</span></label>
                                <input type="text" name="topic" class="wk-input" placeholder="Misal: Review BAB 3 Metodologi" required>
                            </div>
                        </div>
                        <div class="wk-field">
                            <label class="wk-label"><i class="bi bi-list-ul"></i> Poin Diskusi</label>
                            <textarea name="discussion_points" class="wk-textarea" placeholder="Ringkasan pembahasan selama sesi..."></textarea>
                        </div>
                        <div class="wk-field">
                            <label class="wk-label"><i class="bi bi-check2-square"></i> Action Items untuk Mahasiswa</label>
                            <textarea name="action_items" class="wk-textarea" placeholder="1. Perbaiki diagram alir&#10;2. Tambahkan referensi..."></textarea>
                        </div>
                        <div class="wk-field">
                            <label class="wk-label"><i class="bi bi-journal-text"></i> Catatan Dosen</label>
                            <textarea name="lecturer_notes" class="wk-textarea" placeholder="Catatan internal dosen..."></textarea>
                        </div>
                        <label style="display:flex;gap:.5rem;align-items:center;font-size:.85rem;margin-bottom:1rem;cursor:pointer">
                            <input type="checkbox" name="is_attended" value="1" checked>
                            <span>Mahasiswa hadir dalam sesi ini</span>
                        </label>
                        <button type="submit" class="wk-btn-submit"><i class="bi bi-save-fill"></i> Simpan Log Bimbingan</button>
                    </form>
                </div>
            </div>

            <div class="wk-card">
                <div class="wk-card-head">
                    <h3><i class="bi bi-clock-history"></i> Riwayat Bimbingan</h3>
                    <span class="wk-pill wk-pill-info"><?= count($logs) ?> sesi</span>
                </div>
                <div class="wk-card-body">
                    <?php if (empty($logs)): ?>
                        <div class="wk-empty">
                            <div class="wk-empty-icon"><i class="bi bi-journal-x"></i></div>
                            <h4>Belum ada log bimbingan</h4>
                            <p>Catat sesi bimbingan pertama Anda di form di atas.</p>
                        </div>
                    <?php else: foreach ($logs as $l): 
                        $isLecturer = ($l['author_role'] ?? '') === 'lecturer';
                        $initial = strtoupper(substr((string)$l['author_name'], 0, 1));
                    ?>
                        <div class="wk-log-item <?= $isLecturer ? '' : 'wk-log-item-student' ?>">
                            <div class="wk-log-head">
                                <div class="wk-log-author">
                                    <div class="wk-log-avatar"><?= e($initial) ?></div>
                                    <div class="wk-log-author-info">
                                        <b><?= e($l['author_name']) ?></b>
                                        <span><?= e(ucfirst((string)$l['author_role'])) ?></span>
                                    </div>
                                </div>
                                <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap">
                                    <span class="wk-log-date-pill"><i class="bi bi-calendar-event"></i> <?= date('d M Y', strtotime($l['meeting_date'])) ?></span>
                                    <span class="wk-pill <?= $l['is_attended'] ? 'wk-pill-ok' : 'wk-pill-no' ?>">
                                        <i class="bi bi-<?= $l['is_attended'] ? 'check-circle-fill' : 'x-circle-fill' ?>"></i>
                                        <?= $l['is_attended'] ? 'Hadir' : 'Tidak Hadir' ?>
                                    </span>
                                </div>
                            </div>
                            <div class="wk-log-topic"><?= e($l['topic']) ?></div>
                            <?php if ($l['discussion_points']): ?>
                            <div class="wk-log-section">
                                <b><i class="bi bi-chat-square-text"></i> Poin Diskusi</b>
                                <?= nl2br(e($l['discussion_points'])) ?>
                            </div>
                            <?php endif; ?>
                            <?php if ($l['action_items']): ?>
                            <div class="wk-log-section">
                                <b><i class="bi bi-check2-square"></i> Action Items</b>
                                <?= nl2br(e($l['action_items'])) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>

        <!-- PANEL: JADWAL & BOOKING -->
        <div class="wk-panel" id="panel-sched">
            <div class="wk-card">
                <div class="wk-card-head">
                    <h3><i class="bi bi-plus-circle-fill"></i> Buka Slot Bimbingan</h3>
                </div>
                <div class="wk-card-body">
                    <form method="POST" action="<?= BASE_URL ?>/lecturer/schedule/slot" class="wk-slot-form">
                        <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                        <div class="wk-slot-grid">
                            <div class="wk-field">
                                <label class="wk-label"><i class="bi bi-calendar-event"></i> Tanggal <span class="req">*</span></label>
                                <input type="date" name="slot_date" class="wk-input" min="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="wk-field">
                                <label class="wk-label"><i class="bi bi-clock"></i> Mulai <span class="req">*</span></label>
                                <input type="time" name="start_time" class="wk-input" required>
                            </div>
                            <div class="wk-field">
                                <label class="wk-label"><i class="bi bi-clock-fill"></i> Selesai <span class="req">*</span></label>
                                <input type="time" name="end_time" class="wk-input" required>
                            </div>
                            <div class="wk-field">
                                <label class="wk-label"><i class="bi bi-people"></i> Kuota</label>
                                <input type="number" name="max_students" class="wk-input" value="3" min="1" max="10">
                            </div>
                            <div class="wk-field">
                                <label class="wk-label"><i class="bi bi-display"></i> Mode</label>
                                <select name="mode" class="wk-select">
                                    <option value="offline">🏫 Offline</option>
                                    <option value="online">🌐 Online</option>
                                </select>
                            </div>
                            <div class="wk-field">
                                <label class="wk-label"><i class="bi bi-geo-alt"></i> Lokasi</label>
                                <input type="text" name="location" class="wk-input" placeholder="Ruang...">
                            </div>
                            <div class="wk-field" style="grid-column:span 2">
                                <label class="wk-label"><i class="bi bi-link-45deg"></i> Link Meeting</label>
                                <input type="url" name="link_meeting" class="wk-input" placeholder="https://zoom.us/...">
                            </div>
                        </div>
                        <button type="submit" class="wk-btn-submit" style="margin-top:.5rem"><i class="bi bi-plus-circle-fill"></i> Buka Slot</button>
                    </form>
                </div>
            </div>

            <div class="wk-card">
                <div class="wk-card-head">
                    <h3><i class="bi bi-calendar-week"></i> Slot Saya</h3>
                    <span class="wk-pill wk-pill-info"><?= count($slots) ?> slot</span>
                </div>
                <div class="wk-card-body">
                    <?php if (empty($slots)): ?>
                        <div class="wk-empty">
                            <div class="wk-empty-icon"><i class="bi bi-calendar-x"></i></div>
                            <h4>Belum ada slot</h4>
                            <p>Buka slot bimbingan untuk mahasiswa Anda.</p>
                        </div>
                    <?php else: foreach ($slots as $s): 
                        $isFull = (int)$s['booked_count'] >= (int)$s['max_students'];
                    ?>
                        <div class="wk-slot-card">
                            <div class="wk-slot-info">
                                <b><?= date('d M Y', strtotime($s['slot_date'])) ?> • <?= substr((string)$s['start_time'],0,5) ?>-<?= substr((string)$s['end_time'],0,5) ?></b>
                                <span>
                                    <span class="wk-pill <?= $s['mode'] === 'online' ? 'wk-pill-online' : 'wk-pill-offline' ?>">
                                        <?= $s['mode'] === 'online' ? '🌐 Online' : '🏫 Offline' ?>
                                    </span>
                                    <?php if ($s['location']): ?><span><i class="bi bi-geo-alt"></i> <?= e($s['location']) ?></span><?php endif; ?>
                                </span>
                            </div>
                            <div class="wk-slot-quota <?= $isFull ? 'wk-slot-quota-full' : '' ?>">
                                <i class="bi bi-people-fill"></i> <?= (int)$s['booked_count'] ?>/<?= (int)$s['max_students'] ?>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>

            <div class="wk-card">
                <div class="wk-card-head">
                    <h3><i class="bi bi-calendar-check"></i> Permintaan Booking</h3>
                    <span class="wk-pill wk-pill-info"><?= count($bookings) ?> booking</span>
                </div>
                <div class="wk-card-body">
                    <?php if (empty($bookings)): ?>
                        <div class="wk-empty">
                            <div class="wk-empty-icon"><i class="bi bi-inbox"></i></div>
                            <h4>Tidak ada booking</h4>
                            <p>Permintaan booking dari mahasiswa akan muncul di sini.</p>
                        </div>
                    <?php else: foreach ($bookings as $b): ?>
                        <div class="wk-booking-card">
                            <div class="wk-booking-info">
                                <b><?= e($b['student_name'] ?? '-') ?></b>
                                <span>
                                    <i class="bi bi-calendar-event"></i> <?= date('d M Y', strtotime($b['slot_date'])) ?> • <?= substr((string)$b['start_time'],0,5) ?>
                                    <?php if ($b['agenda']): ?><span><i class="bi bi-chat-square-text"></i> <?= e($b['agenda']) ?></span><?php endif; ?>
                                </span>
                            </div>
                            <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap">
                                <span class="wk-pill <?= $b['status']==='approved'?'wk-pill-ok':($b['status']==='pending'?'wk-pill-wait':'wk-pill-no') ?>">
                                    <?= ucfirst($b['status']) ?>
                                </span>
                                <?php if ($b['status'] === 'pending'): ?>
                                <div class="wk-booking-actions">
                                    <form method="POST" action="<?= BASE_URL ?>/lecturer/schedule/respond">
                                        <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                                        <input type="hidden" name="booking_id" value="<?= (int)$b['id'] ?>">
                                        <button name="action" value="approve" class="wk-btn-submit wk-btn-sm" style="background:#10b981"><i class="bi bi-check2"></i> Approve</button>
                                    </form>
                                    <form method="POST" action="<?= BASE_URL ?>/lecturer/schedule/respond">
                                        <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                                        <input type="hidden" name="booking_id" value="<?= (int)$b['id'] ?>">
                                        <button name="action" value="reject" class="wk-btn-submit wk-btn-sm" style="background:#dc2626"><i class="bi bi-x"></i> Reject</button>
                                    </form>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>

        <!-- PANEL: ANALYTICS -->
        <div class="wk-panel" id="panel-analytics">
            <div class="wk-stats">
                <div class="wk-stat">
                    <div class="wk-stat-icon"><i class="bi bi-people-fill"></i></div>
                    <b><?= (int)($analytics['total_students'] ?? 0) ?></b>
                    <span>Mahasiswa Aktif</span>
                </div>
                <div class="wk-stat">
                    <div class="wk-stat-icon" style="background:linear-gradient(135deg,#10b981,#059669)"><i class="bi bi-trophy-fill"></i></div>
                    <b><?= (int)($analytics['completed'] ?? 0) ?></b>
                    <span>Lulus</span>
                </div>
                <div class="wk-stat">
                    <div class="wk-stat-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)"><i class="bi bi-hourglass-split"></i></div>
                    <b><?= (int)($analytics['pending_reviews'] ?? 0) ?></b>
                    <span>Review Menunggu</span>
                </div>
                <div class="wk-stat">
                    <div class="wk-stat-icon" style="background:linear-gradient(135deg,#8b5cf6,#6d28d9)"><i class="bi bi-clock-history"></i></div>
                    <b><?= (float)($analytics['avg_review_days'] ?? 0) ?> hari</b>
                    <span>Rata-rata Review</span>
                </div>
            </div>

            <div class="wk-card">
                <div class="wk-card-head">
                    <h3><i class="bi bi-activity"></i> Aktivitas Terbaru Mahasiswa</h3>
                </div>
                <div class="wk-card-body" style="padding:0">
                    <?php if (empty($analytics['most_active'] ?? [])): ?>
                        <div class="wk-empty">
                            <div class="wk-empty-icon"><i class="bi bi-graph-up"></i></div>
                            <h4>Belum ada aktivitas</h4>
                        </div>
                    <?php else: ?>
                        <table class="wk-table">
                            <thead><tr><th>Mahasiswa</th><th>Judul</th><th>Status</th></tr></thead>
                            <tbody>
                            <?php foreach ($analytics['most_active'] as $ma): ?>
                                <tr>
                                    <td><b><?= e($ma['name']) ?></b></td>
                                    <td><?= e(mb_substr($ma['title'],0,60)) ?>...</td>
                                    <td><span class="wk-pill wk-pill-info"><?= e(AcademicWork::statusLabel($ma['status'])) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
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