<?php
declare(strict_types=1);

class ReportController
{
    public static function exportWork(): void
    {
        Auth::requireRole('lecturer', 'student', 'admin');
        $workId = (int)($_GET['id'] ?? 0);
        $db = Database::connection();

        $stmt = $db->prepare('
            SELECT aw.*, u.name AS student_name, sp.student_number, sp.study_program, sp.faculty,
                   wt.name AS work_type_name
            FROM academic_works aw
            JOIN student_profiles sp ON aw.student_id = sp.id
            JOIN users u ON sp.user_id = u.id
            JOIN work_types wt ON aw.work_type_id = wt.id
            WHERE aw.id = ?
        ');
        $stmt->execute([$workId]);
        $work = $stmt->fetch();
        if (!$work) { $_SESSION['flash_error'] = 'TA tidak ditemukan.'; redirect('/lecturer/work'); }

        if (Auth::role() === 'student') {
            $stmt = $db->prepare('SELECT 1 FROM academic_works aw JOIN student_profiles sp ON aw.student_id = sp.id WHERE aw.id = ? AND sp.user_id = ?');
            $stmt->execute([$workId, Auth::id()]);
            if (!$stmt->fetch()) { redirect('/student/work'); }
        } elseif (Auth::role() === 'lecturer') {
            $stmt = $db->prepare('SELECT 1 FROM supervision_assignments sa JOIN lecturer_profiles lp ON sa.lecturer_id = lp.id WHERE sa.academic_work_id = ? AND lp.user_id = ? AND sa.is_active = 1');
            $stmt->execute([$workId, Auth::id()]);
            if (!$stmt->fetch()) { redirect('/lecturer/work'); }
        }

        $logs = BimbinganLog::getByWork($workId);
        $stmt = $db->prepare('SELECT * FROM work_milestones WHERE academic_work_id = ? ORDER BY due_date ASC');
        $stmt->execute([$workId]); $milestones = $stmt->fetchAll();
        $stmt = $db->prepare('
            SELECT d.title, dt.name AS type_name, dv.version_number, dv.original_filename, dv.uploaded_at, r.review_status
            FROM documents d JOIN document_types dt ON d.document_type_id = dt.id
            LEFT JOIN document_versions dv ON d.id = dv.document_id AND dv.is_current = 1
            LEFT JOIN reviews r ON dv.id = r.document_version_id
            WHERE d.academic_work_id = ? ORDER BY dt.sort_order');
        $stmt->execute([$workId]); $documents = $stmt->fetchAll();
        $stmt = $db->prepare('SELECT u.name, lp.academic_degree, sa.assignment_role FROM supervision_assignments sa JOIN lecturer_profiles lp ON sa.lecturer_id = lp.id JOIN users u ON lp.user_id = u.id WHERE sa.academic_work_id = ? AND sa.is_active = 1');
        $stmt->execute([$workId]); $supervisors = $stmt->fetchAll();

        require dirname(__DIR__) . '/Views/lecturer/work_export.php';
    }
}