<?php
declare(strict_types=1);

class PlagiarismController
{
    public static function index(): void
    {
        $threshold = (float)SystemSetting::get('plagiarism_threshold', 30);
        $rows = PlagiarismCheck::listWorks();
        foreach ($rows as &$r) {
            $r['check'] = PlagiarismCheck::latestForWork((int)$r['id']);
            $r['status'] = PlagiarismCheck::statusForWork((int)$r['id'], $threshold);
        }
        unset($r);
        require dirname(__DIR__) . '/Views/admin/plagiarism.php';
    }

    public static function store(): void
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) { flash_set('error', 'Token tidak valid.'); redirect('/admin/plagiarism'); }

        $versionId = (int)($_POST['document_version_id'] ?? 0);
        $score = (float)($_POST['similarity_score'] ?? -1);
        $provider = trim($_POST['provider'] ?? 'Turnitin');
        $reportUrl = trim($_POST['report_url'] ?? '');

        if ($versionId <= 0 || $score < 0 || $score > 100) {
            flash_set('error', 'Data cek plagiasi tidak valid.');
            redirect('/admin/plagiarism');
        }

        PlagiarismCheck::record([
            'document_version_id' => $versionId,
            'similarity_score'    => $score,
            'provider'            => $provider,
            'report_url'          => $reportUrl ?: null,
            'checked_by'          => Auth::id(),
        ]);

        // Notifikasi mahasiswa pemilik dokumen
        $db = Database::connection();
        $stmt = $db->prepare('
            SELECT sp.user_id, d.academic_work_id FROM document_versions dv
            JOIN documents d ON d.id = dv.document_id
            JOIN academic_works aw ON aw.id = d.academic_work_id
            JOIN student_profiles sp ON sp.id = aw.student_id
            WHERE dv.id = ?
        ');
        $stmt->execute([$versionId]);
        $row = $stmt->fetch();
        if ($row) {
            $threshold = (float)SystemSetting::get('plagiarism_threshold', 30);
            $passed = $score <= $threshold;
            Notification::create(
                (int)$row['user_id'],
                $passed ? 'Cek Plagiasi: LOLOS' : 'Cek Plagiasi: TIDAK LOLOS',
                'Skor similarity dokumen Anda: ' . number_format($score, 2) . '% (ambang ' . number_format($threshold, 2) . '%). ' . ($passed ? 'Dokumen memenuhi syarat sidang.' : 'Silakan lakukan revisi dan unggah versi baru.'),
                $passed ? 'success' : 'danger',
                'plagiarism_checks', $versionId, '/student/history'
            );
        }

        flash_set('success', 'Hasil cek plagiasi (' . number_format($score, 2) . '%) berhasil dicatat.');
        redirect('/admin/plagiarism');
    }
}