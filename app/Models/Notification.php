<?php
declare(strict_types=1);

class Notification
{
    public static function create(
        int $userId,
        string $title,
        string $message,
        string $type = 'system',
        ?string $relatedTable = null,
        ?int $relatedId = null,
        ?string $link = null
    ): void {
        $db = Database::connection();

        $db->prepare(
            'INSERT INTO notifications
             (user_id, title, message, type, related_table, related_id, link)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $userId,
            $title,
            $message,
            $type,
            $relatedTable,
            $relatedId,
            $link,
        ]);
    }

    public static function createMany(array $userIds, string $title, string $message, string $type = 'system', ?string $link = null): void
    {
        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));

        if (!$userIds) {
            return;
        }

        $db = Database::connection();
        $stmt = $db->prepare(
            'INSERT INTO notifications
             (user_id, title, message, type, link)
             VALUES (?, ?, ?, ?, ?)'
        );

        foreach ($userIds as $userId) {
            $stmt->execute([$userId, $title, $message, $type, $link]);
        }
    }

    public static function latest(int $userId, int $limit = 10): array
    {
        $db = Database::connection();
        $stmt = $db->prepare(
            'SELECT id, title, message, type, link, is_read, created_at
             FROM notifications
             WHERE user_id = ?
             ORDER BY created_at DESC
             LIMIT ?'
        );
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function unreadCount(int $userId): int
    {
        $db = Database::connection();
        $stmt = $db->prepare(
            'SELECT COUNT(*) AS total FROM notifications
             WHERE user_id = ? AND is_read = 0'
        );
        $stmt->execute([$userId]);

        return (int) $stmt->fetch()['total'];
    }

    public static function markAsRead(int $userId, ?int $notificationId = null): void
    {
        $db = Database::connection();

        if ($notificationId) {
            $db->prepare(
                'UPDATE notifications
                 SET is_read = 1, read_at = NOW()
                 WHERE id = ? AND user_id = ?'
            )->execute([$notificationId, $userId]);

            return;
        }

        $db->prepare(
            'UPDATE notifications
             SET is_read = 1, read_at = NOW()
             WHERE user_id = ? AND is_read = 0'
        )->execute([$userId]);
    }
}