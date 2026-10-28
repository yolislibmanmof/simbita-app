<?php
declare(strict_types=1);

class StudentWorkController
{
    public static function show(): void
    {
        Auth::requireRole('student');
        $userId = Auth::id();
        $db = Database::connection();

        $stmt = $db->prepare('SELECT id FROM student_profiles WHERE user_id = ?');
        $stmt->execute([$userId]);
        $profile = $stmt->fetch();
        $work = $profile ? AcademicWork::getByStudentId((int)$profile['id']) : null;

        $logs = []; $milestones = []; $documents = []; $supervisors = []; $bookings = []; $slots = [];
        $progress = 0;

        if ($work) {
            $workId = (int)$work['id'];
            $logs       = BimbinganLog::getByWork($workId);
            $milestones = self::milestones($workId);
            $documents  = self::documents($workId);
            $supervisors= self::supervisors($workId);
            $bookings   = Schedule::getStudentBookings($userId);
            $slots      = Schedule::getAvailableSlots(date('Y-m-d'));

            $total = count($milestones);
            $done  = count(array_filter($milestones, fn($m) => !empty($m['completed_at'])));
            $progress = $total > 0 ? (int)round($done / $total * 100) : 0;
            try { $db->prepare('UPDATE academic_works SET progress_percent = ? WHERE id = ?')->execute([$progress, $workId]); } catch (Throwable $e) {}
        }

        require dirname(__DIR__) . '/Views/student/work.php';
    }

    public static function storeBimbingan(): void
    {
        Auth::requireRole('student');
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) { $_SESSION['flash_error'] = 'Token tidak valid.'; redirect('/student/work'); }
        $userId = Auth::id();
        $workId = (int)($_POST['academic_work_id'] ?? 0);
        if (!self::ownsWork($userId, $workId)) { $_SESSION['flash_error'] = 'Akses ditolak.'; redirect('/student/work'); }

        $id = BimbinganLog::create([
            'academic_work_id'    => $workId,
            'meeting_date'        => $_POST['meeting_date'] ?? date('Y-m-d'),
            'topic'               => $_POST['topic'] ?? '',
            'discussion_points'   => $_POST['discussion_points'] ?? '',
            'action_items'        => $_POST['action_items'] ?? '',
            'student_notes'       => $_POST['student_notes'] ?? '',
            'is_attended'         => 1,
            'created_by_user_id'  => $userId,
        ]);

