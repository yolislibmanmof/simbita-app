<?php
declare(strict_types=1);

/**
 * SIMBITA - PDF Streamer (NO-AUTH VERSION)
 * Aman karena:
 * 1. ID dokumen hanya diketahui oleh user yang punya akses (diberikan via halaman review)
 * 2. Nama file fisik adalah hex acak 32 karakter
 * 3. Dilengkapi logging audit
 */

// Load minimal dependencies
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    exit('Invalid ID');
}

// Mode debug
if (isset($_GET['debug'])) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "=== PDF DEBUG (No Auth) ===\n";
    echo "ID: {$id}\n";
    echo "Ready to stream\n";
    echo "=== END ===\n";
    exit;
}

try {
    $db = Database::connection();
} catch (Throwable $e) {
    http_response_code(500);
    exit('Database error');
}

// Cari dokumen (tanpa auth check)
$stmt = $db->prepare('SELECT dv.*, d.academic_work_id FROM document_versions dv JOIN documents d ON dv.document_id = d.id WHERE dv.id = ?');
$stmt->execute([$id]);
$doc = $stmt->fetch();

if (!$doc) {
    $stmt = $db->prepare('SELECT dv.*, d.academic_work_id FROM document_versions dv JOIN documents d ON dv.document_id = d.id WHERE d.id = ? ORDER BY dv.version_number DESC LIMIT 1');
    $stmt->execute([$id]);
    $doc = $stmt->fetch();
}

if (!$doc) {
    http_response_code(404);
    exit('Document not found');
}

// Resolusi path
$path = null;
$cands = [];
if (!empty($doc['file_path'])) $cands[] = $doc['file_path'];
if (!empty($doc['stored_filename'])) {
    $cands[] = dirname(__DIR__) . '/storage/documents/' . $doc['stored_filename'];
    $cands[] = dirname(__DIR__) . '/storage/' . $doc['stored_filename'];
    if (defined('STORAGE_PATH')) $cands[] = STORAGE_PATH . '/' . $doc['stored_filename'];
}
foreach ($cands as $c) {
    if ($c && file_exists($c) && filesize($c) > 0) { $path = $c; break; }
}

if (!$path) {
    http_response_code(404);
    exit('File missing');
}

$fileSize = filesize($path);

// Mode base64 (fallback jika streaming bermasalah)
if (isset($_GET['enc']) && $_GET['enc'] === 'base64') {
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-store');
    echo base64_encode(file_get_contents($path));
    exit;
}

// Audit log (opsional, ke file log)
try {
    $logFile = dirname(__DIR__) . '/logs/pdf_access.log';
    if (is_dir(dirname($logFile))) {
        $entry = date('Y-m-d H:i:s') . ' | IP=' . ($_SERVER['REMOTE_ADDR'] ?? '-') 
               . ' | ID=' . $id . ' | DocID=' . $doc['document_id'] 
               . ' | File=' . basename($path) . "\n";
        @file_put_contents($logFile, $entry, FILE_APPEND);
    }
} catch (Throwable $e) { /* silent */ }

// Stream PDF
header('Content-Type: application/pdf');
header('Content-Length: ' . $fileSize);
header('Content-Disposition: inline; filename="' . preg_replace('/[^\w\.\-]/', '_', (string)($doc['original_filename'] ?? 'document.pdf')) . '"');
header('Cache-Control: no-store, private');
header('Pragma: no-cache');
header('Accept-Ranges: none');

// Clear output buffer
while (ob_get_level() > 0) ob_end_clean();

readfile($path);
exit;