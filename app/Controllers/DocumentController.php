<?php
declare(strict_types=1);

class DocumentController
{
    /**
     * Upload dokumen
     */
    public static function upload(): void
    {
        Auth::requireRole('student');
        
        // Verifikasi CSRF
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($csrfToken)) {
            json_response(['error' => 'Token keamanan tidak valid'], 419);
        }

        // Validasi file
        if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
            json_response(['error' => 'Gagal upload file'], 400);
        }

        $file = $_FILES['document'];
        $academicWorkId = (int)($_POST['academic_work_id'] ?? 0);
        $documentTypeId = (int)($_POST['document_type_id'] ?? 0);
        $note = trim($_POST['student_note'] ?? '');

        // Validasi ukuran file
        if ($file['size'] > MAX_UPLOAD_BYTES) {
            json_response(['error' => 'Ukuran file terlalu besar (max 25MB)'], 400);
        }

        // Validasi MIME type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, ALLOWED_MIME)) {
            json_response(['error' => 'Hanya file PDF yang diperbolehkan'], 400);
        }

        // Buat folder storage jika belum ada
        if (!is_dir(STORAGE_PATH)) {
            mkdir(STORAGE_PATH, 0755, true);
        }

        // Generate nama file unik
        $storedFilename = uniqid('doc_') . '_' . time() . '.pdf';
        $filePath = STORAGE_PATH . '/' . $storedFilename;

        // Pindahkan file
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            json_response(['error' => 'Gagal menyimpan file'], 500);
        }

        // Set permission
        chmod($filePath, 0644);

        $db = Database::connection();
        
        try {
            $db->beginTransaction();

            // Cek atau buat dokumen
            $stmt = $db->prepare('
                SELECT id FROM documents 
                WHERE academic_work_id = ? AND document_type_id = ?
            ');
            $stmt->execute([$academicWorkId, $documentTypeId]);
            $document = $stmt->fetch();

            if (!$document) {
                // Buat dokumen baru
                $stmt = $db->prepare('
                    INSERT INTO documents (academic_work_id, document_type_id, title)
                    VALUES (?, ?, ?)
                ');
                $stmt->execute([$academicWorkId, $documentTypeId, $file['name']]);
                $documentId = (int)$db->lastInsertId();
            } else {
                $documentId = (int)$document['id'];

                // Set versi sebelumnya bukan current
                $stmt = $db->prepare('
                    UPDATE document_versions SET is_current = 0 WHERE document_id = ?
                ');
                $stmt->execute([$documentId]);
            }

            // Hitung nomor versi
            $stmt = $db->prepare('
                SELECT COALESCE(MAX(version_number), 0) + 1 as next_version
                FROM document_versions WHERE document_id = ?
            ');
            $stmt->execute([$documentId]);
            $nextVersion = (int)$stmt->fetch()['next_version'];

            // Simpan versi dokumen
            $stmt = $db->prepare('
                INSERT INTO document_versions 
                (document_id, version_number, original_filename, stored_filename, 
                 file_path, file_mime, file_size_bytes, student_note, uploaded_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                $documentId,
                $nextVersion,
                $file['name'],
                $storedFilename,
                $filePath,
                $mimeType,
                $file['size'],
                $note,
                Auth::id()
            ]);

            $db->commit();

            // Buat notifikasi untuk dosen pembimbing
            self::notifySupervisors($academicWorkId, $file['name'], $nextVersion);

            json_response([
                'success' => true,
                'message' => 'Dokumen berhasil diupload',
                'version' => $nextVersion
            ]);

        } catch (Exception $e) {
            $db->rollBack();
            unlink($filePath); // Hapus file yang gagal
            json_response(['error' => 'Gagal menyimpan ke database: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Download dokumen
     */
    public static function download(): void
    {
        Auth::start();
        
        $documentId = (int)($_GET['id'] ?? 0);
        if (!$documentId) {
            http_response_code(400);
            die('Invalid document ID');
        }

        $db = Database::connection();
        
        // Ambil info dokumen
        $stmt = $db->prepare('
            SELECT dv.*, d.academic_work_id
            FROM document_versions dv
            JOIN documents d ON dv.document_id = d.id
            WHERE dv.document_id = ? AND dv.is_current = 1
        ');
        $stmt->execute([$documentId]);
        $doc = $stmt->fetch();

        if (!$doc) {
            http_response_code(404);
            die('Dokumen tidak ditemukan');
        }

        // Cek akses (hanya mahasiswa yang bersangkutan atau dosen pembimbing)
        $canAccess = false;
        $userRole = Auth::role();
        $userId = Auth::id();

        if ($userRole === 'admin') {
            $canAccess = true;
        } elseif ($userRole === 'student') {
            $stmt = $db->prepare('
                SELECT sp.user_id FROM academic_works aw
                JOIN student_profiles sp ON aw.student_id = sp.id
                WHERE aw.id = ? AND sp.user_id = ?
            ');
            $stmt->execute([$doc['academic_work_id'], $userId]);
            $canAccess = (bool)$stmt->fetch();
        } elseif ($userRole === 'lecturer') {
            $stmt = $db->prepare('
                SELECT lp.user_id FROM supervision_assignments sa
                JOIN lecturer_profiles lp ON sa.lecturer_id = lp.id
                WHERE sa.academic_work_id = ? AND lp.user_id = ? AND sa.is_active = 1
            ');
            $stmt->execute([$doc['academic_work_id'], $userId]);
            $canAccess = (bool)$stmt->fetch();
        }

        if (!$canAccess) {
            http_response_code(403);
            die('Akses ditolak');
        }

        // Kirim file
        if (!file_exists($doc['file_path'])) {
            http_response_code(404);
            die('File tidak ditemukan di server');
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . basename($doc['original_filename']) . '"');
        header('Content-Length: ' . $doc['file_size_bytes']);
        readfile($doc['file_path']);
        exit;
    }

    /**
     * Buat notifikasi untuk dosen pembimbing
     */
    private static function notifySupervisors(int $academicWorkId, string $filename, int $version): void
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                SELECT lp.user_id 
                FROM supervision_assignments sa
                JOIN lecturer_profiles lp ON sa.lecturer_id = lp.id
                WHERE sa.academic_work_id = ? AND sa.is_active = 1
            ');
            $stmt->execute([$academicWorkId]);
            $supervisors = $stmt->fetchAll();

            foreach ($supervisors as $supervisor) {
                Notification::create(
                    (int)$supervisor['user_id'],
                    'Dokumen Baru Diupload',
                    sprintf('Mahasiswa mengupload dokumen %s (versi %d)', $filename, $version),
                    'review',
                    'document_versions',
                    $academicWorkId,
                    '/lecturer/review'
                );
            }
        } catch (Exception $e) {
            error_log('Failed to create notification: ' . $e->getMessage());
        }
    }
}