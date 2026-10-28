<?php
declare(strict_types=1);

class ReviewController
{
    /**
     * API Endpoint: Simpan anotasi/komentar pada halaman PDF
     * Route: POST /api/comments/save
     */
    public static function saveAnnotation(): void
    {
        ini_set('display_errors', '0');
        
        Auth::requireRole('lecturer', 'admin');
        
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($csrfToken)) {
            json_response(['error' => 'Token tidak valid'], 419);
        }

        $inputId = (int)($_POST['version_id'] ?? 0);
        $page = (int)($_POST['page_number'] ?? 0);
        $xPct = (float)($_POST['pos_x_pct'] ?? 0);
        $yPct = (float)($_POST['pos_y_pct'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');
        $priority = $_POST['priority'] ?? 'important';

        if (!in_array($priority, ['minor', 'important', 'mandatory'], true)) {
            $priority = 'important';
        }

        if ($inputId <= 0 || $page <= 0 || empty($comment)) {
            json_response(['error' => 'Data tidak lengkap'], 400);
        }

        try {
            $db = Database::connection();
            $userId = Auth::id();

            $stmt = $db->prepare('SELECT id, document_id FROM document_versions WHERE id = ?');
            $stmt->execute([$inputId]);
            $version = $stmt->fetch();

            if ($version) {
                $versionId = (int)($version['id'] ?? 0);
                $docId = (int)($version['document_id'] ?? 0);
            } else {
                $stmt = $db->prepare('SELECT id, document_id FROM document_versions WHERE document_id = ? ORDER BY id DESC LIMIT 1');
                $stmt->execute([$inputId]);
                $version = $stmt->fetch();
                
                if (!$version) {
                    json_response(['error' => 'Dokumen tidak memiliki versi'], 404);
                }
                $versionId = (int)($version['id'] ?? 0);
                $docId = (int)($version['document_id'] ?? 0);
            }

            $role = Auth::role();
            $allowed = ($role === 'admin');

            if (!$allowed) {
                $stmt = $db->prepare('
                    SELECT 1 FROM documents d
                    JOIN academic_works aw ON d.academic_work_id = aw.id
                    JOIN supervision_assignments sa ON aw.id = sa.academic_work_id
                    JOIN lecturer_profiles lp ON sa.lecturer_id = lp.id
                    WHERE d.id = ? AND lp.user_id = ? AND sa.is_active = 1
                ');
                $stmt->execute([$docId, $userId]);
                $allowed = (bool)$stmt->fetch();
            }

            if (!$allowed) {
                $stmt = $db->prepare('SELECT 1 FROM reviews WHERE document_version_id = ? AND reviewer_id = ?');
                $stmt->execute([$versionId, $userId]);
                $allowed = (bool)$stmt->fetch();
            }

            if (!$allowed) {
                $stmt = $db->prepare('SELECT 1 FROM lecturer_profiles WHERE user_id = ?');
                $stmt->execute([$userId]);
                $allowed = (bool)$stmt->fetch();
            }

            if (!$allowed) {
                json_response(['error' => 'Akses ditolak'], 403);
            }

            $stmt = $db->prepare('SELECT id FROM reviews WHERE document_version_id = ? AND reviewer_id = ? ORDER BY created_at DESC LIMIT 1');
            $stmt->execute([$versionId, $userId]);
            $review = $stmt->fetch();

            if (!$review) {
                $stmt = $db->prepare('INSERT INTO reviews (document_version_id, reviewer_id, review_status, created_at) VALUES (?, ?, ?, NOW())');
                $stmt->execute([$versionId, $userId, 'in_review']);
                $reviewId = (int)$db->lastInsertId();
            } else {
                $reviewId = (int)$review['id'];
            }

            try {
                $stmt = $db->prepare('
                    INSERT INTO review_comments 
                    (review_id, document_version_id, page_number, position_x, position_y, comment_text, priority, is_resolved, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW())
                ');
                $stmt->execute([$reviewId, $versionId, $page, $xPct, $yPct, $comment, $priority]);
                $commentId = (int)$db->lastInsertId();
            } catch (\Throwable $e) {
                $stmt = $db->prepare('
                    INSERT INTO document_comments 
                    (document_version_id, page_number, pos_x_pct, pos_y_pct, comment, priority, author_id, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ');
                $stmt->execute([$versionId, $page, $xPct, $yPct, $comment, $priority, $userId]);
                $commentId = (int)$db->lastInsertId();
            }

            json_response([
                'success' => true, 
                'id' => $commentId,
                'message' => 'Anotasi berhasil disimpan'
            ]);

        } catch (\Throwable $e) {
            error_log('[Save Annotation Error] ' . $e->getMessage());
            json_response(['error' => 'Gagal menyimpan anotasi'], 500);
        }
    }

    /**
     * API Endpoint: Ambil semua anotasi untuk suatu versi dokumen
     * Route: GET /api/comments?version_id=X
     */
    public static function getComments(): void
    {
        Auth::requireRole('lecturer', 'student', 'admin');
        
        $versionId = (int)($_GET['version_id'] ?? 0);
        if (!$versionId) {
            json_response(['error' => 'Version ID required'], 400);
        }

        try {
            $db = Database::connection();
            
            $canAccess = false;
            $userId = Auth::id();
            $role = Auth::role();

            if ($role === 'admin') {
                $canAccess = true;
            } elseif ($role === 'lecturer') {
                $stmt = $db->prepare('
                    SELECT 1 FROM document_versions dv
                    JOIN documents d ON dv.document_id = d.id
                    JOIN academic_works aw ON d.academic_work_id = aw.id
                    JOIN supervision_assignments sa ON aw.id = sa.academic_work_id
                    JOIN lecturer_profiles lp ON sa.lecturer_id = lp.id
                    WHERE dv.id = ? AND lp.user_id = ? AND sa.is_active = 1 LIMIT 1
                ');
                $stmt->execute([$versionId, $userId]);
                $canAccess = (bool)$stmt->fetch();

                if (!$canAccess) {
                    $stmt = $db->prepare('SELECT 1 FROM reviews WHERE document_version_id = ? AND reviewer_id = ?');
                    $stmt->execute([$versionId, $userId]);
                    $canAccess = (bool)$stmt->fetch();
                }
                if (!$canAccess) {
                    $stmt = $db->prepare('SELECT 1 FROM lecturer_profiles WHERE user_id = ?');
                    $stmt->execute([$userId]);
                    $canAccess = (bool)$stmt->fetch();
                }
            } elseif ($role === 'student') {
                $stmt = $db->prepare('
                    SELECT 1 FROM document_versions dv
                    JOIN documents d ON dv.document_id = d.id
                    JOIN academic_works aw ON d.academic_work_id = aw.id
                    JOIN student_profiles sp ON aw.student_id = sp.id
                    WHERE dv.id = ? AND sp.user_id = ? LIMIT 1
                ');
                $stmt->execute([$versionId, $userId]);
                $canAccess = (bool)$stmt->fetch();
            }

            if (!$canAccess) {
                json_response(['error' => 'Akses ditolak'], 403);
            }

            try {
                $stmt = $db->prepare('
                    SELECT rc.id, rc.page_number, rc.position_x as pos_x_pct, rc.position_y as pos_y_pct, 
                           rc.comment_text as comment, rc.priority, rc.is_resolved, rc.resolved_at,
                           rc.created_at, u.name as author_name
                    FROM review_comments rc
                    JOIN reviews r ON rc.review_id = r.id
                    JOIN users u ON r.reviewer_id = u.id
                    WHERE r.document_version_id = ?
                    ORDER BY rc.page_number ASC, rc.created_at ASC
                ');
                $stmt->execute([$versionId]);
            } catch (\Throwable $e) {
                $stmt = $db->prepare('
                    SELECT dc.id, dc.page_number, dc.pos_x_pct, dc.pos_y_pct, 
                           dc.comment, dc.priority, 0 as is_resolved, NULL as resolved_at,
                           dc.created_at, u.name as author_name
                    FROM document_comments dc
                    JOIN users u ON dc.author_id = u.id
                    WHERE dc.document_version_id = ?
                    ORDER BY dc.page_number ASC, dc.created_at ASC
                ');
                $stmt->execute([$versionId]);
            }
            
            json_response([
                'success' => true,
                'annotations' => $stmt->fetchAll()
            ]);

        } catch (\Throwable $e) {
            error_log('[Get Comments Error] ' . $e->getMessage());
            json_response(['error' => 'Gagal mengambil anotasi'], 500);
        }
    }

    /**
     * API Endpoint: Tandai komentar sudah/belum ditindaklanjuti
     * Route: POST /api/comments/resolve
     */
    public static function resolveComment(): void
    {
        Auth::requireRole('student', 'lecturer', 'admin');
        
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($csrfToken)) {
            json_response(['error' => 'Token tidak valid'], 419);
        }

        $commentId = (int)($_POST['comment_id'] ?? 0);
        $resolve = ($_POST['resolve'] ?? '1') === '1';

        if ($commentId <= 0) {
            json_response(['error' => 'Comment ID tidak valid'], 400);
        }

        try {
            $db = Database::connection();
            
            $stmt = $db->prepare('
                SELECT rc.*, r.document_version_id, r.reviewer_id,
                       dv.document_id, d.academic_work_id,
                       sp.user_id AS student_user_id
                FROM review_comments rc
                JOIN reviews r ON rc.review_id = r.id
                JOIN document_versions dv ON r.document_version_id = dv.id
                JOIN documents d ON dv.document_id = d.id
                JOIN academic_works aw ON d.academic_work_id = aw.id
                JOIN student_profiles sp ON aw.student_id = sp.id
                WHERE rc.id = ?
            ');
            $stmt->execute([$commentId]);
            $comment = $stmt->fetch();

            if (!$comment) {
                json_response(['error' => 'Komentar tidak ditemukan'], 404);
            }

            $userId = Auth::id();
            $role = Auth::role();
            $canModify = false;

            if ($role === 'admin') {
                $canModify = true;
            } elseif ($role === 'student' && (int)$comment['student_user_id'] === $userId) {
                $canModify = true;
            } elseif ($role === 'lecturer' && (int)$comment['reviewer_id'] === $userId) {
                $canModify = true;
            }

            if (!$canModify) {
                json_response(['error' => 'Akses ditolak'], 403);
            }

            if ($resolve) {
                $stmt = $db->prepare('
                    UPDATE review_comments 
                    SET is_resolved = 1, resolved_at = NOW(), updated_at = NOW()
                    WHERE id = ?
                ');
            } else {
                $stmt = $db->prepare('
                    UPDATE review_comments 
                    SET is_resolved = 0, resolved_at = NULL, updated_at = NOW()
                    WHERE id = ?
                ');
            }
            $stmt->execute([$commentId]);

            json_response([
                'success' => true, 
                'is_resolved' => $resolve,
                'message' => $resolve ? 'Ditandai sudah ditindaklanjuti' : 'Ditandai belum ditindaklanjuti'
            ]);

        } catch (Exception $e) {
            error_log('[Resolve Comment Error] ' . $e->getMessage());
            json_response(['error' => 'Gagal memperbarui status komentar'], 500);
        }
    }

    /**
     * API Endpoint: Submit keputusan final review & checklist
     * Route: POST /api/reviews/decide
     */
    public static function decide(): void
    {
        ini_set('display_errors', '0');
        
        Auth::requireRole('lecturer', 'admin');
        
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($csrfToken)) {
            json_response(['error' => 'Token tidak valid'], 419);
        }

        $inputId = (int)($_POST['document_version_id'] ?? 0);
        $decision = $_POST['decision'] ?? '';
        $note = trim($_POST['general_note'] ?? '');
        $checklist = $_POST['checklist'] ?? [];

        if ($inputId <= 0 || !in_array($decision, ['approved', 'needs_revision', 'rejected'], true)) {
            json_response(['error' => 'Data tidak valid'], 400);
        }

        try {
            $db = Database::connection();
            
            // ===== VALIDASI: Pastikan document_version_id ada =====
            $stmt = $db->prepare('SELECT id, document_id FROM document_versions WHERE id = ?');
            $stmt->execute([$inputId]);
            $version = $stmt->fetch();
            
            if (!$version) {
                // Fallback: mungkin yang dikirim adalah document_id, cari version_id
                $stmt = $db->prepare('SELECT id, document_id FROM document_versions WHERE document_id = ? AND is_current = 1 ORDER BY id DESC LIMIT 1');
                $stmt->execute([$inputId]);
                $version = $stmt->fetch();
                
                if (!$version) {
                    json_response(['error' => 'Dokumen tidak ditemukan di database'], 404);
                }
            }
            
            $versionId = (int)$version['id'];
            $docId = (int)$version['document_id'];

            $db->beginTransaction();
            $userId = Auth::id();

            // Cari atau buat review record
            $stmt = $db->prepare('SELECT id FROM reviews WHERE document_version_id = ? AND reviewer_id = ? ORDER BY created_at DESC LIMIT 1');
            $stmt->execute([$versionId, $userId]);
            $review = $stmt->fetch();

            if (!$review) {
                // Pastikan foreign key valid sebelum insert
                try {
                    $stmt = $db->prepare('INSERT INTO reviews (document_version_id, reviewer_id, review_status, created_at) VALUES (?, ?, ?, NOW())');
                    $stmt->execute([$versionId, $userId, 'pending']);
                    $reviewId = (int)$db->lastInsertId();
                } catch (\PDOException $fkError) {
                    // Jika foreign key gagal, rollback dan beri pesan jelas
                    $db->rollBack();
                    error_log('[FK Error] ' . $fkError->getMessage());
                    json_response([
                        'error' => 'Gagal membuat review: ID dokumen tidak valid. Silakan refresh halaman dan coba lagi.',
                        'debug' => 'Version ID: ' . $versionId . ', Doc ID: ' . $docId
                    ], 500);
                }
            } else {
                $reviewId = (int)$review['id'];
            }

            // Update status review
            $stmt = $db->prepare('UPDATE reviews SET review_status = ?, general_note = ?, reviewed_at = NOW(), updated_at = NOW() WHERE id = ?');
            $stmt->execute([$decision, $note, $reviewId]);

            // Simpan checklist (abaikan jika tabel belum ada)
            if (!empty($checklist) && is_array($checklist)) {
                try {
                    $db->prepare('DELETE FROM review_checklist_results WHERE review_id = ?')->execute([$reviewId]);
                    $stmt = $db->prepare('INSERT INTO review_checklist_results (review_id, checklist_item_id, result, note, created_at) VALUES (?, ?, ?, ?, NOW())');
                    foreach ($checklist as $itemId => $data) {
                        $result = $data['result'] ?? 'pending';
                        $itemNote = $data['note'] ?? null;
                        if (!in_array($result, ['pending', 'complete', 'incomplete', 'not_applicable'], true)) {
                            $result = 'pending';
                        }
                        $stmt->execute([$reviewId, (int)$itemId, $result, $itemNote]);
                    }
                } catch (\Throwable $e) {
                    error_log('[Checklist Error] ' . $e->getMessage());
                }
            }

            // Notifikasi (abaikan error)
            try {
                $stmt = $db->prepare('
                    SELECT sp.user_id, u.name AS student_name
                    FROM document_versions dv
                    JOIN documents d ON dv.document_id = d.id
                    JOIN academic_works aw ON d.academic_work_id = aw.id
                    JOIN student_profiles sp ON aw.student_id = sp.id
                    JOIN users u ON sp.user_id = u.id
                    WHERE dv.id = ? LIMIT 1
                ');
                $stmt->execute([$versionId]);
                $student = $stmt->fetch();

                if ($student) {
                    $statusLabel = match($decision) {
                        'approved' => 'Disetujui',
                        'needs_revision' => 'Perlu Revisi',
                        'rejected' => 'Ditolak',
                        default => 'Direview'
                    };

                    Notification::create(
                        (int)$student['user_id'],
                        "Dokumen Anda Telah {$statusLabel}",
                        sprintf('%s telah menyelesaikan review dengan keputusan: %s', 
                            Auth::user()['name'] ?? 'Dosen', $statusLabel),
                        'review',
                        'document_versions',
                        $versionId,
                        '/student/history'
                    );
                }
            } catch (\Throwable $e) {
                error_log('[Notification Error] ' . $e->getMessage());
            }

            $db->commit();
            json_response(['success' => true, 'message' => 'Keputusan berhasil disimpan']);

        } catch (\Throwable $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            
            error_log('[Decide Review Error] ' . $e->getMessage());
            
            $errorMsg = defined('APP_DEBUG') && APP_DEBUG 
                ? $e->getMessage() 
                : 'Gagal memproses keputusan. Pastikan dokumen masih ada di database.';
                
            json_response(['error' => $errorMsg], 500);
        }
    }
}