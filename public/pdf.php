<?php
declare(strict_types=1);

/**
 * SIMBITA - PDF Viewer Endpoint
 * Endpoint khusus untuk PDF.js viewer dengan otentikasi
 * Route: GET /pdf.php?id=X
 */

try {
    require_once dirname(__DIR__) . '/config/config.php';
    require_once dirname(__DIR__) . '/config/database.php';
    require_once dirname(__DIR__) . '/app/Core/Helpers.php';
    require_once dirname(__DIR__) . '/app/Core/Csrf.php';
    require_once dirname(__DIR__) . '/app/Core/Auth.php';
    require_once dirname(__DIR__) . '/app/Models/User.php';
    require_once dirname(__DIR__) . '/app/Models/AcademicWork.php';
    
    Auth::start();
    
    // Verifikasi login
    if (!Auth::check()) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid ID']);
        exit;
    }
    
    $db = Database::connection();
    $userId = Auth::id();
    $userRole = Auth::role();
    
    // Ambil data document version
    $stmt = $db->prepare('
        SELECT dv.*, d.academic_work_id
        FROM document_versions dv
        JOIN documents d ON dv.document_id = d.id
        WHERE dv.id = ?
    ');
    $stmt->execute([$id]);
    $doc = $stmt->fetch();
    
    if (!$doc) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Document not found']);
        exit;
    }
    
    // Verifikasi akses
    $canAccess = false;
    
    if ($userRole === 'admin') {
        $canAccess = true;
    } elseif ($userRole === 'lecturer') {
        // Cek apakah dosen adalah pembimbing
        $stmt = $db->prepare('
            SELECT 1 FROM supervision_assignments sa
            JOIN lecturer_profiles lp ON sa.lecturer_id = lp.id
            WHERE sa.academic_work_id = ? AND lp.user_id = ? AND sa.is_active = 1
        ');
        $stmt->execute([$doc['academic_work_id'], $userId]);
        $canAccess = (bool)$stmt->fetch();
        
        // Cek juga jika dosen adalah reviewer
        if (!$canAccess) {
            $stmt = $db->prepare('
                SELECT 1 FROM reviews r
                WHERE r.document_version_id = ? AND r.reviewer_id = ?
            ');
            $stmt->execute([$id, $userId]);
            $canAccess = (bool)$stmt->fetch();
        }
    } elseif ($userRole === 'student') {
        // Mahasiswa hanya bisa akses dokumen sendiri
        $stmt = $db->prepare('
            SELECT 1 FROM academic_works aw
            JOIN student_profiles sp ON aw.student_id = sp.id
            WHERE aw.id = ? AND sp.user_id = ?
        ');
        $stmt->execute([$doc['academic_work_id'], $userId]);
        $canAccess = (bool)$stmt->fetch();
    }
    
    if (!$canAccess) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Access denied']);
        exit;
    }
    
    // Resolve file path
    $filePath = null;
    $candidates = [];
    
    if (!empty($doc['file_path'])) {
        $candidates[] = $doc['file_path'];
    }
    if (!empty($doc['stored_filename'])) {
        $candidates[] = dirname(__DIR__) . '/storage/documents/' . $doc['stored_filename'];
    }
    
    foreach ($candidates as $candidate) {
        if ($candidate && file_exists($candidate) && is_readable($candidate)) {
            $filePath = $candidate;
            break;
        }
    }
    
    if (!$filePath) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'File not found on server']);
        exit;
    }
    
    // Validasi file
    $fileSize = filesize($filePath);
    if ($fileSize < 100) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'File corrupted or empty']);
        exit;
    }
    
    // Streaming PDF dengan header yang benar untuk PDF.js
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    header('Content-Type: application/pdf');
    header('Content-Length: ' . $fileSize);
    header('Cache-Control: private, max-age=3600');
    header('Accept-Ranges: bytes');
    
    // Support range requests untuk PDF.js
    if (isset($_SERVER['HTTP_RANGE'])) {
        $range = $_SERVER['HTTP_RANGE'];
        if (preg_match('/bytes=(\d+)-(\d*)/', $range, $matches)) {
            $start = (int)$matches[1];
            $end = $matches[2] !== '' ? (int)$matches[2] : $fileSize - 1;
            $length = $end - $start + 1;
            
            header('HTTP/1.1 206 Partial Content');
            header("Content-Range: bytes $start-$end/$fileSize");
            header('Content-Length: ' . $length);
            
            $handle = fopen($filePath, 'rb');
            fseek($handle, $start);
            echo fread($handle, $length);
            fclose($handle);
            exit;
        }
    }
    
    // Full file streaming
    if ($fileSize > 1048576) {
        $handle = fopen($filePath, 'rb');
        if ($handle) {
            while (!feof($handle)) {
                echo fread($handle, 8192);
                flush();
            }
            fclose($handle);
        }
    } else {
        readfile($filePath);
    }
    
    exit;
    
} catch (Throwable $e) {
    error_log('[pdf.php] ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Internal server error']);
    exit;
}