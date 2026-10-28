<?php
declare(strict_types=1);

/**
 * =====================================================================
 * SIMBITA - NOTIFICATION MODEL
 * FINAL ULTRA PREMIUM - ENTERPRISE GRADE
 * =====================================================================
 */
class Notification
{
    // ========== TYPE CONSTANTS ==========
    public const TYPE_SYSTEM         = 'system';
    public const TYPE_INFO           = 'info';
    public const TYPE_SUCCESS        = 'success';
    public const TYPE_WARNING        = 'warning';
    public const TYPE_DANGER         = 'danger';
    public const TYPE_REVIEW         = 'review';
    public const TYPE_UPLOAD         = 'upload';
    public const TYPE_STATUS         = 'status_change';
    public const TYPE_DEADLINE       = 'deadline';
    public const TYPE_MILESTONE      = 'milestone';
    public const TYPE_COMMENT        = 'comment';
    public const TYPE_APPROVAL       = 'approval';
    public const TYPE_TITLE_PROPOSAL = 'title_proposals';
    public const TYPE_BIMBINGAN      = 'bimbingan_logs';
    public const TYPE_BOOKING        = 'bookings';
    public const TYPE_WORK           = 'academic_works';

    public const ALL_TYPES = [
        self::TYPE_SYSTEM,
        self::TYPE_INFO,
        self::TYPE_SUCCESS,
        self::TYPE_WARNING,
        self::TYPE_DANGER,
        self::TYPE_REVIEW,
        self::TYPE_UPLOAD,
        self::TYPE_STATUS,
        self::TYPE_DEADLINE,
        self::TYPE_MILESTONE,
        self::TYPE_COMMENT,
        self::TYPE_APPROVAL,
        self::TYPE_TITLE_PROPOSAL,
        self::TYPE_BIMBINGAN,
        self::TYPE_BOOKING,
        self::TYPE_WORK,
    ];

    // Default limits
    public const DEFAULT_LIMIT       = 10;
    public const MAX_TITLE_LENGTH    = 255;
    public const MAX_MESSAGE_LENGTH  = 1000;

    // =================================================================
    // CREATE
    // =================================================================

    public static function create(
        int $userId,
        string $title,
        string $message,
        string $type = self::TYPE_SYSTEM,
        ?string $relatedTable = null,
        ?int $relatedId = null,
        ?string $link = null
    ): int {
        try {
            $db = Database::connection();

            $title   = mb_substr(trim($title), 0, self::MAX_TITLE_LENGTH);
            $message = mb_substr(trim($message), 0, self::MAX_MESSAGE_LENGTH);

            if (!in_array($type, self::ALL_TYPES, true)) {
                $type = self::TYPE_SYSTEM;
            }

            $stmt = $db->prepare('
                INSERT INTO notifications 
                    (user_id, title, message, type, related_table, related_id, link, is_read, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW())
            ');
            $stmt->execute([
                $userId, $title, $message, $type, $relatedTable, $relatedId, $link,
            ]);
            return (int) $db->lastInsertId();
        } catch (Throwable $e) {
            error_log('[Notification::create] ' . $e->getMessage());
            return 0;
        }
    }

    public static function createMany(
        array $userIds,
        string $title,
        string $message,
        string $type = self::TYPE_SYSTEM,
        ?string $link = null
    ): int {
        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));
        if (empty($userIds)) return 0;

