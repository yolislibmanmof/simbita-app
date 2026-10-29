<?php
declare(strict_types=1);

class Faculty
{
    public static function all(): array
    {
        $db = Database::connection();
        return $db->query('SELECT * FROM faculties ORDER BY name')->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $db = Database::connection();
        $stmt = $db->prepare('SELECT * FROM faculties WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(string $code, string $name): int
    {
        $db = Database::connection();
        $stmt = $db->prepare('INSERT INTO faculties (code, name) VALUES (?, ?)');
        $stmt->execute([$code, $name]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, string $code, string $name): bool
    {
        $db = Database::connection();
        $stmt = $db->prepare('UPDATE faculties SET code = ?, name = ? WHERE id = ?');
        return $stmt->execute([$code, $name, $id]);
    }

    public static function delete(int $id): bool
    {
        $db = Database::connection();
        $stmt = $db->prepare('DELETE FROM faculties WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function countStudyPrograms(int $facultyId): int
    {
        $db = Database::connection();
        $stmt = $db->prepare('SELECT COUNT(*) FROM study_programs WHERE faculty_id = ?');
        $stmt->execute([$facultyId]);
        return (int) $stmt->fetchColumn();
    }
}