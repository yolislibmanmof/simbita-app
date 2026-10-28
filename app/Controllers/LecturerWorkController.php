<?php
declare(strict_types=1);

class LecturerWorkController
{
    public static function show(): void
    {
        Auth::requireRole('lecturer');
        $userId = Auth::id();
        $db = Database::connection();

        $stmt = $db->prepare('SELECT id FROM lecturer_profiles WHERE user_id = ?');
        $stmt->execute([$userId]);
        $profile = $stmt->fetch();
        if (!$profile) { $_SESSION['flash_error'] = 'Profil dosen tidak ditemukan.'; redirect('/lecturer/dashboard'); }
        $lecturerId = (int)$profile['id'];

        $works = AcademicWork::getByLecturerId($lecturerId);
        $selectedId = (int)($_GET['id'] ?? 0);
        $work = null;
        foreach ($works as $w) { if ((int)$w['id'] === $selectedId) { $work = $w; break; } }
        if (!$work && !empty($works)) $work = $works[0];

        $logs = []; $milestones = []; $documents = [];
        if ($work) {
            $workId = (int)$work['id'];
            $logs = BimbinganLog::getByWork($workId);
            $stmt = $db->prepare('SELECT * FROM work_milestones WHERE academic_work_id = ? ORDER BY due_date ASC');
            $stmt->execute([$workId]); $milestones = $stmt->fetchAll();
            $stmt = $db->prepare('
                SELECT d.id, dt.name AS type_name, dv.version_number, dv.original_filename, dv.uploaded_at, r.review_status
                FROM documents d
                JOIN document_types dt ON d.document_type_id = dt.id
                LEFT JOIN document_versions dv ON d.id = dv.document_id AND dv.is_current = 1
                LEFT JOIN reviews r ON dv.id = r.document_version_id
                WHERE d.academic_work_id = ? ORDER BY dt.sort_order ASC');
            $stmt->execute([$workId]); $documents = $stmt->fetchAll();
        }

        $slots     = Schedule::getLecturerSlots($lecturerId, date('Y-m-d'));
        $bookings  = Schedule::getLecturerBookings($lecturerId);
        $analytics = Analytics::lecturerDashboard($lecturerId);

        require dirname(__DIR__) . '/Views/lecturer/work.php';
    }

    public static function storeBimbingan(): void
    {
        Auth::requireRole('lecturer');
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) { $_SESSION['flash_error'] = 'Token tidak valid.'; redirect('/lecturer/work'); }
        $userId = Auth::id();
        $workId = (int)($_POST['academic_work_id'] ?? 0);
        if (!self::supervises($userId, $workId)) { $_SESSION['flash_error'] = 'Akses ditolak.'; redirect('/lecturer/work'); }

        $id = BimbinganLog::create([
            'academic_work_id'   => $workId,
            'meeting_date'       => $_POST['meeting_date'] ?? date('Y-m-d'),
            'topic'              => $_POST['topic'] ?? '',
            'discussion_points'  => $_POST['discussion_points'] ?? '',
            'action_items'       => $_POST['action_items'] ?? '',
            'lecturer_notes'     => $_POST['lecturer_notes'] ?? '',
            'is_attended'        => isset($_POST['is_attended']) ? 1 : 0,
            'created_by_user_id' => $userId,
        ]);

        if ($id > 0) {
            $stu = self::studentUserIdOfWork($workId);
            if ($stu) Notification::create($stu, 'Log Bimbingan Baru', (Auth::user()['name'] ?? 'Dosen') . ' mencatat sesi bimbingan: ' . ($_POST['topic'] ?? ''), 'info', 'bimbingan_logs', $id, '/student/work');
            $_SESSION['flash_success'] = 'Log bimbingan disimpan.';
        } else $_SESSION['flash_error'] = 'Gagal menyimpan log.';
        redirect('/lecturer/work');
    }

