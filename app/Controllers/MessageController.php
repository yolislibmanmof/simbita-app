<?php
declare(strict_types=1);

class MessageController
{
    public static function show(): void
    {
        $role = Auth::role();
        $me = Auth::id();
        $partners = DirectMessage::partnersForUser($me, $role);

        $workId = (int)($_GET['work'] ?? 0);
        $partner = null;
        foreach ($partners as $p) {
            if ((int)$p['work_id'] === $workId) { $partner = $p; break; }
        }

        $messages = [];
        if ($partner) {
            $messages = DirectMessage::thread($workId, $me, (int)$partner['partner_id']);
            DirectMessage::markRead($workId, (int)$partner['partner_id'], $me);
        }

        require dirname(__DIR__) . '/Views/messages.php';
    }

    public static function send(): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            json_response(['error' => 'Token tidak valid.'], 419);
        }
        $me = Auth::id();
        $role = Auth::role();
        $workId = (int)($_POST['work_id'] ?? 0);
        $text = trim($_POST['message'] ?? '');

        if ($text === '') { json_response(['error' => 'Pesan kosong.'], 400); }

        $partner = null;
        foreach (DirectMessage::partnersForUser($me, $role) as $p) {
            if ((int)$p['work_id'] === $workId) { $partner = $p; break; }
        }
        if (!$partner) { json_response(['error' => 'Sesi chat tidak valid.'], 403); }

        $id = DirectMessage::send($workId, $me, (int)$partner['partner_id'], $text);
        Notification::create((int)$partner['partner_id'], 'Pesan Baru', (Auth::user()['name'] ?? 'Seseorang') . ': ' . mb_substr($text, 0, 80), 'comment', 'direct_messages', $id, ($role === 'student' ? '/lecturer/messages' : '/student/messages') . '?work=' . $workId);

        json_response(['success' => true, 'id' => $id]);
    }

    public static function api(): void
    {
        $me = Auth::id();
        $role = Auth::role();
        $workId = (int)($_GET['work'] ?? 0);
        $after = (int)($_GET['after'] ?? 0);

        $partner = null;
        foreach (DirectMessage::partnersForUser($me, $role) as $p) {
            if ((int)$p['work_id'] === $workId) { $partner = $p; break; }
        }
        if (!$partner) { json_response(['error' => 'Tidak valid.'], 403); }

        $new = DirectMessage::since($workId, (int)$partner['partner_id'], $me, $after);
        if ($new) { DirectMessage::markRead($workId, (int)$partner['partner_id'], $me); }
        json_response(['success' => true, 'messages' => $new]);
    }
}