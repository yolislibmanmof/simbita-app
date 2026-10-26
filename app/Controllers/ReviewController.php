<?php
declare(strict_types=1);

class ReviewController
{
    public static function saveAnnotation(): void
    {
        Auth::requireRole('lecturer', 'admin');
        
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($csrfToken)) {
            json_response(['error' => 'Token tidak valid'], 419);
        }

        $versionId = (int)($_POST['version_id'] ?? 0);
        $page = (int)($_POST['page_number'] ?? 0);
        $xPct = (float)($_POST['pos_x_pct'] ?? 0);
        $yPct = (float)($_POST['pos_y_pct'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');
        $priority = $_POST['priority'] ?? 'important';

        if ($versionId <= 0 || $page <= 0 || empty($comment)) {
            json_response(['error' => 'Data tidak lengkap'], 400);
        }

        try {
            $db = Database::connection();
            
            $stmt = $db->prepare('SELECT id FROM reviews WHERE document_version_id = ? AND reviewer_id = ? ORDER BY created_at DESC LIMIT 1');
            $stmt->execute([$versionId, Auth::id()]);
            $review = $stmt->fetch();

            if (!$review) {
                $stmt = $db->prepare('INSERT INTO reviews (document_version_id, reviewer_id, review_status, created_at) VALUES (?, ?, ?, NOW())');
                $stmt->execute([$versionId, Auth::id(), 'in_review']);
                $reviewId = (int)$db->lastInsertId();
            } else {
                $reviewId = (int)$review['id'];
            }

            $stmt = $db->prepare('INSERT INTO review_comments (review_id, page_number, position_x, position_y, comment_text, priority, is_resolved, created_at) VALUES (?, ?, ?, ?, ?, ?, 0, NOW())');
            $stmt->execute([$reviewId, $page, $xPct, $yPct, $comment, $priority]);

            json_response(['success' => true, 'id' => (int)$db->lastInsertId()]);

        } catch (Exception $e) {
            error_log('[Save Annotation Error] ' . $e->getMessage());
            json_response(['error' => 'Gagal menyimpan anotasi'], 500);
        }
    }

    public static function getComments(): void
    {
        Auth::requireRole('lecturer', 'student', 'admin');
        
        $versionId = (int)($_GET['version_id'] ?? 0);
        if (!$versionId) {
            json_response(['error' => 'Version ID required'], 400);
        }

        try {
            $db = Database::connection();
            
            $stmt = $db->prepare('SELECT rc.id, rc.page_number, rc.position_x as pos_x_pct, rc.position_y as pos_y_pct, rc.comment_text as comment, rc.priority, rc.created_at, u.name as author_name FROM review_comments rc JOIN reviews r ON rc.review_id = r.id JOIN users u ON r.reviewer_id = u.id WHERE r.document_version_id = ? ORDER BY rc.page_number ASC, rc.created_at ASC');
            $stmt->execute([$versionId]);
            
            json_response(['success' => true, 'annotations' => $stmt->fetchAll()]);

        } catch (Exception $e) {
            json_response(['error' => 'Gagal mengambil anotasi'], 500);
        }
    }

    public static function decide(): void
    {
        Auth::requireRole('lecturer', 'admin');
        
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($csrfToken)) {
            json_response(['error' => 'Token tidak valid'], 419);
        }

        $versionId = (int)($_POST['document_version_id'] ?? 0);
        $decision = $_POST['decision'] ?? '';
        $note = trim($_POST['general_note'] ?? '');
        $checklist = $_POST['checklist'] ?? [];

        if ($versionId <= 0 || !in_array($decision, ['approved', 'needs_revision', 'rejected'], true)) {
            json_response(['error' => 'Data tidak valid'], 400);
        }

        try {
            $db = Database::connection();
            $db->beginTransaction();

            $stmt = $db->prepare('SELECT id FROM reviews WHERE document_version_id = ? AND reviewer_id = ? ORDER BY created_at DESC LIMIT 1');
            $stmt->execute([$versionId, Auth::id()]);
            $review = $stmt->fetch();

            if (!$review) {
                $stmt = $db->prepare('INSERT INTO reviews (document_version_id, reviewer_id, review_status, created_at) VALUES (?, ?, ?, NOW())');
                $stmt->execute([$versionId, Auth::id(), 'pending']);
                $reviewId = (int)$db->lastInsertId();
            } else {
                $reviewId = (int)$review['id'];
            }

            $stmt = $db->prepare('UPDATE reviews SET review_status = ?, general_note = ?, reviewed_at = NOW(), updated_at = NOW() WHERE id = ?');
            $stmt->execute([$decision, $note, $reviewId]);

            if (!empty($checklist) && is_array($checklist)) {
                $db->prepare('DELETE FROM review_checklist_results WHERE review_id = ?')->execute([$reviewId]);

                $stmt = $db->prepare('INSERT INTO review_checklist_results (review_id, checklist_item_id, result, note, created_at) VALUES (?, ?, ?, ?, NOW())');
                
                foreach ($checklist as $itemId => $data) {
                    $result = $data['result'] ?? 'pending';
                    $itemNote = $data['note'] ?? null;
                    $stmt->execute([$reviewId, (int)$itemId, $result, $itemNote]);
                }
            }

            $db->commit();
            json_response(['success' => true, 'message' => 'Keputusan berhasil disimpan']);

        } catch (Exception $e) {
            $db->rollBack();
            json_response(['error' => 'Gagal memproses keputusan'], 500);
        }
    }
}