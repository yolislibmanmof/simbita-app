<?php
declare(strict_types=1);

class Analytics
{
    public static function lecturerDashboard(int $lecturerProfileId): array
    {
        try {
            $db = Database::connection();
            $data = [];

            $stmt = $db->prepare("SELECT COUNT(*) t FROM academic_works aw JOIN supervision_assignments sa ON aw.id = sa.academic_work_id WHERE sa.lecturer_id = ? AND sa.is_active = 1");
            $stmt->execute([$lecturerProfileId]); $data['total_students'] = (int)$stmt->fetch()['t'];

            $stmt = $db->prepare("SELECT COUNT(*) t FROM academic_works aw JOIN supervision_assignments sa ON aw.id = sa.academic_work_id WHERE sa.lecturer_id = ? AND sa.is_active = 1 AND aw.status = 'completed'");
            $stmt->execute([$lecturerProfileId]); $data['completed'] = (int)$stmt->fetch()['t'];

            $stmt = $db->prepare("SELECT COUNT(*) t FROM reviews r JOIN supervision_assignments sa ON sa.academic_work_id IN (SELECT academic_work_id FROM documents WHERE id = (SELECT document_id FROM document_versions WHERE id = r.document_version_id)) WHERE r.reviewer_id = (SELECT user_id FROM lecturer_profiles WHERE id = ?) AND r.review_status = 'pending'");
            $stmt->execute([$lecturerProfileId]); $data['pending_reviews'] = (int)$stmt->fetch()['t'];

            $stmt = $db->prepare("SELECT AVG(DATEDIFF(r.reviewed_at, r.created_at)) avg_days FROM reviews r WHERE r.reviewer_id = (SELECT user_id FROM lecturer_profiles WHERE id = ?) AND r.reviewed_at IS NOT NULL");
            $stmt->execute([$lecturerProfileId]);
            $avg = $stmt->fetch()['avg_days']; $data['avg_review_days'] = $avg !== null ? round((float)$avg, 1) : 0;

            $stmt = $db->prepare("SELECT u.name, sp.student_number, aw.title, aw.status FROM academic_works aw JOIN supervision_assignments sa ON aw.id = sa.academic_work_id JOIN student_profiles sp ON aw.student_id = sp.id JOIN users u ON sp.user_id = u.id WHERE sa.lecturer_id = ? AND sa.is_active = 1 ORDER BY aw.updated_at DESC LIMIT 5");
            $stmt->execute([$lecturerProfileId]); $data['most_active'] = $stmt->fetchAll();

            return $data;
        } catch (Throwable $e) { error_log('[Analytics] '.$e->getMessage()); return ['total_students' => 0, 'completed' => 0, 'pending_reviews' => 0, 'avg_review_days' => 0, 'most_active' => []]; }
    }
}