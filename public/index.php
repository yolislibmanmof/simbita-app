<?php
declare(strict_types=1);

/**
 * =====================================================================
 * SIMBITA - MAIN ENTRY POINT
 * FINAL ULTRA PREMIUM - ENTERPRISE GRADE
 * =====================================================================
 * 
 * File ini adalah pintu masuk aplikasi. Menangani:
 * 1. Loading semua file core (dengan require_once anti-redeclare)
 * 2. Inisialisasi sistem (session, auth)
 * 3. Routing request ke controller yang sesuai
 * 4. Error handling & 404 page
 */

// =====================================================================
// 0. ERROR REPORTING (Development Mode)
// =====================================================================
// Ganti ke error_reporting(0) saat production
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', dirname(__DIR__) . '/logs/php_error.log');

// Pastikan folder logs ada
if (!is_dir(dirname(__DIR__) . '/logs')) {
    @mkdir(dirname(__DIR__) . '/logs', 0755, true);
}

// =====================================================================
// 1. MUAT KONFIGURASI & CORE (require_once = anti double-load)
// =====================================================================
try {
    require_once dirname(__DIR__) . '/config/config.php';
    require_once dirname(__DIR__) . '/config/database.php';
    require_once dirname(__DIR__) . '/app/Core/Helpers.php';
    require_once dirname(__DIR__) . '/app/Core/Csrf.php';
    require_once dirname(__DIR__) . '/app/Core/Auth.php';
} catch (Throwable $e) {
    http_response_code(500);
    die('<h1>⚠️ Fatal Error: Core Files</h1><pre>' . htmlspecialchars($e->getMessage()) . '</pre>');
}

// =====================================================================
// 2. MUAT MODELS (require_once)
// =====================================================================
try {
    require_once dirname(__DIR__) . '/app/Models/User.php';
    require_once dirname(__DIR__) . '/app/Models/AcademicWork.php';
    require_once dirname(__DIR__) . '/app/Models/Notification.php';
} catch (Throwable $e) {
    http_response_code(500);
    die('<h1>⚠️ Fatal Error: Models</h1><pre>' . htmlspecialchars($e->getMessage()) . '</pre>');
}

// =====================================================================
// 3. MUAT CONTROLLERS (require_once)
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
    require_once dirname(__DIR__) . '/app/Controllers/NotificationController.php';
} catch (Throwable $e) {
    http_response_code(500);
    die('<h1>⚠️ Fatal Error: Controllers</h1><pre>' . htmlspecialchars($e->getMessage()) . '</pre>');
}

// =====================================================================
// 4. INISIALISASI SISTEM
// =====================================================================
try {
    Auth::start();
} catch (Throwable $e) {
    error_log('[Auth::start] ' . $e->getMessage());
    // Fallback: continue tanpa session (untuk halaman publik)
}

// =====================================================================
// 5. ROUTING ENGINE
// =====================================================================

// Ambil path dari URL
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

// Strip base path jika aplikasi di-subfolder (misal: /simbita-app/public)
$basePath = parse_url(BASE_URL, PHP_URL_PATH);
if ($basePath && $basePath !== '/' && $basePath !== false) {
    $path = preg_replace('#^' . preg_quote($basePath, '#') . '#', '', $path);
}

// Normalisasi path
$path = $path ?: '/';
$path = rtrim($path, '/');
if ($path === '') {
    $path = '/';
}

$method = $_SERVER['REQUEST_METHOD'];
$isAjax = is_ajax();

// =====================================================================
// 6. MIDDLEWARE HELPER
// =====================================================================

/**
 * Require auth sebelum mengakses route
 * @param string $fallback Redirect URL jika belum login
 */
function requireAuth(string $fallback = '/login'): void
{
    if (!Auth::check()) {
        $_SESSION['intended_url'] = current_url();
        flash_set('warning', 'Silakan login terlebih dahulu untuk melanjutkan.');
        redirect($fallback);
    }
}

