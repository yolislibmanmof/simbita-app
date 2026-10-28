<?php
declare(strict_types=1);

class RecommendController
{
    public static function show(): void
    {
        Auth::requireRole('student');
        $title = trim($_GET['title'] ?? '');
        $abstract = trim($_GET['abstract'] ?? '');
        $results = [];

        if ($title === '') {
            $db = Database::connection();
            $stmt = $db->prepare('SELECT title, description FROM title_proposals WHERE proposer_user_id = ? ORDER BY id DESC LIMIT 1');
            $stmt->execute([Auth::id()]);
            $last = $stmt->fetch();
            if ($last) {
                $title = $last['title'];
                $abstract = $last['description'] ?? '';
            }
        }
        if ($title !== '') {
            $results = LecturerExpertise::recommend($title, $abstract, 5);
        }

        require dirname(__DIR__) . '/Views/student/recommend.php';
    }
}