        $count = 0;
        foreach ($userIds as $userId) {
            if (self::create($userId, $title, $message, $type, null, null, $link) > 0) {
                $count++;
            }
        }
        return $count;
    }

    public static function broadcastToRole(
        string $role,
        string $title,
        string $message,
        string $type = self::TYPE_SYSTEM,
        ?string $link = null
    ): int {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('SELECT id FROM users WHERE role = ? AND is_active = 1');
            $stmt->execute([$role]);
            $userIds = array_column($stmt->fetchAll(), 'id');
            return self::createMany($userIds, $title, $message, $type, $link);
        } catch (Throwable $e) {
            error_log('[Notification::broadcastToRole] ' . $e->getMessage());
            return 0;
        }
    }

    // =================================================================
    // READ
    // =================================================================

    public static function latest(int $userId, int $limit = self::DEFAULT_LIMIT): array
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                SELECT id, title, message, type, link, related_table, related_id, is_read, created_at
                FROM notifications
                WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT ?
            ');
            $stmt->bindValue(1, $userId, PDO::PARAM_INT);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            error_log('[Notification::latest] ' . $e->getMessage());
            return [];
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

            if (!empty($filters['search'])) {
                $where[] = '(title LIKE ? OR message LIKE ?)';
                $search = '%' . $filters['search'] . '%';
                $params[] = $search;
                $params[] = $search;
            }

            if (!empty($filters['date_from'])) {
                $where[] = 'created_at >= ?';
                $params[] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $where[] = 'created_at <= ?';
                $params[] = $filters['date_to'] . ' 23:59:59';
            }

            $whereClause = 'WHERE ' . implode(' AND ', $where);

            $countStmt = $db->prepare("SELECT COUNT(*) AS total FROM notifications $whereClause");
            $countStmt->execute($params);
            $total = (int) $countStmt->fetch()['total'];

            $offset = max(0, ($page - 1) * $perPage);
            $dataStmt = $db->prepare("
                SELECT * FROM notifications 
                $whereClause 
                ORDER BY created_at DESC 
                LIMIT $perPage OFFSET $offset
            ");
            $dataStmt->execute($params);
            $data = $dataStmt->fetchAll();

            return [
                'data'        => $data,
                'total'       => $total,
                'page'        => $page,
                'per_page'    => $perPage,
                'total_pages' => (int) ceil($total / $perPage),
            ];
        } catch (Throwable $e) {
            error_log('[Notification::getAll] ' . $e->getMessage());
            return ['data' => [], 'total' => 0, 'page' => $page, 'per_page' => $perPage, 'total_pages' => 0];
        }
    }

    public static function findById(int $id, int $userId): ?array
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('SELECT * FROM notifications WHERE id = ? AND user_id = ?');
            $stmt->execute([$id, $userId]);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (Throwable $e) {
            error_log('[Notification::findById] ' . $e->getMessage());
            return null;
        }
    }

    public static function countUnread(int $userId): int
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('SELECT COUNT(*) AS total FROM notifications WHERE user_id = ? AND is_read = 0');
            $stmt->execute([$userId]);
            return (int) $stmt->fetch()['total'];
        } catch (Throwable $e) {
            return 0;
        }
    }

    public static function countAll(int $userId): int
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('SELECT COUNT(*) AS total FROM notifications WHERE user_id = ?');
            $stmt->execute([$userId]);
            return (int) $stmt->fetch()['total'];
        } catch (Throwable $e) {
            return 0;
        }
    }

    // =================================================================
    // UPDATE STATUS
    // =================================================================

    public static function markAsRead(int $userId, ?int $notificationId = null): int
    {
        try {
            $db = Database::connection();

            if ($notificationId !== null) {
                $stmt = $db->prepare('
                    UPDATE notifications
                    SET is_read = 1, read_at = NOW()
                    WHERE id = ? AND user_id = ? AND is_read = 0
                ');
                $stmt->execute([$notificationId, $userId]);
                return $stmt->rowCount();
            }

            $stmt = $db->prepare('
                UPDATE notifications
                SET is_read = 1, read_at = NOW()
                WHERE user_id = ? AND is_read = 0
            ');
            $stmt->execute([$userId]);
            return $stmt->rowCount();
        } catch (Throwable $e) {
            error_log('[Notification::markAsRead] ' . $e->getMessage());
            return 0;
        }
    }

    public static function markAsUnread(int $userId, ?int $notificationId = null): bool
    {
        try {
            $db = Database::connection();

            if ($notificationId !== null) {
                $stmt = $db->prepare('
                    UPDATE notifications
                    SET is_read = 0, read_at = NULL
                    WHERE id = ? AND user_id = ?
                ');
                return $stmt->execute([$notificationId, $userId]);
            }

            $stmt = $db->prepare('
                UPDATE notifications
                SET is_read = 0, read_at = NULL
                WHERE user_id = ? AND is_read = 1
            ');
            return $stmt->execute([$userId]);
        } catch (Throwable $e) {
            error_log('[Notification::markAsUnread] ' . $e->getMessage());
            return false;
        }
    }

    // =================================================================
    // DELETE / CLEANUP
    // =================================================================

    public static function delete(int $id, int $userId): bool
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('DELETE FROM notifications WHERE id = ? AND user_id = ?');
            return $stmt->execute([$id, $userId]);
        } catch (Throwable $e) {
            error_log('[Notification::delete] ' . $e->getMessage());
            return false;
        }
    }

    public static function deleteAll(int $userId): bool
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('DELETE FROM notifications WHERE user_id = ?');
            return $stmt->execute([$userId]);
        } catch (Throwable $e) {
            error_log('[Notification::deleteAll] ' . $e->getMessage());
            return false;
        }
    }

    public static function cleanup(int $maxDays = 30, ?int $userId = null): int
    {
        try {
            $db = Database::connection();
            $sql = 'DELETE FROM notifications WHERE is_read = 1 AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)';
            $params = [$maxDays];

            if ($userId !== null) {
                $sql .= ' AND user_id = ?';
                $params[] = $userId;
            }

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (Throwable $e) {
            error_log('[Notification::cleanup] ' . $e->getMessage());
            return 0;
        }
    }

    public static function cleanupOrphaned(string $table, int $relatedId): int
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('DELETE FROM notifications WHERE related_table = ? AND related_id = ?');
            $stmt->execute([$table, $relatedId]);
            return $stmt->rowCount();
        } catch (Throwable $e) {
            error_log('[Notification::cleanupOrphaned] ' . $e->getMessage());
            return 0;
        }
    }
}