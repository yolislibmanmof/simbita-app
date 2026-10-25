<?php
declare(strict_types=1);

require dirname(__DIR__) . '/config/config.php';
require dirname(__DIR__) . '/config/database.php';
require dirname(__DIR__) . '/app/Core/Helpers.php';
require dirname(__DIR__) . '/app/Core/Auth.php';
require dirname(__DIR__) . '/app/Core/Csrf.php';
require dirname(__DIR__) . '/app/Models/User.php';
require dirname(__DIR__) . '/app/Models/AcademicWork.php';
require dirname(__DIR__) . '/app/Controllers/AuthController.php';
require dirname(__DIR__) . '/app/Controllers/DocumentController.php';
require dirname(__DIR__) . '/app/Controllers/ReviewController.php';
require dirname(__DIR__) . '/app/Controllers/CompareController.php';
require dirname(__DIR__) . '/app/Models/Notification.php';
require dirname(__DIR__) . '/app/Core/NotificationView.php';
require dirname(__DIR__) . '/app/Controllers/NotificationController.php';

Auth::start();

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/simbita-app/public', '', $path);

match ($path) {
    '/', '' => redirect('/login.php'),
    '/login.php' => AuthController::login(),
    '/logout.php' => AuthController::logout(),
    '/api/documents/upload' => DocumentController::upload(),
    '/documents/download.php' => DocumentController::download(),
    '/lecturer/review.php' => require __DIR__ . '/../app/Views/lecturer/review.php',
    '/lecturer/history.php' => require __DIR__ . '/../app/Views/lecturer/history.php',
    '/student/history.php' => require __DIR__ . '/../app/Views/student/history.php',
    '/student/version_compare.php' => require __DIR__ . '/../app/Views/student/version_compare.php',
    '/api/reviews/comment' => ReviewController::addComment(),
    '/api/reviews/checklist' => ReviewController::saveChecklist(),
    '/api/reviews/decide' => ReviewController::decide(),
    '/api/comments' => CompareController::comments(),
    '/api/comments/resolve' => CompareController::resolve(),
    '/notifications.php' => require __DIR__ . '/../app/Views/notifications.php',
    '/api/notifications/read-all' => NotificationController::markAllRead(),
    '/api/notifications/read' => NotificationController::markOneRead(),
    default => http_response_code(404),
};
