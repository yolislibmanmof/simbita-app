<?php
declare(strict_types=1);
$flashError = $_SESSION['flash_error'] ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

$work = $work ?? [];
$documents = $documents ?? [];
$supervisors = $work['supervisors'] ?? [];

$initials = function(string $name): string {
    $parts = array_filter(explode(' ', trim($name)));
    if (empty($parts)) return '-';
    return strtoupper(substr($parts[0], 0, 1)) . (count($parts) > 1 ? strtoupper(substr(end($parts), 0, 1)) : '');
};

$statusMeta = [
    'draft'            => ['class' => 'lr-pill-draft',     'label' => 'Draft'],
    'proposal'         => ['class' => 'lr-pill-proposal',  'label' => 'Proposal'],
    'feasibility_test' => ['class' => 'lr-pill-info',      'label' => 'Uji Kelayakan'],
    'writing'          => ['class' => 'lr-pill-writing',   'label' => 'Penulisan'],
    'progress_exam'    => ['class' => 'lr-pill-info',      'label' => 'Ujian Kemajuan'],
    'revision'         => ['class' => 'lr-pill-revision',  'label' => 'Revisi'],
    'closed_exam'      => ['class' => 'lr-pill-info',      'label' => 'Ujian Tertutup'],
    'ready_for_defense'=> ['class' => 'lr-pill-proposal',  'label' => 'Siap Sidang'],
    'defense'          => ['class' => 'lr-pill-proposal',  'label' => 'Sidang'],
    'completed'        => ['class' => 'lr-pill-success',   'label' => 'Selesai'],
    'cancelled'        => ['class' => 'lr-pill-danger',    'label' => 'Dibatalkan'],
];
$currentStatus = $work['status'] ?? 'draft';
$currentMeta = $statusMeta[$currentStatus] ?? $statusMeta['draft'];

