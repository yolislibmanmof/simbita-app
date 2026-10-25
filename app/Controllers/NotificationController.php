<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/Notification.php';

class NotificationController
{
    public static function markAllRead(): void
    {
        Auth::start();
        Auth::requireRole('admin', 'lecturer', 'student');

        $input = json_decode(file_get_contents('php://input'), true);
        $token = $input['csrf_token'] ?? '';

        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            json_response(['message' => 'Sesi tidak valid.'], 419);
        }

        Notification::markAsRead(Auth::id());

        json_response(['message' => 'Semua notifikasi ditandai sudah dibaca.']);
    }

    public static function markOneRead(): void
    {
        Auth::start();
        Auth::requireRole('admin', 'lecturer', 'student');

        $id = (int) ($_POST['notification_id'] ?? 0);
        Notification::markAsRead(Auth::id(), $id);

        json_response(['message' => 'Notifikasi ditandai sudah dibaca.']);
    }
}