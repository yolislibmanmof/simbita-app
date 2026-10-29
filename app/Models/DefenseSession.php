<?php
declare(strict_types=1);

class DefenseSession
{
    public static function create(array $d): int
    {
        $db = Database::connection();
        $stmt = $db->prepare('INSERT INTO defense_sessions (academic_work_id, session_type, scheduled_date, room, online_link, status) VALUES (?, ?, ?, ?, ?, \'scheduled\')');
        $stmt->execute([$d['academic_work_id'], $d['session_type'], $d['scheduled_date'], $d['room'] ?? null, $d['online_link'] ?? null]);
        return (int)$db->lastInsertId();
    }

    public static function all(): array
    {
        $db = Database::connection();
        return $db->query('
            SELECT ds.*, aw.title, u.name AS student_name, sp.student_number
            FROM defense_sessions ds
            JOIN academic_works aw ON aw.id = ds.academic_work_id
            JOIN student_profiles sp ON sp.id = aw.student_id
            JOIN users u ON u.id = sp.user_id
            ORDER BY ds.scheduled_date DESC
        ')->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $db = Database::connection();
        $stmt = $db->prepare('SELECT * FROM defense_sessions WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function assignExaminer(int $sessionId, int $lecturerProfileId, string $role): void
    {
        $db = Database::connection();
        $stmt = $db->prepare('INSERT INTO examiner_assignments (defense_session_id, lecturer_id, role) VALUES (?, ?, ?)');
        $stmt->execute([$sessionId, $lecturerProfileId, $role]);
    }

    public static function examiners(int $sessionId): array
    {
        $db = Database::connection();
        $stmt = $db->prepare('
            SELECT ea.*, u.name AS lecturer_name, lp.academic_degree
            FROM examiner_assignments ea
            JOIN lecturer_profiles lp ON lp.id = ea.lecturer_id
            JOIN users u ON u.id = lp.user_id
            WHERE ea.defense_session_id = ?
            ORDER BY FIELD(ea.role, \'chair\', \'examiner_1\', \'examiner_2\', \'secretary\')
        ');
        $stmt->execute([$sessionId]);
        return $stmt->fetchAll();
    }

    public static function assignmentsForLecturer(int $userId): array
    {
        $db = Database::connection();
        $stmt = $db->prepare('
            SELECT ea.*, ds.session_type, ds.scheduled_date, ds.status AS session_status, ds.room, ds.online_link,
                   aw.title, u.name AS student_name
            FROM examiner_assignments ea
            JOIN defense_sessions ds ON ds.id = ea.defense_session_id
            JOIN academic_works aw ON aw.id = ds.academic_work_id
            JOIN student_profiles sp ON sp.id = aw.student_id
            JOIN users u ON u.id = sp.user_id
            JOIN lecturer_profiles lp ON lp.id = ea.lecturer_id
            WHERE lp.user_id = ?
            ORDER BY ds.scheduled_date DESC
        ');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function saveGrade(int $sessionId, int $lecturerUserId, float $grade, string $notes): void
    {
        $db = Database::connection();
        $stmt = $db->prepare('
            UPDATE examiner_assignments ea
            JOIN lecturer_profiles lp ON lp.id = ea.lecturer_id
            SET ea.grade = ?, ea.notes = ?
            WHERE ea.defense_session_id = ? AND lp.user_id = ?
        ');
        $stmt->execute([$grade, $notes, $sessionId, $lecturerUserId]);
    }

    public static function gradesComplete(int $sessionId): bool
    {
        $db = Database::connection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM examiner_assignments WHERE defense_session_id = ? AND role IN ('chair','examiner_1','examiner_2') AND grade IS NULL");
        $stmt->execute([$sessionId]);
        return ((int)$stmt->fetchColumn()) === 0;
    }

    public static function finalGrade(int $sessionId): ?float
    {
        $db = Database::connection();
        $stmt = $db->prepare("SELECT AVG(grade) FROM examiner_assignments WHERE defense_session_id = ? AND grade IS NOT NULL AND role IN ('chair','examiner_1','examiner_2')");
        $stmt->execute([$sessionId]);
        $val = $stmt->fetchColumn();
        return $val !== null ? round((float)$val, 2) : null;
    }

    public static function complete(int $sessionId, float $finalGrade): void
    {
        $db = Database::connection();
        $db->beginTransaction();
        try {
            $db->prepare("UPDATE defense_sessions SET status = 'completed' WHERE id = ?")->execute([$sessionId]);
            $db->prepare("UPDATE academic_works SET status = 'completed', completed_at = CURDATE() WHERE id = (SELECT academic_work_id FROM defense_sessions WHERE id = ?)")->execute([$sessionId]);
            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }
}