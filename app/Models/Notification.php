<?php
declare(strict_types=1);

class Notification
{
    public const TYPE_SYSTEM         = 'system';
    public const TYPE_INFO           = 'info';
    public const TYPE_SUCCESS        = 'success';
    public const TYPE_WARNING        = 'warning';
    public const TYPE_DANGER         = 'danger';
    public const TYPE_REVIEW         = 'review';
    public const TYPE_UPLOAD         = 'upload';
    public const TYPE_DEADLINE       = 'deadline';
    public const TYPE_MILESTONE      = 'milestone';
    public const TYPE_COMMENT        = 'comment';
    public const TYPE_APPROVAL       = 'approval';
    public const TYPE_TITLE_PROPOSAL = 'title_proposals';
    public const TYPE_BIMBINGAN      = 'bimbingan_logs';
    public const TYPE_BOOKING        = 'bookings';
    public const TYPE_WORK           = 'academic_works';

    public const ALL_TYPES = [
        self::TYPE_SYSTEM, self::TYPE_INFO, self::TYPE_SUCCESS, self::TYPE_WARNING,
        self::TYPE_DANGER, self::TYPE_REVIEW, self::TYPE_UPLOAD, self::TYPE_DEADLINE,
        self::TYPE_MILESTONE, self::TYPE_COMMENT, self::TYPE_APPROVAL, self::TYPE_TITLE_PROPOSAL,
        self::TYPE_BIMBINGAN, self::TYPE_BOOKING, self::TYPE_WORK
    ];

    public const DEFAULT_LIMIT = 10;

    public static function create(int $userId, string $title, string $message, string $type = self::TYPE_SYSTEM, ?string $relatedTable = null, ?int $relatedId = null, ?string $link = null): int
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('INSERT INTO notifications (user_id, title, message, type, related_table, related_id, link, is_read, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW())');
            $stmt->execute([$userId, $title, $message, $type, $relatedTable, $relatedId, $link]);
            return (int) $db->lastInsertId();
        } catch (Throwable $e) {
            error_log('[Notification::create] ' . $e->getMessage());
            return 0;
        }
    }

    public static function latest(int $userId, int $limit = self::DEFAULT_LIMIT): array
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('SELECT id, title, message, type, link, related_table, related_id, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?');
            $stmt->bindValue(1, $userId, PDO::PARAM_INT);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            return [];
        }
    }

    public static function countUnread(int $userId): int
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('SELECT COUNT(*) AS total FROM notifications WHERE user_id = ? AND is_read = 0');
            $stmt->execute([$userId]);
            return (int) ($stmt->fetch()['total'] ?? 0);
        } catch (Throwable $e) {
            return 0;
        }
    }

    public static function markAsRead(int $userId, ?int $notificationId = null): int
    {
        try {
            $db = Database::connection();
            if ($notificationId !== null) {
                $stmt = $db->prepare('UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ? AND user_id = ? AND is_read = 0');
                $stmt->execute([$notificationId, $userId]);
            } else {
                $stmt = $db->prepare('UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0');
                $stmt->execute([$userId]);
            }
            return $stmt->rowCount();
        } catch (Throwable $e) {
            return 0;
        }
    }

    public static function getAll(int $userId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        try {
            $db = Database::connection();
            $where = ['user_id = ?'];
            $params = [$userId];

            if (!empty($filters['type']) && in_array($filters['type'], self::ALL_TYPES, true)) {
                $where[] = 'type = ?';
                $params[] = $filters['type'];
            }
            if (isset($filters['is_read']) && $filters['is_read'] !== '') {
                $where[] = 'is_read = ?';
                $params[] = (int) $filters['is_read'];
            }

            $whereClause = 'WHERE ' . implode(' AND ', $where);
            $countStmt = $db->prepare("SELECT COUNT(*) AS total FROM notifications $whereClause");
            $countStmt->execute($params);
            $total = (int) $countStmt->fetch()['total'];

            $offset = max(0, ($page - 1) * $perPage);
            $dataStmt = $db->prepare("SELECT * FROM notifications $whereClause ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
            $dataStmt->execute($params);
            
            return [
                'data' => $dataStmt->fetchAll(),
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => (int) ceil($total / $perPage)
            ];
        } catch (Throwable $e) {
            return ['data' => [], 'total' => 0, 'page' => $page, 'per_page' => $perPage, 'total_pages' => 0];
        }
    }
}