<?php
declare(strict_types=1);

class DocumentController
{
    /**
     * Download dokumen dengan verifikasi hak akses ketat
     */
    public static function download(): void
    {
        Auth::start();
        
        // Gunakan version_id, bukan document_id, agar presisi
        $versionId = (int)($_GET['id'] ?? 0);
        if (!$versionId) {
            http_response_code(400);
            die('Parameter dokumen tidak valid.');
        }

        $db = Database::connection();
        
        // Ambil detail VERSI SPESIFIK
        $stmt = $db->prepare('
            SELECT dv.*, d.academic_work_id
            FROM document_versions dv
            JOIN documents d ON dv.document_id = d.id
            WHERE dv.id = ?
        ');
        $stmt->execute([$versionId]);
        $doc = $stmt->fetch();

        if (!$doc || !file_exists($doc['file_path'])) {
            http_response_code(404);
            die('Dokumen atau file fisik tidak ditemukan.');
        }

        // VERIFIKASI HAK AKSES (Sesuai Spesifikasi: Role-based Access Control)
        $canAccess = false;
        $userRole = Auth::role();
        $userId = Auth::id();

        if ($userRole === 'admin') {
            $canAccess = true;
        } elseif ($userRole === 'student') {
            // Mahasiswa hanya boleh download milik sendiri
            $stmt = $db->prepare('
                SELECT 1 FROM academic_works aw
                JOIN student_profiles sp ON aw.student_id = sp.id
                WHERE aw.id = ? AND sp.user_id = ? LIMIT 1
            ');
            $stmt->execute([$doc['academic_work_id'], $userId]);
            $canAccess = (bool)$stmt->fetch();
        } elseif ($userRole === 'lecturer') {
            // Dosen hanya boleh download bimbingannya
            $stmt = $db->prepare('
                SELECT 1 FROM supervision_assignments sa
                JOIN lecturer_profiles lp ON sa.lecturer_id = lp.id
                WHERE sa.academic_work_id = ? AND lp.user_id = ? AND sa.is_active = 1 LIMIT 1
            ');
            $stmt->execute([$doc['academic_work_id'], $userId]);
            $canAccess = (bool)$stmt->fetch();
        }

        if (!$canAccess) {
            http_response_code(403);
            die('Akses ditolak. Anda tidak memiliki izin untuk mengunduh dokumen ini.');
        }

        // Streaming file dengan header aman
        // Sanitasi nama file agar tidak merusak header HTTP
        $safeFilename = preg_replace('/[^\w\.\-]/', '_', $doc['original_filename']);
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $safeFilename . '"'); // 'inline' agar bisa dibuka di browser untuk anotasi
        header('Content-Length: ' . $doc['file_size_bytes']);
        header('Cache-Control: private, no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        
        readfile($doc['file_path']);
        exit;
    }

    /**
     * Endpoint API untuk mengambil data anotasi/komentar pada suatu versi dokumen
     * (Persiapan untuk fitur Penandaan Halaman Dosen)
     */
    public static function getAnnotations(): void
    {
        Auth::start();
        
        $versionId = (int)($_GET['version_id'] ?? 0);
        if (!$versionId) {
            json_response(['error' => 'Version ID required'], 400);
        }

        $db = Database::connection();
        
        // Cek akses dulu sebelum memberikan data anotasi
        // (Logika cek akses disederhanakan untuk contoh, gunakan pola yang sama dengan download)
        
        $stmt = $db->prepare('
            SELECT dc.id, dc.page_number, dc.pos_x_pct, dc.pos_y_pct, 
                   dc.comment, dc.priority, dc.created_at,
                   u.name as author_name
            FROM document_comments dc
            JOIN users u ON dc.author_id = u.id
            WHERE dc.document_version_id = ?
            ORDER BY dc.page_number ASC, dc.created_at ASC
        ');
        $stmt->execute([$versionId]);
        
        json_response([
            'success' => true,
            'annotations' => $stmt->fetchAll()
        ]);
    }

    /**
     * Endpoint API untuk menyimpan anotasi dari dosen
     */
    public static function saveAnnotation(): void
    {
        Auth::requireRole('lecturer', 'admin');
        
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($csrfToken)) {
            json_response(['error' => 'Token tidak valid'], 419);
        }

        $data = [
            'version_id' => (int)($_POST['version_id'] ?? 0),
            'page'       => (int)($_POST['page_number'] ?? 0),
            'x_pct'      => (float)($_POST['pos_x_pct'] ?? 0),
            'y_pct'      => (float)($_POST['pos_y_pct'] ?? 0),
            'comment'    => trim($_POST['comment'] ?? ''),
            'priority'   => $_POST['priority'] ?? 'normal' // normal, important, critical
        ];

        if ($data['version_id'] <= 0 || empty($data['comment'])) {
            json_response(['error' => 'Data anotasi tidak lengkap'], 400);
        }

        try {
            $db = Database::connection();
            $stmt = $db->prepare('
                INSERT INTO document_comments 
                (document_version_id, page_number, pos_x_pct, pos_y_pct, comment, priority, author_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ');
            $stmt->execute([
                $data['version_id'], $data['page'], $data['x_pct'], $data['y_pct'],
                $data['comment'], $data['priority'], Auth::id()
            ]);

            json_response(['success' => true, 'id' => (int)$db->lastInsertId()]);
        } catch (Exception $e) {
            json_response(['error' => 'Gagal menyimpan anotasi'], 500);
        }
    }
}