/**
 * Require role tertentu
 * @param string ...$roles Role yang diizinkan
 */
function requireRole(string ...$roles): void
{
    requireAuth();
    if (!in_array(Auth::role(), $roles, true)) {
        flash_set('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        
        // Redirect ke dashboard sesuai role
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
            => (function() {
                Auth::requireGuest('/admin/dashboard'); // Redirect jika sudah login
                AuthController::showLogin();
            })(),
        
        $path === '/login' && $method === 'POST' 
            => AuthController::login(),
        
        $path === '/logout' 
            => AuthController::logout(),
        
        // =================================================================
        // ADMIN ROUTES (Admin Only)
        // =================================================================
        
        // Dashboard
        $path === '/admin/dashboard' && $method === 'GET' 
            => (function() {
                requireRole('admin');
                DashboardController::admin();
            })(),
        
        // Students CRUD
        $path === '/admin/students' && $method === 'GET' 
            => (function() {
                requireRole('admin');
                AdminController::students();
            })(),
        
        $path === '/admin/students/create' && $method === 'GET' 
            => (function() {
                requireRole('admin');
                AdminController::createStudent();
            })(),
        
        $path === '/admin/students/create' && $method === 'POST' 
            => (function() {
                requireRole('admin');
                AdminController::storeStudent();
            })(),
        
        $path === '/admin/students/edit' && $method === 'GET' 
            => (function() {
                requireRole('admin');
                AdminController::editStudent();
            })(),
        
        $path === '/admin/students/edit' && $method === 'POST' 
            => (function() {
                requireRole('admin');
                AdminController::updateStudent();
            })(),
        
        $path === '/admin/students/delete' && $method === 'POST' 
            => (function() {
                requireRole('admin');
                AdminController::deleteStudent();
            })(),
        
        // Lecturers CRUD
        $path === '/admin/lecturers' && $method === 'GET' 
            => (function() {
                requireRole('admin');
                AdminController::lecturers();
            })(),
        
        $path === '/admin/lecturers/create' && $method === 'GET' 
            => (function() {
                requireRole('admin');
                AdminController::createLecturer();
            })(),
        
        $path === '/admin/lecturers/create' && $method === 'POST' 
            => (function() {
                requireRole('admin');
                AdminController::storeLecturer();
            })(),
        
        $path === '/admin/lecturers/edit' && $method === 'GET' 
            => (function() {
                requireRole('admin');
                AdminController::editLecturer();
            })(),
        
        $path === '/admin/lecturers/edit' && $method === 'POST' 
            => (function() {
                requireRole('admin');
                AdminController::updateLecturer();
            })(),
        
        $path === '/admin/lecturers/delete' && $method === 'POST' 
            => (function() {
                requireRole('admin');
                AdminController::deleteLecturer();
            })(),
        
        // Works CRUD
        $path === '/admin/works' && $method === 'GET' 
            => (function() {
                requireRole('admin');
                AdminController::works();
            })(),
        
        $path === '/admin/works/create' && $method === 'GET' 
            => (function() {
                requireRole('admin');
                AdminController::createWork();
            })(),
        
        $path === '/admin/works/create' && $method === 'POST' 
            => (function() {
                requireRole('admin');
                AdminController::storeWork();
            })(),
        
        $path === '/admin/works/edit' && $method === 'GET' 
            => (function() {
                requireRole('admin');
                AdminController::editWork();
            })(),
        
        $path === '/admin/works/edit' && $method === 'POST' 
            => (function() {
                requireRole('admin');
                AdminController::updateWork();
            })(),
        
        $path === '/admin/works/delete' && $method === 'POST' 
            => (function() {
                requireRole('admin');
                AdminController::deleteWork();
            })(),
        
        $path === '/admin/work' && $method === 'GET' 
            => (function() {
                requireRole('admin');
                AdminController::showWork();
            })(),
        
        // =================================================================
        // STUDENT ROUTES (Student Only)
        // =================================================================
        $path === '/student/dashboard' && $method === 'GET' 
            => (function() {
                requireRole('student');
                DashboardController::student();
            })(),
        
        $path === '/student/upload' && $method === 'GET' 
            => (function() {
                requireRole('student');
                StudentController::uploadForm();
            })(),
        
        $path === '/student/upload' && $method === 'POST' 
            => (function() {
                requireRole('student');
                StudentController::upload();
            })(),
        
        $path === '/student/history' && $method === 'GET' 
            => (function() {
                requireRole('student');
                StudentController::history();
            })(),
        
        $path === '/student/version-compare' && $method === 'GET' 
            => (function() {
                requireRole('student');
                CompareController::showVersionCompare();
            })(),
        
        // =================================================================
        // LECTURER ROUTES (Lecturer Only)
        // =================================================================
        $path === '/lecturer/dashboard' && $method === 'GET' 
            => (function() {
                requireRole('lecturer');
                DashboardController::lecturer();
            })(),
        
        $path === '/lecturer/students' && $method === 'GET' 
            => (function() {
                requireRole('lecturer');
                LecturerController::students();
            })(),
        
        $path === '/lecturer/review' && $method === 'GET' 
            => (function() {
                requireRole('lecturer');
                LecturerController::review();
            })(),
        
        $path === '/lecturer/reviews' && $method === 'GET' 
            => (function() {
                requireRole('lecturer');
                LecturerController::reviews();
            })(),
        
        $path === '/lecturer/history' && $method === 'GET' 
            => (function() {
                requireRole('lecturer');
                LecturerController::history();
            })(),
        
        // =================================================================
        // DOCUMENT API (Auth Required)
        // =================================================================
        $path === '/api/documents/upload' && $method === 'POST' 
            => (function() {
                requireAuth();
                DocumentController::upload();
            })(),
        
        $path === '/documents/download' && $method === 'GET' 
            => (function() {
                requireAuth();
                DocumentController::download();
            })(),
        
        // =================================================================
        // REVIEW API (Lecturer Only)
        // =================================================================
        $path === '/api/reviews/comment' && $method === 'POST' 
            => (function() {
                requireRole('lecturer', 'admin');
                ReviewController::addComment();
            })(),
        
        $path === '/api/reviews/checklist' && $method === 'POST' 
            => (function() {
                requireRole('lecturer', 'admin');
                ReviewController::saveChecklist();
            })(),
        
        $path === '/api/reviews/decide' && $method === 'POST' 
            => (function() {
                requireRole('lecturer', 'admin');
                ReviewController::decide();
            })(),
        
        // =================================================================
        // COMMENTS API (Auth Required)
        // =================================================================
        $path === '/api/comments' && $method === 'GET' 
            => (function() {
                requireAuth();
                CompareController::getComments();
            })(),
        
        $path === '/api/comments/resolve' && $method === 'POST' 
            => (function() {
                requireAuth();
                CompareController::resolveComment();
            })(),
        
        // =================================================================
        // NOTIFICATIONS (Auth Required)
        // =================================================================
        $path === '/notifications' && $method === 'GET' 
            => (function() {
                requireAuth();
                NotificationController::showNotifications();
            })(),
        
        $path === '/api/notifications/read-all' && $method === 'POST' 
            => (function() {
                requireAuth();
                NotificationController::markAllRead();
            })(),
        
        $path === '/api/notifications/read' && $method === 'POST' 
            => (function() {
                requireAuth();
                NotificationController::markOneRead();
            })(),
        
        // =================================================================
        // 404 - NOT FOUND
        // =================================================================
        default => throw new Exception('Halaman tidak ditemukan', 404),
    };

} catch (Throwable $e) {
    // Log error untuk debugging
    error_log("[{$method} {$path}] " . $e->getMessage());
    
    // Handle 404
    if ($e->getCode() === 404) {
        http_response_code(404);
        
        // Untuk AJAX, return JSON
        if ($isAjax) {
            json_response(['error' => 'Not Found', 'path' => $path], 404);
            exit;
        }
        
        // Untuk web, tampilkan 404 page yang lebih informatif
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
                .container {
                    text-align: center;
                    padding: 2rem;
                    max-width: 500px;
                }
                h1 {
                    font-size: 6rem;
                    font-weight: 800;
                    margin-bottom: 1rem;
                    text-shadow: 0 4px 20px rgba(0,0,0,0.3);
                }
                h2 {
                    font-size: 1.5rem;
                    margin-bottom: 1rem;
                    opacity: 0.9;
                }
                p {
                    font-size: 1rem;
                    margin-bottom: 2rem;
                    opacity: 0.8;
                    line-height: 1.6;
                }
                .btn {
                    display: inline-block;
                    padding: 0.875rem 2rem;
                    background: white;
                    color: #667eea;
                    text-decoration: none;
                    border-radius: 0.75rem;
                    font-weight: 700;
                    transition: all 0.3s;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                }
                .btn:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 15px 40px rgba(0,0,0,0.3);
                }
                .path {
                    margin-top: 2rem;
                    padding: 1rem;
                    background: rgba(255,255,255,0.1);
                    border-radius: 0.5rem;
                    font-family: 'Courier New', monospace;
                    font-size: 0.875rem;
                    word-break: break-all;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>404</h1>
                <h2>Halaman Tidak Ditemukan</h2>
                <p>Maaf, halaman yang Anda cari tidak ada atau telah dipindahkan.</p>
                <a href="<?= e(BASE_URL) ?>/" class="btn">
                    ← Kembali ke Beranda
                </a>
                <div class="path">
                    <strong>Path:</strong> <?= e($path) ?>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
    
    // Handle 500 error
    http_response_code(500);
    
    // Untuk AJAX, return JSON
    if ($isAjax) {
        json_response([
            'error' => 'Internal Server Error',
            'message' => APP_DEBUG ? $e->getMessage() : 'Terjadi kesalahan pada server',
        ], 500);
        exit;
    }
    
    // Untuk web, tampilkan error page
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
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                padding: 2rem;
            }
            .container {
                text-align: center;
                max-width: 600px;
            }
            h1 {
                font-size: 5rem;
                font-weight: 800;
                margin-bottom: 1rem;
            }
            h2 {
                font-size: 1.5rem;
                margin-bottom: 1rem;
                opacity: 0.9;
            }
            p {
                margin-bottom: 2rem;
                opacity: 0.8;
                line-height: 1.6;
            }
            .error-box {
                background: rgba(0,0,0,0.3);
                padding: 1.5rem;
                border-radius: 0.75rem;
                text-align: left;
                margin-bottom: 2rem;
                overflow-x: auto;
            }
            .error-box pre {
                font-family: 'Courier New', monospace;
                font-size: 0.8rem;
                line-height: 1.5;
                white-space: pre-wrap;
                word-break: break-word;
            }
            .btn {
                display: inline-block;
                padding: 0.875rem 2rem;
                background: white;
                color: #ef4444;
                text-decoration: none;
                border-radius: 0.75rem;
                font-weight: 700;
                transition: all 0.3s;
                margin: 0 0.5rem;
            }
            .btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>500</h1>
            <h2>⚠️ Internal Server Error</h2>
            <p>Terjadi kesalahan pada server. Silakan coba lagi beberapa saat.</p>
            
            <?php if (APP_DEBUG): ?>
                <div class="error-box">
                    <pre><strong>Error:</strong> <?= e($e->getMessage()) ?>

<strong>File:</strong> <?= e($e->getFile()) ?>:<?= $e->getLine() ?>

<strong>Trace:</strong>
<?= e($e->getTraceAsString()) ?></pre>
                </div>
            <?php endif; ?>
            
            <a href="<?= e(BASE_URL) ?>/" class="btn">← Beranda</a>
            <a href="javascript:history.back()" class="btn">← Kembali</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}