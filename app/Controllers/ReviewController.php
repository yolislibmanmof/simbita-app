<?php
declare(strict_types=1);

class ReviewController
{
    /**
     * Tampilkan halaman review
     */
    public static function showReview(): void
    {
        Auth::requireRole('lecturer');
        
        $academicWorkId = (int)($_GET['id'] ?? 0);
        
        if (!$academicWorkId) {
            redirect('/lecturer/dashboard');
        }

        $work = AcademicWork::getWithSupervisors($academicWorkId);
        
        if (!$work) {
            $_SESSION['flash_error'] = 'Tugas akhir tidak ditemukan';
            redirect('/lecturer/dashboard');
        }

        // Ambil dokumen
        $db = Database::connection();
        $stmt = $db->prepare('
            SELECT d.*, dt.name as type_name, dt.code as type_code,
                   dv.id as version_id, dv.version_number, dv.original_filename,
                   dv.file_path, dv.student_note
            FROM documents d
            JOIN document_types dt ON d.document_type_id = dt.id
            LEFT JOIN document_versions dv ON d.id = dv.document_id AND dv.is_current = 1
            WHERE d.academic_work_id = ?
            ORDER BY dt.sort_order
        ');
        $stmt->execute([$academicWorkId]);
        $documents = $stmt->fetchAll();

        require dirname(__DIR__) . '/Views/lecturer/review.php';
    }

    /**
     * Tampilkan history review
     */
    public static function showHistory(): void
    {
        Auth::requireRole('lecturer');
        
        $userId = Auth::id();
        
        $db = Database::connection();
        $stmt = $db->prepare('
            SELECT r.*, dv.original_filename, aw.title as work_title,
                   u.name as student_name, r.created_at
            FROM reviews r
            JOIN document_versions dv ON r.document_version_id = dv.id
            JOIN documents d ON dv.document_id = d.id
            JOIN academic_works aw ON d.academic_work_id = aw.id
            JOIN student_profiles sp ON aw.student_id = sp.id
            JOIN users u ON sp.user_id = u.id
            WHERE r.reviewer_id = ?
            ORDER BY r.created_at DESC
            LIMIT 50
        ');
        $stmt->execute([$userId]);
        $history = $stmt->fetchAll();

        require dirname(__DIR__) . '/Views/lecturer/history.php';
    }

    /**
     * Tambah komentar
     */
    public static function addComment(): void
    {
        Auth::requireRole('lecturer');
        
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($csrfToken)) {
            json_response(['error' => 'Token tidak valid'], 419);
        }

        $reviewId = (int)($_POST['review_id'] ?? 0);
        $pageNumber = (int)($_POST['page_number'] ?? 1);
        $positionX = (float)($_POST['position_x'] ?? 0);
        $positionY = (float)($_POST['position_y'] ?? 0);
        $commentText = trim($_POST['comment_text'] ?? '');
        $priority = $_POST['priority'] ?? 'important';

        if (!$reviewId || empty($commentText)) {
            json_response(['error' => 'Data tidak lengkap'], 400);
        }

        $db = Database::connection();
        $stmt = $db->prepare('
            INSERT INTO review_comments 
            (review_id, page_number, position_x, position_y, comment_text, priority)
            VALUES (?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $reviewId,
            $pageNumber,
            $positionX,
            $positionY,
            $commentText,
            $priority
        ]);

        json_response(['success' => true, 'message' => 'Komentar berhasil ditambahkan']);
    }

    /**
     * Simpan checklist review
     */
    public static function saveChecklist(): void
    {
        Auth::requireRole('lecturer');
        
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($csrfToken)) {
            json_response(['error' => 'Token tidak valid'], 419);
        }

        $reviewId = (int)($_POST['review_id'] ?? 0);
        $checklistData = $_POST['checklist'] ?? [];

        if (!$reviewId || empty($checklistData)) {
            json_response(['error' => 'Data tidak lengkap'], 400);
        }

        $db = Database::connection();
        $db->beginTransaction();

        try {
            foreach ($checklistData as $itemId => $result) {
                $stmt = $db->prepare('
                    INSERT INTO review_checklist_results 
                    (review_id, checklist_item_id, result, note)
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE result = ?, note = ?
                ');
                $stmt->execute([
                    $reviewId,
                    $itemId,
                    $result['result'],
                    $result['note'] ?? null,
                    $result['result'],
                    $result['note'] ?? null
                ]);
            }

            $db->commit();
            json_response(['success' => true, 'message' => 'Checklist berhasil disimpan']);
        } catch (Exception $e) {
            $db->rollBack();
            json_response(['error' => 'Gagal menyimpan checklist'], 500);
        }
    }

    /**
     * Keputusan review (approve/reject/needs revision)
     */
    public static function decide(): void
    {
        Auth::requireRole('lecturer');
        
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($csrfToken)) {
            json_response(['error' => 'Token tidak valid'], 419);
        }

        $reviewId = (int)($_POST['review_id'] ?? 0);
        $decision = $_POST['decision'] ?? ''; // approved, rejected, needs_revision
        $generalNote = trim($_POST['general_note'] ?? '');

        if (!$reviewId || !in_array($decision, ['approved', 'rejected', 'needs_revision'])) {
            json_response(['error' => 'Data tidak valid'], 400);
        }

        $db = Database::connection();
        
        // Update review status
        $stmt = $db->prepare('
            UPDATE reviews 
            SET review_status = ?, general_note = ?, reviewed_at = NOW()
            WHERE id = ?
        ');
        $stmt->execute([$decision, $generalNote, $reviewId]);

        // Ambil info dokumen untuk notifikasi
        $stmt = $db->prepare('
            SELECT dv.document_id, d.academic_work_id, aw.student_id
            FROM reviews r
            JOIN document_versions dv ON r.document_version_id = dv.id
            JOIN documents d ON dv.document_id = d.id
            JOIN academic_works aw ON d.academic_work_id = aw.id
            WHERE r.id = ?
        ');
        $stmt->execute([$reviewId]);
        $info = $stmt->fetch();

        if ($info) {
            // Buat notifikasi untuk mahasiswa
            $statusText = match($decision) {
                'approved' => 'disetujui',
                'rejected' => 'ditolak',
                'needs_revision' => 'perlu revisi',
            };

            Notification::create(
                (int)$info['student_id'],
                'Hasil Review Dokumen',
                sprintf('Dokumen Anda telah direview dan %s. Catatan: %s', $statusText, $generalNote),
                'review',
                'reviews',
                $reviewId,
                '/student/dashboard'
            );
        }

        json_response(['success' => true, 'message' => 'Keputusan berhasil disimpan']);
    }
}