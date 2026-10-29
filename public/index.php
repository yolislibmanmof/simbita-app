<?php
declare(strict_types=1);

/**
 * 1. SIMBITA - MAIN ENTRY POINT
 */

try {
    require_once dirname(__DIR__) . '/config/config.php';
    require_once dirname(__DIR__) . '/config/database.php';
    require_once dirname(__DIR__) . '/app/Core/Helpers.php';
    require_once dirname(__DIR__) . '/app/Core/Csrf.php';
    require_once dirname(__DIR__) . '/app/Core/Auth.php';
} catch (Throwable $e) {
    error_log('[Bootstrap: core] ' . $e->getMessage());
    http_response_code(500);
    exit('Terjadi kesalahan internal pada server.');
}

// =====================================================================
// 2. MUAT MODELS
// =====================================================================
try {
    require_once dirname(__DIR__) . '/app/Models/User.php';
    require_once dirname(__DIR__) . '/app/Models/AcademicWork.php';
    require_once dirname(__DIR__) . '/app/Models/Notification.php';
    require_once dirname(__DIR__) . '/app/Models/TitleProposal.php';
    require_once dirname(__DIR__) . '/app/Models/BimbinganLog.php';
    require_once dirname(__DIR__) . '/app/Models/Schedule.php';
    require_once dirname(__DIR__) . '/app/Models/Repository.php';
    require_once dirname(__DIR__) . '/app/Models/LecturerExpertise.php';
    require_once dirname(__DIR__) . '/app/Models/Analytics.php';
    require_once dirname(__DIR__) . '/app/Models/SystemSetting.php';
    require_once dirname(__DIR__) . '/app/Models/Faculty.php';
    require_once dirname(__DIR__) . '/app/Models/StudyProgram.php';
    require_once dirname(__DIR__) . '/app/Models/DefenseSession.php';
    require_once dirname(__DIR__) . '/app/Models/DirectMessage.php';
    require_once dirname(__DIR__) . '/app/Models/PlagiarismCheck.php';
} catch (Throwable $e) {
    error_log('[Bootstrap: models] ' . $e->getMessage());
    http_response_code(500);
    exit('Terjadi kesalahan internal pada server.');
}

// =====================================================================
// 3. MUAT CONTROLLERS
// =====================================================================
try {
    require_once dirname(__DIR__) . '/app/Controllers/AuthController.php';
    require_once dirname(__DIR__) . '/app/Controllers/DashboardController.php';
    require_once dirname(__DIR__) . '/app/Controllers/AdminController.php';
    require_once dirname(__DIR__) . '/app/Controllers/StudentController.php';
    require_once dirname(__DIR__) . '/app/Controllers/LecturerController.php';
    require_once dirname(__DIR__) . '/app/Controllers/DocumentController.php';
    require_once dirname(__DIR__) . '/app/Controllers/ReviewController.php';
    require_once dirname(__DIR__) . '/app/Controllers/CompareController.php';
    require_once dirname(__DIR__) . '/app/Controllers/MilestoneController.php';
    require_once dirname(__DIR__) . '/app/Controllers/NotificationController.php';
    require_once dirname(__DIR__) . '/app/Controllers/ProposalController.php';
    require_once dirname(__DIR__) . '/app/Controllers/StudentWorkController.php';
    require_once dirname(__DIR__) . '/app/Controllers/LecturerWorkController.php';
    require_once dirname(__DIR__) . '/app/Controllers/RepositoryController.php';
    require_once dirname(__DIR__) . '/app/Controllers/RecommendController.php';
    require_once dirname(__DIR__) . '/app/Controllers/ReportController.php';
    require_once dirname(__DIR__) . '/app/Controllers/SettingController.php';
    require_once dirname(__DIR__) . '/app/Controllers/MasterController.php';
    require_once dirname(__DIR__) . '/app/Controllers/DefenseController.php';
    require_once dirname(__DIR__) . '/app/Controllers/PlagiarismController.php';
    require_once dirname(__DIR__) . '/app/Controllers/MessageController.php';
} catch (Throwable $e) {
    error_log('[Bootstrap: models] ' . $e->getMessage());
    http_response_code(500);
    exit('Terjadi kesalahan internal pada server.');
}

