<?php
declare(strict_types=1);

class DirectMessage
{
    public static function send(int $workId, int $senderId, int $receiverId, string $text): int
    {
        $db = Database::connection();
        $stmt = $db->prepare('INSERT INTO direct_messages (academic_work_id, sender_id, receiver_id, message) VALUES (?, ?, ?, ?)');
        $stmt->execute([$workId, $senderId, $receiverId, $text]);
        return (int)$db->lastInsertId();
    }

    public static function partnersForUser(int $userId, string $role): array
    {
        $db = Database::connection();
        
        if ($role === 'student') {
            $sql = 'SELECT aw.id AS work_id, aw.title, u.id AS partner_id, u.name AS partner_name, lp.academic_degree AS partner_meta
                    FROM academic_works aw
                    JOIN student_profiles sp ON sp.id = aw.student_id
                    JOIN supervision_assignments sa ON sa.academic_work_id = aw.id AND sa.is_active = 1
                    JOIN lecturer_profiles lp ON lp.id = sa.lecturer_id
                    JOIN users u ON u.id = lp.user_id
                    WHERE sp.user_id = ? ORDER BY u.name';
        } else {
            $sql = 'SELECT aw.id AS work_id, aw.title, u.id AS partner_id, u.name AS partner_name, sp.student_number AS partner_meta
                    FROM supervision_assignments sa
                    JOIN academic_works aw ON aw.id = sa.academic_work_id
                    JOIN student_profiles sp ON sp.id = aw.student_id
                    JOIN users u ON u.id = sp.user_id
                    JOIN lecturer_profiles lp ON lp.id = sa.lecturer_id
                    WHERE lp.user_id = ? AND sa.is_active = 1 ORDER BY u.name';
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll();

        // Hitung pesan belum dibaca untuk setiap partner secara akurat
        foreach ($rows as &$r) {
            $st = $db->prepare('SELECT COUNT(*) FROM direct_messages WHERE academic_work_id = ? AND sender_id = ? AND receiver_id = ? AND is_read = 0');
            $st->execute([$r['work_id'], $r['partner_id'], $userId]);
            $r['unread'] = (int)$st->fetchColumn();
        }
        unset($r); // Lepaskan referensi array

        return $rows;
    }

    public static function thread(int $workId, int $me, int $partner): array
    {
        $db = Database::connection();
        $stmt = $db->prepare('
            SELECT dm.*, u.name AS sender_name FROM direct_messages dm
            JOIN users u ON u.id = dm.sender_id
            WHERE dm.academic_work_id = ? AND ((dm.sender_id = ? AND dm.receiver_id = ?) OR (dm.sender_id = ? AND dm.receiver_id = ?))
            ORDER BY dm.id ASC LIMIT 500
        ');
        $stmt->execute([$workId, $me, $partner, $partner, $me]);
        return $stmt->fetchAll();
    }

    public static function since(int $workId, int $partner, int $me, int $afterId): array
    {
        $db = Database::connection();
        $stmt = $db->prepare('SELECT dm.*, u.name AS sender_name FROM direct_messages dm JOIN users u ON u.id = dm.sender_id WHERE dm.academic_work_id = ? AND dm.sender_id = ? AND dm.receiver_id = ? AND dm.id > ? ORDER BY dm.id ASC');
        $stmt->execute([$workId, $partner, $me, $afterId]);
        return $stmt->fetchAll();
    }

    public static function markRead(int $workId, int $fromUserId, int $me): void
    {
        $db = Database::connection();
        $stmt = $db->prepare('UPDATE direct_messages SET is_read = 1 WHERE academic_work_id = ? AND sender_id = ? AND receiver_id = ? AND is_read = 0');
        $stmt->execute([$workId, $fromUserId, $me]);
    }

    public static function countUnread(int $userId): int
    {
        $db = Database::connection();
        $stmt = $db->prepare('SELECT COUNT(*) FROM direct_messages WHERE receiver_id = ? AND is_read = 0');
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }
}