<?php
declare(strict_types=1);

class ProposalController
{
    // =================================================================
    // STUDENT
    // =================================================================
    public static function studentIndex(): void
    {
        Auth::requireRole('student');
        $userId = Auth::id();
        $db = Database::connection();

        $stmt = $db->prepare('SELECT id FROM student_profiles WHERE user_id = ?');
        $stmt->execute([$userId]);
        $profile = $stmt->fetch();

        $myWork = $profile ? AcademicWork::getByStudentId((int) $profile['id']) : null;

        $incoming = TitleProposal::getIncomingPending($userId);
        $outgoing = TitleProposal::getOutgoing($userId);
        $pendingOutgoing = 0;
        foreach ($outgoing as $o) {
            if ($o['status'] === 'pending') $pendingOutgoing++;
        }

        $lecturers = $db->query("
            SELECT u.id, u.name, lp.academic_degree, lp.department
            FROM users u
            JOIN lecturer_profiles lp ON lp.user_id = u.id
            WHERE u.role = 'lecturer' AND u.is_active = 1
            ORDER BY u.name
        ")->fetchAll();

        $workTypes = $db->query('SELECT id, name FROM work_types ORDER BY name')->fetchAll();

        require dirname(__DIR__) . '/Views/student/proposals.php';
    }

    public static function studentSubmit(): void
    {
        Auth::requireRole('student');
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token tidak valid.';
            redirect('/student/proposals');
        }
        
        // Gerbang penutupan pendaftaran
        if ((string) SystemSetting::get('is_registration_open', '1') !== '1') {
            $_SESSION['flash_error'] = 'Mohon maaf, pendaftaran pengajuan judul tugas akhir saat ini sedang ditutup oleh administrator.';
            redirect('/student/proposals');   // PERBAIKAN: tanpa BASE_URL
        }

        $userId = Auth::id();   // PERBAIKAN: hapus duplikasi (sebelumnya muncul 2x)
        $db = Database::connection();

        $stmt = $db->prepare('SELECT id FROM student_profiles WHERE user_id = ?');
        $stmt->execute([$userId]);
        $profile = $stmt->fetch();
        if (!$profile) {
            $_SESSION['flash_error'] = 'Profil mahasiswa tidak ditemukan.';
            redirect('/student/proposals');
        }
        if (AcademicWork::getByStudentId((int) $profile['id'])) {
            $_SESSION['flash_error'] = 'Anda sudah memiliki tugas akhir aktif.';
            redirect('/student/proposals');
        }
        if (TitleProposal::hasPendingOutgoing($userId)) {
            $_SESSION['flash_error'] = 'Anda masih memiliki pengajuan yang menunggu respon dosen.';
            redirect('/student/proposals');
        }

        $lecturerId = (int) ($_POST['lecturer_user_id'] ?? 0);
        $workTypeId = (int) ($_POST['work_type_id'] ?? 0);
        $titles = [trim($_POST['title_1'] ?? ''), trim($_POST['title_2'] ?? ''), trim($_POST['title_3'] ?? '')];
        $descs  = [trim($_POST['desc_1'] ?? ''), trim($_POST['desc_2'] ?? ''), trim($_POST['desc_3'] ?? '')];

        $stmt = $db->prepare("SELECT id FROM users WHERE id = ? AND role = 'lecturer' AND is_active = 1");
        $stmt->execute([$lecturerId]);
        if (!$stmt->fetch()) {
            $_SESSION['flash_error'] = 'Dosen tujuan tidak valid.';
            redirect('/student/proposals');
        }
        if ($titles[0] === '') {
            $_SESSION['flash_error'] = 'Judul pertama wajib diisi.';
            redirect('/student/proposals');
        }

        $created = 0;
        foreach ($titles as $i => $t) {
            if ($t === '') continue;
            $id = TitleProposal::create([
                'proposer_role'    => 'student',
                'proposer_user_id' => $userId,
                'target_user_id'   => $lecturerId,
                'title'            => $t,
                'description'      => $descs[$i] ?? '',
                'work_type_id'     => $workTypeId,
            ]);
            if ($id > 0) $created++;
        }

        if ($created > 0) {
            Notification::create(
                $lecturerId,
                'Pengajuan Judul Baru',
                (Auth::user()['name'] ?? 'Mahasiswa') . " mengajukan $created judul tugas akhir untuk Anda review.",
                'info', 'title_proposals', null, '/lecturer/proposals'
            );
            $_SESSION['flash_success'] = "$created judul berhasil diajukan ke dosen.";
        } else {
            $_SESSION['flash_error'] = 'Tidak ada judul yang dikirim.';
        }
        redirect('/student/proposals');
    }