        if ($id > 0) {
            foreach (self::supervisorUserIds($workId) as $sid) {
                Notification::create($sid, 'Log Bimbingan Baru',
                    (Auth::user()['name'] ?? 'Mahasiswa') . ' mencatat sesi bimbingan: ' . ($_POST['topic'] ?? ''),
                    'info', 'bimbingan_logs', $id, '/lecturer/work');
            }
            $_SESSION['flash_success'] = 'Log bimbingan berhasil disimpan.';
        } else {
            $_SESSION['flash_error'] = 'Gagal menyimpan log bimbingan.';
        }
        redirect('/student/work');
    }

    public static function bookSlot(): void
    {
        Auth::requireRole('student');
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) { $_SESSION['flash_error'] = 'Token tidak valid.'; redirect('/student/work'); }
        $userId = Auth::id();
        $slotId = (int)($_POST['schedule_slot_id'] ?? 0);
        $db = Database::connection();

        $stmt = $db->prepare('SELECT id FROM student_profiles WHERE user_id = ?');
        $stmt->execute([$userId]);
        $sp = $stmt->fetch();
        $work = $sp ? AcademicWork::getByStudentId((int)$sp['id']) : null;
        if (!$work) { $_SESSION['flash_error'] = 'Anda belum memiliki tugas akhir.'; redirect('/student/work'); }

        $stmt = $db->prepare("SELECT ss.*, (SELECT COUNT(*) FROM bookings b WHERE b.schedule_slot_id = ss.id AND b.status IN ('pending','approved')) AS booked FROM schedule_slots ss WHERE ss.id = ?");
        $stmt->execute([$slotId]);
        $slot = $stmt->fetch();
        if (!$slot) { $_SESSION['flash_error'] = 'Slot tidak ditemukan.'; redirect('/student/work'); }
        if ((int)$slot['booked'] >= (int)$slot['max_students']) { $_SESSION['flash_error'] = 'Slot sudah penuh.'; redirect('/student/work'); }

        $stmt = $db->prepare("SELECT id FROM bookings WHERE schedule_slot_id = ? AND student_user_id = ? AND status IN ('pending','approved')");
        $stmt->execute([$slotId, $userId]);
        if ($stmt->fetch()) { $_SESSION['flash_error'] = 'Anda sudah memesan slot ini.'; redirect('/student/work'); }

        $id = Schedule::bookSlot([
            'schedule_slot_id' => $slotId,
            'academic_work_id' => (int)$work['id'],
            'student_user_id'  => $userId,
            'agenda'           => $_POST['agenda'] ?? '',
        ]);

        if ($id > 0) {
            $stmt = $db->prepare('SELECT user_id FROM lecturer_profiles WHERE id = ?');
            $stmt->execute([(int)$slot['lecturer_id']]);
            $lu = $stmt->fetch();
            if ($lu) {
                Notification::create((int)$lu['user_id'], 'Permintaan Booking Bimbingan',
                    (Auth::user()['name'] ?? 'Mahasiswa') . ' meminta slot ' . $slot['slot_date'] . ' ' . substr((string)$slot['start_time'], 0, 5),
                    'info', 'bookings', $id, '/lecturer/work');
            }
            $_SESSION['flash_success'] = 'Permintaan booking terkirim, menunggu persetujuan dosen.';
        } else {
            $_SESSION['flash_error'] = 'Gagal melakukan booking.';
        }
        redirect('/student/work');
    }

    public static function cancelBooking(): void
    {
        Auth::requireRole('student');
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) { $_SESSION['flash_error'] = 'Token tidak valid.'; redirect('/student/work'); }
        $userId = Auth::id();
        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $db = Database::connection();

        $stmt = $db->prepare("SELECT bk.*, ss.lecturer_id FROM bookings bk JOIN schedule_slots ss ON bk.schedule_slot_id = ss.id WHERE bk.id = ? AND bk.student_user_id = ? AND bk.status IN ('pending','approved')");
        $stmt->execute([$bookingId, $userId]);
        $bk = $stmt->fetch();
        if (!$bk) { $_SESSION['flash_error'] = 'Booking tidak ditemukan.'; redirect('/student/work'); }

        $db->prepare("UPDATE bookings SET status = 'cancelled', updated_at = NOW() WHERE id = ?")->execute([$bookingId]);
        $stmt = $db->prepare('SELECT user_id FROM lecturer_profiles WHERE id = ?');
        $stmt->execute([(int)$bk['lecturer_id']]);
        $lu = $stmt->fetch();
        if ($lu) Notification::create((int)$lu['user_id'], 'Booking Dibatalkan', (Auth::user()['name'] ?? 'Mahasiswa') . ' membatalkan booking bimbingan.', 'warning', 'bookings', $bookingId, '/lecturer/work');
        $_SESSION['flash_success'] = 'Booking dibatalkan.';
        redirect('/student/work');
    }

    // ===== HELPERS =====
    private static function ownsWork(int $userId, int $workId): bool
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('SELECT 1 FROM academic_works aw JOIN student_profiles sp ON aw.student_id = sp.id WHERE aw.id = ? AND sp.user_id = ?');
            $stmt->execute([$workId, $userId]);
            return (bool)$stmt->fetch();
        } catch (Throwable $e) { return false; }
    }

    private static function supervisorUserIds(int $workId): array
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('SELECT lp.user_id FROM supervision_assignments sa JOIN lecturer_profiles lp ON sa.lecturer_id = lp.id WHERE sa.academic_work_id = ? AND sa.is_active = 1');
            $stmt->execute([$workId]);
            return array_map('intval', array_column($stmt->fetchAll(), 'user_id'));
        } catch (Throwable $e) { return []; }
    }

    private static function milestones(int $workId): array
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('SELECT * FROM work_milestones WHERE academic_work_id = ? ORDER BY due_date ASC');
            $stmt->execute([$workId]);
            return $stmt->fetchAll();
        } catch (Throwable $e) { return []; }
    }

    private static function documents(int $workId): array
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                SELECT d.id, d.title, dt.name AS type_name,
                       dv.version_number, dv.original_filename, dv.uploaded_at, dv.page_count,
                       r.review_status
                FROM documents d
                JOIN document_types dt ON d.document_type_id = dt.id
                LEFT JOIN document_versions dv ON d.id = dv.document_id AND dv.is_current = 1
                LEFT JOIN reviews r ON dv.id = r.document_version_id
                WHERE d.academic_work_id = ?
                ORDER BY dt.sort_order ASC
            ');
            $stmt->execute([$workId]);
            return $stmt->fetchAll();
        } catch (Throwable $e) { return []; }
    }

    private static function supervisors(int $workId): array
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                SELECT u.name, lp.academic_degree, sa.assignment_role
                FROM supervision_assignments sa
                JOIN lecturer_profiles lp ON sa.lecturer_id = lp.id
                JOIN users u ON lp.user_id = u.id
                WHERE sa.academic_work_id = ? AND sa.is_active = 1
            ');
            $stmt->execute([$workId]);
            return $stmt->fetchAll();
        } catch (Throwable $e) { return []; }
    }
}