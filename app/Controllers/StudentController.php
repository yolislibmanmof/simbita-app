<?php
declare(strict_types=1);

class StudentController
{
    public static function uploadForm(): void
    {
        Auth::requireRole('student');
        
        $userId = Auth::id();
        $db = Database::connection();
        
        // Get student profile & academic work
        $stmt = $db->prepare('SELECT sp.id FROM student_profiles sp WHERE sp.user_id = ?');
        $stmt->execute([$userId]);
        $student = $stmt->fetch();
        
        if (!$student) {
            $_SESSION['flash_error'] = 'Profil mahasiswa tidak ditemukan';
            redirect('/student/dashboard');
        }
        
        $work = AcademicWork::getByStudentId((int)$student['id']);
        
        if (!$work) {
            $_SESSION['flash_error'] = 'Anda belum memiliki tugas akhir yang terdaftar.';
            redirect('/student/dashboard');
        }
        
        // Get document types
        $docTypes = $db->prepare('
            SELECT * FROM document_types 
            WHERE work_type_id IS NULL OR work_type_id = ?
            ORDER BY sort_order ASC
        ');
        $docTypes->execute([$work['work_type_id']]);
        $documentTypes = $docTypes->fetchAll();
        
        // Get current documents with latest version info
        $stmt = $db->prepare('
            SELECT d.*, dt.name as type_name, dt.code as type_code,
                   dv.id as version_id, dv.version_number, dv.original_filename,
                   dv.uploaded_at, r.review_status
            FROM documents d
            JOIN document_types dt ON d.document_type_id = dt.id
            LEFT JOIN document_versions dv ON d.id = dv.document_id AND dv.is_current = 1
            LEFT JOIN reviews r ON dv.id = r.document_version_id
            WHERE d.academic_work_id = ?
            ORDER BY dt.sort_order ASC
        ');
        $stmt->execute([$work['id']]);
        $documents = $stmt->fetchAll();
        
        require dirname(__DIR__) . '/Views/student/upload.php';
    }

    public static function upload(): void
    {
        Auth::requireRole('student');
        
        // Verifikasi CSRF
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($csrfToken)) {
            $_SESSION['flash_error'] = 'Token keamanan tidak valid.';
            redirect('/student/upload');
        }

        // Validasi dasar
        if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'Gagal mengunggah file. Silakan coba lagi.';
            redirect('/student/upload');
        }

        $file = $_FILES['document'];
        $documentTypeId = (int)($_POST['document_type_id'] ?? 0);
        $note = trim($_POST['student_note'] ?? '');

        if ($file['size'] > MAX_UPLOAD_BYTES) {
            $_SESSION['flash_error'] = "Ukuran file terlalu besar (Maksimal " . (MAX_UPLOAD_BYTES / 1024 / 1024) . " MB).";
            redirect('/student/upload');
        }

        // Validasi MIME Type ketat
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, ALLOWED_MIME, true)) {
            $_SESSION['flash_error'] = 'Hanya file PDF yang diperbolehkan untuk diunggah.';
            redirect('/student/upload');
        }

        // Pastikan folder storage aman ada
        if (!is_dir(STORAGE_PATH)) {
            mkdir(STORAGE_PATH, 0755, true);
        }

        // Generate nama file acak yang aman
        $storedFilename = bin2hex(random_bytes(16)) . '.pdf';
        $filePath = STORAGE_PATH . '/' . $storedFilename;

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            $_SESSION['flash_error'] = 'Gagal menyimpan file ke server.';
            redirect('/student/upload');
        }

        chmod($filePath, 0640); // Permission lebih ketat (hanya owner & group read)

        $db = Database::connection();
        
        try {
            $db->beginTransaction();

            $userId = Auth::id();
            
            // Ambil Work ID secara aman
            $stmt = $db->prepare('SELECT aw.id FROM academic_works aw JOIN student_profiles sp ON aw.student_id = sp.id WHERE sp.user_id = ?');
            $stmt->execute([$userId]);
            $work = $stmt->fetch();
            
            if (!$work) throw new Exception('Data tugas akhir tidak valid.');

            // Cek atau buat entri dokumen induk
            $stmt = $db->prepare('SELECT id FROM documents WHERE academic_work_id = ? AND document_type_id = ?');
            $stmt->execute([$work['id'], $documentTypeId]);
            $document = $stmt->fetch();

            if (!$document) {
                $stmt = $db->prepare('INSERT INTO documents (academic_work_id, document_type_id, title) VALUES (?, ?, ?)');
                $stmt->execute([$work['id'], $documentTypeId, $file['name']]);
                $documentId = (int)$db->lastInsertId();
            } else {
                $documentId = (int)$document['id'];
                // Nonaktifkan versi sebelumnya
                $db->prepare('UPDATE document_versions SET is_current = 0 WHERE document_id = ?')->execute([$documentId]);
            }

            // Hitung nomor versi berikutnya
            $stmt = $db->prepare('SELECT COALESCE(MAX(version_number), 0) + 1 as next_version FROM document_versions WHERE document_id = ?');
            $stmt->execute([$documentId]);
            $nextVersion = (int)$stmt->fetch()['next_version'];

            // Simpan metadata versi baru
            $stmt = $db->prepare('
                INSERT INTO document_versions 
                (document_id, version_number, original_filename, stored_filename, 
                 file_path, file_mime, file_size_bytes, student_note, uploaded_by, uploaded_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ');
            $stmt->execute([
                $documentId, $nextVersion, $file['name'], $storedFilename,
                $filePath, $mimeType, $file['size'], $note, $userId
            ]);

            $versionId = (int)$db->lastInsertId();

            // Buat status review PENDING untuk versi baru ini
            $stmt = $db->prepare('INSERT INTO reviews (document_version_id, review_status, created_at) VALUES (?, ?, NOW())');
            $stmt->execute([$versionId, 'pending']);

            // Notifikasi ke SEMUA dosen pembimbing aktif (Supervisor 1 & 2)
            $stmt = $db->prepare('
                SELECT lp.user_id FROM supervision_assignments sa
                JOIN lecturer_profiles lp ON sa.lecturer_id = lp.id
                WHERE sa.academic_work_id = ? AND sa.is_active = 1
            ');
            $stmt->execute([$work['id']]);
            $supervisors = $stmt->fetchAll();
            
            foreach ($supervisors as $sup) {
                Notification::create(
                    (int)$sup['user_id'],
                    'Dokumen Baru Diupload',
                    sprintf('%s mengunggah %s (Versi %d)', Auth::user()['name'] ?? 'Mahasiswa', $file['name'], $nextVersion),
                    'upload',
                    'document_versions',
                    $versionId,
                    '/lecturer/review?id=' . $work['id']
                );
            }

            $db->commit();
            $_SESSION['flash_success'] = "Dokumen berhasil diunggah sebagai Versi $nextVersion.";
            redirect('/student/upload');

        } catch (Exception $e) {
            $db->rollBack();
            if (file_exists($filePath)) unlink($filePath); // Hapus file fisik jika DB gagal
            error_log('[Student Upload Error] ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Terjadi kesalahan sistem saat menyimpan dokumen.';
            redirect('/student/upload');
        }
    }

    public static function history(): void
    {
        Auth::requireRole('student');
        
        $userId = Auth::id();
        $db = Database::connection();
        
        $stmt = $db->prepare('SELECT sp.id FROM student_profiles sp WHERE sp.user_id = ?');
        $stmt->execute([$userId]);
        $student = $stmt->fetch();
        
        if (!$student) redirect('/student/dashboard');
        
        // 1. History lengkap dengan status review terbaru
        $stmt = $db->prepare('
            SELECT dv.*, d.academic_work_id, dt.name as type_name, dt.code as type_code,
                   aw.title as work_title,
                   r.review_status, r.general_note, r.reviewed_at, r.reviewer_id,
                   u.name as reviewer_name
            FROM document_versions dv
            JOIN documents d ON dv.document_id = d.id
            JOIN document_types dt ON d.document_type_id = dt.id
            JOIN academic_works aw ON d.academic_work_id = aw.id
            LEFT JOIN reviews r ON dv.id = r.document_version_id
            LEFT JOIN users u ON r.reviewer_id = u.id
            WHERE aw.student_id = ?
            ORDER BY dv.uploaded_at DESC
        ');
        $stmt->execute([$student['id']]);
        $history = $stmt->fetchAll();
        
        // 2. AMBIL DATA ANOTASI UNTUK SETIAP VERSI DOKUMEN (KRUSIAL!)
        $annotationsMap = [];
        if (!empty($history)) {
            $versionIds = array_column($history, 'id');
            $placeholders = implode(',', array_fill(0, count($versionIds), '?'));
            
            $stmt = $db->prepare("
                SELECT rc.*, r.document_version_id 
                FROM review_comments rc
                JOIN reviews r ON rc.review_id = r.id
                WHERE r.document_version_id IN ($placeholders)
                ORDER BY rc.page_number ASC, rc.created_at ASC
            ");
            $stmt->execute($versionIds);
            
            foreach ($stmt->fetchAll() as $ann) {
                $annotationsMap[$ann['document_version_id']][] = $ann;
            }
        }
        
        // 3. Kirim SEMUA variabel ke view
        require dirname(__DIR__) . '/Views/student/history.php';
    }
 } 