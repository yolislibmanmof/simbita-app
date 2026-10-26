<?php
declare(strict_types=1);

class MilestoneController
{
    /**
     * Scan milestone yang mendekati deadline & kirim notifikasi
     * Dipanggil manual (tombol admin) atau via cron job
     * Route: POST /api/milestones/scan
     */
    public static function scanDeadlines(): void
    {
        Auth::requireRole('admin'); // Hanya admin yang bisa trigger manual
        
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($csrfToken)) {
            json_response(['error' => 'Token tidak valid'], 419);
        }

        try {
            $result = self::runDeadlineScan();
            json_response([
                'success' => true,
                'scanned' => $result['scanned'],
                'notifications_sent' => $result['sent'],
                'message' => sprintf('Scan selesai. %d milestone diproses, %d notifikasi dikirim.', 
                    $result['scanned'], $result['sent'])
            ]);
        } catch (Exception $e) {
            error_log('[Milestone Scan Error] ' . $e->getMessage());
            json_response(['error' => 'Gagal menjalankan scan'], 500);
        }
    }

    /**
     * Core logic untuk scan deadline (bisa dipanggil dari cron atau controller)
     * @return array ['scanned' => int, 'sent' => int, 'details' => array]
     */
    public static function runDeadlineScan(): array
    {
        $db = Database::connection();
        $today = date('Y-m-d');
        $daysAhead = [7, 3, 1]; // Trigger notifikasi di H-7, H-3, H-1
        
        // Ambil milestone aktif yang belum selesai dengan due_date H-7, H-3, atau H-1
        $placeholders = implode(',', array_fill(0, count($daysAhead), '?'));
        $targetDates = array_map(fn($d) => date('Y-m-d', strtotime("+{$d} days")), $daysAhead);
        
        $stmt = $db->prepare("
            SELECT wm.*, aw.title AS work_title, aw.student_id,
                   sp.user_id AS student_user_id, u.name AS student_name, u.email AS student_email
            FROM work_milestones wm
            JOIN academic_works aw ON wm.academic_work_id = aw.id
            JOIN student_profiles sp ON aw.student_id = sp.id
            JOIN users u ON sp.user_id = u.id
            WHERE wm.completed_at IS NULL
              AND wm.due_date IN ($placeholders)
              AND aw.status NOT IN ('completed', 'cancelled')
        ");
        $stmt->execute($targetDates);
        $milestones = $stmt->fetchAll();

        $scanned = count($milestones);
        $sent = 0;
        $details = [];

        foreach ($milestones as $m) {
            $daysLeft = (int)((strtotime($m['due_date']) - strtotime($today)) / 86400);
            $urgency = $daysLeft <= 1 ? 'critical' : ($daysLeft <= 3 ? 'high' : 'normal');
            
            $milestoneLabel = match($m['milestone_type']) {
                'proposal' => 'Pengajuan Proposal',
                'feasibility_test' => 'Uji Kelayakan',
                'writing' => 'Penulisan',
                'progress_exam' => 'Ujian Kemajuan',
                'revision' => 'Revisi',
                'closed_exam' => 'Ujian Tertutup',
                'defense_preparation' => 'Persiapan Sidang',
                'defense' => 'Sidang',
                'final_revision' => 'Revisi Akhir',
                'publication' => 'Publikasi',
                default => ucfirst(str_replace('_', ' ', $m['milestone_type']))
            };

            // === NOTIFIKASI KE MAHASISWA ===
            $notifSent = self::sendIfNotExists(
                (int)$m['student_user_id'],
                "⏰ Deadline {$milestoneLabel} {$daysLeft} Hari Lagi!",
                sprintf('Deadline "%s" untuk TA "%s" adalah tanggal %s (%d hari lagi). Segera selesaikan!',
                    $m['title'],
                    $m['work_title'],
                    date('d M Y', strtotime($m['due_date'])),
                    $daysLeft
                ),
                'deadline',
                'work_milestones',
                (int)$m['id'],
                '/student/dashboard',
                $urgency
            );
            if ($notifSent) $sent++;

            // === NOTIFIKASI KE DOSEN PEMBIMBING ===
            $stmtSup = $db->prepare('
                SELECT lp.user_id, u.name AS lecturer_name
                FROM supervision_assignments sa
                JOIN lecturer_profiles lp ON sa.lecturer_id = lp.id
                JOIN users u ON lp.user_id = u.id
                WHERE sa.academic_work_id = ? AND sa.is_active = 1
            ');
            $stmtSup->execute([$m['academic_work_id']]);
            $supervisors = $stmtSup->fetchAll();

            foreach ($supervisors as $sup) {
                $notifSentSup = self::sendIfNotExists(
                    (int)$sup['user_id'],
                    "⏰ Mahasiswa Deadline {$daysLeft} Hari Lagi",
                    sprintf('Mahasiswa %s memiliki deadline "%s" untuk "%s" pada %s. Mohon monitoring.',
                        $m['student_name'],
                        $m['title'],
                        $m['work_title'],
                        date('d M Y', strtotime($m['due_date']))
                    ),
                    'deadline',
                    'work_milestones',
                    (int)$m['id'],
                    '/lecturer/students',
                    $urgency
                );
                if ($notifSentSup) $sent++;
            }

            $details[] = [
                'milestone' => $m['title'],
                'student' => $m['student_name'],
                'days_left' => $daysLeft,
                'due_date' => $m['due_date'],
                'urgency' => $urgency,
            ];
        }

        // Log aktivitas scan
        error_log(sprintf('[Milestone Scan] Scanned: %d, Notifications sent: %d', $scanned, $sent));

        return ['scanned' => $scanned, 'sent' => $sent, 'details' => $details];
    }

    /**
     * Kirim notifikasi HANYA jika belum ada notifikasi serupa hari ini
     * Mencegah spam notifikasi
     */
    private static function sendIfNotExists(
        int $userId,
        string $title,
        string $message,
        string $type,
        string $relatedTable,
        int $relatedId,
        string $link,
        string $urgency = 'normal'
    ): bool {
        $db = Database::connection();
        $today = date('Y-m-d');
        
        // Cek apakah sudah ada notifikasi serupa hari ini
        $stmt = $db->prepare('
            SELECT id FROM notifications 
            WHERE user_id = ? 
              AND type = ? 
              AND related_table = ? 
              AND related_id = ?
              AND DATE(created_at) = ?
            LIMIT 1
        ');
        $stmt->execute([$userId, $type, $relatedTable, $relatedId, $today]);
        
        if ($stmt->fetch()) {
            return false; // Sudah ada, skip
        }

        // Tambahkan emoji urgency
        $urgencyPrefix = match($urgency) {
            'critical' => '🚨 ',
            'high' => '⚠️ ',
            default => ''
        };

        Notification::create(
            $userId,
            $urgencyPrefix . $title,
            $message,
            $type,
            $relatedTable,
            $relatedId,
            $link
        );
        
        return true;
    }

    /**
     * Ambil milestone mendatang untuk dashboard mahasiswa
     * @param int $studentProfileId
     * @return array
     */
    public static function getUpcomingForStudent(int $studentProfileId, int $limit = 5): array
    {
        $db = Database::connection();
        $stmt = $db->prepare('
            SELECT wm.*, aw.title AS work_title,
                   DATEDIFF(wm.due_date, CURDATE()) AS days_left
            FROM work_milestones wm
            JOIN academic_works aw ON wm.academic_work_id = aw.id
            WHERE aw.student_id = ?
              AND wm.completed_at IS NULL
              AND wm.due_date >= CURDATE()
            ORDER BY wm.due_date ASC
            LIMIT ?
        ');
        $stmt->execute([$studentProfileId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Ambil milestone mendatang untuk semua mahasiswa bimbingan dosen
     * @param int $lecturerProfileId
     * @return array
     */
    public static function getUpcomingForLecturer(int $lecturerProfileId, int $limit = 10): array
    {
        $db = Database::connection();
        $stmt = $db->prepare('
            SELECT wm.*, aw.title AS work_title,
                   u.name AS student_name,
                   sp.student_number,
                   DATEDIFF(wm.due_date, CURDATE()) AS days_left
            FROM work_milestones wm
            JOIN academic_works aw ON wm.academic_work_id = aw.id
            JOIN supervision_assignments sa ON aw.id = sa.academic_work_id
            JOIN student_profiles sp ON aw.student_id = sp.id
            JOIN users u ON sp.user_id = u.id
            WHERE sa.lecturer_id = ?
              AND sa.is_active = 1
              AND wm.completed_at IS NULL
              AND wm.due_date >= CURDATE()
            ORDER BY wm.due_date ASC
            LIMIT ?
        ');
        $stmt->execute([$lecturerProfileId, $limit]);
        return $stmt->fetchAll();
    }
}