// Hitung durasi
$startedAt = !empty($work['started_at']) ? strtotime($work['started_at']) : time();
$daysElapsed = max(0, (int) ((time() - $startedAt) / 86400));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Dokumen - <?= e($work['title'] ?? 'Tugas Akhir') ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">
    <!-- PDF.js CSS & JS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf_viewer.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';</script>
    <style>
        /* =========================================================
           LECTURER REVIEW DETAIL ULTRA PREMIUM - SCOPED STYLES
           Namespace: .lr- | Tema: Emerald Green (#10b981)
           ========================================================= */
        .lr-view {
            --lr-primary: #10b981;
            --lr-primary-dark: #059669;
            --lr-primary-light: #d1fae5;
            --lr-gradient: linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%);
            --lr-glow: 0 10px 30px -10px rgba(16, 185, 129, 0.4);
            animation: lrFade 0.4s ease;
        }
        @keyframes lrFade { from { opacity: 0; } to { opacity: 1; } }

        /* --- Page Header --- */
        .lr-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .lr-back-btn {
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

        .lr-back-btn:hover {
            background: var(--gray-50);
            border-color: var(--gray-400);
            transform: translateX(-3px);
        }

        /* --- Hero Card --- */
        .lr-hero {
            background: var(--lr-gradient);
            border-radius: var(--radius-2xl);
            padding: 2.5rem;
            color: white;
            position: relative;
            overflow: hidden;
            margin-bottom: 1.75rem;
            box-shadow: 0 20px 40px -10px rgba(16,185,129,0.4);
            animation: lrSlideDown 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes lrSlideDown {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .lr-hero::before {
            content: '';
            position: absolute;
            top: -60%; right: -5%;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            border-radius: 50%;
            animation: lrFloat 8s ease-in-out infinite;
        }

        .lr-hero::after {
            content: '';
            position: absolute;
            bottom: -40%; left: 5%;
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(245,158,11,0.2) 0%, transparent 70%);
            border-radius: 50%;
            animation: lrFloat 10s ease-in-out infinite reverse;
        }

        @keyframes lrFloat {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(-20px, 15px) scale(1.1); }
        }

        .lr-hero-content {
            position: relative;
            z-index: 2;
        }

        .lr-hero-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1.5rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }

        .lr-hero-label {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.85rem;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.75rem;
        }

        .lr-hero-title {
            font-size: 1.75rem;
            font-weight: 800;
            line-height: 1.3;
            margin: 0;
            max-width: 800px;
        }

        .lr-hero-student {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(10px);
            padding: 1rem 1.25rem;
            border-radius: var(--radius-xl);
            border: 1px solid rgba(255,255,255,0.2);
            min-width: 280px;
        }

        .lr-hero-student-avatar {
            width: 52px; height: 52px;
            border-radius: var(--radius-lg);
            background: rgba(255,255,255,0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.1rem;
            flex-shrink: 0;
            border: 2px solid rgba(255,255,255,0.4);
        }

        .lr-hero-student-info { min-width: 0; }

        .lr-hero-student-name {
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 0.15rem;
        }

        .lr-hero-student-nim {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.8rem;
            opacity: 0.9;
        }

        .lr-hero-meta {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            font-size: 0.9rem;
            opacity: 0.95;
            margin-top: 1.25rem;
            padding-top: 1.25rem;
            border-top: 1px solid rgba(255,255,255,0.2);
        }

        .lr-hero-meta-item {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        /* --- Status Pill --- */
        .lr-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 0.9rem;
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .lr-pill::before {
            content: '';
            width: 7px; height: 7px;
            border-radius: 50%;
            background: currentColor;
        }

        .lr-pill-draft { background: var(--gray-100); color: var(--gray-600); }
        .lr-pill-proposal { background: #dbeafe; color: #1e40af; }
        .lr-pill-writing { background: #cffafe; color: #155e75; }
        .lr-pill-revision { background: #fef3c7; color: #92400e; }
        .lr-pill-success { background: #d1fae5; color: #065f46; }
        .lr-pill-info { background: #ede9fe; color: #6d28d9; }
        .lr-pill-danger { background: #fee2e2; color: #991b1b; }

        .lr-hero-pill {
            background: rgba(255,255,255,0.25) !important;
            color: white !important;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
        }

        .lr-hero-pill::before { background: white !important; }

        /* --- Grid Layout --- */
        .lr-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
        }

        @media (max-width: 968px) { .lr-grid { grid-template-columns: 1fr; } }

        /* --- Card --- */
        .lr-card {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-xl);
            overflow: hidden;
            animation: lrUp 0.5s ease backwards;
        }

        .lr-card.delay-1 { animation-delay: 0.1s; }
        .lr-card.delay-2 { animation-delay: 0.2s; }
        .lr-card.delay-3 { animation-delay: 0.3s; }

        @keyframes lrUp {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .lr-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            background: linear-gradient(to right, var(--gray-50), white);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .lr-card-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--gray-800);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
        }

        .lr-card-title i { color: var(--lr-primary); font-size: 1.1rem; }

        .lr-card-count {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.25rem 0.7rem;
            background: var(--lr-primary-light);
            color: #065f46;
            border-radius: var(--radius-full);
            font-size: 0.72rem;
            font-weight: 700;
        }

        .lr-card-body { padding: 1.5rem; }

        /* --- Document Card --- */
        .lr-doc-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .lr-doc {
            display: flex;
            gap: 1rem;
            padding: 1.25rem;
            background: white;
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-xl);
            transition: all 0.3s;
            animation: lrUp 0.45s ease backwards;
        }

        .lr-doc:hover {
            border-color: var(--lr-primary);
            box-shadow: 0 10px 25px -8px rgba(16,185,129,0.15);
            transform: translateY(-2px);
        }

        .lr-doc-icon {
            width: 56px; height: 56px;
            border-radius: var(--radius-lg);
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: #dc2626;
            flex-shrink: 0;
            box-shadow: var(--shadow-md);
        }

        .lr-doc-content { flex: 1; min-width: 0; }

        .lr-doc-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
            flex-wrap: wrap;
        }

        .lr-doc-title {
            font-weight: 700;
            color: var(--gray-900);
            font-size: 0.95rem;
            line-height: 1.3;
            word-break: break-word;
        }

        .lr-doc-version {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.25rem 0.6rem;
            background: #ede9fe;
            color: #6d28d9;
            border-radius: var(--radius-full);
            font-size: 0.72rem;
            font-weight: 700;
            font-family: 'JetBrains Mono', monospace;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .lr-doc-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            font-size: 0.8rem;
            color: var(--gray-500);
            margin-bottom: 0.85rem;
        }

        .lr-doc-meta-item {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .lr-doc-meta-item i { color: var(--lr-primary); font-size: 0.9rem; }

        .lr-doc-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .lr-doc-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.55rem 1rem;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 0.8rem;
            text-decoration: none;
            transition: all 0.25s;
            border: 1.5px solid transparent;
            cursor: pointer;
            font-family: inherit;
        }

        .lr-doc-btn-primary {
            background: var(--lr-gradient);
            color: white;
            box-shadow: 0 4px 10px -4px rgba(16,185,129,0.5);
        }

        .lr-doc-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px -5px rgba(16,185,129,0.6);
        }

        .lr-doc-btn-secondary {
            background: white;
            color: var(--gray-700);
            border-color: var(--gray-200);
        }

        .lr-doc-btn-secondary:hover {
            background: var(--gray-50);
            border-color: var(--gray-400);
        }

        /* --- Empty Doc --- */
        .lr-empty-doc {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--gray-500);
        }

        .lr-empty-doc-icon {
            width: 80px; height: 80px;
            margin: 0 auto 1rem;
            border-radius: 50%;
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.25rem;
            color: #b45309;
            animation: lrEmptyPulse 2s ease-in-out infinite;
        }

        @keyframes lrEmptyPulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(245,158,11,0.3); }
            50% { transform: scale(1.05); box-shadow: 0 0 0 15px rgba(245,158,11,0); }
        }

        .lr-empty-doc h4 { font-size: 1.1rem; color: var(--gray-800); margin-bottom: 0.35rem; }
        .lr-empty-doc p { font-size: 0.85rem; margin: 0; }

        /* --- Review Form --- */
        .lr-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .lr-form-section-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--gray-800);
            margin: 0 0 0.85rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .lr-form-section-title i { color: var(--lr-primary); }

        .lr-form-section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--gray-200);
            margin-left: 0.5rem;
        }

        /* Custom Radio Group */
        .lr-decision-group {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
        }

        @media (max-width: 640px) { .lr-decision-group { grid-template-columns: 1fr; } }

        .lr-decision-card {
            position: relative;
            padding: 1rem;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-lg);
            cursor: pointer;
            transition: all 0.3s;
            background: white;
        }

        .lr-decision-card:hover {
            border-color: var(--gray-400);
            transform: translateY(-2px);
        }

        .lr-decision-card input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .lr-decision-card.is-selected {
            border-color: transparent;
            box-shadow: 0 8px 20px -6px;
        }

        .lr-decision-card.opt-approve {
            --card-color: #10b981;
        }
        .lr-decision-card.opt-revision {
            --card-color: #3b82f6;
        }
        .lr-decision-card.opt-reject {
            --card-color: #ef4444;
        }

        .lr-decision-card.is-selected.opt-approve {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            box-shadow: 0 8px 20px -6px rgba(16,185,129,0.4);
        }
        .lr-decision-card.is-selected.opt-revision {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            box-shadow: 0 8px 20px -6px rgba(59,130,246,0.4);
        }
        .lr-decision-card.is-selected.opt-reject {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            box-shadow: 0 8px 20px -6px rgba(239,68,68,0.4);
        }

        .lr-decision-icon {
            width: 40px; height: 40px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
            margin-bottom: 0.75rem;
            transition: all 0.3s;
        }

        .lr-decision-card.opt-approve .lr-decision-icon { background: #10b981; }
        .lr-decision-card.opt-revision .lr-decision-icon { background: #3b82f6; }
        .lr-decision-card.opt-reject .lr-decision-icon { background: #ef4444; }

        .lr-decision-card.is-selected .lr-decision-icon {
            background: white;
            transform: scale(1.05);
        }
        .lr-decision-card.is-selected.opt-approve .lr-decision-icon { color: #059669; }
        .lr-decision-card.is-selected.opt-revision .lr-decision-icon { color: #1e40af; }
        .lr-decision-card.is-selected.opt-reject .lr-decision-icon { color: #b91c1c; }

        .lr-decision-label {
            font-weight: 700;
            color: var(--gray-800);
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
            transition: color 0.3s;
        }

        .lr-decision-desc {
            font-size: 0.75rem;
            color: var(--gray-500);
            line-height: 1.4;
        }

        .lr-decision-check {
            position: absolute;
            top: 0.75rem; right: 0.75rem;
            width: 20px; height: 20px;
            border-radius: 50%;
            border: 2px solid var(--gray-300);
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .lr-decision-card.is-selected .lr-decision-check {
            background: var(--card-color);
            border-color: var(--card-color);
        }

        .lr-decision-card.is-selected .lr-decision-check::after {
            content: '\F26B';
            font-family: 'bootstrap-icons';
            color: white;
            font-size: 0.7rem;
        }

        /* Comment Textarea */
        .lr-comment-wrap {
            position: relative;
        }

        .lr-comment {
            width: 100%;
            padding: 1rem;
            border: 1.5px solid var(--gray-300);
            border-radius: var(--radius-lg);
            font-size: 0.9rem;
            font-family: inherit;
            resize: vertical;
            min-height: 140px;
            transition: all 0.3s;
            line-height: 1.6;
            background: white;
            color: var(--gray-800);
        }

        .lr-comment:focus {
            outline: none;
            border-color: var(--lr-primary);
            box-shadow: 0 0 0 4px rgba(16,185,129,0.12);
        }

        .lr-comment::placeholder { color: var(--gray-400); }

        .lr-comment-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 0.5rem;
            font-size: 0.78rem;
            color: var(--gray-500);
        }

        .lr-comment-hint {
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .lr-comment-counter {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 600;
            transition: color 0.2s;
        }
        .lr-comment-counter.warn { color: var(--accent-orange); }
        .lr-comment-counter.danger { color: var(--accent-red); }

        /* --- Quick Info Card --- */
        .lr-info-list {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
        }

        .lr-info-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding-bottom: 0.85rem;
            border-bottom: 1px dashed var(--gray-200);
        }

        .lr-info-item:last-child { border-bottom: none; padding-bottom: 0; }

        .lr-info-icon {
            width: 36px; height: 36px;
            border-radius: var(--radius-md);
            background: var(--lr-primary-light);
            color: var(--lr-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
            flex-shrink: 0;
        }

        .lr-info-content { flex: 1; min-width: 0; }

        .lr-info-label {
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.15rem;
        }

        .lr-info-value {
            font-weight: 600;
            color: var(--gray-800);
            font-size: 0.875rem;
            line-height: 1.4;
            word-break: break-word;
        }

        .lr-mono {
            font-family: 'JetBrains Mono', monospace;
            color: var(--lr-primary);
            font-weight: 600;
        }

        /* --- Supervisor Cards --- */
        .lr-sup-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .lr-sup-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.85rem;
            background: var(--gray-50);
            border-radius: var(--radius-lg);
            transition: all 0.3s;
        }

        .lr-sup-item:hover {
            background: white;
            box-shadow: var(--shadow-md);
            transform: translateX(3px);
        }

        .lr-sup-avatar {
            width: 40px; height: 40px;
            border-radius: var(--radius-md);
            background: var(--lr-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .lr-sup-info { flex: 1; min-width: 0; }

        .lr-sup-role {
            font-size: 0.68rem;
            color: var(--lr-primary);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .lr-sup-name {
            font-weight: 700;
            color: var(--gray-800);
            font-size: 0.85rem;
            line-height: 1.3;
        }

        .lr-sup-degree {
            font-size: 0.75rem;
            color: var(--gray-500);
        }

        /* --- Submit Area --- */
        .lr-submit-area {
            padding: 1.25rem 1.5rem;
            background: linear-gradient(to right, var(--gray-50), white);
            border-top: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .lr-submit-hint {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.8rem;
            color: var(--gray-500);
        }

        .lr-submit-hint i { color: var(--accent-yellow); }

        .lr-submit-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.85rem 2rem;
            background: var(--lr-gradient);
            color: white;
            border: none;
            border-radius: var(--radius-lg);
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            font-family: inherit;
            box-shadow: var(--lr-glow);
            position: relative;
            overflow: hidden;
        }

        .lr-submit-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 15px 25px -5px rgba(16,185,129,0.5);
        }

        .lr-submit-btn:disabled { opacity: 0.6; cursor: not-allowed; }

        .lr-spinner {
            display: none;
            width: 16px; height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: lrSpin 0.7s linear infinite;
        }

        .lr-submit-btn.is-loading .lr-spinner { display: inline-block; }
        .lr-submit-btn.is-loading .lr-btn-text { opacity: 0.8; }

        @keyframes lrSpin { to { transform: rotate(360deg); } }

        /* --- Validation --- */
        .lr-comment.is-invalid {
            border-color: var(--accent-red);
            box-shadow: 0 0 0 4px rgba(239,68,68,0.1);
        }

        .lr-error-msg {
            color: var(--accent-red);
            font-size: 0.78rem;
            margin-top: 0.35rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            animation: lrShake 0.3s;
        }

        @keyframes lrShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-4px); }
            75% { transform: translateX(4px); }
        }

        .lr-decision-group.is-invalid .lr-decision-card {
            border-color: var(--accent-red);
        }

        @media (max-width: 768px) {
            .lr-hero { padding: 1.5rem; }
            .lr-hero-title { font-size: 1.35rem; }
            .lr-hero-student { min-width: auto; }
            .lr-card-body { padding: 1.25rem; }
            .lr-submit-area { flex-direction: column; text-align: center; }
            .lr-submit-btn { width: 100%; justify-content: center; }
        }
/* --- PDF Viewer Toolbar --- */
.lr-pdf-toolbar { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1.25rem; background: var(--gray-50); border-bottom: 1px solid var(--gray-200); flex-wrap: wrap; gap: 0.75rem; }
.lr-pdf-controls, .lr-zoom-controls { display: flex; align-items: center; gap: 0.5rem; }
.lr-page-indicator { font-family: 'JetBrains Mono', monospace; font-size: 0.85rem; font-weight: 600; color: var(--gray-700); min-width: 80px; text-align: center; }
.lr-zoom-select { padding: 0.4rem 0.75rem; border: 1px solid var(--gray-300); border-radius: var(--radius-md); font-size: 0.85rem; font-family: inherit; background: white; cursor: pointer; }

/* --- Canvas Wrapper --- */
.lr-canvas-wrapper { position: relative; width: 100%; background: #525659; overflow: auto; min-height: 600px; display: flex; justify-content: center; padding: 2rem; }
.lr-canvas-container { position: relative; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
#pdfCanvas { display: block; }

/* --- Annotation Layer --- */
.lr-annotation-layer { position: absolute; top: 0; left: 0; width: 100%; height: 100%; cursor: crosshair; z-index: 10; }
.lr-annotation-marker { position: absolute; width: 24px; height: 24px; border-radius: 50%; transform: translate(-50%, -50%); cursor: pointer; z-index: 20; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: white; font-weight: 700; box-shadow: 0 2px 8px rgba(0,0,0,0.3); transition: transform 0.2s; }
.lr-annotation-marker:hover { transform: translate(-50%, -50%) scale(1.2); z-index: 30; }
.lr-annotation-marker.priority-minor { background: #3b82f6; }
.lr-annotation-marker.priority-important { background: #f59e0b; }
.lr-annotation-marker.priority-mandatory { background: #ef4444; animation: lrPulseMarker 2s infinite; }
@keyframes lrPulseMarker { 0%, 100% { box-shadow: 0 0 0 0 rgba(239,68,68,0.6); } 50% { box-shadow: 0 0 0 8px rgba(239,68,68,0); } }

/* --- Annotation Popup Modal --- */
.lr-popup-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); backdrop-filter: blur(2px); z-index: 1000; display: none; align-items: center; justify-content: center; }
.lr-popup-overlay.active { display: flex; }
.lr-popup { background: white; border-radius: var(--radius-xl); padding: 1.5rem; width: 100%; max-width: 400px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); animation: lrPopIn 0.3s ease; }
@keyframes lrPopIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
.lr-popup-title { font-size: 1rem; font-weight: 700; color: var(--gray-900); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
.lr-popup-title i { color: var(--lr-primary); }
.lr-popup-field { margin-bottom: 1rem; }
.lr-popup-label { display: block; font-size: 0.8rem; font-weight: 600; color: var(--gray-700); margin-bottom: 0.4rem; }
.lr-popup-textarea { width: 100%; padding: 0.6rem; border: 1.5px solid var(--gray-300); border-radius: var(--radius-md); font-size: 0.85rem; font-family: inherit; resize: vertical; min-height: 80px; }
.lr-popup-textarea:focus { outline: none; border-color: var(--lr-primary); }
.lr-popup-priority { display: flex; gap: 0.5rem; }
.lr-popup-priority label { flex: 1; padding: 0.5rem; border: 1.5px solid var(--gray-200); border-radius: var(--radius-md); text-align: center; font-size: 0.75rem; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.lr-popup-priority input { display: none; }
.lr-popup-priority input:checked + span { background: var(--lr-primary); color: white; border-color: var(--lr-primary); }
.lr-popup-priority span { display: block; padding: 0.25rem; border-radius: var(--radius-sm); }
.lr-popup-actions { display: flex; gap: 0.75rem; margin-top: 1.25rem; }
.lr-popup-btn { flex: 1; padding: 0.65rem; border-radius: var(--radius-md); font-weight: 600; font-size: 0.85rem; cursor: pointer; transition: all 0.2s; border: none; font-family: inherit; }
.lr-popup-btn-cancel { background: var(--gray-100); color: var(--gray-700); }
.lr-popup-btn-cancel:hover { background: var(--gray-200); }
.lr-popup-btn-save { background: var(--lr-gradient); color: white; box-shadow: 0 4px 10px -4px rgba(16,185,129,0.4); }
.lr-popup-btn-save:hover { transform: translateY(-1px); }

/* --- Checklist Styles --- */
.lr-checklist-list { display: flex; flex-direction: column; gap: 0.6rem; max-height: 400px; overflow-y: auto; padding-right: 0.5rem; }
.lr-checklist-item { display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.75rem; background: var(--gray-50); border-radius: var(--radius-md); border: 1px solid transparent; transition: all 0.2s; cursor: pointer; }
.lr-checklist-item:hover { background: white; border-color: var(--gray-200); }
.lr-checklist-item input[type="checkbox"] { width: 18px; height: 18px; margin-top: 2px; accent-color: var(--lr-primary); cursor: pointer; flex-shrink: 0; }
.lr-checklist-text { font-size: 0.85rem; color: var(--gray-700); line-height: 1.4; flex: 1; }
.lr-checklist-note { width: 100%; margin-top: 0.5rem; padding: 0.5rem; border: 1px solid var(--gray-300); border-radius: var(--radius-md); font-size: 0.8rem; font-family: inherit; resize: vertical; min-height: 60px; display: none; }
.lr-checklist-item.has-note .lr-checklist-note { display: block; }
    </style>
</head>
<body>
    <?php require dirname(__DIR__) . '/partials/navbar.php'; ?>

    <div class="container lr-view">
        <!-- Page Header -->
        <div class="lr-header">
            <a href="<?= BASE_URL ?>/lecturer/students" class="lr-back-btn">
                <i class="bi bi-arrow-left"></i> Kembali ke Daftar Mahasiswa
            </a>
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

        <!-- Hero Card -->
        <div class="lr-hero">
            <div class="lr-hero-content">
                <div class="lr-hero-top">
                    <div>
                        <span class="lr-hero-label">
                            <i class="bi bi-journal-bookmark-fill"></i>
                            <?= e($work['work_type_name'] ?? 'Tugas Akhir') ?>
                        </span>
                        <h1 class="lr-hero-title"><?= e($work['title']) ?></h1>
                    </div>
                    <div class="lr-hero-student">
                        <div class="lr-hero-student-avatar">
                            <?= e($initials($work['student_name'])) ?>
                        </div>
                        <div class="lr-hero-student-info">
                            <div class="lr-hero-student-name"><?= e($work['student_name']) ?></div>
                            <div class="lr-hero-student-nim"><?= e($work['student_number'] ?? '—') ?></div>
                        </div>
                    </div>
                </div>
                <div class="lr-hero-meta">
                    <div class="lr-hero-meta-item">
                        <i class="bi bi-building"></i>
                        <?= e($work['study_program']) ?>
                    </div>
                    <div class="lr-hero-meta-item">
                        <i class="bi bi-flag-fill"></i>
                        <span class="lr-pill lr-hero-pill"><?= $currentMeta['label'] ?></span>
                    </div>
                    <div class="lr-hero-meta-item">
                        <i class="bi bi-calendar-event"></i>
                        Dimulai <?= date('d M Y', strtotime($work['started_at'])) ?>
                    </div>
                    <div class="lr-hero-meta-item">
                        <i class="bi bi-clock-fill"></i>
                        <?= $daysElapsed ?> hari berjalan
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Grid -->
        <div class="lr-grid">
            <!-- Left: PDF Viewer + Checklist + Decision Form -->
            <div>
                <!-- PDF Viewer Container -->
                <div class="lr-card delay-1" style="margin-bottom: 1.5rem;">
                    <div class="lr-card-header">
                        <h3 class="lr-card-title"><i class="bi bi-file-earmark-pdf-fill"></i> Dokumen Review</h3>
                        <?php if (!empty($documents)): ?>
                            <span class="lr-card-count">v<?= e($documents[0]['version_number']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div style="padding: 0; background: #525659;">
                        <?php if (empty($documents)): ?>
                            <div class="lr-empty-doc" style="background: white; border-radius: var(--radius-xl); margin: 1.5rem;">
                                <div class="lr-empty-doc-icon"><i class="bi bi-folder-x"></i></div>
                                <h4>Belum Ada Dokumen</h4>
                                <p>Mahasiswa belum mengunggah dokumen.</p>
                            </div>
                        <?php else: ?>
                            <!-- Toolbar PDF -->
                            <div class="lr-pdf-toolbar">
                                <div class="lr-pdf-controls">
                                    <button type="button" id="prevPage" class="lr-doc-btn lr-doc-btn-secondary" style="padding: 0.4rem 0.6rem;"><i class="bi bi-chevron-left"></i></button>
                                    <span class="lr-page-indicator"><span id="currentPage">1</span> / <span id="totalPages">-</span></span>
                                    <button type="button" id="nextPage" class="lr-doc-btn lr-doc-btn-secondary" style="padding: 0.4rem 0.6rem;"><i class="bi bi-chevron-right"></i></button>
                                </div>
                                <div class="lr-zoom-controls">
                                    <button type="button" id="zoomOut" class="lr-doc-btn lr-doc-btn-secondary" style="padding: 0.4rem 0.6rem;"><i class="bi bi-dash-lg"></i></button>
                                    <select id="zoomSelect" class="lr-zoom-select">
                                        <option value="0.75">75%</option><option value="1" selected>100%</option><option value="1.25">125%</option><option value="1.5">150%</option>
                                    </select>
                                    <button type="button" id="zoomIn" class="lr-doc-btn lr-doc-btn-secondary" style="padding: 0.4rem 0.6rem;"><i class="bi bi-plus-lg"></i></button>
                                </div>
                            </div>
                            
                            <!-- Canvas Wrapper & Annotation Layer -->
                            <div class="lr-canvas-wrapper" id="pdfWrapper">
                                <div class="lr-canvas-container" id="canvasContainer">
                                    <canvas id="pdfCanvas"></canvas>
                                    <div class="lr-annotation-layer" id="annotationLayer"></div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Checklist Card (Hanya muncul jika ada dokumen) -->
                <?php if (!empty($documents) && !empty($checklistItems)): ?>
                <div class="lr-card delay-2" style="margin-bottom: 1.5rem;">
                    <div class="lr-card-header">
                        <h3 class="lr-card-title"><i class="bi bi-ui-checks-grid"></i> Checklist Pemeriksaan</h3>
                    </div>
                    <div class="lr-card-body">
                        <div class="lr-checklist-list" id="checklistList">
                            <?php foreach ($checklistItems as $item): 
                                $result = $checklistResults[$item['id']] ?? null;
                                $isChecked = $result && in_array($result['result'], ['complete', 'not_applicable']);
                                $note = $result['note'] ?? '';
                            ?>
                            <label class="lr-checklist-item <?= $note ? 'has-note' : '' ?>" data-item-id="<?= $item['id'] ?>">
                                <input type="checkbox" name="checklist[<?= $item['id'] ?>][result]" value="complete" <?= $isChecked ? 'checked' : '' ?>>
                                <input type="hidden" name="checklist[<?= $item['id'] ?>][note]" value="<?= e($note) ?>">
                                <span class="lr-checklist-text"><?= e($item['item_text']) ?></span>
                                <textarea class="lr-checklist-note" placeholder="Catatan opsional..."><?= e($note) ?></textarea>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Review Decision Form Card -->
                <div class="lr-card delay-3">
                    <div class="lr-card-header">
                        <h3 class="lr-card-title"><i class="bi bi-pencil-square-fill"></i> Keputusan Review</h3>
                    </div>
                    <form method="POST" action="<?= BASE_URL ?>/api/reviews/decide" id="lrReviewForm">
                        <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                        <input type="hidden" name="document_version_id" value="<?= !empty($documents) ? $documents[0]['id'] : '' ?>">
                        
                        <div class="lr-card-body">
                            <div class="lr-form">
                                <!-- Decision Selection (HTML TETAP SAMA SEPERTI FILE LAMA ANDA) -->
                                <div>
                                    <h4 class="lr-form-section-title"><i class="bi bi-ui-radios"></i> Pilih Keputusan</h4>
                                    <div class="lr-decision-group" id="lrDecisionGroup">
                                        <label class="lr-decision-card opt-approve">
                                            <input type="radio" name="decision" value="approved" required>
                                            <div class="lr-decision-check"></div>
                                            <div class="lr-decision-icon"><i class="bi bi-check-circle-fill"></i></div>
                                            <div class="lr-decision-label">Disetujui</div>
                                            <div class="lr-decision-desc">Dokumen telah sesuai standar</div>
                                        </label>
                                        <label class="lr-decision-card opt-revision">
                                            <input type="radio" name="decision" value="needs_revision">
                                            <div class="lr-decision-check"></div>
                                            <div class="lr-decision-icon"><i class="bi bi-pencil-square"></i></div>
                                            <div class="lr-decision-label">Perlu Revisi</div>
                                            <div class="lr-decision-desc">Perlu perbaikan tertentu</div>
                                        </label>
                                        <label class="lr-decision-card opt-reject">
                                            <input type="radio" name="decision" value="rejected">
                                            <div class="lr-decision-check"></div>
                                            <div class="lr-decision-icon"><i class="bi bi-x-circle-fill"></i></div>
                                            <div class="lr-decision-label">Ditolak</div>
                                            <div class="lr-decision-desc">Harus dibuat ulang</div>
                                        </label>
                                    </div>
                                </div>

                                <!-- Comment (HTML TETAP SAMA SEPERTI FILE LAMA ANDA) -->
                                <div>
                                    <h4 class="lr-form-section-title"><i class="bi bi-chat-square-text-fill"></i> Komentar & Catatan Review</h4>
                                    <div class="lr-comment-wrap">
                                        <textarea name="general_note" id="lrComment" class="lr-comment" maxlength="2000" rows="6" placeholder="Tuliskan catatan umum untuk mahasiswa..."></textarea>
                                    </div>
                                    <div class="lr-comment-footer">
                                        <div class="lr-comment-hint"><i class="bi bi-info-circle-fill"></i> Komentar akan dikirim ke mahasiswa</div>
                                        <div class="lr-comment-counter" id="lrCommentCounter">0/2000</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="lr-submit-area">
                            <div class="lr-submit-hint"><i class="bi bi-shield-check"></i> <span>Keputusan Anda akan tercatat permanen</span></div>
                            <button type="submit" class="lr-submit-btn" id="lrSubmitBtn">
                                <span class="lr-spinner"></span>
                                <span class="lr-btn-text"><i class="bi bi-send-fill"></i> Kirim Keputusan</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Popup Modal untuk Anotasi (Tempelkan di luar grid, sebelum div container ditutup) -->
            <div class="lr-popup-overlay" id="annotationPopup">
                <div class="lr-popup">
                    <div class="lr-popup-title"><i class="bi bi-chat-left-quote-fill"></i> Tambah Komentar Halaman</div>
                    <div class="lr-popup-field">
                        <label class="lr-popup-label">Komentar / Catatan</label>
                        <textarea class="lr-popup-textarea" id="popupComment" placeholder="Tulis catatan revisi di sini..."></textarea>
                    </div>
                    <div class="lr-popup-field">
                        <label class="lr-popup-label">Prioritas</label>
                        <div class="lr-popup-priority">
                            <label><input type="radio" name="priority" value="minor"><span>Ringan</span></label>
                            <label><input type="radio" name="priority" value="important" checked><span>Penting</span></label>
                            <label><input type="radio" name="priority" value="mandatory"><span>Wajib</span></label>
                        </div>
                    </div>
                    <div class="lr-popup-actions">
                        <button type="button" class="lr-popup-btn lr-popup-btn-cancel" id="cancelAnnotation">Batal</button>
                        <button type="button" class="lr-popup-btn lr-popup-btn-save" id="saveAnnotation">Simpan</button>
                    </div>
                </div>
            </div>

            <!-- Right: Info Panel -->
            <div>
                <!-- Info Card -->
                <div class="lr-card delay-1">
                    <div class="lr-card-header">
                        <h3 class="lr-card-title">
                            <i class="bi bi-info-circle-fill"></i>
                            Informasi Tugas Akhir
                        </h3>
                    </div>
                    <div class="lr-card-body">
                        <div class="lr-info-list">
                            <div class="lr-info-item">
                                <div class="lr-info-icon"><i class="bi bi-person-fill"></i></div>
                                <div class="lr-info-content">
                                    <div class="lr-info-label">Mahasiswa</div>
                                    <div class="lr-info-value"><?= e($work['student_name']) ?></div>
                                    <div class="lr-mono" style="font-size: 0.8rem;"><?= e($work['student_number'] ?? '—') ?></div>
                                </div>
                            </div>
                            <div class="lr-info-item">
                                <div class="lr-info-icon"><i class="bi bi-building"></i></div>
                                <div class="lr-info-content">
                                    <div class="lr-info-label">Program Studi</div>
                                    <div class="lr-info-value"><?= e($work['study_program']) ?></div>
                                </div>
                            </div>
                            <div class="lr-info-item">
                                <div class="lr-info-icon"><i class="bi bi-flag-fill"></i></div>
                                <div class="lr-info-content">
                                    <div class="lr-info-label">Status</div>
                                    <div class="lr-info-value">
                                        <span class="lr-pill <?= $currentMeta['class'] ?>">
                                            <?= $currentMeta['label'] ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="lr-info-item">
                                <div class="lr-info-icon"><i class="bi bi-calendar-event"></i></div>
                                <div class="lr-info-content">
                                    <div class="lr-info-label">Tanggal Mulai</div>
                                    <div class="lr-info-value"><?= date('d F Y', strtotime($work['started_at'])) ?></div>
                                    <div style="font-size: 0.75rem; color: var(--gray-500); margin-top: 0.15rem;">
                                        <?= $daysElapsed ?> hari yang lalu
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Supervisors Card -->
                <div class="lr-card delay-2" style="margin-top: 1.5rem;">
                    <div class="lr-card-header">
                        <h3 class="lr-card-title">
                            <i class="bi bi-people-fill"></i>
                            Tim Pembimbing
                        </h3>
                    </div>
                    <div class="lr-card-body">
                        <?php if (empty($supervisors)): ?>
                            <div style="text-align: center; padding: 1.5rem 1rem; color: var(--gray-500);">
                                <i class="bi bi-person-x" style="font-size: 2rem; opacity: 0.3; display: block; margin-bottom: 0.5rem;"></i>
                                <div style="font-size: 0.85rem;">Belum ada pembimbing</div>
                            </div>
                        <?php else: ?>
                            <div class="lr-sup-list">
                                <?php foreach ($supervisors as $sup): ?>
                                <div class="lr-sup-item">
                                    <div class="lr-sup-avatar"><?= e($initials($sup['lecturer_name'])) ?></div>
                                    <div class="lr-sup-info">
                                        <div class="lr-sup-role"><?= ucwords(str_replace('_', ' ', $sup['assignment_role'])) ?></div>
                                        <div class="lr-sup-name"><?= e($sup['lecturer_name']) ?></div>
                                        <?php if (!empty($sup['academic_degree'])): ?>
                                        <div class="lr-sup-degree"><?= e($sup['academic_degree']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/admin.js"></script>
    <script>
        (function() {
        'use strict';
        
        // Konfigurasi PDF
        const pdfUrl = "<?= !empty($documents) ? e(BASE_URL . '/documents/download?id=' . $documents[0]['id']) : '' ?>";
        const versionId = <?= !empty($documents) ? (int)$documents[0]['id'] : 0 ?>;
        let pdfDoc = null, pageNum = 1, pageRendering = false, pageNumPending = null, scale = 1.0;
        let currentAnnotations = [];
        let pendingClick = null;

        const canvas = document.getElementById('pdfCanvas');
        const ctx = canvas.getContext('2d');
        const annotLayer = document.getElementById('annotationLayer');

        // Render Halaman PDF
        const renderPage = (num) => {
            if (!pdfDoc) return;
            pageRendering = true;
            pdfDoc.getPage(num).then((page) => {
                const viewport = page.getViewport({ scale });
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                page.render({ canvasContext: ctx, viewport }).promise.then(() => {
                    pageRendering = false;
                    if (pageNumPending !== null) { renderPage(pageNumPending); pageNumPending = null; }
                    document.getElementById('currentPage').textContent = pageNum;
                    document.getElementById('totalPages').textContent = pdfDoc.numPages;
                    renderAnnotationsOnCurrentPage();
                });
            });
        };

        // Load PDF & Anotasi Awal
        if (pdfUrl) {
            pdfjsLib.getDocument(pdfUrl).promise.then((pdf) => {
                pdfDoc = pdf;
                renderPage(pageNum);
                fetchAnnotations();
            }).catch(err => console.error('PDF Load Error:', err));
        }

        // Navigasi & Zoom
        document.getElementById('prevPage')?.addEventListener('click', () => { if (pageNum > 1) { pageNum--; renderPage(pageNum); } });
        document.getElementById('nextPage')?.addEventListener('click', () => { if (pdfDoc && pageNum < pdfDoc.numPages) { pageNum++; renderPage(pageNum); } });
        document.getElementById('zoomIn')?.addEventListener('click', () => { scale += 0.25; renderPage(pageNum); });
        document.getElementById('zoomOut')?.addEventListener('click', () => { if (scale > 0.5) { scale -= 0.25; renderPage(pageNum); } });
        document.getElementById('zoomSelect')?.addEventListener('change', (e) => { scale = parseFloat(e.target.value); renderPage(pageNum); });

        // Sistem Anotasi
        const fetchAnnotations = async () => {
            if (!versionId) return;
            try {
                const res = await fetch(`<?= BASE_URL ?>/api/comments?version_id=${versionId}`);
                const data = await res.json();
                if (data.success) currentAnnotations = data.annotations || [];
                renderAnnotationsOnCurrentPage();
            } catch (e) { console.error(e); }
        };

        const renderAnnotationsOnCurrentPage = () => {
            if (!annotLayer) return;
            annotLayer.innerHTML = '';
            currentAnnotations.filter(a => a.page_number === pageNum).forEach(ann => {
                const marker = document.createElement('div');
                marker.className = `lr-annotation-marker priority-${ann.priority}`;
                marker.style.left = ann.pos_x_pct + '%';
                marker.style.top = ann.pos_y_pct + '%';
                marker.textContent = '!';
                marker.title = ann.comment;
                marker.addEventListener('click', (e) => { e.stopPropagation(); alert(`Halaman ${ann.page_number}\nPrioritas: ${ann.priority}\n\n${ann.comment}`); });
                annotLayer.appendChild(marker);
            });
        };

        // Klik pada PDF untuk menambah anotasi
        annotLayer?.addEventListener('click', (e) => {
            if (!pdfDoc) return;
            const rect = annotLayer.getBoundingClientRect();
            pendingClick = { 
                page: pageNum, 
                x: ((e.clientX - rect.left) / rect.width * 100).toFixed(2), 
                y: ((e.clientY - rect.top) / rect.height * 100).toFixed(2) 
            };
            document.getElementById('popupComment').value = '';
            document.getElementById('annotationPopup').classList.add('active');
        });

        // Simpan Anotasi
        document.getElementById('saveAnnotation')?.addEventListener('click', async () => {
            const comment = document.getElementById('popupComment').value.trim();
            const priority = document.querySelector('input[name="priority"]:checked')?.value || 'important';
            if (!comment) { alert('Komentar tidak boleh kosong'); return; }
            
            const formData = new FormData();
            formData.append('csrf_token', document.querySelector('[name="csrf_token"]').value);
            formData.append('version_id', versionId);
            formData.append('page_number', pendingClick.page);
            formData.append('pos_x_pct', pendingClick.x);
            formData.append('pos_y_pct', pendingClick.y);
            formData.append('comment', comment);
            formData.append('priority', priority);

            try {
                const res = await fetch('<?= BASE_URL ?>/api/comments/save', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) {
                    currentAnnotations.push({ id: data.id, page_number: pendingClick.page, pos_x_pct: pendingClick.x, pos_y_pct: pendingClick.y, comment, priority, author_name: 'Anda' });
                    renderAnnotationsOnCurrentPage();
                    document.getElementById('annotationPopup').classList.remove('active');
                } else { alert('Gagal menyimpan: ' + (data.error || 'Unknown')); }
            } catch (e) { alert('Error jaringan saat menyimpan anotasi'); }
        });

        document.getElementById('cancelAnnotation')?.addEventListener('click', () => { document.getElementById('annotationPopup').classList.remove('active'); });

        // Logika Checklist Interaktif
        document.querySelectorAll('.lr-checklist-item input[type="checkbox"]').forEach(cb => {
            cb.addEventListener('change', function() {
                const noteArea = this.closest('.lr-checklist-item').querySelector('.lr-checklist-note');
                if (this.checked && !noteArea.value.trim()) { noteArea.style.display = 'block'; noteArea.focus(); } 
                else if (!this.checked) { noteArea.style.display = 'none'; }
            });
        });

        // Logika Decision Cards & Form Validation (Sama seperti sebelumnya)
        document.querySelectorAll('.lr-decision-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.lr-decision-card').forEach(c => c.classList.remove('is-selected'));
                this.classList.add('is-selected');
                this.querySelector('input').checked = true;
            });
        });

        const form = document.getElementById('lrReviewForm');
        const submitBtn = document.getElementById('lrSubmitBtn');
        if (form) {
            form.addEventListener('submit', function(e) {
                const decisionSelected = form.querySelector('input[name="decision"]:checked');
                if (!decisionSelected) { e.preventDefault(); alert('Pilih salah satu keputusan terlebih dahulu.'); return; }
                submitBtn.classList.add('is-loading'); submitBtn.disabled = true;
            });
        }
        
        // Counter komentar tetap dipertahankan
        const commentEl = document.getElementById('lrComment');
        const counterEl = document.getElementById('lrCommentCounter');
        if(commentEl && counterEl) {
            commentEl.addEventListener('input', () => {
                counterEl.textContent = commentEl.value.length + '/2000';
            });
        }
    })();
    </script>
</body>
</html>