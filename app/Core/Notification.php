<?php
declare(strict_types=1);

/**
 * =====================================================================
 * SIMBITA - NOTIFICATION SYSTEM
 * =====================================================================
 */
class Notification
{
    /**
     * Buat notifikasi baru
     */
    public static function create(int $userId, string $title, string $message, ?string $link = null): int
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                INSERT INTO notifications (user_id, title, message, link, is_read, created_at)
                VALUES (?, ?, ?, ?, 0, NOW())
            ');
            $stmt->execute([$userId, $title, $message, $link]);
            return (int) $db->lastInsertId();
        } catch (Exception $e) {
            error_log('Notification create failed: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Kirim notifikasi ke banyak user sekaligus
     */
    public static function broadcast(array $userIds, string $title, string $message, ?string $link = null): int
    {
        $count = 0;
        foreach ($userIds as $userId) {
            if (self::create((int) $userId, $title, $message, $link) > 0) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Ambil notifikasi terbaru
     */
    public static function latest(int $userId, int $limit = 10): array
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                SELECT id, title, message, link, is_read, created_at 
                FROM notifications 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?
            ');
            $stmt->execute([$userId, $limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log('Notification latest failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Hitung notifikasi belum dibaca
     */
    public static function unreadCount(int $userId): int
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                SELECT COUNT(*) AS total 
                FROM notifications 
                WHERE user_id = ? AND is_read = 0
            ');
            $stmt->execute([$userId]);
            return (int) $stmt->fetch()['total'];
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Tandai notifikasi sebagai sudah dibaca
     */
    public static function markAsRead(int $notificationId, int $userId): bool
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                UPDATE notifications 
                SET is_read = 1, read_at = NOW() 
                WHERE id = ? AND user_id = ?
            ');
            return $stmt->execute([$notificationId, $userId]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Tandai semua notifikasi sebagai sudah dibaca
     */
    public static function markAllAsRead(int $userId): bool
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                UPDATE notifications 
                SET is_read = 1, read_at = NOW() 
                WHERE user_id = ? AND is_read = 0
            ');
            return $stmt->execute([$userId]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Hapus notifikasi lama (lebih dari 30 hari)
     */
    public static function cleanup(int $maxDays = 30): int
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                DELETE FROM notifications 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY) 
                AND is_read = 1
            ');
            $stmt->execute([$maxDays]);
            return $stmt->rowCount();
        } catch (Exception $e) {
            return 0;
        }
    }
}