<?php
declare(strict_types=1);

class NotificationController
{
    /**
     * Halaman notifikasi penuh (dengan filter + pagination)
     * Route: GET /notifications
     */
    public static function showNotifications(): void
    {
        Auth::requireRole('admin', 'lecturer', 'student');
        
        $userId = Auth::id();
        $filter = $_GET['filter'] ?? 'all';
        $page = max(1, (int)($_GET['page'] ?? 1));
        
        // Siapkan filter untuk method getAll
        $filters = [];
        if ($filter === 'unread') {
            $filters['is_read'] = 0;
        } elseif (in_array($filter, Notification::ALL_TYPES, true)) {
            $filters['type'] = $filter;
        }
        
        // Ambil data dengan pagination
        $data = Notification::getAll($userId, $filters, $page, 15);
        
        // Ambil stats untuk tabs
        $db = Database::connection();
        $stmtStats = $db->prepare('
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN type = "review" THEN 1 ELSE 0 END) as review_count,
                SUM(CASE WHEN type = "deadline" THEN 1 ELSE 0 END) as deadline_count,
                SUM(CASE WHEN type = "system" THEN 1 ELSE 0 END) as system_count,
                SUM(CASE WHEN type = "approval" THEN 1 ELSE 0 END) as approval_count,
                SUM(CASE WHEN type = "comment" THEN 1 ELSE 0 END) as comment_count,
                SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread_count
            FROM notifications WHERE user_id = ?
        ');
        $stmtStats->execute([$userId]);
        $stats = $stmtStats->fetch();
        
        // Kirim ke view
        require dirname(__DIR__) . '/Views/notifications.php';
    }

    /**
     * API: Hitung notifikasi belum dibaca (untuk polling navbar)
     * Route: GET /api/notifications/count
     */
    public static function getUnreadCount(): void
    {
        Auth::requireRole('admin', 'lecturer', 'student');
        json_response([
            'success' => true,
            'count' => Notification::countUnread(Auth::id())
        ]);
    }

    /**
     * API: Tandai semua notifikasi sudah dibaca
     * Route: POST /api/notifications/read-all
     */
    public static function markAllRead(): void
    {
        Auth::requireRole('admin', 'lecturer', 'student');
        
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($csrfToken)) {
            json_response(['error' => 'Token tidak valid'], 419);
        }

        $affected = Notification::markAsRead(Auth::id(), null);
        json_response([
            'success' => true, 
            'affected' => $affected,
            'message' => sprintf('%d notifikasi ditandai sudah dibaca', $affected)
        ]);
    }

    /**
     * API: Tandai satu notifikasi sudah dibaca
     * Route: POST /api/notifications/read
     */
    public static function markOneRead(): void
    {
        Auth::requireRole('admin', 'lecturer', 'student');
        
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($csrfToken)) {
            json_response(['error' => 'Token tidak valid'], 419);
        }

        $notificationId = (int)($_POST['notification_id'] ?? 0);
        
        if ($notificationId <= 0) {
            json_response(['error' => 'Notification ID tidak valid'], 400);
        }

        $affected = Notification::markAsRead(Auth::id(), $notificationId);
        json_response([
            'success' => true, 
            'affected' => $affected,
            'message' => 'Notifikasi ditandai sudah dibaca'
        ]);
    }
}