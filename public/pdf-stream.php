<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('log_errors', '1');
ini_set('display_errors', '0');

function pdf_fail(int $code, string $msg): void
{
    while (ob_get_level() > 0) { ob_end_clean(); }
    http_response_code($code);
    header('Content-Type: text/plain; charset=utf-8');
    exit($msg);
}

try {
    require_once dirname(__DIR__) . '/config/config.php';
    require_once dirname(__DIR__) . '/config/database.php';
    require_once dirname(__DIR__) . '/app/Core/Helpers.php';
    require_once dirname(__DIR__) . '/app/Core/Auth.php';

    Auth::start();

    if (!Auth::check()) {
        pdf_fail(401, 'Unauthorized: silakan login terlebih dahulu.');
    }

    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        pdf_fail(400, 'Bad Request: parameter id tidak valid.');
    }

    $db = Database::connection();

    // Cari sebagai document_versions.id, lalu fallback sebagai documents.id
    $stmt = $db->prepare('SELECT dv.*, d.academic_work_id FROM document_versions dv JOIN documents d ON dv.document_id = d.id WHERE dv.id = ? LIMIT 1');
    $stmt->execute([$id]);
    $doc = $stmt->fetch();

    if (!$doc) {
        $stmt = $db->prepare('SELECT dv.*, d.academic_work_id FROM document_versions dv JOIN documents d ON dv.document_id = d.id WHERE d.id = ? ORDER BY dv.is_current DESC, dv.version_number DESC LIMIT 1');
        $stmt->execute([$id]);
        $doc = $stmt->fetch();
    }

    if (!$doc) {
        pdf_fail(404, 'Not Found: dokumen/versi id=' . $id . ' tidak ada di database.');
    }

    // RBAC
    $role = Auth::role();
    $userId = Auth::id();
    $ok = false;

    if ($role === 'admin') {
        $ok = true;
    } elseif ($role === 'student') {
        $s = $db->prepare('SELECT 1 FROM academic_works aw JOIN student_profiles sp ON aw.student_id = sp.id WHERE aw.id = ? AND sp.user_id = ? LIMIT 1');
        $s->execute([$doc['academic_work_id'], $userId]);
        $ok = (bool)$s->fetch();
    } elseif ($role === 'lecturer') {
        $s = $db->prepare('SELECT 1 FROM supervision_assignments sa JOIN lecturer_profiles lp ON sa.lecturer_id = lp.id WHERE sa.academic_work_id = ? AND lp.user_id = ? AND sa.is_active = 1 LIMIT 1');
        $s->execute([$doc['academic_work_id'], $userId]);
        $ok = (bool)$s->fetch();
        if (!$ok) {
            $s = $db->prepare('SELECT 1 FROM reviews r WHERE r.document_version_id = ? AND r.reviewer_id = ? LIMIT 1');
            $s->execute([$doc['id'], $userId]);
            $ok = (bool)$s->fetch();
        }
    }

    if (!$ok) {
        pdf_fail(403, 'Forbidden: Anda tidak punya akses ke dokumen ini.');
    }

    // Resolve file fisik
    $filePath = null;
    $candidates = [];
    if (!empty($doc['file_path'])) $candidates[] = $doc['file_path'];
    if (!empty($doc['stored_filename'])) {
        $candidates[] = dirname(__DIR__) . '/storage/documents/' . $doc['stored_filename'];
        if (defined('STORAGE_PATH')) $candidates[] = STORAGE_PATH . '/' . $doc['stored_filename'];
    }

    foreach ($candidates as $c) {
        if ($c && is_file($c) && is_readable($c)) { $filePath = $c; break; }
    }

    if ($filePath === null) {
        pdf_fail(404, 'Not Found: file fisik tidak ada. Dicoba: ' . implode(' | ', $candidates));
    }

    $fileSize = filesize($filePath);
    if ($fileSize < 100) {
        pdf_fail(410, 'Gone: file kosong/rusak (' . $fileSize . ' byte).');
    }

    // Validasi magic bytes PDF
    $fh = fopen($filePath, 'rb');
    $magic = fread($fh, 5);
    fclose($fh);
    if ($magic !== '%PDF-') {
        pdf_fail(415, 'Bukan PDF: magic bytes = ' . bin2hex((string)$magic) . '. File ini bukan PDF asli.');
    }

    // Bersihkan semua buffer sebelum streaming
    while (ob_get_level() > 0) { ob_end_clean(); }
    if (function_exists('apache_setenv')) { @apache_setenv('no-gzip', '1'); }
    @ini_set('zlib.output_compression', '0');

    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="document-' . (int)$doc['id'] . '.pdf"');
    header('Accept-Ranges: bytes');
    header('Cache-Control: private, max-age=3600');

    // Range support (wajib untuk PDF.js)
    if (isset($_SERVER['HTTP_RANGE']) && preg_match('/bytes=(\d+)-(\d*)/', $_SERVER['HTTP_RANGE'], $m)) {
        $start = (int)$m[1];
        $end = (($m[2] ?? '') !== '') ? min((int)$m[2], $fileSize - 1) : $fileSize - 1;
        $len = $end - $start + 1;

        http_response_code(206);
        header('Content-Range: bytes ' . $start . '-' . $end . '/' . $fileSize);
        header('Content-Length: ' . $len);

        $fh = fopen($filePath, 'rb');
        fseek($fh, $start);
        $sent = 0;
        while (!feof($fh) && $sent < $len) {
            $chunk = fread($fh, min(8192, $len - $sent));
            if ($chunk === false) break;
            echo $chunk;
            $sent += strlen($chunk);
            flush();
        }
        fclose($fh);
        exit;
    }

    header('Content-Length: ' . $fileSize);
    http_response_code(200);
    readfile($filePath);
    exit;

} catch (Throwable $e) {
    error_log('[pdf-stream] ' . $e->getMessage());
    pdf_fail(500, 'Server Error: ' . $e->getMessage());
}