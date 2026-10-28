<?php
declare(strict_types=1);

class LecturerExpertise
{
    public static function getForLecturer(int $lecturerProfileId): array
    {
        try {
            $db = Database::connection();
            $stmt = $db->prepare('SELECT keyword FROM lecturer_expertise WHERE lecturer_id = ?');
            $stmt->execute([$lecturerProfileId]);
            return array_column($stmt->fetchAll(), 'keyword');
        } catch (Throwable $e) { return []; }
    }

    public static function setForLecturer(int $lecturerProfileId, array $keywords): void
    {
        try {
            $db = Database::connection();
            $db->beginTransaction();
            $db->prepare('DELETE FROM lecturer_expertise WHERE lecturer_id = ?')->execute([$lecturerProfileId]);
            $stmt = $db->prepare('INSERT INTO lecturer_expertise (lecturer_id, keyword) VALUES (?, ?)');
            foreach ($keywords as $k) {
                $k = trim($k);
                if ($k !== '') $stmt->execute([$lecturerProfileId, mb_substr($k, 0, 100)]);
            }
            $db->commit();
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
        }
    }

    /**
     * SMART RECOMMENDATION: cari dosen dengan kecocokan keyword tertinggi
     */
    public static function recommend(string $title, string $abstract = '', int $topN = 3): array
    {
        try {
            $db = Database::connection();
            $text = strtolower($title . ' ' . $abstract);
            $stmt = $db->prepare("
                SELECT le.lecturer_id, le.keyword,
                    lp.user_id, u.name, lp.academic_degree, lp.department
                FROM lecturer_expertise le
                JOIN lecturer_profiles lp ON le.lecturer_id = lp.id
                JOIN users u ON lp.user_id = u.id
                WHERE u.is_active = 1
            ");
            $stmt->execute();
            $rows = $stmt->fetchAll();

            $scores = [];
            foreach ($rows as $r) {
                $lid = (int)$r['lecturer_id'];
                if (!isset($scores[$lid])) {
                    $scores[$lid] = ['lecturer_id' => $lid, 'user_id' => (int)$r['user_id'], 'name' => $r['name'], 'degree' => $r['academic_degree'], 'department' => $r['department'], 'matches' => [], 'score' => 0];
                }
                $kw = strtolower($r['keyword']);
                if (str_contains($text, $kw)) {
                    $scores[$lid]['matches'][] = $r['keyword'];
                    $scores[$lid]['score']++;
                }
            }

            usort($scores, fn($a,$b) => $b['score'] <=> $a['score']);
            return array_slice(array_filter($scores, fn($s) => $s['score'] > 0), 0, $topN);
        } catch (Throwable $e) { error_log('[LE::recommend] '.$e->getMessage()); return []; }
    }
}