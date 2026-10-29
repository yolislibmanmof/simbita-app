<?php
declare(strict_types=1);

class DefenseController
{
    public static function index(): void
    {
        $db = Database::connection();
        $sessions = DefenseSession::all();
        foreach ($sessions as &$s) {
            $s['examiners']   = DefenseSession::examiners((int)$s['id']);
            $s['final_grade'] = DefenseSession::finalGrade((int)$s['id']);
            $s['grades_done'] = DefenseSession::gradesComplete((int)$s['id']);
        }
        unset($s);

        $works = $db->query("
            SELECT aw.id, aw.title, u.name AS student_name
            FROM academic_works aw
            JOIN student_profiles sp ON sp.id = aw.student_id
            JOIN users u ON u.id = sp.user_id
            WHERE aw.status NOT IN ('completed','cancelled')
            ORDER BY u.name
        ")->fetchAll();

        $lecturers = $db->query("
            SELECT lp.id, u.name, lp.academic_degree
            FROM lecturer_profiles lp JOIN users u ON u.id = lp.user_id
            WHERE u.is_active = 1 ORDER BY u.name
        ")->fetchAll();

        require dirname(__DIR__) . '/Views/admin/defense.php';
    }

    public static function store(): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) { flash_set('error', 'Token tidak valid.'); redirect('/admin/defense'); }

        $workId  = (int)($_POST['academic_work_id'] ?? 0);
        $type    = $_POST['session_type'] ?? '';
        $date    = trim($_POST['scheduled_date'] ?? '');
        $room    = trim($_POST['room'] ?? '');
        $link    = trim($_POST['online_link'] ?? '');

        if ($workId <= 0 || $type === '' || $date === '') {
            flash_set('error', 'Data sidang tidak lengkap.');
            redirect('/admin/defense');
        }

        // GERBANG PLAGIASI
        if ((string)SystemSetting::get('require_plagiarism_check', '1') === '1') {
            $threshold = (float)SystemSetting::get('plagiarism_threshold', 30);
            $status = PlagiarismCheck::statusForWork($workId, $threshold);
            if ($status !== 'passed') {
                flash_set('error', 'Sidang tidak dapat dijadwalkan: dokumen belum lolos cek plagiasi (status: ' . ($status === 'none' ? 'belum dicek' : 'tidak lolos') . ').');
                redirect('/admin/defense');
            }
        }

        $sessionId = DefenseSession::create([
            'academic_work_id' => $workId,
            'session_type'     => $type,
            'scheduled_date'   => date('Y-m-d H:i:s', strtotime($date)),
            'room'             => $room ?: null,
            'online_link'      => $link ?: null,
        ]);

        $roles = ['chair' => 'Ketua Sidang', 'examiner_1' => 'Penguji 1', 'examiner_2' => 'Penguji 2', 'secretary' => 'Sekretaris'];
        $assigned = 0;
        foreach ($roles as $role => $label) {
            $lpId = (int)($_POST['ex_' . $role] ?? 0);
            if ($lpId > 0) { DefenseSession::assignExaminer($sessionId, $lpId, $role); $assigned++; }
        }
        if ($assigned === 0) {
            flash_set('error', 'Minimal satu penguji harus ditunjuk.');
            redirect('/admin/defense');
        }

        // Notifikasi mahasiswa & penguji
        $db = Database::connection();
        $stmt = $db->prepare('SELECT sp.user_id FROM academic_works aw JOIN student_profiles sp ON sp.id = aw.student_id WHERE aw.id = ?');
        $stmt->execute([$workId]);
        $studentUserId = (int)$stmt->fetchColumn();
        if ($studentUserId) {
            Notification::create($studentUserId, 'Jadwal Sidang Terbit', 'Sidang Anda telah dijadwalkan pada ' . date('d/m/Y H:i', strtotime($date)) . '. Silakan persiapkan diri.', 'deadline', 'defense_sessions', $sessionId, '/student/dashboard');
        }

        flash_set('success', 'Sidang berhasil dijadwalkan dengan ' . $assigned . ' penguji.');
        redirect('/admin/defense');
    }

    public static function complete(): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) { flash_set('error', 'Token tidak valid.'); redirect('/admin/defense'); }
        $id = (int)($_POST['id'] ?? 0);
        $session = DefenseSession::findById($id);
        if (!$session) { flash_set('error', 'Sidang tidak ditemukan.'); redirect('/admin/defense'); }
        if (!DefenseSession::gradesComplete($id)) { flash_set('error', 'Belum semua penguji memasukkan nilai.'); redirect('/admin/defense'); }

        $final = DefenseSession::finalGrade($id);
        DefenseSession::complete($id, (float)$final);

        $db = Database::connection();
        $stmt = $db->prepare('SELECT sp.user_id FROM academic_works aw JOIN student_profiles sp ON sp.id = aw.student_id WHERE aw.id = ?');
        $stmt->execute([$session['academic_work_id']]);
        $studentUserId = (int)$stmt->fetchColumn();
        if ($studentUserId) {
            Notification::create($studentUserId, 'Sidang Selesai', 'Sidang Anda telah selesai dengan nilai akhir ' . number_format((float)$final, 2) . '. Selamat!', 'status_change', 'defense_sessions', $id, '/student/dashboard');
        }

        flash_set('success', 'Sidang ditandai selesai. Nilai akhir: ' . number_format((float)$final, 2));
        redirect('/admin/defense');
    }

    public static function grading(): void
    {
        $assignments = DefenseSession::assignmentsForLecturer(Auth::id());
        require dirname(__DIR__) . '/Views/lecturer/grading.php';
    }

    public static function saveGrade(): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) { flash_set('error', 'Token tidak valid.'); redirect('/lecturer/grading'); }
        $sessionId = (int)($_POST['session_id'] ?? 0);
        $grade = (float)($_POST['grade'] ?? -1);
        $notes = trim($_POST['notes'] ?? '');

        $session = DefenseSession::findById($sessionId);
        if (!$session || $session['status'] === 'completed') { flash_set('error', 'Sidang tidak valid atau sudah selesai.'); redirect('/lecturer/grading'); }
        if ($grade < 0 || $grade > 100) { flash_set('error', 'Nilai harus antara 0 sampai 100.'); redirect('/lecturer/grading'); }

        DefenseSession::saveGrade($sessionId, Auth::id(), $grade, $notes);
        flash_set('success', 'Nilai berhasil disimpan.');
        redirect('/lecturer/grading');
    }
}