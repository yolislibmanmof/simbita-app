<?php
declare(strict_types=1);

class DocumentController
{
    /**
     * Upload dokumen baru (dipanggil mahasiswa)
     */
    public static function upload(): void
    {
        Auth::requireRole('student');
        
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            json_response(['error' => 'Token CSRF tidak valid'], 419);
        }

        $db = Database::connection();
        $userId = Auth::id();
        
        // Ambil student profile
        $stmt = $db->prepare('SELECT id FROM student_profiles WHERE user_id = ?');
        $stmt->execute([$userId]);
        $sp = $stmt->fetch();
        if (!$sp) {
            json_response(['error' => 'Profil mahasiswa tidak ditemukan'], 404);
        }

        // Ambil academic work mahasiswa
        $work = AcademicWork::getByStudentId((int)$sp['id']);
        if (!$work) {
            json_response(['error' => 'Anda belum memiliki tugas akhir'], 400);
        }

        // Validasi file upload
        if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            json_response(['error' => 'File tidak valid atau upload gagal'], 400);
        }

        $file = $_FILES['file'];
        $maxSize = 50 * 1024 * 1024; // 50MB
        if ($file['size'] > $maxSize) {
            json_response(['error' => 'Ukuran file terlalu besar (maks 50MB)'], 400);
        }

        // Validasi ekstensi
        $allowedExts = ['pdf', 'doc', 'docx'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts)) {
            json_response(['error' => 'Format file tidak diizinkan. Gunakan PDF, DOC, atau DOCX'], 400);
        }

        // Validasi MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowedMimes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
        if (!in_array($mime, $allowedMimes)) {
            json_response(['error' => 'Tipe file tidak valid'], 400);
        }

        // Generate nama file unik
        $storedFilename = bin2hex(random_bytes(16)) . '.' . $ext;
        $storageDir = dirname(__DIR__, 2) . '/storage/documents';
        
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        $filePath = $storageDir . '/' . $storedFilename;

        // Pindahkan file
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            json_response(['error' => 'Gagal menyimpan file ke server'], 500);
        }

        $docTypeId = (int)($_POST['document_type_id'] ?? 1);
        $title = trim($_POST['title'] ?? $file['name']);

        try {
            $db->beginTransaction();

            // Cek apakah document sudah ada
            $stmt = $db->prepare('SELECT id FROM documents WHERE academic_work_id = ? AND document_type_id = ?');
            $stmt->execute([$work['id'], $docTypeId]);
            $existingDoc = $stmt->fetch();

            if ($existingDoc) {
                $docId = (int)$existingDoc['id'];
                // Nonaktifkan versi sebelumnya
                $db->prepare('UPDATE document_versions SET is_current = 0 WHERE document_id = ?')
                    ->execute([$docId]);
                // Ambil versi terakhir
                $stmt = $db->prepare('SELECT MAX(version_number) AS max_ver FROM document_versions WHERE document_id = ?');
                $stmt->execute([$docId]);
                $maxVer = (int)($stmt->fetch()['max_ver'] ?? 0);
                $newVer = $maxVer + 1;
            } else {
                // Buat document baru
                $stmt = $db->prepare('
                    INSERT INTO documents (academic_work_id, document_type_id, title, created_at)
                    VALUES (?, ?, ?, NOW())
                ');
                $stmt->execute([$work['id'], $docTypeId, $title]);
                $docId = (int)$db->lastInsertId();
                $newVer = 1;
            }

            // Buat versi baru
            $stmt = $db->prepare('
                INSERT INTO document_versions 
                    (document_id, version_number, is_current, original_filename, stored_filename, 
                     file_path, file_size_bytes, file_mime, uploaded_at, uploader_id)
                VALUES (?, ?, 1, ?, ?, ?, ?, ?, NOW(), ?)
            ');
            $stmt->execute([
                $docId, $newVer, $file['name'], $storedFilename,
                $filePath, $file['size'], $mime, $userId
            ]);
            $versionId = (int)$db->lastInsertId();

            // Buat review otomatis
            $stmt = $db->prepare('
                INSERT INTO reviews 
                    (document_version_id, reviewer_id, review_status, created_at)
                VALUES (?, NULL, \'pending\', NOW())
            ');
            $stmt->execute([$versionId]);

            // Update status work ke "writing"
            if ($work['status'] === 'draft' || $work['status'] === 'proposal') {
                AcademicWork::updateStatus((int)$work['id'], 'writing');
            }

            $db->commit();

            // Kirim notifikasi ke supervisor
            try {
                $stmt = $db->prepare('
                    SELECT lp.user_id FROM supervision_assignments sa
                    JOIN lecturer_profiles lp ON sa.lecturer_id = lp.id
                    WHERE sa.academic_work_id = ? AND sa.is_active = 1
                ');
                $stmt->execute([$work['id']]);
                $supervisors = $stmt->fetchAll();
                foreach ($supervisors as $s) {
                    Notification::create(
                        (int)$s['user_id'],
                        'Dokumen Baru Diupload',
                        (Auth::user()['name'] ?? 'Mahasiswa') . ' mengupload dokumen: ' . $title,
                        'upload', 'documents', $docId, '/lecturer/review?id=' . $work['id']
                    );
                }
            } catch (Throwable $e) {
                error_log('[DocumentController::upload notification] ' . $e->getMessage());
            }

            json_response([
                'success' => true,
                'message' => 'Dokumen berhasil diupload (Versi ' . $newVer . ')',
                'version_id' => $versionId,
                'document_id' => $docId,
            ]);

        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            if (file_exists($filePath)) @unlink($filePath);
            error_log('[DocumentController::upload] ' . $e->getMessage());
            json_response(['error' => 'Gagal memproses upload: ' . $e->getMessage()], 500);
        }
    }

    public static function download(): void
    {
        // 1. Inisialisasi Session (Wajib untuk Auth)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            http_response_code(400);
            exit('Invalid ID');
        }

        $db = Database::connection();
        $doc = null;

        // 2. LOGIKA PENCARIAN DOKUMEN (3 LAYER FALLBACK)
        // Layer 1: Cari berdasarkan document_versions.id
        $stmt = $db->prepare('SELECT dv.*, d.academic_work_id FROM document_versions dv JOIN documents d ON dv.document_id = d.id WHERE dv.id = ? LIMIT 1');
        $stmt->execute([$id]);
        $doc = $stmt->fetch();

        // Layer 2: Cari berdasarkan documents.id (ambil versi current)
        if (!$doc) {
            $stmt = $db->prepare('SELECT dv.*, d.academic_work_id FROM document_versions dv JOIN documents d ON dv.document_id = d.id WHERE d.id = ? AND dv.is_current = 1 LIMIT 1');
            $stmt->execute([$id]);
            $doc = $stmt->fetch();
        }

        // Layer 3: Cari berdasarkan documents.id (ambil versi terbaru)
        if (!$doc) {
            $stmt = $db->prepare('SELECT dv.*, d.academic_work_id FROM document_versions dv JOIN documents d ON dv.document_id = d.id WHERE d.id = ? ORDER BY dv.id DESC LIMIT 1');
            $stmt->execute([$id]);
            $doc = $stmt->fetch();
        }

        if (!$doc) {
            http_response_code(404);
            exit('Dokumen tidak ditemukan di database.');
        }

        // 3. VERIFIKASI AKSES (Diperbaiki: Menambahkan cek Reviewer untuk Dosen)
        $canAccess = false;
        $userRole = $_SESSION['simbita_user']['role'] ?? null;
        $userId = $_SESSION['simbita_user']['id'] ?? null;

        if ($userRole === 'admin') {
            $canAccess = true;
        } elseif ($userRole === 'student') {
            $stmt = $db->prepare('SELECT 1 FROM academic_works aw JOIN student_profiles sp ON aw.student_id = sp.id WHERE aw.id = ? AND sp.user_id = ? LIMIT 1');
            $stmt->execute([$doc['academic_work_id'], $userId]);
            $canAccess = (bool)$stmt->fetch();
        } elseif ($userRole === 'lecturer') {
            // Cek apakah dosen adalah pembimbing
            $stmt = $db->prepare('SELECT 1 FROM supervision_assignments sa JOIN lecturer_profiles lp ON sa.lecturer_id = lp.id WHERE sa.academic_work_id = ? AND lp.user_id = ? AND sa.is_active = 1 LIMIT 1');
            $stmt->execute([$doc['academic_work_id'], $userId]);
            $canAccess = (bool)$stmt->fetch();
            
            // Cek juga jika dosen adalah reviewer
            if (!$canAccess) {
                $stmt = $db->prepare('SELECT 1 FROM reviews r WHERE r.document_version_id = ? AND r.reviewer_id = ? LIMIT 1');
                $stmt->execute([$id, $userId]);
                $canAccess = (bool)$stmt->fetch();
            }
        }

        if (!$canAccess) {
            http_response_code(403);
            exit('Akses ditolak.');
        }

        // 4. MENCARI FILE FISIK
        $filePath = null;
        $candidates = [
            $doc['file_path'] ?? null,
            defined('STORAGE_PATH') ? STORAGE_PATH . '/' . ($doc['stored_filename'] ?? '') : null,
            dirname(__DIR__, 2) . '/storage/documents/' . ($doc['stored_filename'] ?? ''),
        ];

        foreach ($candidates as $candidate) {
            if ($candidate && file_exists($candidate) && is_readable($candidate) && filesize($candidate) > 0) {
                $filePath = $candidate;
                break;
            }
        }

        if (!$filePath) {
            http_response_code(404);
            exit('File fisik tidak ditemukan atau rusak.');
        }

        $fileSize = filesize($filePath);

        // 5. STREAMING FILE DENGAN DUKUNGAN HTTP RANGE (PENTING UNTUK PDF.JS)
        
        // A. Bersihkan SEMUA output buffer yang ada (Wajib!)
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // B. Matikan kompresi output
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', 1);
        }
        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);

        // C. Kirim Header PDF Dasar
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
        header('Cache-Control: private, max-age=3600');
        header('Accept-Ranges: bytes');

        // D. Dukungan HTTP Range Request (Kunci agar PDF.js tidak gagal load)
        if (isset($_SERVER['HTTP_RANGE'])) {
            $range = $_SERVER['HTTP_RANGE'];
            if (preg_match('/bytes=(\d+)-(\d*)/', $range, $matches)) {
                $start = (int)$matches[1];
                $end = $matches[2] !== '' ? (int)$matches[2] : $fileSize - 1;
                $length = $end - $start + 1;
                
                http_response_code(206); // 206 Partial Content
                header('Content-Range: bytes ' . $start . '-' . $end . '/' . $fileSize);
                header('Content-Length: ' . $length);
                
                $handle = fopen($filePath, 'rb');
                fseek($handle, $start);
                echo fread($handle, $length);
                fclose($handle);
                exit;
            }
        }

        // E. Full file streaming (Stabil untuk file besar > 1MB)
        header('Content-Length: ' . $fileSize);
        
        if ($fileSize > 1048576) {
            $handle = fopen($filePath, 'rb');
            if ($handle) {
                while (!feof($handle)) {
                    echo fread($handle, 8192);
                    flush();
                }
                fclose($handle);
            }
        } else {
            readfile($filePath);
        }
        
        // F. Matikan script segera
        exit;
    }

    /**
     * Ambil data anotasi/komentar pada versi dokumen
     */
    public static function getAnnotations(): void
    {
        Auth::start();

        $versionId = (int)($_GET['version_id'] ?? 0);
        if (!$versionId) {
            json_response(['error' => 'Version ID required'], 400);
        }

        $db = Database::connection();

        $stmt = $db->prepare('
            SELECT dc.id, dc.page_number, dc.pos_x_pct, dc.pos_y_pct, 
                   dc.comment, dc.priority, dc.created_at,
                   u.name as author_name
            FROM document_comments dc
            JOIN users u ON dc.author_id = u.id
            WHERE dc.document_version_id = ?
            ORDER BY dc.page_number ASC, dc.created_at ASC
        ');
        $stmt->execute([$versionId]);

        json_response([
            'success' => true,
            'annotations' => $stmt->fetchAll()
        ]);
    }

    /**
     * Simpan anotasi dari dosen
     */
    public static function saveAnnotation(): void
    {
        Auth::requireRole('lecturer', 'admin');

        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($csrfToken)) {
            json_response(['error' => 'Token tidak valid'], 419);
        }

        $data = [
            'version_id' => (int)($_POST['version_id'] ?? 0),
            'page'       => (int)($_POST['page_number'] ?? 0),
            'x_pct'      => (float)($_POST['pos_x_pct'] ?? 0),
            'y_pct'      => (float)($_POST['pos_y_pct'] ?? 0),
            'comment'    => trim($_POST['comment'] ?? ''),
            'priority'   => $_POST['priority'] ?? 'normal'
        ];

        if ($data['version_id'] <= 0 || empty($data['comment'])) {
            json_response(['error' => 'Data anotasi tidak lengkap'], 400);
        }

        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                INSERT INTO document_comments 
                (document_version_id, page_number, pos_x_pct, pos_y_pct, comment, priority, author_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ');
            $stmt->execute([
                $data['version_id'], $data['page'], $data['x_pct'], $data['y_pct'],
                $data['comment'], $data['priority'], Auth::id()
            ]);

            json_response(['success' => true, 'id' => (int)$db->lastInsertId()]);
        } catch (Throwable $e) {
            error_log('[DocumentController::saveAnnotation] ' . $e->getMessage());
            json_response(['error' => 'Gagal menyimpan anotasi'], 500);
        }
    }

    /**
     * Tandai komentar selesai/belum
     */
    public static function resolveComment(): void
    {
        Auth::start();

        $commentId = (int)($_POST['comment_id'] ?? 0);
        $resolved = (int)($_POST['resolved'] ?? 1);

        if ($commentId <= 0) {
            json_response(['error' => 'Comment ID tidak valid'], 400);
        }

        try {
            $db = Database::connection();
            $stmt = $db->prepare('UPDATE document_comments SET is_resolved = ? WHERE id = ?');
            $stmt->execute([$resolved, $commentId]);

            json_response(['success' => true]);
        } catch (Throwable $e) {
            json_response(['error' => 'Gagal update komentar'], 500);
        }
    }
}