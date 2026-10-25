<?php
declare(strict_types=1);

class StudentController
{
    public static function uploadForm(): void
    {
        Auth::requireRole('student');
        
        $userId = Auth::id();
        $db = Database::connection();
        
        // Get student profile
        $stmt = $db->prepare('SELECT id FROM student_profiles WHERE user_id = ?');
        $stmt->execute([$userId]);
        $student = $stmt->fetch();
        
        if (!$student) {
            $_SESSION['flash_error'] = 'Profil mahasiswa tidak ditemukan';
            redirect('/student/dashboard');
        }
        
        // Get academic work
        $work = AcademicWork::getByStudentId((int)$student['id']);
        
        if (!$work) {
            $_SESSION['flash_error'] = 'Anda belum memiliki tugas akhir';
            redirect('/student/dashboard');
        }
        
        // Get document types
        $docTypes = $db->prepare('
            SELECT * FROM document_types 
            WHERE work_type_id IS NULL OR work_type_id = ?
            ORDER BY sort_order
        ');
        $docTypes->execute([$work['work_type_id']]);
        $documentTypes = $docTypes->fetchAll();
        
        // Get current documents
        $stmt = $db->prepare('
            SELECT d.*, dt.name as type_name, dt.code as type_code,
                   dv.id as version_id, dv.version_number, dv.original_filename,
                   dv.uploaded_at
            FROM documents d
            JOIN document_types dt ON d.document_type_id = dt.id
            LEFT JOIN document_versions dv ON d.id = dv.document_id AND dv.is_current = 1
            WHERE d.academic_work_id = ?
            ORDER BY dt.sort_order
        ');
        $stmt->execute([$work['id']]);
        $documents = $stmt->fetchAll();
        
        require dirname(__DIR__) . '/Views/student/upload.php';
    }

    public static function upload(): void
    {
        Auth::requireRole('student');
        
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($csrfToken)) {
            json_response(['error' => 'Token tidak valid'], 419);
        }

        // Validate file
        if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
            json_response(['error' => 'Gagal upload file'], 400);
        }

        $file = $_FILES['document'];
        $documentTypeId = (int)($_POST['document_type_id'] ?? 0);
        $note = trim($_POST['student_note'] ?? '');

        if ($file['size'] > MAX_UPLOAD_BYTES) {
            json_response(['error' => 'Ukuran file terlalu besar (max 25MB)'], 400);
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, ALLOWED_MIME)) {
            json_response(['error' => 'Hanya file PDF yang diperbolehkan'], 400);
        }

        if (!is_dir(STORAGE_PATH)) {
            mkdir(STORAGE_PATH, 0755, true);
        }

        $storedFilename = uniqid('doc_') . '_' . time() . '.pdf';
        $filePath = STORAGE_PATH . '/' . $storedFilename;

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            json_response(['error' => 'Gagal menyimpan file'], 500);
        }

        chmod($filePath, 0644);

        $db = Database::connection();
        
        try {
            $db->beginTransaction();

            // Get student's academic work
            $userId = Auth::id();
            $stmt = $db->prepare('SELECT sp.id FROM student_profiles sp WHERE sp.user_id = ?');
            $stmt->execute([$userId]);
            $student = $stmt->fetch();
            
            $stmt = $db->prepare('SELECT id FROM academic_works WHERE student_id = ?');
            $stmt->execute([$student['id']]);
            $work = $stmt->fetch();
            
            if (!$work) {
                throw new Exception('Tugas akhir tidak ditemukan');
            }

            // Check or create document
            $stmt = $db->prepare('
                SELECT id FROM documents 
                WHERE academic_work_id = ? AND document_type_id = ?
            ');
            $stmt->execute([$work['id'], $documentTypeId]);
            $document = $stmt->fetch();

            if (!$document) {
                $stmt = $db->prepare('
                    INSERT INTO documents (academic_work_id, document_type_id, title)
                    VALUES (?, ?, ?)
                ');
                $stmt->execute([$work['id'], $documentTypeId, $file['name']]);
                $documentId = (int)$db->lastInsertId();
            } else {
                $documentId = (int)$document['id'];
                $db->prepare('UPDATE document_versions SET is_current = 0 WHERE document_id = ?')
                   ->execute([$documentId]);
            }

            // Get next version
            $stmt = $db->prepare('
                SELECT COALESCE(MAX(version_number), 0) + 1 as next_version
                FROM document_versions WHERE document_id = ?
            ');
            $stmt->execute([$documentId]);
            $nextVersion = (int)$stmt->fetch()['next_version'];

            // Save version
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

            // Create review
            $stmt = $db->prepare('
                INSERT INTO reviews (document_version_id, reviewer_id, review_status)
                VALUES (?, ?, ?)
            ');
            
            // Get supervisor
            $stmt2 = $db->prepare('
                SELECT lp.user_id FROM supervision_assignments sa
                JOIN lecturer_profiles lp ON sa.lecturer_id = lp.id
                WHERE sa.academic_work_id = ? AND sa.assignment_role = ? AND sa.is_active = 1
                LIMIT 1
            ');
            $stmt2->execute([$work['id'], 'supervisor_1']);
            $supervisor = $stmt2->fetch();
            
            if ($supervisor) {
                $stmt->execute([(int)$db->lastInsertId(), $supervisor['user_id'], 'pending']);
                
                // Notify supervisor
                Notification::create(
                    (int)$supervisor['user_id'],
                    'Dokumen Baru Diupload',
                    sprintf('Mahasiswa mengupload %s (versi %d)', $file['name'], $nextVersion),
                    'review',
                    'document_versions',
                    $documentId,
                    '/lecturer/reviews'
                );
            }

            $db->commit();
            $_SESSION['flash_success'] = "Dokumen berhasil diupload (Versi $nextVersion)";
            redirect('/student/upload');

        } catch (Exception $e) {
            $db->rollBack();
            if (file_exists($filePath)) unlink($filePath);
            $_SESSION['flash_error'] = 'Gagal: ' . $e->getMessage();
            redirect('/student/upload');
        }
    }

    public static function history(): void
    {
        Auth::requireRole('student');
        
        $userId = Auth::id();
        $db = Database::connection();
        
        $stmt = $db->prepare('SELECT id FROM student_profiles WHERE user_id = ?');
        $stmt->execute([$userId]);
        $student = $stmt->fetch();
        
        if (!$student) {
            redirect('/student/dashboard');
        }
        
        $stmt = $db->prepare('
            SELECT dv.*, d.academic_work_id, dt.name as type_name,
                   aw.title as work_title,
                   r.review_status, r.general_note, r.reviewed_at
            FROM document_versions dv
            JOIN documents d ON dv.document_id = d.id
            JOIN document_types dt ON d.document_type_id = dt.id
            JOIN academic_works aw ON d.academic_work_id = aw.id
            LEFT JOIN reviews r ON dv.id = r.document_version_id
            WHERE aw.student_id = ?
            ORDER BY dv.uploaded_at DESC
        ');
        $stmt->execute([$student['id']]);
        $history = $stmt->fetchAll();
        
        require dirname(__DIR__) . '/Views/student/history.php';
    }
}