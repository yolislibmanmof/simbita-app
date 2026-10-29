<?php
declare(strict_types=1);

class PlagiarismCheck
{
    public static function record(array $d): int
    {
        $db = Database::connection();
        $stmt = $db->prepare('INSERT INTO plagiarism_checks (document_version_id, similarity_score, provider, report_url, checked_by) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$d['document_version_id'], $d['similarity_score'], $d['provider'], $d['report_url'] ?? null, $d['checked_by']]);
        return (int)$db->lastInsertId();
    }

    public static function latestForWork(int $workId): ?array
    {
        $db = Database::connection();
        $stmt = $db->prepare('
            SELECT pc.*, dv.version_number, dv.original_filename, u.name AS checker_name
            FROM plagiarism_checks pc
            JOIN document_versions dv ON dv.id = pc.document_version_id
            JOIN documents d ON d.id = dv.document_id
            JOIN users u ON u.id = pc.checked_by
            WHERE d.academic_work_id = ?
            ORDER BY pc.id DESC LIMIT 1
        ');
        $stmt->execute([$workId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function statusForWork(int $workId, float $threshold): string
    {
        $latest = self::latestForWork($workId);
        if (!$latest) return 'none';
        return ((float)$latest['similarity_score'] <= $threshold) ? 'passed' : 'failed';
    }

    public static function listWorks(): array
    {
        $db = Database::connection();
        return $db->query('
            SELECT aw.id, aw.title, u.name AS student_name, sp.student_number,
                   dv.id AS version_id, dv.version_number, dv.original_filename
            FROM academic_works aw
            JOIN student_profiles sp ON sp.id = aw.student_id
            JOIN users u ON u.id = sp.user_id
            JOIN documents d ON d.academic_work_id = aw.id
            JOIN document_versions dv ON dv.document_id = d.id AND dv.is_current = 1
            WHERE aw.status NOT IN (\'completed\', \'cancelled\')
            ORDER BY u.name
        ')->fetchAll();
    }
}