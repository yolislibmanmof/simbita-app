<?php
declare(strict_types=1);

class LecturerController
{
    public static function students(): void
    {
        Auth::requireRole('lecturer');
        
        $userId = Auth::id();
        $db = Database::connection();
        
        $stmt = $db->prepare('SELECT id FROM lecturer_profiles WHERE user_id = ?');
        $stmt->execute([$userId]);
        $lecturer = $stmt->fetch();
        
        if (!$lecturer) {
            redirect('/lecturer/dashboard');
        }
        
        $students = AcademicWork::getByLecturerId((int)$lecturer['id']);
        
        require dirname(__DIR__) . '/Views/lecturer/students.php';
    }

    public static function review(): void
    {
        Auth::requireRole('lecturer');
        
        $workId = (int)($_GET['id'] ?? 0);
        if (!$workId) {
            redirect('/lecturer/dashboard');
        }

        $work = AcademicWork::getWithSupervisors($workId);
        if (!$work) {
            $_SESSION['flash_error'] = 'Tugas akhir tidak ditemukan';
            redirect('/lecturer/dashboard');
        }

        $db = Database::connection();
        
        // 1. Ambil dokumen versi saat ini (untuk PDF Viewer & Anotasi)
        $stmt = $db->prepare('
            SELECT d.*, dt.name as type_name, dt.code as type_code,
                   dv.id as version_id, dv.version_number, dv.original_filename,
                   dv.file_path, dv.student_note, dv.uploaded_at
            FROM documents d
            JOIN document_types dt ON d.document_type_id = dt.id
            LEFT JOIN document_versions dv ON d.id = dv.document_id AND dv.is_current = 1
            WHERE d.academic_work_id = ?
            ORDER BY dt.sort_order
        ');
        $stmt->execute([$workId]);
        $documents = $stmt->fetchAll();

        // 2. Inisialisasi variabel Checklist & Results (Wajib ada agar view tidak error)
        $currentDoc = !empty($documents) ? $documents[0] : null;
        $checklistItems = [];
        $checklistResults = [];
        $reviewId = null;

        if ($currentDoc) {
            // Ambil checklist items berdasarkan jenis dokumen
            $stmt = $db->prepare('
                SELECT * FROM checklist_items 
                WHERE document_type_id = ? AND is_active = 1 
                ORDER BY item_order ASC
            ');
            $stmt->execute([$currentDoc['document_type_id']]);
            $checklistItems = $stmt->fetchAll();

            // Cek apakah sudah ada review aktif untuk versi dokumen ini
            $stmt = $db->prepare('
                SELECT id FROM reviews 
                WHERE document_version_id = ? AND reviewer_id = ? 
                ORDER BY created_at DESC LIMIT 1
            ');
            $stmt->execute([$currentDoc['version_id'], Auth::id()]);
            $activeReview = $stmt->fetch();

            if ($activeReview) {
                $reviewId = (int)$activeReview['id'];
                
                // Ambil hasil checklist yang sudah disimpan sebelumnya
                $stmt = $db->prepare('
                    SELECT checklist_item_id, result, note 
                    FROM review_checklist_results 
                    WHERE review_id = ?
                ');
                $stmt->execute([$reviewId]);
                foreach ($stmt->fetchAll() as $res) {
                    $checklistResults[$res['checklist_item_id']] = $res;
                }
            }
        }

        // Kirim semua variabel ke view
        require dirname(__DIR__) . '/Views/lecturer/review.php';
    }

    public static function reviews(): void
    {
        Auth::requireRole('lecturer');
        
        $userId = Auth::id();
        $db = Database::connection();
        
        $stmt = $db->prepare('
            SELECT r.*, dv.original_filename, dv.version_number,
                   aw.title as work_title, aw.id as work_id,
                   u.name as student_name, d.academic_work_id
            FROM reviews r
            JOIN document_versions dv ON r.document_version_id = dv.id
            JOIN documents d ON dv.document_id = d.id
            JOIN academic_works aw ON d.academic_work_id = aw.id
            JOIN student_profiles sp ON aw.student_id = sp.id
            JOIN users u ON sp.user_id = u.id
            WHERE r.reviewer_id = ?
            ORDER BY r.created_at DESC
        ');
        $stmt->execute([$userId]);
        $reviews = $stmt->fetchAll();
        
        require dirname(__DIR__) . '/Views/lecturer/reviews.php';
    }

    public static function history(): void
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
            ORDER BY r.reviewed_at DESC
            LIMIT 50
        ');
        $stmt->execute([$userId]);
        $history = $stmt->fetchAll();

        require dirname(__DIR__) . '/Views/lecturer/history.php';
    }
}