<?php
declare(strict_types=1);

class Repository
{
    public static function publish(int $workId): bool
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare("
                INSERT INTO ta_repository (academic_work_id, published_at, is_public)
                VALUES (?, NOW(), 1)
                ON DUPLICATE KEY UPDATE is_public = 1, published_at = NOW()
            ");
            return $stmt->execute([$workId]);
        } catch (Throwable $e) { return false; }
    }

    public static function search(string $q = '', array $filters = [], int $page = 1, int $perPage = 12): array
    {
        try {
            $db = Database::connection();
            $where = ['aw.status = ?', 'tr.is_public = 1'];
            $params = ['completed'];

            if ($q !== '') {
                $where[] = '(aw.title LIKE ? OR aw.abstract LIKE ? OR aw.keywords LIKE ? OR u.name LIKE ?)';
                $s = '%'.$q.'%'; $params[] = $s; $params[] = $s; $params[] = $s; $params[] = $s;
            }
            if (!empty($filters['work_type_id'])) { $where[] = 'aw.work_type_id = ?'; $params[] = (int)$filters['work_type_id']; }
            if (!empty($filters['study_program'])) { $where[] = 'sp.study_program = ?'; $params[] = $filters['study_program']; }
            if (!empty($filters['year'])) { $where[] = 'YEAR(tr.published_at) = ?'; $params[] = (int)$filters['year']; }

            $w = 'WHERE '.implode(' AND ', $where);
            $countStmt = $db->prepare("SELECT COUNT(*) t FROM academic_works aw JOIN student_profiles sp ON aw.student_id = sp.id JOIN users u ON sp.user_id = u.id JOIN ta_repository tr ON tr.academic_work_id = aw.id $w");
            $countStmt->execute($params);
            $total = (int)$countStmt->fetch()['t'];

            $offset = max(0, ($page - 1) * $perPage);
            $dataStmt = $db->prepare("
                SELECT aw.*, sp.study_program, sp.faculty, sp.enrollment_year, u.name AS student_name,
                    wt.name AS work_type_name, tr.published_at, tr.download_count
                FROM academic_works aw
                JOIN student_profiles sp ON aw.student_id = sp.id
                JOIN users u ON sp.user_id = u.id
                JOIN work_types wt ON aw.work_type_id = wt.id
                JOIN ta_repository tr ON tr.academic_work_id = aw.id
                $w
                ORDER BY tr.published_at DESC
                LIMIT $perPage OFFSET $offset
            ");
            $dataStmt->execute($params);

            return ['data' => $dataStmt->fetchAll(), 'total' => $total, 'page' => $page, 'per_page' => $perPage, 'total_pages' => (int)ceil($total/$perPage)];
        } catch (Throwable $e) { error_log('[Repo::search] '.$e->getMessage()); return ['data' => [], 'total' => 0, 'page' => $page, 'per_page' => $perPage, 'total_pages' => 0]; }
    }

    public static function incrementDownload(int $workId): void
    {
        try { Database::connection()->prepare('UPDATE ta_repository SET download_count = download_count + 1 WHERE academic_work_id = ?')->execute([$workId]); }
        catch (Throwable $e) {}
    }

    public static function isPublished(int $workId): bool
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('SELECT 1 FROM ta_repository WHERE academic_work_id = ? AND is_public = 1');
            $stmt->execute([$workId]);
            return (bool)$stmt->fetch();
        } catch (Throwable $e) { return false; }
    }
}