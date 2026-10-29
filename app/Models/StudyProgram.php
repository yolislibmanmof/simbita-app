<?php
declare(strict_types=1);

class StudyProgram
{
    public static function all(): array
    {
        $db = Database::connection();
        return $db->query('
            SELECT sp.*, f.name AS faculty_name
            FROM study_programs sp
            JOIN faculties f ON sp.faculty_id = f.id
            ORDER BY f.name, sp.name
        ')->fetchAll();
    }

    public static function findByFaculty(int $facultyId): array
    {
        $db = Database::connection();
        $stmt = $db->prepare('SELECT * FROM study_programs WHERE faculty_id = ? ORDER BY name');
        $stmt->execute([$facultyId]);
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $db = Database::connection();
        $stmt = $db->prepare('SELECT * FROM study_programs WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(int $facultyId, string $code, string $name, string $degreeLevel): int
    {
        $db = Database::connection();
        $stmt = $db->prepare('INSERT INTO study_programs (faculty_id, code, name, degree_level) VALUES (?, ?, ?, ?)');
        $stmt->execute([$facultyId, $code, $name, $degreeLevel]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, int $facultyId, string $code, string $name, string $degreeLevel): bool
    {
        $db = Database::connection();
        $stmt = $db->prepare('UPDATE study_programs SET faculty_id = ?, code = ?, name = ?, degree_level = ? WHERE id = ?');
        return $stmt->execute([$facultyId, $code, $name, $degreeLevel, $id]);
    }

    public static function delete(int $id): bool
    {
        $db = Database::connection();
        $stmt = $db->prepare('DELETE FROM study_programs WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function countStudents(int $prodiId): int
    {
        $db = Database::connection();
        $stmt = $db->prepare('SELECT COUNT(*) FROM student_profiles WHERE study_program_id = ?');
        $stmt->execute([$prodiId]);
        return (int) $stmt->fetchColumn();
    }

    public static function countLecturers(int $prodiId): int
    {
        $db = Database::connection();
        $stmt = $db->prepare('SELECT COUNT(*) FROM lecturer_profiles WHERE department_id = ?');
        $stmt->execute([$prodiId]);
        return (int) $stmt->fetchColumn();
    }
}