// =====================================================================
// 4. INISIALISASI SISTEM
// =====================================================================
try {
    Auth::start();
} catch (Throwable $e) {
    error_log('[Auth::start] ' . $e->getMessage());
    http_response_code(500);
    exit('Terjadi kesalahan internal pada server.');
}

// =====================================================================
// 5. ROUTING ENGINE
// =====================================================================
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

$basePath = parse_url(BASE_URL, PHP_URL_PATH);
if ($basePath && $basePath !== '/' && $basePath !== false) {
    $path = preg_replace('#^' . preg_quote($basePath, '#') . '#', '', $path);
}

$path = $path ?: '/';
$path = rtrim($path, '/');
if ($path === '') $path = '/';

$method = $_SERVER['REQUEST_METHOD'];
$isAjax = is_ajax();

// =====================================================================
// 6. MIDDLEWARE HELPER
// =====================================================================
function requireAuth(string $fallback = '/login'): void
{
    if (!Auth::check()) {
        $_SESSION['intended_url'] = current_url();
        flash_set('warning', 'Silakan login terlebih dahulu untuk melanjutkan.');
        redirect($fallback);
    }
}

function requireRole(string ...$roles): void
{
    requireAuth();
    if (!in_array(Auth::role(), $roles, true)) {
        flash_set('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        $role = Auth::role();
        $fallback = match ($role) {
            'admin'    => '/admin/dashboard',
            'lecturer' => '/lecturer/dashboard',
            'student'  => '/student/dashboard',
            default    => '/',
        };
        redirect($fallback);
    }
}

// =====================================================================
// 7. ROUTE MATCHING
// =====================================================================
try {
    match (true) {
        // =================================================================
        // PUBLIC WEBSITE
        // =================================================================
        $path === '/' && $method === 'GET' 
            => require dirname(__DIR__) . '/app/Views/public/landing.php',
        
        // =================================================================
        // AUTH ROUTES (Guest Only)
        // =================================================================
        $path === '/login' && $method === 'GET' 
            => AuthController::showLogin(),
        
        $path === '/login' && $method === 'POST' 
            => AuthController::login(),
        
        $path === '/logout' 
            => AuthController::logout(),
        
        $path === '/change-password' && $method === 'GET' 
            => (function() {
                if (!Auth::check()) {
                    $_SESSION['flash_error'] = 'Silakan login terlebih dahulu.';
                    header('Location: ' . BASE_URL . '/login');
                    exit;
                }
                AuthController::showChangePassword();
            })(),
        
        $path === '/change-password' && $method === 'POST' 
            => (function() {
                if (!Auth::check()) {
                    $_SESSION['flash_error'] = 'Silakan login terlebih dahulu.';
                    header('Location: ' . BASE_URL . '/login');
                    exit;
                }
                AuthController::changePassword();
            })(),

        $path === '/forgot-password' && $method === 'GET' 
            => AuthController::showForgotPassword(),
        
        $path === '/forgot-password' && $method === 'POST' 
            => AuthController::processForgotPassword(),
        
        // =================================================================
        // ADMIN ROUTES
        // =================================================================
        $path === '/admin/dashboard' && $method === 'GET' 
            => (function() { requireRole('admin'); DashboardController::admin(); })(),
        
        $path === '/admin/students' && $method === 'GET' 
            => (function() { requireRole('admin'); AdminController::students(); })(),
        
        $path === '/admin/students/create' && $method === 'GET' 
            => (function() { requireRole('admin'); AdminController::createStudent(); })(),
        
        $path === '/admin/students/create' && $method === 'POST' 
            => (function() { requireRole('admin'); AdminController::storeStudent(); })(),
        
        $path === '/admin/students/edit' && $method === 'GET' 
            => (function() { requireRole('admin'); AdminController::editStudent(); })(),
        
        $path === '/admin/students/edit' && $method === 'POST' 
            => (function() { requireRole('admin'); AdminController::updateStudent(); })(),
        
        $path === '/admin/students/delete' && $method === 'POST' 
            => (function() { requireRole('admin'); AdminController::deleteStudent(); })(),
        
        $path === '/admin/lecturers' && $method === 'GET' 
            => (function() { requireRole('admin'); AdminController::lecturers(); })(),
        
        $path === '/admin/lecturers/create' && $method === 'GET' 
            => (function() { requireRole('admin'); AdminController::createLecturer(); })(),
        
        $path === '/admin/lecturers/create' && $method === 'POST' 
            => (function() { requireRole('admin'); AdminController::storeLecturer(); })(),
        
        $path === '/admin/lecturers/edit' && $method === 'GET' 
            => (function() { requireRole('admin'); AdminController::editLecturer(); })(),
        
        $path === '/admin/lecturers/edit' && $method === 'POST' 
            => (function() { requireRole('admin'); AdminController::updateLecturer(); })(),
        
        $path === '/admin/lecturers/delete' && $method === 'POST' 
            => (function() { requireRole('admin'); AdminController::deleteLecturer(); })(),
        
        $path === '/admin/works' && $method === 'GET' 
            => (function() { requireRole('admin'); AdminController::works(); })(),
        
        $path === '/admin/works/create' && $method === 'GET' 
            => (function() { requireRole('admin'); AdminController::createWork(); })(),
        
        $path === '/admin/works/create' && $method === 'POST' 
            => (function() { requireRole('admin'); AdminController::storeWork(); })(),
        
        $path === '/admin/works/edit' && $method === 'GET' 
            => (function() { requireRole('admin'); AdminController::editWork(); })(),
        
        $path === '/admin/works/edit' && $method === 'POST' 
            => (function() { requireRole('admin'); AdminController::updateWork(); })(),
        
        $path === '/admin/works/delete' && $method === 'POST' 
            => (function() { requireRole('admin'); AdminController::deleteWork(); })(),
        
        $path === '/admin/work' && $method === 'GET' 
            => (function() { requireRole('admin'); AdminController::showWork(); })(),
        
        $path === '/admin/logs' && $method === 'GET' 
            => (function() { requireRole('admin'); AdminController::logs(); })(),
        
        $path === '/admin/logs/export' && $method === 'GET' 
            => (function() { requireRole('admin'); AdminController::exportLogs(); })(),

        $path === '/admin/settings' && $method === 'GET' 
            => (function() { requireRole('admin'); SettingController::index(); })(),
            
        $path === '/admin/settings' && $method === 'POST' 
            => (function() { requireRole('admin'); SettingController::update(); })(),

        // =================================================================
        // STUDENT ROUTES
        // =================================================================
        $path === '/student/dashboard' && $method === 'GET' 
            => (function() { requireRole('student'); DashboardController::student(); })(),
        
        $path === '/student/upload' && $method === 'GET' 
            => (function() { requireRole('student'); StudentController::uploadForm(); })(),
        
        $path === '/student/upload' && $method === 'POST' 
            => (function() { requireRole('student'); StudentController::upload(); })(),
        
        $path === '/student/history' && $method === 'GET' 
            => (function() { requireRole('student'); StudentController::history(); })(),
        
        $path === '/student/version-compare' && $method === 'GET' 
            => (function() { requireRole('student'); CompareController::showVersionCompare(); })(),

        $path === '/student/proposals' && $method === 'GET' 
            => (function() { requireRole('student'); ProposalController::studentIndex(); })(),
        
        $path === '/student/proposals/submit' && $method === 'POST' 
            => (function() { requireRole('student'); ProposalController::studentSubmit(); })(),
        
        $path === '/student/proposals/respond' && $method === 'POST' 
            => (function() { requireRole('student'); ProposalController::studentRespond(); })(),    
        
        $path === '/student/work' && $method === 'GET' 
            => (function() { requireRole('student'); StudentWorkController::show(); })(),
        $path === '/student/bimbingan/store' && $method === 'POST' 
            => (function() { requireRole('student'); StudentWorkController::storeBimbingan(); })(),
        $path === '/student/schedule/book' && $method === 'POST' 
            => (function() { requireRole('student'); StudentWorkController::bookSlot(); })(),
        $path === '/student/schedule/cancel' && $method === 'POST' 
            => (function() { requireRole('student'); StudentWorkController::cancelBooking(); })(),

        // =================================================================
        // LECTURER ROUTES
        // =================================================================
        $path === '/lecturer/dashboard' && $method === 'GET' 
            => (function() { requireRole('lecturer'); DashboardController::lecturer(); })(),
        
        $path === '/lecturer/students' && $method === 'GET' 
            => (function() { requireRole('lecturer'); LecturerController::students(); })(),
        
        $path === '/lecturer/review' && $method === 'GET' 
            => (function() { requireRole('lecturer'); LecturerController::review(); })(),
        
        $path === '/lecturer/reviews' && $method === 'GET' 
            => (function() { requireRole('lecturer'); LecturerController::reviews(); })(),
        
        $path === '/lecturer/history' && $method === 'GET' 
            => (function() { requireRole('lecturer'); LecturerController::history(); })(),
        
        $path === '/lecturer/proposals' && $method === 'GET' 
            => (function() { requireRole('lecturer'); ProposalController::lecturerIndex(); })(),
        
        $path === '/lecturer/proposals/submit' && $method === 'POST' 
            => (function() { requireRole('lecturer'); ProposalController::lecturerSubmit(); })(),
        
        $path === '/lecturer/proposals/respond' && $method === 'POST' 
            => (function() { requireRole('lecturer'); ProposalController::lecturerRespond(); })(),

        $path === '/lecturer/work' && $method === 'GET' 
            => (function() { requireRole('lecturer'); LecturerWorkController::show(); })(),
        $path === '/lecturer/bimbingan/store' && $method === 'POST' 
            => (function() { requireRole('lecturer'); LecturerWorkController::storeBimbingan(); })(),
        $path === '/lecturer/schedule/slot' && $method === 'POST' 
            => (function() { requireRole('lecturer'); LecturerWorkController::createSlot(); })(),
        $path === '/lecturer/schedule/respond' && $method === 'POST' 
            => (function() { requireRole('lecturer'); LecturerWorkController::respondBooking(); })(),

        // =================================================================
        // REPOSITORY & RECOMMENDATION & REPORT
        // =================================================================
        $path === '/repository' && $method === 'GET' 
            => (function() { requireAuth(); RepositoryController::index(); })(),
        $path === '/repository/download' && $method === 'GET' 
            => (function() { requireAuth(); RepositoryController::download(); })(),
        $path === '/repository/publish' && $method === 'POST' 
            => (function() { requireRole('lecturer', 'admin'); RepositoryController::publish(); })(),
        $path === '/student/recommend' && $method === 'GET' 
            => (function() { requireRole('student'); RecommendController::show(); })(),
        $path === '/lecturer/work/export' && $method === 'GET' 
            => (function() { requireRole('lecturer', 'student', 'admin'); ReportController::exportWork(); })(),

        // =================================================================
        // DOCUMENT API
        // =================================================================
        $path === '/api/documents/upload' && $method === 'POST' 
            => (function() { requireAuth(); DocumentController::upload(); })(),
        
        $path === '/documents/download' && in_array($method, ['GET', 'HEAD'], true) 
           => (function() { requireAuth(); DocumentController::download(); })(),

        // =================================================================
        // REVIEW & COMMENTS API
        // =================================================================
        $path === '/api/comments' && $method === 'GET' 
            => (function() { requireAuth(); ReviewController::getComments(); })(),

        $path === '/api/comments/save' && $method === 'POST' 
            => (function() { requireRole('lecturer', 'admin'); ReviewController::saveAnnotation(); })(),

        $path === '/api/comments/resolve' && $method === 'POST' 
            => (function() { requireAuth(); ReviewController::resolveComment(); })(),

        $path === '/api/reviews/decide' && $method === 'POST' 
            => (function() { requireRole('lecturer', 'admin'); ReviewController::decide(); })(),
        
        // =================================================================
        // MILESTONE API
        // =================================================================
        $path === '/api/milestones/scan' && $method === 'POST' 
            => (function() { requireRole('admin'); MilestoneController::scanDeadlines(); })(),

        // =================================================================
        // NOTIFICATIONS
        // =================================================================
        $path === '/notifications' && $method === 'GET' 
            => (function() { requireAuth(); NotificationController::showNotifications(); })(),
        
        $path === '/api/notifications/count' && $method === 'GET' 
            => (function() { requireAuth(); NotificationController::getUnreadCount(); })(),
        
        $path === '/api/notifications/read-all' && $method === 'POST' 
            => (function() { requireAuth(); NotificationController::markAllRead(); })(),
        
        $path === '/api/notifications/read' && $method === 'POST' 
            => (function() { requireAuth(); NotificationController::markOneRead(); })(),
        
                // =================================================================
        // MASTER DATA (FAKULTAS & PRODI)
        // =================================================================
        $path === '/admin/faculties' && $method === 'GET'
            => (function() { requireRole('admin'); MasterController::faculties(); })(),
        $path === '/admin/faculties/store' && $method === 'POST'
            => (function() { requireRole('admin'); MasterController::storeFaculty(); })(),
        $path === '/admin/faculties/update' && $method === 'POST'
            => (function() { requireRole('admin'); MasterController::updateFaculty(); })(),
        $path === '/admin/faculties/delete' && $method === 'POST'
            => (function() { requireRole('admin'); MasterController::deleteFaculty(); })(),
        $path === '/admin/study-programs' && $method === 'GET'
            => (function() { requireRole('admin'); MasterController::studyPrograms(); })(),
        $path === '/admin/study-programs/store' && $method === 'POST'
            => (function() { requireRole('admin'); MasterController::storeProdi(); })(),
        $path === '/admin/study-programs/update' && $method === 'POST'
            => (function() { requireRole('admin'); MasterController::updateProdi(); })(),
        $path === '/admin/study-programs/delete' && $method === 'POST'
            => (function() { requireRole('admin'); MasterController::deleteProdi(); })(),

        // =================================================================
        // SIDANG & PENILAIAN
        // =================================================================
        $path === '/admin/defense' && $method === 'GET'
            => (function() { requireRole('admin'); DefenseController::index(); })(),
        $path === '/admin/defense/store' && $method === 'POST'
            => (function() { requireRole('admin'); DefenseController::store(); })(),
        $path === '/admin/defense/complete' && $method === 'POST'
            => (function() { requireRole('admin'); DefenseController::complete(); })(),
        $path === '/lecturer/grading' && $method === 'GET'
            => (function() { requireRole('lecturer'); DefenseController::grading(); })(),
        $path === '/lecturer/grading/save' && $method === 'POST'
            => (function() { requireRole('lecturer'); DefenseController::saveGrade(); })(),

        // =================================================================
        // CEK PLAGIASI
        // =================================================================
        $path === '/admin/plagiarism' && $method === 'GET'
            => (function() { requireRole('admin'); PlagiarismController::index(); })(),
        $path === '/admin/plagiarism/store' && $method === 'POST'
            => (function() { requireRole('admin'); PlagiarismController::store(); })(),

        // =================================================================
        // CHAT BIMBINGAN
        // =================================================================
        $path === '/student/messages' && $method === 'GET'
            => (function() { requireRole('student'); MessageController::show(); })(),
        $path === '/lecturer/messages' && $method === 'GET'
            => (function() { requireRole('lecturer'); MessageController::show(); })(),
        $path === '/messages/send' && $method === 'POST'
            => (function() { requireRole('student', 'lecturer'); MessageController::send(); })(),
        $path === '/api/messages' && $method === 'GET'
            => (function() { requireRole('student', 'lecturer'); MessageController::api(); })(),

        // =================================================================
        // 404 - NOT FOUND
        // =================================================================
        default => throw new Exception('Halaman tidak ditemukan', 404),
    };

} catch (Throwable $e) {
    error_log("[{$method} {$path}] " . $e->getMessage());
    
    if ($e->getCode() === 404) {
        http_response_code(404);
        if ($isAjax) {
            json_response(['error' => 'Not Found', 'path' => $path], 404);
            exit;
        }
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>404 - Halaman Tidak Ditemukan | <?= e(APP_NAME) ?></title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: white;
                }
                .container { text-align: center; padding: 2rem; max-width: 500px; }
                h1 { font-size: 6rem; font-weight: 800; margin-bottom: 1rem; text-shadow: 0 4px 20px rgba(0,0,0,0.3); }
                h2 { font-size: 1.5rem; margin-bottom: 1rem; opacity: 0.9; }
                p { font-size: 1rem; margin-bottom: 2rem; opacity: 0.8; line-height: 1.6; }
                .btn {
                    display: inline-block; padding: 0.875rem 2rem; background: white; color: #667eea;
                    text-decoration: none; border-radius: 0.75rem; font-weight: 700; transition: all 0.3s;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                }
                .btn:hover { transform: translateY(-2px); box-shadow: 0 15px 40px rgba(0,0,0,0.3); }
                .path {
                    margin-top: 2rem; padding: 1rem; background: rgba(255,255,255,0.1);
                    border-radius: 0.5rem; font-family: 'Courier New', monospace; font-size: 0.875rem;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>404</h1>
                <h2>Halaman Tidak Ditemukan</h2>
                <p>Maaf, halaman yang Anda cari tidak ada atau telah dipindahkan.</p>
                <a href="<?= e(BASE_URL) ?>/" class="btn">← Kembali ke Beranda</a>
                <div class="path"><strong>Path:</strong> <?= e($path) ?></div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
    
    http_response_code(500);
    if ($isAjax) {
        json_response([
            'error' => 'Internal Server Error',
            'message' => (defined('APP_DEBUG') && APP_DEBUG) ? $e->getMessage() : 'Terjadi kesalahan pada server',
        ], 500);
        exit;
    }
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>500 - Server Error | <?= e(APP_NAME) ?></title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
                background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
                min-height: 100vh; display: flex; align-items: center; justify-content: center;
                color: white; padding: 2rem;
            }
            .container { text-align: center; max-width: 600px; }
            h1 { font-size: 5rem; font-weight: 800; margin-bottom: 1rem; }
            h2 { font-size: 1.5rem; margin-bottom: 1rem; opacity: 0.9; }
            p { margin-bottom: 2rem; opacity: 0.8; line-height: 1.6; }
            .error-box {
                background: rgba(0,0,0,0.3); padding: 1.5rem; border-radius: 0.75rem;
                text-align: left; margin-bottom: 2rem; overflow-x: auto;
            }
            .error-box pre {
                font-family: 'Courier New', monospace; font-size: 0.8rem;
                line-height: 1.5; white-space: pre-wrap; word-break: break-word;
            }
            .btn {
                display: inline-block; padding: 0.875rem 2rem; background: white; color: #ef4444;
                text-decoration: none; border-radius: 0.75rem; font-weight: 700; transition: all 0.3s; margin: 0 0.5rem;
            }
            .btn:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>500</h1>
            <h2>⚠️ Internal Server Error</h2>
            <p>Terjadi kesalahan pada server. Silakan coba lagi beberapa saat.</p>
            <?php if (defined('APP_DEBUG') && APP_DEBUG): ?>
            <div class="error-box">
                <pre><strong>Error:</strong> <?= e($e->getMessage()) ?>

<strong>File:</strong> <?= e($e->getFile()) ?>:<?= $e->getLine() ?>

<strong>Trace:</strong>
<?= e($e->getTraceAsString()) ?></pre>
            </div>
            <?php endif; ?>
            <a href="<?= e(BASE_URL) ?>/" class="btn">← Beranda</a
            <a href="javascript:history.back()" class="btn">← Kembali</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}