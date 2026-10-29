<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/app/Core/Auth.php';

// Selaraskan dengan public/index.php yang saat ini memulai sesi sebelum config dimuat.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    Auth::start();

    if (!Auth::check()) {
        http_response_code(401);
        exit('Silakan login untuk mengakses dokumen.');
    }

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id || $id < 1) {
        http_response_code(400);
        exit('ID dokumen tidak valid.');
    }

    $db = Database::connection();

    // Terima ID versi dokumen, atau ID dokumen lama untuk menjaga kompatibilitas.
    $stmt = $db->prepare(
        'SELECT dv.*, d.academic_work_id
         FROM document_versions dv
         JOIN documents d ON dv.document_id = d.id
         WHERE dv.id = ?'
    );
    $stmt->execute([$id]);
    $doc = $stmt->fetch();

    if (!$doc) {
        $stmt = $db->prepare(
            'SELECT dv.*, d.academic_work_id
             FROM document_versions dv
             JOIN documents d ON dv.document_id = d.id
             WHERE d.id = ?
             ORDER BY dv.version_number DESC, dv.id DESC
             LIMIT 1'
        );
        $stmt->execute([$id]);
        $doc = $stmt->fetch();
    }

    if (!$doc) {
        http_response_code(404);
        exit('Dokumen tidak ditemukan.');
    }

    $allowed = false;
    $role = Auth::role();
    $userId = Auth::id();

    if ($role === 'admin') {
        $allowed = true;
    } elseif ($role === 'student' && $userId !== null) {
        $stmt = $db->prepare(
            'SELECT 1
             FROM academic_works aw
             JOIN student_profiles sp ON sp.id = aw.student_id
             WHERE aw.id = ? AND sp.user_id = ?
             LIMIT 1'
        );
        $stmt->execute([(int)$doc['academic_work_id'], $userId]);
        $allowed = (bool)$stmt->fetchColumn();
    } elseif ($role === 'lecturer' && $userId !== null) {
        $stmt = $db->prepare(
            'SELECT 1
             FROM supervision_assignments sa
             JOIN lecturer_profiles lp ON lp.id = sa.lecturer_id
             WHERE sa.academic_work_id = ?
               AND lp.user_id = ?
               AND sa.is_active = 1
             LIMIT 1'
        );
        $stmt->execute([(int)$doc['academic_work_id'], $userId]);
        $allowed = (bool)$stmt->fetchColumn();
    }

    if (!$allowed) {
        http_response_code(403);
        exit('Anda tidak memiliki izin untuk mengakses dokumen ini.');
    }

    // Hanya izinkan file yang berada di dalam direktori penyimpanan dokumen.
    $storageRoot = realpath(STORAGE_PATH);
    if ($storageRoot === false) {
        http_response_code(404);
        exit('File dokumen tidak tersedia.');
    }

    $candidates = [];
    if (!empty($doc['file_path'])) {
        $candidates[] = (string)$doc['file_path'];
    }
    if (!empty($doc['stored_filename'])) {
        $storedFilename = (string)$doc['stored_filename'];
        if (basename($storedFilename) === $storedFilename) {
            $candidates[] = $storageRoot . DIRECTORY_SEPARATOR . $storedFilename;
        }
    }

    $path = null;
    $storagePrefix = rtrim($storageRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

    foreach ($candidates as $candidate) {
        $resolved = realpath($candidate);
        if ($resolved === false || !is_file($resolved) || !is_readable($resolved)) {
            continue;
        }

        $insideStorage = DIRECTORY_SEPARATOR === '\\'
            ? strncasecmp($resolved, $storagePrefix, strlen($storagePrefix)) === 0
            : strncmp($resolved, $storagePrefix, strlen($storagePrefix)) === 0;

        if ($insideStorage) {
            $path = $resolved;
            break;
        }
    }

    if ($path === null) {
        http_response_code(404);
        exit('File dokumen tidak tersedia.');
    }

    $fileInfo = new finfo(FILEINFO_MIME_TYPE);
    if ($fileInfo->file($path) !== 'application/pdf') {
        http_response_code(415);
        exit('File ini bukan PDF.');
    }

    $fileSize = filesize($path);
    if ($fileSize === false) {
        http_response_code(500);
        exit('File dokumen tidak dapat dibaca.');
    }

    $filename = preg_replace(
        '/[^\w.-]/',
        '_',
        (string)($doc['original_filename'] ?? 'document.pdf')
    ) ?: 'document.pdf';

    header('Content-Type: application/pdf');
    header('Content-Length: ' . $fileSize);
    header('Content-Disposition: inline; filename="' . $filename . '"');
    header('Cache-Control: no-store, private');
    header('X-Content-Type-Options: nosniff');
    header('Accept-Ranges: none');

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    readfile($path);
    exit;
} catch (Throwable $e) {
    error_log('[PDF streamer] ' . $e->getMessage());
    http_response_code(500);
    exit('Terjadi kesalahan saat memuat dokumen.');
}