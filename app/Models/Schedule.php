<?php
declare(strict_types=1);

class Schedule
{
    public static function createSlot(array $d): int
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare("
                INSERT INTO schedule_slots (lecturer_id, slot_date, start_time, end_time, max_students, mode, location, link_meeting)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                (int)$d['lecturer_id'], $d['slot_date'], $d['start_time'], $d['end_time'],
                (int)($d['max_students'] ?? 3), $d['mode'] ?? 'offline',
                trim($d['location'] ?? '') ?: null, trim($d['link_meeting'] ?? '') ?: null,
            ]);
            return (int)$db->lastInsertId();
        } catch (Throwable $e) { error_log('[Sch::createSlot] '.$e->getMessage()); return 0; }
    }

    public static function getLecturerSlots(int $lecturerId, string $fromDate): array
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare("
                SELECT ss.*,
                    (SELECT COUNT(*) FROM bookings b WHERE b.schedule_slot_id = ss.id AND b.status IN ('pending','approved')) AS booked_count
                FROM schedule_slots ss
                WHERE ss.lecturer_id = ? AND ss.slot_date >= ?
                ORDER BY ss.slot_date ASC, ss.start_time ASC
            ");
            $stmt->execute([$lecturerId, $fromDate]);
            return $stmt->fetchAll();
        } catch (Throwable $e) { return []; }
    }

    public static function getAvailableSlots(string $fromDate): array
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare("
                SELECT ss.*, u.name AS lecturer_name, lp.academic_degree,
                    (SELECT COUNT(*) FROM bookings b WHERE b.schedule_slot_id = ss.id AND b.status IN ('pending','approved')) AS booked_count
                FROM schedule_slots ss
                JOIN lecturer_profiles lp ON ss.lecturer_id = lp.id
                JOIN users u ON lp.user_id = u.id
                WHERE ss.slot_date >= ?
                ORDER BY ss.slot_date ASC, ss.start_time ASC
            ");
            $stmt->execute([$fromDate]);
            return $stmt->fetchAll();
        } catch (Throwable $e) { return []; }
    }

    public static function bookSlot(array $d): int
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare("
                INSERT INTO bookings (schedule_slot_id, academic_work_id, student_user_id, agenda, status)
                VALUES (?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([
                (int)$d['schedule_slot_id'], (int)$d['academic_work_id'],
                (int)$d['student_user_id'], trim($d['agenda'] ?? '') ?: null,
            ]);
            return (int)$db->lastInsertId();
        } catch (Throwable $e) { return 0; }
    }

    public static function respondBooking(int $bookingId, string $status, string $note = ''): bool
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare("UPDATE bookings SET status = ?, lecturer_note = ? WHERE id = ?");
            return $stmt->execute([$status, $note ?: null, $bookingId]);
        } catch (Throwable $e) { return false; }
    }

    public static function getStudentBookings(int $userId): array
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare("
                SELECT bk.*, ss.slot_date, ss.start_time, ss.end_time, ss.mode, ss.location, ss.link_meeting,
                    u.name AS lecturer_name
                FROM bookings bk
                JOIN schedule_slots ss ON bk.schedule_slot_id = ss.id
                JOIN lecturer_profiles lp ON ss.lecturer_id = lp.id
                JOIN users u ON lp.user_id = u.id
                WHERE bk.student_user_id = ?
                ORDER BY ss.slot_date DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (Throwable $e) { return []; }
    }

    public static function getLecturerBookings(int $lecturerId): array
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare("
                SELECT bk.*, ss.slot_date, ss.start_time, ss.end_time, ss.mode, ss.location,
                    u.name AS student_name, aw.title AS work_title
                FROM bookings bk
                JOIN schedule_slots ss ON bk.schedule_slot_id = ss.id
                JOIN student_profiles sp ON bk.academic_work_id IN (SELECT id FROM academic_works WHERE student_id = sp.id)
                JOIN users u ON sp.user_id = u.id
                JOIN academic_works aw ON bk.academic_work_id = aw.id
                WHERE ss.lecturer_id = ?
                ORDER BY ss.slot_date DESC
            ");
            $stmt->execute([$lecturerId]);
            return $stmt->fetchAll();
        } catch (Throwable $e) { return []; }
    }
}