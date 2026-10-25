<?php
declare(strict_types=1);

class DashboardController
{
    /**
     * Admin Dashboard
     */
    public static function admin(): void
    {
        Auth::requireRole('admin');

        $stats = [
            'total_students' => self::countTable('student_profiles'),
            'total_lecturers' => self::countTable('lecturer_profiles'),
            'active_works' => AcademicWork::countByStatus('writing'),
            'pending_reviews' => self::countTable('reviews', "review_status = 'pending'"),
        ];

        $recentWorks = AcademicWork::getAllWithDetails();
        $recentWorks = array_slice($recentWorks, 0, 10);

        require dirname(__DIR__) . '/Views/admin/dashboard.php';
    }

    /**
     * Lecturer Dashboard
     */
    public static function lecturer(): void
    {
        Auth::requireRole('lecturer');

        $userId = Auth::id();
        
        // Dapatkan lecturer_id dari user_id
        $db = Database::connection();
        $stmt = $db->prepare('SELECT id FROM lecturer_profiles WHERE user_id = ?');
        $stmt->execute([$userId]);
        $lecturer = $stmt->fetch();

        if (!$lecturer) {
            $_SESSION['flash_error'] = 'Profil dosen tidak ditemukan.';
            redirect('/logout');
        }

        $myStudents = AcademicWork::getByLecturerId((int)$lecturer['id']);
        $pendingReviews = self::getPendingReviewsForLecturer((int)$lecturer['id']);

        require dirname(__DIR__) . '/Views/lecturer/dashboard.php';
    }

    /**
     * Student Dashboard
     */
    public static function student(): void
    {
        Auth::requireRole('student');

        $userId = Auth::id();
        
        // Dapatkan student_id dari user_id
        $db = Database::connection();
        $stmt = $db->prepare('SELECT id FROM student_profiles WHERE user_id = ?');
        $stmt->execute([$userId]);
        $student = $stmt->fetch();

        if (!$student) {
            $_SESSION['flash_error'] = 'Profil mahasiswa tidak ditemukan.';
            redirect('/logout');
        }

        $myWork = AcademicWork::getByStudentId((int)$student['id']);
        $myDocuments = self::getDocumentsForWork($myWork['id'] ?? 0);
        $myMilestones = self::getMilestonesForWork($myWork['id'] ?? 0);

        require dirname(__DIR__) . '/Views/student/dashboard.php';
    }

    /**
     * Helper: Hitung rows di tabel
     */
    private static function countTable(string $table, string $where = '1=1'): int
    {
        $db = Database::connection();
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM {$table} WHERE {$where}");
        $stmt->execute();
        return (int)$stmt->fetch()['total'];
    }

    /**
     * Helper: Ambil pending reviews untuk lecturer
     */
    private static function getPendingReviewsForLecturer(int $lecturerId): array
    {
        $db = Database::connection();
        $stmt = $db->prepare('
            SELECT r.*, dv.original_filename, aw.title as work_title,
                   u.name as student_name
            FROM reviews r
            JOIN document_versions dv ON r.document_version_id = dv.id
            JOIN documents d ON dv.document_id = d.id
            JOIN academic_works aw ON d.academic_work_id = aw.id
            JOIN student_profiles sp ON aw.student_id = sp.id
            JOIN users u ON sp.user_id = u.id
            JOIN supervision_assignments sa ON aw.id = sa.academic_work_id
            WHERE sa.lecturer_id = ? AND r.review_status = \'pending\' AND sa.is_active = 1
            ORDER BY r.created_at DESC
            LIMIT 10
        ');
        $stmt->execute([$lecturerId]);
        return $stmt->fetchAll();
    }

    /**
     * Helper: Ambil documents untuk academic work
     */
    private static function getDocumentsForWork(int $workId): array
    {
        if (!$workId) return [];
        
        $db = Database::connection();
        $stmt = $db->prepare('
            SELECT d.*, dt.name as type_name, dt.code as type_code,
                   dv.version_number, dv.original_filename, dv.uploaded_at
            FROM documents d
            JOIN document_types dt ON d.document_type_id = dt.id
            LEFT JOIN document_versions dv ON d.id = dv.document_id AND dv.is_current = 1
            WHERE d.academic_work_id = ?
            ORDER BY dt.sort_order
        ');
        $stmt->execute([$workId]);
        return $stmt->fetchAll();
    }

    /**
     * Helper: Ambil milestones untuk academic work
     */
    private static function getMilestonesForWork(int $workId): array
    {
        if (!$workId) return [];
        
        $db = Database::connection();
        $stmt = $db->prepare('
            SELECT * FROM work_milestones
            WHERE academic_work_id = ?
            ORDER BY due_date ASC
        ');
        $stmt->execute([$workId]);
        return $stmt->fetchAll();
    }
}