    public static function createSlot(): void
    {
        Auth::requireRole('lecturer');
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) { $_SESSION['flash_error'] = 'Token tidak valid.'; redirect('/lecturer/work'); }
        $userId = Auth::id();
        $db = Database::connection();
        $stmt = $db->prepare('SELECT id FROM lecturer_profiles WHERE user_id = ?');
        $stmt->execute([$userId]);
        $lp = $stmt->fetch();
        if (!$lp) { redirect('/lecturer/work'); }

        $date = $_POST['slot_date'] ?? '';
        $start = $_POST['start_time'] ?? '';
        $end = $_POST['end_time'] ?? '';
        if ($date === '' || $start === '' || $end === '' || $end <= $start) {
            $_SESSION['flash_error'] = 'Jam mulai harus sebelum jam selesai.';
            redirect('/lecturer/work');
        }
        if ($date < date('Y-m-d')) {
            $_SESSION['flash_error'] = 'Tanggal slot tidak boleh di masa lalu.';
            redirect('/lecturer/work');
        }

        $id = Schedule::createSlot([
            'lecturer_id'  => (int)$lp['id'],
            'slot_date'    => $date,
            'start_time'   => $start,
            'end_time'     => $end,
            'max_students' => (int)($_POST['max_students'] ?? 3),
            'mode'         => ($_POST['mode'] ?? 'offline') === 'online' ? 'online' : 'offline',
            'location'     => $_POST['location'] ?? '',
            'link_meeting' => $_POST['link_meeting'] ?? '',
        ]);

        $_SESSION['flash_success'] = $id > 0 ? 'Slot bimbingan berhasil dibuka.' : 'Gagal membuat slot (mungkin bentrok).';
        redirect('/lecturer/work');
    }

    public static function respondBooking(): void
    {
        Auth::requireRole('lecturer');
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) { $_SESSION['flash_error'] = 'Token tidak valid.'; redirect('/lecturer/work'); }
        $userId = Auth::id();
        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $action = ($_POST['action'] ?? '') === 'approve' ? 'approved' : 'rejected';
        $note = trim($_POST['note'] ?? '');
        $db = Database::connection();

        $stmt = $db->prepare("
            SELECT bk.* FROM bookings bk
            JOIN schedule_slots ss ON bk.schedule_slot_id = ss.id
            JOIN lecturer_profiles lp ON ss.lecturer_id = lp.id
            WHERE bk.id = ? AND lp.user_id = ? AND bk.status = 'pending'
        ");
        $stmt->execute([$bookingId, $userId]);
        $bk = $stmt->fetch();
        if (!$bk) { $_SESSION['flash_error'] = 'Booking tidak ditemukan.'; redirect('/lecturer/work'); }

        Schedule::respondBooking($bookingId, $action, $note);
        Notification::create((int)$bk['student_user_id'],
            $action === 'approved' ? 'Booking Disetujui' : 'Booking Ditolak',
            'Permintaan bimbingan Anda ' . ($action === 'approved' ? 'disetujui' : 'ditolak') . ($note !== '' ? ': ' . $note : ''),
            $action === 'approved' ? 'success' : 'danger', 'bookings', $bookingId, '/student/work');
        $_SESSION['flash_success'] = 'Booking ' . ($action === 'approved' ? 'disetujui' : 'ditolak') . '.';
        redirect('/lecturer/work');
    }

    // ===== HELPERS =====
    private static function supervises(int $userId, int $workId): bool
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('SELECT 1 FROM supervision_assignments sa JOIN lecturer_profiles lp ON sa.lecturer_id = lp.id WHERE sa.academic_work_id = ? AND lp.user_id = ? AND sa.is_active = 1');
            $stmt->execute([$workId, $userId]);
            return (bool)$stmt->fetch();
        } catch (Throwable $e) { return false; }
    }

    private static function studentUserIdOfWork(int $workId): ?int
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('SELECT sp.user_id FROM academic_works aw JOIN student_profiles sp ON aw.student_id = sp.id WHERE aw.id = ?');
            $stmt->execute([$workId]);
            $r = $stmt->fetch();
            return $r ? (int)$r['user_id'] : null;
        } catch (Throwable $e) { return null; }
    }
}