<?php
declare(strict_types=1);

class BimbinganLog
{
    public static function create(array $d): int
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare("
                INSERT INTO bimbingan_logs
                    (academic_work_id, meeting_date, topic, discussion_points, action_items, student_notes, lecturer_notes, is_attended, created_by_user_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                (int)$d['academic_work_id'],
                $d['meeting_date'],
                mb_substr(trim($d['topic'] ?? ''), 0, 255),
                trim($d['discussion_points'] ?? '') ?: null,
                trim($d['action_items'] ?? '') ?: null,
                trim($d['student_notes'] ?? '') ?: null,
                trim($d['lecturer_notes'] ?? '') ?: null,
                (int)($d['is_attended'] ?? 1),
                (int)$d['created_by_user_id'],
            ]);
            return (int)$db->lastInsertId();
        } catch (Throwable $e) { error_log('[BL::create] '.$e->getMessage()); return 0; }
    }

    public static function getByWork(int $workId): array
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare("
                SELECT bl.*, u.name AS author_name, u.role AS author_role
                FROM bimbingan_logs bl
                JOIN users u ON bl.created_by_user_id = u.id
                WHERE bl.academic_work_id = ?
                ORDER BY bl.meeting_date DESC, bl.created_at DESC
            ");
            $stmt->execute([$workId]);
            return $stmt->fetchAll();
        } catch (Throwable $e) { return []; }
    }

    public static function countByWork(int $workId): int
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('SELECT COUNT(*) AS total FROM bimbingan_logs WHERE academic_work_id = ?');
            $stmt->execute([$workId]);
            return (int)$stmt->fetch()['total'];
        } catch (Throwable $e) { return 0; }
    }

    public static function findById(int $id): ?array
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('SELECT * FROM bimbingan_logs WHERE id = ?');
            $stmt->execute([$id]);
            $r = $stmt->fetch(); return $r ?: null;
        } catch (Throwable $e) { return null; }
    }
}