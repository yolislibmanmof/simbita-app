<?php
declare(strict_types=1);

class RepositoryController
{
    public static function index(): void
    {
        Auth::requireRole('admin', 'lecturer', 'student');
        $q = trim($_GET['q'] ?? '');
        $filters = [
            'work_type_id'  => $_GET['work_type_id'] ?? '',
            'study_program' => trim($_GET['study_program'] ?? ''),
            'year'          => $_GET['year'] ?? '',
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = Repository::search($q, $filters, $page, 12);

        $db = Database::connection();
        $workTypes = $db->query('SELECT id, name FROM work_types ORDER BY name')->fetchAll();
        $programs  = $db->query('SELECT DISTINCT study_program FROM student_profiles ORDER BY study_program')->fetchAll(PDO::FETCH_COLUMN);
        $years     = $db->query('SELECT DISTINCT YEAR(published_at) y FROM ta_repository WHERE published_at IS NOT NULL ORDER BY y DESC')->fetchAll(PDO::FETCH_COLUMN);

        require dirname(__DIR__) . '/Views/repository.php';
    }

    public static function download(): void
    {
        Auth::requireRole('admin', 'lecturer', 'student');
        $workId = (int)($_GET['id'] ?? 0);
        if (!Repository::isPublished($workId)) {
            $_SESSION['flash_error'] = 'Dokumen tidak tersedia di repository.';
            redirect('/repository');
        }
        $db = Database::connection();
        $stmt = $db->prepare('SELECT dv.* FROM documents d JOIN document_versions dv ON d.id = dv.document_id AND dv.is_current = 1 WHERE d.academic_work_id = ? LIMIT 1');
        $stmt->execute([$workId]);
        $v = $stmt->fetch();
        if (!$v || !file_exists($v['file_path'])) {
            $_SESSION['flash_error'] = 'File tidak ditemukan.';
            redirect('/repository');
        }
        Repository::incrementDownload($workId);
        header('Content-Type: ' . ($v['file_mime'] ?: 'application/pdf'));
        header('Content-Disposition: attachment; filename="' . basename($v['original_filename']) . '"');
        header('Content-Length: ' . filesize($v['file_path']));
        readfile($v['file_path']);
        exit;
    }

    public static function publish(): void
    {
        Auth::requireRole('lecturer', 'admin');
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) { redirect('/lecturer/work'); }
        $workId = (int)($_POST['academic_work_id'] ?? 0);
        $db = Database::connection();

        if (Auth::role() === 'lecturer') {
            $stmt = $db->prepare('SELECT 1 FROM supervision_assignments sa JOIN lecturer_profiles lp ON sa.lecturer_id = lp.id WHERE sa.academic_work_id = ? AND lp.user_id = ? AND sa.is_active = 1');
            $stmt->execute([$workId, Auth::id()]);
            if (!$stmt->fetch()) { $_SESSION['flash_error'] = 'Akses ditolak.'; redirect('/lecturer/work'); }
        }

        Repository::publish($workId);
        $_SESSION['flash_success'] = 'Tugas akhir dipublikasikan ke Repository.';
        redirect('/lecturer/work');
    }
}