<?php
declare(strict_types=1);

class CompareController
{
    /**
     * Tampilkan halaman perbandingan versi
     */
    public static function showVersionCompare(): void
    {
        Auth::requireRole('student');
        
        $documentId = (int)($_GET['document_id'] ?? 0);
        
        if (!$documentId) {
            redirect('/student/dashboard');
        }

        $db = Database::connection();
        
        // Ambil semua versi dokumen
        $stmt = $db->prepare('
            SELECT * FROM document_versions
            WHERE document_id = ?
            ORDER BY version_number DESC
        ');
        $stmt->execute([$documentId]);
        $versions = $stmt->fetchAll();

        require dirname(__DIR__) . '/Views/student/version_compare.php';
    }

    /**
     * Ambil komentar untuk perbandingan
     */
    public static function getComments(): void
    {
        Auth::start();
        
        $versionId = (int)($_GET['version_id'] ?? 0);
        
        if (!$versionId) {
            json_response(['error' => 'Invalid version ID'], 400);
        }

        $db = Database::connection();
        $stmt = $db->prepare('
            SELECT rc.*, r.review_status, u.name as reviewer_name
            FROM review_comments rc
            JOIN reviews r ON rc.review_id = r.id
            JOIN users u ON r.reviewer_id = u.id
            WHERE r.document_version_id = ?
            ORDER BY rc.page_number, rc.created_at
        ');
        $stmt->execute([$versionId]);
        $comments = $stmt->fetchAll();

        json_response(['comments' => $comments]);
    }

    /**
     * Resolve komentar
     */
    public static function resolveComment(): void
    {
        Auth::requireRole('student');
        
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($csrfToken)) {
            json_response(['error' => 'Token tidak valid'], 419);
        }

        $commentId = (int)($_POST['comment_id'] ?? 0);
        
        if (!$commentId) {
            json_response(['error' => 'Invalid comment ID'], 400);
        }

        $db = Database::connection();
        $stmt = $db->prepare('
            UPDATE review_comments 
            SET is_resolved = 1, resolved_at = NOW()
            WHERE id = ?
        ');
        $stmt->execute([$commentId]);

        json_response(['success' => true, 'message' => 'Komentar ditandai selesai']);
    }
}