    public static function studentRespond(): void
    {
        Auth::requireRole('student');
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token tidak valid.';
            redirect('/student/proposals');
        }

        $userId = Auth::id();
        $proposalId = (int) ($_POST['proposal_id'] ?? 0);
        $action = ($_POST['action'] ?? '') === 'accept' ? 'accepted' : 'rejected';
        $note = trim($_POST['note'] ?? '');

        $p = TitleProposal::findById($proposalId);
        if (!$p || (int) $p['target_user_id'] !== $userId || $p['status'] !== 'pending') {
            $_SESSION['flash_error'] = 'Proposal tidak ditemukan atau sudah diproses.';
            redirect('/student/proposals');
        }
        if ($p['proposer_role'] !== 'lecturer') {
            $_SESSION['flash_error'] = 'Hanya tawaran dari dosen yang direspon di halaman ini.';
            redirect('/student/proposals');
        }

        if ($action === 'accepted') {
            self::finalizeAccept($p, $userId, $note);
        } else {
            TitleProposal::respond($proposalId, 'rejected', $userId, $note);
            Notification::create(
                (int) $p['proposer_user_id'],
                'Tawaran Judul Ditolak',
                (Auth::user()['name'] ?? 'Mahasiswa') . ' menolak tawaran judul: ' . $p['title'],
                'danger', 'title_proposals', $proposalId, '/lecturer/proposals'
            );
            $_SESSION['flash_success'] = 'Tawaran judul ditolak.';
        }
        redirect('/student/proposals');
    }

    // =================================================================
    // LECTURER
    // =================================================================
    public static function lecturerIndex(): void
    {
        Auth::requireRole('lecturer');
        $userId = Auth::id();
        $db = Database::connection();

        $incoming = TitleProposal::getIncomingPending($userId);
        $outgoing = TitleProposal::getOutgoing($userId);

        $students = $db->query("
            SELECT u.id, u.name, sp.student_number, sp.study_program
            FROM users u
            JOIN student_profiles sp ON sp.user_id = u.id
            LEFT JOIN academic_works aw ON aw.student_id = sp.id
            WHERE u.role = 'student' AND u.is_active = 1 AND aw.id IS NULL
            ORDER BY u.name
        ")->fetchAll();

        $workTypes = $db->query('SELECT id, name FROM work_types ORDER BY name')->fetchAll();

        require dirname(__DIR__) . '/Views/lecturer/proposals.php';
    }

    public static function lecturerSubmit(): void
    {
        Auth::requireRole('lecturer');
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token tidak valid.';
            redirect('/lecturer/proposals');
        }

        $userId = Auth::id();
        $db = Database::connection();

        $studentUserId = (int) ($_POST['student_user_id'] ?? 0);
        $workTypeId = (int) ($_POST['work_type_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $desc = trim($_POST['description'] ?? '');

        $stmt = $db->prepare("
            SELECT sp.id FROM users u
            JOIN student_profiles sp ON sp.user_id = u.id
            WHERE u.id = ? AND u.role = 'student' AND u.is_active = 1
        ");
        $stmt->execute([$studentUserId]);
        $sp = $stmt->fetch();
        if (!$sp) {
            $_SESSION['flash_error'] = 'Mahasiswa tujuan tidak valid.';
            redirect('/lecturer/proposals');
        }
        if (AcademicWork::getByStudentId((int) $sp['id'])) {
            $_SESSION['flash_error'] = 'Mahasiswa tersebut sudah memiliki tugas akhir aktif.';
            redirect('/lecturer/proposals');
        }
        if ($title === '') {
            $_SESSION['flash_error'] = 'Judul wajib diisi.';
            redirect('/lecturer/proposals');
        }

        $id = TitleProposal::create([
            'proposer_role'    => 'lecturer',
            'proposer_user_id' => $userId,
            'target_user_id'   => $studentUserId,
            'title'            => $title,
            'description'      => $desc,
            'work_type_id'     => $workTypeId,
        ]);

        if ($id > 0) {
            Notification::create(
                $studentUserId,
                'Tawaran Judul dari Dosen',
                (Auth::user()['name'] ?? 'Dosen') . ' menawarkan judul: ' . $title,
                'info', 'title_proposals', $id, '/student/proposals'
            );
            $_SESSION['flash_success'] = 'Tawaran judul berhasil dikirim ke mahasiswa.';
        } else {
            $_SESSION['flash_error'] = 'Gagal mengirim tawaran.';
        }
        redirect('/lecturer/proposals');
    }

    public static function lecturerRespond(): void
    {
        Auth::requireRole('lecturer');
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Token tidak valid.';
            redirect('/lecturer/proposals');
        }

        $userId = Auth::id();
        $proposalId = (int) ($_POST['proposal_id'] ?? 0);
        $action = ($_POST['action'] ?? '') === 'accept' ? 'accepted' : 'rejected';
        $note = trim($_POST['note'] ?? '');

        $p = TitleProposal::findById($proposalId);
        if (!$p || (int) $p['target_user_id'] !== $userId || $p['status'] !== 'pending') {
            $_SESSION['flash_error'] = 'Proposal tidak ditemukan atau sudah diproses.';
            redirect('/lecturer/proposals');
        }
        if ($p['proposer_role'] !== 'student') {
            $_SESSION['flash_error'] = 'Hanya pengajuan dari mahasiswa yang direspon di halaman ini.';
            redirect('/lecturer/proposals');
        }

        if ($action === 'accepted') {
            self::finalizeAccept($p, $userId, $note);
        } else {
            TitleProposal::respond($proposalId, 'rejected', $userId, $note);
            Notification::create(
                (int) $p['proposer_user_id'],
                'Pengajuan Judul Ditolak',
                (Auth::user()['name'] ?? 'Dosen') . ' menolak pengajuan judul: ' . $p['title'],
                'danger', 'title_proposals', $proposalId, '/student/proposals'
            );
            $_SESSION['flash_success'] = 'Pengajuan judul ditolak.';
        }
        redirect('/lecturer/proposals');
    }

    // =================================================================
    // CORE: Terima proposal -> buat TA otomatis
    // =================================================================
    private static function finalizeAccept(array $p, int $responderId, string $note): void
    {
        $db = Database::connection();
        $lecturerUserId = $p['proposer_role'] === 'lecturer' ? (int) $p['proposer_user_id'] : (int) $p['target_user_id'];
        $studentUserId  = $p['proposer_role'] === 'student'  ? (int) $p['proposer_user_id'] : (int) $p['target_user_id'];

        try {
            $db->beginTransaction();

            $stmt = $db->prepare('SELECT id FROM student_profiles WHERE user_id = ?');
            $stmt->execute([$studentUserId]);
            $sp = $stmt->fetch();

            $stmt = $db->prepare('SELECT id FROM lecturer_profiles WHERE user_id = ?');
            $stmt->execute([$lecturerUserId]);
            $lp = $stmt->fetch();

            if (!$sp || !$lp) throw new Exception('Profil mahasiswa/dosen tidak lengkap.');
            if (AcademicWork::getByStudentId((int) $sp['id'])) throw new Exception('Mahasiswa sudah memiliki tugas akhir aktif.');

            $workId = AcademicWork::create([
                'student_id'   => (int) $sp['id'],
                'work_type_id' => (int) ($p['work_type_id'] ?: 1),
                'title'        => $p['title'],
                'abstract'     => $p['description'] ?? '',
                'keywords'     => $p['keywords'] ?? '',
                'status'       => AcademicWork::STATUS_PROPOSAL,
                'started_at'   => date('Y-m-d'),
            ]);
            if ($workId <= 0) throw new Exception('Gagal membuat tugas akhir.');

            AcademicWork::addSupervisor($workId, (int) $lp['id'], 'supervisor_1', $responderId);
            TitleProposal::respond((int) $p['id'], 'accepted', $responderId, $note);
            TitleProposal::rejectOtherPendingForStudent($studentUserId, (int) $p['id']);

            $db->commit();

            Notification::create($lecturerUserId, 'Judul Disetujui - TA Terbentuk',
                'Judul "' . $p['title'] . '" disetujui. Tugas akhir otomatis terbentuk.',
                'success', 'title_proposals', (int) $p['id'], '/lecturer/proposals');
            Notification::create($studentUserId, 'Tugas Akhir Terbentuk',
                'Judul "' . $p['title'] . '" resmi menjadi tugas akhir Anda. Selamat mengerjakan!',
                'success', 'academic_works', $workId, '/student/dashboard');

            $_SESSION['flash_success'] = 'Proposal diterima! Tugas akhir otomatis terbentuk.';
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('[ProposalController::finalizeAccept] ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Gagal memproses: ' . $e->getMessage();
        }
    }
}