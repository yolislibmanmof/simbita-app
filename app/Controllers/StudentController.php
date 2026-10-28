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
            $err = $_FILES['document']['error'] ?? 'missing';
            $msgs = [
                UPLOAD_ERR_INI_SIZE   => 'File melebihi batas upload_max_filesize di php.ini. Perkecil ukuran file.',
                UPLOAD_ERR_FORM_SIZE  => 'File melebihi batas ukuran form.',
                UPLOAD_ERR_PARTIAL    => 'File hanya terunggah sebagian. Coba ulang.',
                UPLOAD_ERR_NO_FILE    => 'Tidak ada file yang dipilih.',
                UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary server tidak ditemukan.',
                UPLOAD_ERR_CANT_WRITE => 'Server gagal menulis file ke disk.',
            ];
            $_SESSION['flash_error'] = $msgs[$err] ?? 'Gagal mengunggah file (kode error: ' . $err . ').';
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

        chmod($filePath, 0640); // Permission ketat

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

// Hitung jumlah halaman PDF dengan cara yang lebih aman
$pageCount = 1;
if ($mimeType === 'application/pdf' && file_exists($filePath)) {
    $handle = fopen($filePath, 'rb');
    if ($handle) {
        $content = fread($handle, 1048576); // Baca 1MB saja
        fclose($handle);
        preg_match_all('/\/Type\s*\/Page\b/', $content, $matches);
        $pageCount = count($matches[0]) > 0 ? count($matches[0]) : 1;
    }
}

            // Simpan metadata versi baru (dengan page_count)
            $stmt = $db->prepare('
                INSERT INTO document_versions 
                (document_id, version_number, original_filename, stored_filename, 
                 file_path, file_mime, file_size_bytes, page_count, student_note, uploaded_by, uploaded_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ');
            $stmt->execute([
                $documentId, $nextVersion, $file['name'], $storedFilename,
                $filePath, $mimeType, $file['size'], $pageCount, $note, $userId
            ]);

            $versionId = (int)$db->lastInsertId();

            // Ambil dosen pembimbing aktif & buat review PENDING per dosen
            $stmt = $db->prepare('
                SELECT lp.user_id FROM supervision_assignments sa
                JOIN lecturer_profiles lp ON sa.lecturer_id = lp.id
                WHERE sa.academic_work_id = ? AND sa.is_active = 1
            ');
            $stmt->execute([$work['id']]);
            $supervisors = $stmt->fetchAll();

            $supervisorUserIds = [];
            foreach ($supervisors as $sup) {
                $supervisorUserIds[] = (int)$sup['user_id'];

                // Buat review PENDING per dosen (reviewer_id WAJIB diisi)
                $stmtReview = $db->prepare('
                    INSERT INTO reviews (document_version_id, reviewer_id, review_status, created_at)
                    VALUES (?, ?, ?, NOW())
                ');
                $stmtReview->execute([$versionId, (int)$sup['user_id'], 'pending']);
            }

            // ===== TINDAK LANJUT REVISI: Carry-over komentar belum terselesaikan =====
            if ($nextVersion > 1 && !empty($supervisorUserIds)) {
                // Ambil komentar unresolved dari versi sebelumnya
                $stmtOldComments = $db->prepare('
                    SELECT rc.id, rc.page_number, rc.position_x, rc.position_y, 
                           rc.comment_text, rc.priority,
                           r.reviewer_id
                    FROM review_comments rc
                    JOIN reviews r ON rc.review_id = r.id
                    JOIN document_versions dv ON r.document_version_id = dv.id
                    WHERE dv.document_id = ? 
                      AND dv.version_number = ?
                      AND rc.is_resolved = 0
                ');
                $stmtOldComments->execute([$documentId, $nextVersion - 1]);
                $oldUnresolved = $stmtOldComments->fetchAll();

                if (!empty($oldUnresolved)) {
                    // Ambil review PENDING milik dosen untuk versi baru ini
                    $placeholders = implode(',', array_fill(0, count($supervisorUserIds), '?'));
                    $stmtNewReviews = $db->prepare("
                        SELECT r.id, r.reviewer_id 
                        FROM reviews r 
                        WHERE r.document_version_id = ? 
                          AND r.reviewer_id IN ($placeholders)
                          AND r.review_status = 'pending'
                    ");
                    $stmtNewReviews->execute(array_merge([$versionId], $supervisorUserIds));
                    
                    $newReviewsByReviewer = [];
                    foreach ($stmtNewReviews->fetchAll() as $nr) {
                        $newReviewsByReviewer[(int)$nr['reviewer_id']] = (int)$nr['id'];
                    }

                    // Duplikasi komentar unresolved ke review versi baru (carry-over)
                    $stmtCopy = $db->prepare('
                        INSERT INTO review_comments 
                        (review_id, document_version_id, page_number, position_x, position_y, 
                         comment_text, priority, is_resolved, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW())
                    ');
                    foreach ($oldUnresolved as $old) {
                        $targetReviewId = $newReviewsByReviewer[(int)$old['reviewer_id']] ?? null;
                        if ($targetReviewId) {
                            $stmtCopy->execute([
                                $targetReviewId, $versionId,
                                $old['page_number'], $old['position_x'], $old['position_y'],
                                '[CARRY-OVER] ' . $old['comment_text'],
                                $old['priority']
                            ]);
                        }
                    }
                }
            }
            // =================================================================

            // Notifikasi ke SEMUA dosen pembimbing aktif
            foreach ($supervisors as $sup) {
                Notification::create(
                    (int)$sup['user_id'],
                    'Dokumen Baru Diupload',
                    sprintf('%s mengunggah %s (Versi %d)', Auth::user()['name'] ?? 'Mahasiswa', $file['name'], $nextVersion),
                    'system',
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
            if (file_exists($filePath)) unlink($filePath);
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
        
        // 2. Ambil data anotasi + flag carry-over
        $annotationsMap = [];
        $versionStats = [];
        
        if (!empty($history)) {
            $versionIds = array_column($history, 'id');
            $placeholders = implode(',', array_fill(0, count($versionIds), '?'));
            
            $stmt = $db->prepare("
                SELECT rc.*, r.document_version_id, u.name AS author_name,
                       (rc.comment_text LIKE '[CARRY-OVER]%') AS is_carried_over
                FROM review_comments rc
                JOIN reviews r ON rc.review_id = r.id
                JOIN users u ON r.reviewer_id = u.id
                WHERE r.document_version_id IN ($placeholders)
                ORDER BY rc.page_number ASC, rc.created_at ASC
            ");
            $stmt->execute($versionIds);
            
            foreach ($stmt->fetchAll() as $ann) {
                $annotationsMap[$ann['document_version_id']][] = $ann;
            }

            // 3. Hitung statistik per versi: total, resolved, unresolved, percent
            foreach ($history as $h) {
                $vid = (int)$h['id'];
                $anns = $annotationsMap[$vid] ?? [];
                $total = count($anns);
                $resolved = count(array_filter($anns, fn($a) => !empty($a['is_resolved'])));
                $versionStats[$vid] = [
                    'total' => $total,
                    'resolved' => $resolved,
                    'unresolved' => $total - $resolved,
                    'percent' => $total > 0 ? (int)round(($resolved / $total) * 100) : 0,
                ];
            }
        }
        
        // 4. Kirim SEMUA variabel ke view
        require dirname(__DIR__) . '/Views/student/history.php';
    }
}