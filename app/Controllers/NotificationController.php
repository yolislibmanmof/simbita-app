<?php
declare(strict_types=1);

class NotificationController
{
    /**
     * Tampilkan halaman notifikasi
     */
    public static function showNotifications(): void
    {
        Auth::start();
        
        $userId = Auth::id();
        $notifications = Notification::latest($userId, 50);

        require dirname(__DIR__) . '/Views/notifications.php';
    }

    /**
     * Tandai semua notifikasi sudah dibaca
     */
    public static function markAllRead(): void
    {
        Auth::start();
        
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($csrfToken)) {
            json_response(['error' => 'Token tidak valid'], 419);
        }

        Notification::markAsRead(Auth::id());
        json_response(['success' => true, 'message' => 'Semua notifikasi ditandai sudah dibaca']);
    }

    /**
     * Tandai satu notifikasi sudah dibaca
     */
    public static function markOneRead(): void
    {
        Auth::start();
        
        $notificationId = (int)($_POST['notification_id'] ?? 0);
        
        if (!$notificationId) {
            json_response(['error' => 'Invalid notification ID'], 400);
        }

        Notification::markAsRead(Auth::id(), $notificationId);
        json_response(['success' => true, 'message' => 'Notifikasi ditandai sudah dibaca']);
    }
}