<?php
declare(strict_types=1);
$flashError   = $_SESSION['flash_error']   ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

// Data dokumen (asumsi dari controller)
$documentTypes = $documentTypes ?? [
    ['id' => 1, 'name' => 'Proposal Tugas Akhir', 'code' => 'proposal'],
    ['id' => 2, 'name' => 'Bab 1-3 (Pendahuluan s.d. Metode)', 'code' => 'bab1_3'],
    ['id' => 3, 'name' => 'Bab 4-5 (Hasil s.d. Penutup)', 'code' => 'bab4_5'],
    ['id' => 4, 'name' => 'Naskah Lengkap (Full Thesis)', 'code' => 'full'],
    ['id' => 5, 'name' => 'Dokumen Revisi', 'code' => 'revision'],
];

$maxSizeMB = 25;
$maxSizeBytes = $maxSizeMB * 1024 * 1024;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Dokumen - <?= e(APP_NAME) ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">
    
    <style>
        /* =========================================================
           STUDENT UPLOAD ULTRA PREMIUM - SCOPED STYLES
           Namespace: .su- | Tema: Ocean Blue (#3b82f6)
           ========================================================= */
        .su-view {
            --su-primary: #3b82f6;
            --su-primary-dark: #2563eb;
            --su-primary-light: #dbeafe;
            --su-gradient: linear-gradient(135deg, #3b82f6 0%, #2563eb 50%, #1e40af 100%);
            --su-glow: 0 10px 30px -10px rgba(59, 130, 246, 0.4);
            animation: suFade 0.4s ease;
        }
        @keyframes suFade { from { opacity: 0; } to { opacity: 1; } }

        /* --- Page Header --- */
        .su-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .su-header-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.875rem;
            font-weight: 800;
            color: var(--gray-900);
            margin: 0 0 0.35rem 0;
        }

        .su-header-icon {
            width: 48px; height: 48px;
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2563eb;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .su-header-sub {
            color: var(--gray-500);
            font-size: 0.95rem;
            margin: 0 0 0 calc(48px + 0.75rem);
        }

        .su-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            background: white;
            color: var(--gray-700);
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-lg);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.3s;
        }

        .su-back-btn:hover {
            background: var(--gray-50);
            border-color: var(--gray-400);
            transform: translateX(-3px);
        }

        /* --- Main Card --- */
        .su-card {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            animation: suUp 0.5s ease 0.2s backwards;
            max-width: 800px;
            margin: 0 auto;
        }

        @keyframes suUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .su-card-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            background: linear-gradient(to right, var(--gray-50), white);
        }

        .su-card-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
        }

        .su-card-title i { color: var(--su-primary); }

        .su-card-body { padding: 2rem; }

        /* --- Form Groups --- */
        .su-form-group { margin-bottom: 1.5rem; }

        .su-label {
            display: block;
            font-weight: 600;
            color: var(--gray-700);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .su-label-hint {
            font-weight: 400;
            color: var(--gray-500);
            font-size: 0.8rem;
            margin-left: 0.25rem;
        }

        .su-select {
            width: 100%;
            padding: 0.85rem 1rem;
            border: 1.5px solid var(--gray-300);
            border-radius: var(--radius-lg);
            font-size: 0.95rem;
            font-family: inherit;
            background: white;
            color: var(--gray-800);
            transition: all 0.25s;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 1rem center;
            background-repeat: no-repeat;
            background-size: 1.25rem;
        }

        .su-select:focus {
            outline: none;
            border-color: var(--su-primary);
            box-shadow: 0 0 0 4px rgba(59,130,246,0.12);
        }

        /* --- Drag & Drop Zone --- */
        .su-dropzone {
            border: 2px dashed var(--gray-300);
            border-radius: var(--radius-xl);
            padding: 3rem 2rem;
            text-align: center;
            background: var(--gray-50);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            cursor: pointer;
            position: relative;
        }

        .su-dropzone:hover, .su-dropzone.is-dragover {
            border-color: var(--su-primary);
            background: var(--su-primary-light);
            transform: translateY(-2px);
        }

        .su-dropzone.is-invalid {
            border-color: var(--accent-red);
            background: #fef2f2;
        }

        .su-dropzone-icon {
            width: 64px; height: 64px;
            margin: 0 auto 1rem;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: var(--su-primary);
            box-shadow: var(--shadow-md);
            transition: transform 0.3s;
        }

        .su-dropzone:hover .su-dropzone-icon { transform: scale(1.1) rotate(-5deg); }

        .su-dropzone-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: 0.5rem;
        }

        .su-dropzone-title span {
            color: var(--su-primary);
            text-decoration: underline;
            text-underline-offset: 2px;
        }

        .su-dropzone-desc {
            font-size: 0.85rem;
            color: var(--gray-500);
        }

        .su-file-input {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }

        /* --- File Preview List --- */
        .su-file-list {
            margin-top: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .su-file-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: white;
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-lg);
            animation: suSlideIn 0.3s ease;
        }

        @keyframes suSlideIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .su-file-icon {
            width: 44px; height: 44px;
            border-radius: var(--radius-md);
            background: #fee2e2;
            color: #dc2626;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .su-file-info { flex: 1; min-width: 0; }

        .su-file-name {
            font-weight: 600;
            color: var(--gray-900);
            font-size: 0.9rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 0.25rem;
        }

        .su-file-meta {
            font-size: 0.78rem;
            color: var(--gray-500);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .su-file-remove {
            width: 36px; height: 36px;
            border-radius: var(--radius-md);
            border: none;
            background: transparent;
            color: var(--gray-400);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .su-file-remove:hover {
            background: #fee2e2;
            color: #dc2626;
        }

        /* --- Submit Button --- */
        .su-submit-area {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--gray-200);
            display: flex;
            justify-content: flex-end;
        }

        .su-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.85rem 2rem;
            background: var(--su-gradient);
            color: white;
            border: none;
            border-radius: var(--radius-lg);
            font-size: 0.95rem;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: var(--su-glow);
            position: relative;
            overflow: hidden;
        }

        .su-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -5px rgba(59,130,246,0.5);
        }

        .su-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .su-spinner {
            display: none;
            width: 18px; height: 18px;
            border: 2.5px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: suSpin 0.7s linear infinite;
        }

        .su-btn.is-loading .su-spinner { display: inline-block; }
        .su-btn.is-loading .su-btn-text { opacity: 0.8; }
        .su-btn.is-loading i.bi-cloud-arrow-up { display: none; }

        @keyframes suSpin { to { transform: rotate(360deg); } }

        /* --- Error Message --- */
        .su-error-msg {
            color: var(--accent-red);
            font-size: 0.8rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            animation: suShake 0.3s ease;
        }

        @keyframes suShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-4px); }
            75% { transform: translateX(4px); }
        }

        /* --- Alerts --- */
        .su-view .alert-dismissible {
            animation: suSlideRight 0.4s ease;
        }

        @keyframes suSlideRight {
            from { opacity: 0; transform: translateX(30px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @media (max-width: 640px) {
            .su-card-body { padding: 1.5rem; }
            .su-dropzone { padding: 2rem 1rem; }
            .su-submit-area { justify-content: stretch; }
            .su-btn { width: 100%; }
        }
    </style>
</head>
<body>
    <?php require dirname(__DIR__) . '/partials/navbar.php'; ?>

    <div class="container su-view">
        <!-- Page Header -->
        <div class="su-header">
            <div>
                <h1 class="su-header-title">
                    <span class="su-header-icon"><i class="bi bi-cloud-arrow-up-fill"></i></span>
                    Upload Dokumen
                </h1>
                <p class="su-header-sub">Unggah file tugas akhir Anda dalam format PDF (Maks. <?= $maxSizeMB ?> MB).</p>
            </div>
            <a href="<?= BASE_URL ?>/student/dashboard" class="su-back-btn">
                <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>

        <!-- Flash Messages -->
        <?php if ($flashError): ?>
            <div class="alert alert-danger alert-dismissible" style="max-width: 800px; margin: 0 auto 1.5rem;">
                <div class="alert-content"><i class="bi bi-exclamation-triangle-fill"></i> <?= e($flashError) ?></div>
                <button class="btn-close">&times;</button>
            </div>
        <?php endif; ?>
        <?php if ($flashSuccess): ?>
            <div class="alert alert-success alert-dismissible" style="max-width: 800px; margin: 0 auto 1.5rem;">
                <div class="alert-content"><i class="bi bi-check-circle-fill"></i> <?= e($flashSuccess) ?></div>
                <button class="btn-close">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Upload Card -->
        <div class="su-card">
            <div class="su-card-header">
                <h3 class="su-card-title">
                    <i class="bi bi-file-earmark-arrow-up"></i>
                    Form Unggah File
                </h3>
            </div>
            <div class="su-card-body">
                <form id="uploadForm" method="POST" action="<?= BASE_URL ?>/student/upload" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">

                    <!-- Document Type -->
                    <div class="su-form-group">
                        <label class="su-label" for="docType">
                            Jenis Dokumen <span class="su-label-hint">(Pilih kategori file yang akan diunggah)</span>
                        </label>
                        <select name="document_type_id" id="docType" class="su-select" required>
                            <option value="">-- Pilih Jenis Dokumen --</option>
                            <?php foreach ($documentTypes as $type): ?>
                                <option value="<?= $type['id'] ?>"><?= e($type['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Drag & Drop Zone -->
                    <div class="su-form-group">
                        <label class="su-label">File Dokumen <span style="color: var(--accent-red);">*</span></label>
                        <div class="su-dropzone" id="dropzone">
                            <input type="file" name="document_file" id="fileInput" class="su-file-input" accept=".pdf" required>
                            <div class="su-dropzone-icon">
                                <i class="bi bi-file-earmark-pdf-fill"></i>
                            </div>
                            <div class="su-dropzone-title">
                                Seret dan lepas file PDF di sini, atau <span>klik untuk memilih</span>
                            </div>
                            <div class="su-dropzone-desc">
                                Format: PDF saja | Maksimal ukuran: <?= $maxSizeMB ?> MB
                            </div>
                        </div>
                        <div id="fileError" class="su-error-msg" style="display: none;">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            <span id="fileErrorText"></span>
                        </div>
                    </div>

                    <!-- File Preview List -->
                    <div class="su-file-list" id="fileList"></div>

                    <!-- Submit Area -->
                    <div class="su-submit-area">
                        <button type="submit" class="su-btn" id="submitBtn">
                            <span class="su-spinner"></span>
                            <span class="su-btn-text">
                                <i class="bi bi-cloud-arrow-up"></i> Unggah Dokumen
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/admin.js"></script>
    <script>
    (function() {
        'use strict';

        const dropzone = document.getElementById('dropzone');
        const fileInput = document.getElementById('fileInput');
        const fileList = document.getElementById('fileList');
        const fileError = document.getElementById('fileError');
        const fileErrorText = document.getElementById('fileErrorText');
        const submitBtn = document.getElementById('submitBtn');
        const uploadForm = document.getElementById('uploadForm');

        const MAX_SIZE = <?= $maxSizeBytes ?>;
        const ALLOWED_TYPE = 'application/pdf';

        let currentFile = null;

        // ===== FORMAT FILE SIZE =====
        const formatSize = (bytes) => {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        };

        // ===== VALIDATE FILE =====
        const validateFile = (file) => {
            fileError.style.display = 'none';
            dropzone.classList.remove('is-invalid');

            if (file.type !== ALLOWED_TYPE) {
                showError('Hanya file PDF yang diperbolehkan.');
                return false;
            }
            if (file.size > MAX_SIZE) {
                showError(`Ukuran file melebihi batas maksimal <?= $maxSizeMB ?> MB.`);
                return false;
            }
            return true;
        };

        const showError = (msg) => {
            fileErrorText.textContent = msg;
            fileError.style.display = 'flex';
            dropzone.classList.add('is-invalid');
            currentFile = null;
            fileList.innerHTML = '';
            fileInput.value = '';
        };

        // ===== RENDER FILE PREVIEW =====
        const renderFile = (file) => {
            fileList.innerHTML = `
                <div class="su-file-item">
                    <div class="su-file-icon">
                        <i class="bi bi-file-earmark-pdf-fill"></i>
                    </div>
                    <div class="su-file-info">
                        <div class="su-file-name" title="${file.name}">${file.name}</div>
                        <div class="su-file-meta">
                            <span>${formatSize(file.size)}</span>
                            <span>•</span>
                            <span>PDF Document</span>
                        </div>
                    </div>
                    <button type="button" class="su-file-remove" id="removeFile" title="Hapus file">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            `;

            document.getElementById('removeFile').addEventListener('click', () => {
                currentFile = null;
                fileList.innerHTML = '';
                fileInput.value = '';
                dropzone.classList.remove('is-invalid');
                fileError.style.display = 'none';
            });
        };

        // ===== DRAG & DROP EVENTS =====
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => dropzone.classList.add('is-dragover'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => dropzone.classList.remove('is-dragover'), false);
        });

        dropzone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles(files);
        });

        fileInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });

        const handleFiles = (files) => {
            if (files.length > 0) {
                const file = files[0];
                if (validateFile(file)) {
                    currentFile = file;
                    renderFile(file);
                }
            }
        };

        // ===== FORM SUBMISSION =====
        uploadForm.addEventListener('submit', (e) => {
            if (!currentFile) {
                e.preventDefault();
                showError('Silakan pilih file dokumen terlebih dahulu.');
                return;
            }

            const docType = document.getElementById('docType').value;
            if (!docType) {
                e.preventDefault();
                document.getElementById('docType').focus();
                document.getElementById('docType').style.borderColor = 'var(--accent-red)';
                document.getElementById('docType').style.boxShadow = '0 0 0 4px rgba(239, 68, 68, 0.1)';
                return;
            }

            // Show loading state
            submitBtn.classList.add('is-loading');
            submitBtn.disabled = true;
        });

        // Reset select style on change
        document.getElementById('docType').addEventListener('change', function() {
            this.style.borderColor = '';
            this.style.boxShadow = '';
        });

        // ===== ALERT AUTO-DISMISS =====
        document.querySelectorAll('.su-view .alert-dismissible .btn-close').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const alert = e.target.closest('.alert');
                alert.style.transition = 'all 0.3s';
                alert.style.opacity = '0';
                alert.style.transform = 'translateX(30px)';
                setTimeout(() => alert.remove(), 300);
            });
        });

        document.querySelectorAll('.su-view .alert-success').forEach(alert => {
            setTimeout(() => {
                if (alert.parentElement) {
                    alert.style.transition = 'all 0.3s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }
            }, 5000);
        });

        console.log('%c✓ Student Upload Loaded', 'color: #3b82f6; font-weight: bold;');
    })();
    </script>
</body>
</html>