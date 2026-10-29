<?php
declare(strict_types=1);
$flashError = $_SESSION['flash_error'] ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

$get = fn(string $k, string $d = ''): string => (string) SystemSetting::get($k, $d);
$uploadMb     = $get('max_upload_size_mb', '25');
$maxSup       = $get('max_supervisors', '2');
$plagiarism   = $get('plagiarism_threshold', '30.00');
$regOpen      = $get('is_registration_open', '1') === '1';
$plagRequired = $get('require_plagiarism_check', '1') === '1';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Sistem - <?= e(APP_NAME) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">

    <style>
        /* =========================================================
           SYSTEM SETTINGS ULTRA PREMIUM - SCOPED STYLES
           Namespace: .st- | Tema: Indigo (#6366f1)
           ========================================================= */
        .st-view { animation: stFade 0.4s ease; }
        @keyframes stFade { from { opacity: 0; } to { opacity: 1; } }

        /* --- Page Header --- */
        .st-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .st-header-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.875rem;
            font-weight: 800;
            color: var(--gray-900);
            margin: 0 0 0.35rem 0;
        }

        .st-header-icon {
            width: 48px; height: 48px;
            background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4f46e5;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .st-header-sub {
            color: var(--gray-500);
            font-size: 0.95rem;
            margin: 0 0 0 calc(48px + 0.75rem);
        }

        .st-header-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.55rem 1.15rem;
            border-radius: var(--radius-full);
            font-weight: 700;
            font-size: 0.8rem;
            letter-spacing: 0.02em;
        }
        .st-header-pill.is-open   { background: #d1fae5; color: #065f46; }
        .st-header-pill.is-closed { background: #fee2e2; color: #991b1b; }

        /* --- Stats Strip --- */
        .st-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.75rem;
        }

        .st-stat {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-xl);
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s;
            animation: stUp 0.5s ease backwards;
        }
        .st-stat:nth-child(1) { animation-delay: 0.05s; }
        .st-stat:nth-child(2) { animation-delay: 0.12s; }
        .st-stat:nth-child(3) { animation-delay: 0.19s; }
        .st-stat:nth-child(4) { animation-delay: 0.26s; }

        @keyframes stUp {
            from { opacity: 0; transform: translateY(15px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .st-stat:hover { transform: translateY(-3px); box-shadow: var(--shadow-lg); }

        .st-stat-icon {
            width: 46px; height: 46px;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: white;
            flex-shrink: 0;
        }
        .st-stat-icon.i-upload { background: linear-gradient(135deg,#6366f1,#4f46e5); }
        .st-stat-icon.i-sup    { background: linear-gradient(135deg,#10b981,#059669); }
        .st-stat-icon.i-plag   { background: linear-gradient(135deg,#f59e0b,#d97706); }
        .st-stat-icon.i-reg    { background: linear-gradient(135deg,#06b6d4,#0891b2); }

        .st-stat-num {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--gray-900);
            line-height: 1.1;
            font-variant-numeric: tabular-nums;
        }
        .st-stat-num small { font-size: 0.85rem; font-weight: 700; color: var(--gray-500); }

        .st-stat-label {
            font-size: 0.75rem;
            color: var(--gray-500);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        /* --- Card --- */
        .st-card {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .st-card-head {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            background: linear-gradient(to right, var(--gray-50), white);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .st-card-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--gray-800);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
        }
        .st-card-title i { color: #6366f1; }

        .st-badge {
            padding: 0.28rem 0.75rem;
            border-radius: var(--radius-full);
            background: #e0e7ff;
            color: #4338ca;
            font-size: 0.72rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .st-body { padding: 0.5rem 1.5rem 1.5rem; }

        /* --- Section & Rows --- */
        .st-section-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--gray-700);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 1.6rem 0 0.25rem;
        }
        .st-section-title i { color: #6366f1; }
        .st-section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--gray-200);
            margin-left: 0.5rem;
        }

        .st-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.25rem;
            padding: 1.1rem 0;
            border-bottom: 1px dashed var(--gray-200);
        }
        .st-row:last-child { border-bottom: none; }

        .st-row-info { display: flex; align-items: flex-start; gap: 0.9rem; flex: 1; min-width: 0; }

        .st-row-icon {
            width: 40px; height: 40px;
            border-radius: var(--radius-md);
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6366f1;
            font-size: 1.05rem;
            flex-shrink: 0;
        }

        .st-row-label { font-weight: 700; color: var(--gray-800); font-size: 0.9rem; margin-bottom: 0.15rem; }
        .st-row-desc  { font-size: 0.78rem; color: var(--gray-500); line-height: 1.45; }

        .st-row-control { flex-shrink: 0; display: flex; align-items: center; gap: 0.6rem; }

        .st-input {
            width: 110px;
            padding: 0.65rem 0.85rem;
            border: 1.5px solid var(--gray-300);
            border-radius: var(--radius-md);
            font-size: 0.9rem;
            font-family: inherit;
            font-weight: 600;
            text-align: center;
            transition: all 0.25s;
            background: white;
            color: var(--gray-800);
        }
        .st-input:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99,102,241,0.12);
        }
        .st-input-suffix { font-size: 0.8rem; font-weight: 700; color: var(--gray-500); }

        /* --- Toggle Switch --- */
        .st-switch { position: relative; display: inline-block; width: 52px; height: 28px; flex-shrink: 0; }
        .st-switch input { opacity: 0; width: 0; height: 0; }
        .st-slider {
            position: absolute; inset: 0;
            background: var(--gray-300);
            border-radius: 999px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .st-slider::before {
            content: '';
            position: absolute;
            width: 22px; height: 22px;
            left: 3px; top: 3px;
            background: white;
            border-radius: 50%;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .st-switch input:checked + .st-slider { background: linear-gradient(135deg,#6366f1,#4f46e5); }
        .st-switch input:checked + .st-slider::before { transform: translateX(24px); }

        .st-switch-state { font-size: 0.72rem; font-weight: 800; min-width: 62px; text-align: right; letter-spacing: 0.04em; }
        .st-switch-state.on  { color: #059669; }
        .st-switch-state.off { color: var(--gray-400); }

        /* --- Footer --- */
        .st-footer {
            padding: 1.25rem 1.5rem;
            background: linear-gradient(to right, var(--gray-50), white);
            border-top: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .st-footer-hint { display: flex; align-items: center; gap: 0.4rem; font-size: 0.8rem; color: var(--gray-500); }
        .st-footer-hint i { color: #f59e0b; }

        .st-save-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.85rem 2rem;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            border: none;
            border-radius: var(--radius-lg);
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            font-family: inherit;
            box-shadow: 0 10px 20px -8px rgba(99,102,241,0.5);
        }
        .st-save-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 15px 25px -8px rgba(99,102,241,0.6);
        }
        .st-save-btn:disabled { opacity: 0.65; cursor: not-allowed; }

        /* --- Info Grid --- */
        .st-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
            padding: 1.5rem;
        }
        .st-info-item {
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-lg);
            padding: 1rem 1.15rem;
            transition: all 0.25s;
        }
        .st-info-item:hover { background: white; box-shadow: var(--shadow-md); }
        .st-info-label {
            font-size: 0.68rem;
            font-weight: 700;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.3rem;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }
        .st-info-label i { color: #6366f1; }
        .st-info-value { font-weight: 700; color: var(--gray-800); font-size: 0.9rem; }
        .st-info-value.mono { font-family: 'JetBrains Mono', monospace; font-size: 0.82rem; color: #4f46e5; }

        @media (max-width: 768px) {
            .st-header-sub { margin-left: 0; }
            .st-row { flex-direction: column; align-items: stretch; gap: 0.9rem; }
            .st-row-control { justify-content: flex-end; }
            .st-footer { flex-direction: column; }
            .st-save-btn { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>
    <?php require dirname(__DIR__) . '/partials/navbar.php'; ?>

    <div class="container st-view">
        <!-- Page Header -->
        <div class="st-header">
            <div>
                <h1 class="st-header-title">
                    <span class="st-header-icon"><i class="bi bi-gear-fill"></i></span>
                    Pengaturan Sistem
                </h1>
                <p class="st-header-sub">Kelola batasan berkas, integritas akademik, dan saklar operasional <?= e(APP_NAME) ?>.</p>
            </div>
            <span class="st-header-pill <?= $regOpen ? 'is-open' : 'is-closed' ?>" id="stHeaderPill">
                <i class="bi bi-<?= $regOpen ? 'unlock-fill' : 'lock-fill' ?>"></i>
                <span id="stHeaderPillText"><?= $regOpen ? 'Pendaftaran Dibuka' : 'Pendaftaran Ditutup' ?></span>
            </span>
        </div>

        <!-- Flash Messages -->
        <?php if ($flashError): ?>
            <div class="alert alert-danger alert-dismissible">
                <div class="alert-content"><i class="bi bi-exclamation-triangle-fill"></i> <?= e($flashError) ?></div>
                <button class="btn-close">&times;</button>
            </div>
        <?php endif; ?>
        <?php if ($flashSuccess): ?>
            <div class="alert alert-success alert-dismissible">
                <div class="alert-content"><i class="bi bi-check-circle-fill"></i> <?= e($flashSuccess) ?></div>
                <button class="btn-close">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Stats Strip -->
        <div class="st-stats">
            <div class="st-stat">
                <div class="st-stat-icon i-upload"><i class="bi bi-cloud-arrow-up-fill"></i></div>
                <div>
                    <div class="st-stat-num"><span id="stStatUpload"><?= e($uploadMb) ?></span> <small>MB</small></div>
                    <div class="st-stat-label">Batas Upload</div>
                </div>
            </div>
            <div class="st-stat">
                <div class="st-stat-icon i-sup"><i class="bi bi-person-check-fill"></i></div>
                <div>
                    <div class="st-stat-num"><span id="stStatSup"><?= e($maxSup) ?></span> <small>Dosen</small></div>
                    <div class="st-stat-label">Maks. Pembimbing</div>
                </div>
            </div>
            <div class="st-stat">
                <div class="st-stat-icon i-plag"><i class="bi bi-shield-exclamation"></i></div>
                <div>
                    <div class="st-stat-num"><span id="stStatPlag"><?= e($plagiarism) ?></span><small>%</small></div>
                    <div class="st-stat-label">Ambang Plagiasi</div>
                </div>
            </div>
            <div class="st-stat">
                <div class="st-stat-icon i-reg"><i class="bi bi-toggle2-on"></i></div>
                <div>
                    <div class="st-stat-num" style="font-size: 1.15rem;" id="stStatReg"><?= $regOpen ? 'DIBUKA' : 'DITUTUP' ?></div>
                    <div class="st-stat-label">Pendaftaran Judul</div>
                </div>
            </div>
        </div>

        <!-- Form Konfigurasi -->
        <form method="POST" action="<?= BASE_URL ?>/admin/settings" id="stForm">
            <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">

            <div class="st-card">
                <div class="st-card-head">
                    <h3 class="st-card-title"><i class="bi bi-sliders"></i> Konfigurasi Utama</h3>
                    <span class="st-badge"><i class="bi bi-shield-lock-fill"></i> Khusus Admin</span>
                </div>

                <div class="st-body">
                    <h4 class="st-section-title"><i class="bi bi-hdd-stack-fill"></i> Batasan Berkas</h4>

                    <div class="st-row">
                        <div class="st-row-info">
                            <div class="st-row-icon"><i class="bi bi-cloud-arrow-up-fill"></i></div>
                            <div>
                                <div class="st-row-label">Batas Ukuran Upload</div>
                                <div class="st-row-desc">Ukuran maksimum berkas dokumen yang dapat diunggah mahasiswa, dalam satuan megabyte.</div>
                            </div>
                        </div>
                        <div class="st-row-control">
                            <input type="number" name="max_upload_size_mb" id="stInputUpload" class="st-input" min="1" max="100" value="<?= e($uploadMb) ?>" required>
                            <span class="st-input-suffix">MB</span>
                        </div>
                    </div>

                    <div class="st-row">
                        <div class="st-row-info">
                            <div class="st-row-icon"><i class="bi bi-person-check-fill"></i></div>
                            <div>
                                <div class="st-row-label">Maksimal Dosen Pembimbing</div>
                                <div class="st-row-desc">Jumlah maksimum dosen pembimbing yang dapat ditugaskan pada satu tugas akhir.</div>
                            </div>
                        </div>
                        <div class="st-row-control">
                            <input type="number" name="max_supervisors" id="stInputSup" class="st-input" min="1" max="5" value="<?= e($maxSup) ?>" required>
                            <span class="st-input-suffix">Dosen</span>
                        </div>
                    </div>

                    <h4 class="st-section-title"><i class="bi bi-shield-exclamation"></i> Integritas Akademik</h4>

                    <div class="st-row">
                        <div class="st-row-info">
                            <div class="st-row-icon"><i class="bi bi-clipboard2-pulse-fill"></i></div>
                            <div>
                                <div class="st-row-label">Ambang Batas Plagiasi Maksimum</div>
                                <div class="st-row-desc">Persentase similarity tertinggi yang masih ditoleransi sebelum dokumen dinyatakan tidak lolos.</div>
                            </div>
                        </div>
                        <div class="st-row-control">
                            <input type="number" step="0.01" name="plagiarism_threshold" id="stInputPlag" class="st-input" min="0" max="100" value="<?= e($plagiarism) ?>" required>
                            <span class="st-input-suffix">%</span>
                        </div>
                    </div>

                    <div class="st-row">
                        <div class="st-row-info">
                            <div class="st-row-icon"><i class="bi bi-file-earmark-check-fill"></i></div>
                            <div>
                                <div class="st-row-label">Wajib Cek Plagiasi</div>
                                <div class="st-row-desc">Mahasiswa wajib melampirkan hasil cek plagiasi yang lolos sebelum dapat mendaftar sidang.</div>
                            </div>
                        </div>
                        <div class="st-row-control">
                            <input type="hidden" name="require_plagiarism_check" value="0">
                            <span class="st-switch-state <?= $plagRequired ? 'on' : 'off' ?>" id="stPlagState"><?= $plagRequired ? 'WAJIB' : 'OPSIONAL' ?></span>
                            <label class="st-switch">
                                <input type="checkbox" name="require_plagiarism_check" value="1" id="stTogglePlag" <?= $plagRequired ? 'checked' : '' ?>>
                                <span class="st-slider"></span>
                            </label>
                        </div>
                    </div>

                    <h4 class="st-section-title"><i class="bi bi-toggle2-on"></i> Saklar Operasional</h4>

                    <div class="st-row">
                        <div class="st-row-info">
                            <div class="st-row-icon"><i class="bi bi-lightbulb-fill"></i></div>
                            <div>
                                <div class="st-row-label">Pendaftaran Pengajuan Judul</div>
                                <div class="st-row-desc">Jika ditutup, mahasiswa tidak dapat mengirim pengajuan judul baru hingga saklar dibuka kembali.</div>
                            </div>
                        </div>
                        <div class="st-row-control">
                            <input type="hidden" name="is_registration_open" value="0">
                            <span class="st-switch-state <?= $regOpen ? 'on' : 'off' ?>" id="stRegState"><?= $regOpen ? 'DIBUKA' : 'DITUTUP' ?></span>
                            <label class="st-switch">
                                <input type="checkbox" name="is_registration_open" value="1" id="stToggleReg" <?= $regOpen ? 'checked' : '' ?>>
                                <span class="st-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="st-footer">
                    <div class="st-footer-hint"><i class="bi bi-info-circle-fill"></i> Perubahan berlaku seketika setelah disimpan.</div>
                    <button type="submit" class="st-save-btn" id="stSaveBtn">
                        <i class="bi bi-save-fill"></i> Simpan Pengaturan
                    </button>
                </div>
            </div>
        </form>

        <!-- Informasi Sistem (Read-only) -->
        <div class="st-card">
            <div class="st-card-head">
                <h3 class="st-card-title"><i class="bi bi-cpu-fill"></i> Informasi Sistem</h3>
                <span class="st-badge">Read-only</span>
            </div>
            <div class="st-info-grid">
                <div class="st-info-item">
                    <div class="st-info-label"><i class="bi bi-tag-fill"></i> Versi Aplikasi</div>
                    <div class="st-info-value mono"><?= e(APP_VERSION) ?></div>
                </div>
                <div class="st-info-item">
                    <div class="st-info-label"><i class="bi bi-filetype-php"></i> Versi PHP</div>
                    <div class="st-info-value mono"><?= e(PHP_VERSION) ?></div>
                </div>
                <div class="st-info-item">
                    <div class="st-info-label"><i class="bi bi-database-fill"></i> Basis Data</div>
                    <div class="st-info-value mono"><?= e(DB_NAME) ?></div>
                </div>
                <div class="st-info-item">
                    <div class="st-info-label"><i class="bi bi-clock-fill"></i> Zona Waktu</div>
                    <div class="st-info-value"><?= e(date_default_timezone_get()) ?></div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/admin.js"></script>
    <script>
    (function() {
        'use strict';

        const $ = (id) => document.getElementById(id);

        // ===== LIVE SYNC: input -> stats strip =====
        const syncStat = (inputId, statId) => {
            const input = $(inputId), stat = $(statId);
            if (!input || !stat) return;
            input.addEventListener('input', () => { stat.textContent = input.value || '0'; });
        };
        syncStat('stInputUpload', 'stStatUpload');
        syncStat('stInputSup',    'stStatSup');
        syncStat('stInputPlag',   'stStatPlag');

        // ===== TOGGLE: Wajib Cek Plagiasi =====
        const plagToggle = $('stTogglePlag');
        plagToggle?.addEventListener('change', () => {
            const on = plagToggle.checked;
            const state = $('stPlagState');
            if (state) {
                state.textContent = on ? 'WAJIB' : 'OPSIONAL';
                state.className = 'st-switch-state ' + (on ? 'on' : 'off');
            }
        });

        // ===== TOGGLE: Pendaftaran Judul (sync ke pill header + stats) =====
        const regToggle = $('stToggleReg');
        regToggle?.addEventListener('change', () => {
            const on = regToggle.checked;
            const state = $('stRegState');
            const pill = $('stHeaderPill');
            const pillText = $('stHeaderPillText');
            const statReg = $('stStatReg');

            if (state) {
                state.textContent = on ? 'DIBUKA' : 'DITUTUP';
                state.className = 'st-switch-state ' + (on ? 'on' : 'off');
            }
            if (pill) {
                pill.className = 'st-header-pill ' + (on ? 'is-open' : 'is-closed');
                const icon = pill.querySelector('i');
                if (icon) icon.className = 'bi bi-' + (on ? 'unlock-fill' : 'lock-fill');
            }
            if (pillText) pillText.textContent = on ? 'Pendaftaran Dibuka' : 'Pendaftaran Ditutup';
            if (statReg) statReg.textContent = on ? 'DIBUKA' : 'DITUTUP';
        });

        // ===== SAVE BUTTON LOADING STATE =====
        $('stForm')?.addEventListener('submit', () => {
            const btn = $('stSaveBtn');
            if (!btn) return;
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyimpan...';
        });

        // ===== ALERT AUTO-DISMISS =====
        document.querySelectorAll('.st-view .alert-dismissible .btn-close').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const alert = e.target.closest('.alert');
                alert.style.transition = 'all 0.3s';
                alert.style.opacity = '0';
                alert.style.transform = 'translateX(30px)';
                setTimeout(() => alert.remove(), 300);
            });
        });

        document.querySelectorAll('.st-view .alert-success').forEach(alert => {
            setTimeout(() => {
                if (alert.parentElement) {
                    alert.style.transition = 'all 0.3s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }
            }, 5000);
        });

        console.log('%c✓ System Settings Loaded', 'color: #6366f1; font-weight: bold;');
    })();
    </script>
</body>
</html>